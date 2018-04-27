<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/4/10 0010 16:11
|--------------------------------------------------------------------------
|                                                 微信扫码登录
|--------------------------------------------------------------------------
*/

namespace app\lib\traits;

use app\common\service\CurrentUser;
use app\common\service\JWTAuth;
use app\lib\enum\Status;
use app\lib\exception\WXException;
use app\common\model\WXAdminModel;
use think\facade\Request;

trait ScanLogin
{
    protected $WXOpenAppId;
    protected $WXOpenAppSecret;
    protected $code;

    public function wxGetInfo()
    {
       $this->wxInitial();

       $content = $this->wxGetOpenId();

       $contentInfo = $this->wxGetUnionId($content['access_token'], $content['openid']);

      return [
          'open_id' => $content['openid'],
          'union_id' => $contentInfo['unionid'],
          'nickname' => $contentInfo['nickname'],
          'head_img_url' => $contentInfo['headimgurl']
      ];
    }

    public function wxInitial()
    {
        if(is_null($code = Request::param('code')))
            throw new WXException(28001);

        $this->WXOpenAppId = config('wx.appId');
        $this->WXOpenAppSecret = config('wx.appSecret');
        $this->code = $code;
        return $this;
    }

    public function wxGetOpenId()
    {
        $para = [
        'appid' => $this->WXOpenAppId,
        'secret' => $this->WXOpenAppSecret,
        'code' => $this->code,
        'grant_type' => 'authorization_code'
    ];
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?';
        $url.= http_build_query($para);

        $content = json_decode(file_get_contents($url), true);

        if( !is_array($content)) throw new WXException(28002);

        if( key_exists('errcode', $content))
            throw new WXException([
                'code' =>$content['errcode'], 'msg' => $content['errmsg']
            ]);

        return $content;
    }

    public function wxGetUnionId($accessToken, $openId)
    {
        $para = [
            'access_token' => $accessToken,
            'openid' => $openId
        ];

        $url = 'https://api.weixin.qq.com/sns/userinfo?' . http_build_query($para);

        $infoContent = json_decode(file_get_contents($url), true);

        if( !is_array($infoContent)) throw new WXException(28002);

        if( key_exists('errcode', $infoContent))
            throw new WXException([
                'code' =>$infoContent['errcode'], 'msg' => $infoContent['errmsg']
            ]);

        return $infoContent;
    }

    protected function wxSaveOrUpdate($userInfo)
    {
        try{
            $record = WXAdminModel::get(['union_id' => $userInfo['union_id']]);
            //如果没有就新建
            if(is_null($record)) {
                $userInfo['admin_id'] = 0;
                $record = WXAdminModel::create($userInfo);
            }
            //如果有就更新
            else{
                foreach ($userInfo as $k => $v){
                    $record->{$k} = $v;
                    $record->save();
                }
            }

            return $record;
        }catch (Exception $e){
            throw $e;
        }
    }

    protected function wxHasBind($wxRecord)
    {
        $admin = AdminModel::get(['id' => $wxRecord->admin_id, 'status' => Status::SHOW]);
        //如果用户绑定的账号现在异常，则跳到未绑定
        if(is_null($admin)) return $this->unBind($wxRecord);
        //根据admins表里的记录生成token
        $token = JWTAuth::generateToken($admin);
        //之前已经绑定的路由
        $url = config('wx.bindWebUrl');
        $para = [
            'token' =>$token,
            /*  'name' => $admin->name,
              'logo' => $wxRecord->head_img_url,*/
        ];

        return $url.'?'.http_build_query($para);
    }

    /**
     * @param $wxRecord
     * @return string
     * @throws \app\lib\exception\TokenException
     * 如果用户之前未绑定过走此逻辑
     */
    protected function wxUnBind($wxRecord)
    {
        //根据wechat_admins表里的记录生成token
        $token = JWTAuth::generateToken($wxRecord);
        $url = config('wx.unbindWebUrl');
        $para = [
            'token' =>$token,
        ];

        return $url.'?'.http_build_query($para);
    }

    public function wxLogin()
    {
        //从微信服务器拿来的用户信息
        $userInfo = $this->wxGetInfo();
        //有则更新无则新建
        $wxRecord = $this->wxSaveOrUpdate($userInfo);
        //判断有没有绑定
        if($wxRecord->admin_id == 0 || $wxRecord->status == Status::UNBIND){
            //没绑定
            return $this->wxUnBind($wxRecord);
        }
        //已绑定
        else {
            return $this->wxHasBind($wxRecord);
        }
    }
}