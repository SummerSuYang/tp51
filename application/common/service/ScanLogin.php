<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/5/16 0016 10:06
|--------------------------------------------------------------------------
|                                                      说明
|--------------------------------------------------------------------------
*/

namespace app\common\service;

use app\common\model\Admin;
use app\lib\exception\ScanLoginException;
use app\lib\traits\WeChat;
use think\Exception;
use app\lib\enum\Status;

class ScanLogin
{
    //有关微信登录的相关功能
    use WeChat;

    protected $type = 'wx';
    protected $scene = 'default';
    protected $config = [];

    public function __construct($type = '', $scene = '')
    {
        if( !empty($type)){
            $this->type = $type;
        }

        if( !empty($scene)){
            $this->scene = $scene;
        }

        $this->setConfig();
    }

    /**
     * @return string
     * @throws Exception
     * 扫码登陆
     */
    public function scanLogin()
    {
        //从第三方服务器拿来的用户信息
        if( !method_exists($this, $method = $this->type.'ScanLogin')){
            throw new ScanLoginException(18003);
        }

        //调用具体的第三方登录的方法获取用户数据
        $userInfo = call_user_func_array([$this, $method], $this->config);

        //有则更新无则新建
        $wxRecord = $this->saveOrUpdate($userInfo);

        //判断有没有绑定
        if($wxRecord->admin_id == 0 || $wxRecord->status == Status::UNBIND){
            //没绑定
            return $this->unBind($wxRecord);
        }
        //已绑定
        else {
            return $this->hasBind($wxRecord);
        }
    }
    /**
     * 新建或更新第三方用户数据
     */
    protected function SaveOrUpdate($userInfo)
    {
        try{
            $class = $this->config['model'];
            $model = new $class;
            $record = $model->get(['union_id' => $userInfo['union_id']]);

            //如果没有就新建
            if(is_null($record)) {
                $userInfo['admin_id'] = 0;
                $record = $model->create($userInfo);
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

    /**
     * @param $scene
     * @return $this
     * 设置应用场景
     */
    protected function setScene($scene)
    {
        $this->scene = $scene;
        return $this;
    }

    /**
     * @param $type
     * @return $this
     * 设置扫码登录方式，比如微信或钉钉
     */
    protected function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return $this
     * @throws ScanLoginException
     * 加载配置信息
     */
    protected function setConfig()
    {
        if( !key_exists($this->type, config())){
            throw new ScanLoginException(18001);
        }
        $config = config($this->type);

        if( !key_exists($this->scene, $config[$this->scene])){
            throw new ScanLoginException(18002);
        }
        $this->config = $config[$this->scene];

        if( !key_exists('model', $this->config)){
            throw new ScanLoginException(18004);
        }

        return $this;
    }


    /**
     * @param $wxRecord
     * @return string
     * @throws \think\exception\DbException
     * 用户已经绑定的路由
     */
    protected function hasBind($wxRecord)
    {
        $admin = Admin::get(['id' => $wxRecord->admin_id, 'status' => Status::NORMAL]);
        //如果用户绑定的账号现在异常，则跳到未绑定
        if(is_null($admin)) {
            return $this->unBind($wxRecord);
        }
        //根据admins表里的记录生成token
        $token = JWTAuth::generateToken($admin);
        //之前已经绑定的路由
        $url = $this->config['bindUrl'];
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
     * 用户为绑定跳转的路由
     */
    protected function unBind($wxRecord)
    {
        //根据第三方用户表里的记录生成token
        $token = JWTAuth::generateToken($wxRecord);
        $url = $this->config['unbindUrl'];
        $para = [
            'token' =>$token,
        ];

        return $url.'?'.http_build_query($para);
    }
}