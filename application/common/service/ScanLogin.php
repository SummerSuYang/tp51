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
use think\Exception;
use app\lib\enum\Status;
use think\facade\Config;

abstract class ScanLogin
{
	//应用场景
    protected static $scene = 'default';
    //配置信息
    protected static $config = [];
    //第三方用户表的model
    protected $model;
    //第三方用户信息中能唯一辨别用户的字段
    protected $identifier;

    abstract protected function getUserInfo();
	/**
	 * @param string $scene
	 * @return mixed
	 * @throws ScanLoginException
	 * 返回具体处理逻辑的对象
	 */
    public static function createHandler($scene = '')
    {
    	if(!empty($scene)){
    		self::$scene = $scene;
	    }

	    self::$config = Config::get('scan.'.self::$scene);

    	if(key_exists('appId', self::$config) ||
	        key_exists('appSecret', self::$config) ||
	        key_exists('bindWebUrl', self::$config) ||
	        key_exists('unbindWebUrl', self::$config) ||
	        key_exists('type', self::$config))
    		throw  new ScanLoginException(18001);

    	$class = "\\app\\lib\\scan\\".ucfirst(self::$config['type'])."ScanLogin";
    	if( !class_exists($class)){
    		throw new ScanLoginException(18002);
	    }

	    return new $class;
    }

	/**
	 * @param string $scene
	 * @return mixed
	 * @throws ScanLoginException
	 * 开始处理
	 */
    public static function handle($scene = '')
    {
    	$handler = self::createHandler($scene);

    	//从第三方服务器（微信或钉钉）拉取用户信息
    	$userInfo = $handler->getUserInfo();

    	$record = $handler->saveOrUpdate($userInfo);

	    //判断有没有绑定
	    if($record->admin_id == Status::ORIGINAL ||
	       $record->status == Status::UNBIND){
		    //没绑定
		    return $handler->unBind($record);
	    }
	    //已绑定
	    else {
		    return $handler->hasBind($record);
	    }
    }

	/**
	 * @param $userInfo
	 * @return mixed
	 * @throws Exception
	 * 更新或者处理第三方用户的信息
	 */
	public function saveOrUpdate($userInfo)
	{
		try{
			/*$this->model是存储第三方用户信息表所对应的模型。
			$this->identifier 是从第三方返回的用户数据中能唯一标识用户的字段。
			这两个字段都是在具体的子类的构造函数中设定的*/

			$model = new $this->model;

			$record = $model->get([
				$this->identifier => $userInfo[$this->identifier]
			]);

			//如果没有就新建
			if(is_null($record)){
				$userInfo['admin_id'] = Status::ORIGINAL;
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
     * @param $wxRecord
     * @return string
     * @throws \think\exception\DbException
     * 如果用户已经绑定的路由
     */
    protected function hasBind($wxRecord)
    {
        $admin = Admin::get(
        	[
        		'id' => $wxRecord->admin_id,
		        'status' => Status::NORMAL
	        ]);

        //如果用户绑定的账号现在异常，则跳到未绑定
        if(is_null($admin)) {
            return $this->unBind($wxRecord);
        }

        //根据admins表里的记录生成token
        $token = JWTAuth::generateToken($admin);

        //之前已经绑定的路由
        $url = self::$config['bindWebUrl'];

        $para = [
              'token' =>$token,
              'name' => $admin->name,
              'logo' => $wxRecord->head_img_url,
        ];

        return $url.'?'.http_build_query($para);
    }

    /**
     * @param $wxRecord
     * @return string
     * 如果用户未绑定跳转的路由
     */
    protected function unBind($wxRecord)
    {
        //根据第三方用户表里的记录生成token
        $token = JWTAuth::generateToken($wxRecord);

        $url = self::$config['unbindWebUrl'];

        $para = [
            'token' =>$token,
        ];

        return $url.'?'.http_build_query($para);
    }
}