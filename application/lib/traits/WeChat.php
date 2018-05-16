<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/4/10 0010 16:11
|--------------------------------------------------------------------------
|                                          微信授权以及扫码登录
|--------------------------------------------------------------------------
*/

namespace app\lib\traits;

use app\lib\exception\WXException;
use think\facade\Request;

trait WeChat
{
    protected $wxAppId;
    protected $wxAppSecret;
    protected $wxCode;

    /**
     * @param $config
     * @return array
     * @throws WXException
     * 微信扫码登陆
     */
    protected function wxScanLogin($config)
    {
       $content = $this->wxGetAccessToken($config);

       $contentInfo = $this->wxGetUserInfo($content['access_token'], $content['openid']);

      return [
          'open_id' => $content['openid'],
          'union_id' => $contentInfo['unionid'],
          'nickname' => $contentInfo['nickname'],
          'head_img_url' => $contentInfo['headimgurl']
      ];
    }

    /**
     * @param $config
     * @return $this
     * @throws WXException
     * 初始化配置信息
     */
    protected function wxInitial($config)
    {
        if(is_null($code = Request::param('code'))){
            throw new WXException(28001);
        }
        $this->wxCode = $code;

        if( !key_exists('appId', $config)){
            throw new WXException(15004);
        }
        $this->wxAppId = $config['appId'];

        if( !key_exists('appSecret', $config)){
            throw new WXException(15005);
        }
        $this->wxAppSecret = $config['appSecret'];

        return $this;
    }

    /**
     * @param $config
     * @return mixed
     * @throws WXException
     * 获取access token
     */
    protected function wxGetAccessToken($config)
    {
        $this->wxInitial($config);

        $para = [
        'appid' => $this->wxAppId,
        'secret' => $this->wxAppSecret,
        'code' => $this->wxCode,
        'grant_type' => 'authorization_code'
    ];
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?';
        $url.= http_build_query($para);

        $content = json_decode(file_get_contents($url), true);

        if( !is_array($content)){
            throw new WXException(28002);
        }

        if( key_exists('errcode', $content))
            throw new WXException([
                'code' =>$content['errcode'], 'msg' => $content['errmsg']
            ]);

        return $content;
    }

    /**
     * @param $accessToken
     * @param $openId
     * @return mixed
     * @throws WXException
     * 获取用户信息
     */
    protected function wxGetUserInfo($accessToken, $openId)
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
}