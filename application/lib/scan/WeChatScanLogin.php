<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/5/29 0029 15:21
|--------------------------------------------------------------------------
|                                                      说明
|--------------------------------------------------------------------------
*/

namespace app\lib\scan;

use app\common\model\WechatAdmin;
use app\lib\exception\WXException;
use app\common\service\ScanLogin;
use think\facade\Request;

class WeChatScanLogin extends ScanLogin
{
	public function __construct()
	{
		$this->model = WechatAdmin::class;
		$this->identifier = 'union_id';
	}

	/**
	 * @return mixed
	 * @throws WXException
	 * 获取access token
	 */
	protected function getAccessToken()
	{
		$para = [
			'appid' => self::$config['appId'],
			'secret' => self::$config['appSecret'],
			'code' => Request::get('code'),
			'grant_type' => 'authorization_code'
		];

		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?';
		$url.= http_build_query($para);

		$result = json_decode(file_get_contents($url), true);

		if( !is_array($result)){
			throw new WXException(28002);
		}

		if( key_exists('errcode', $result))
			throw new WXException([
				'code' =>$result['errcode'], 'msg' => $result['errmsg']
			]);

		return $result;
	}

	/**
	 * @return mixed
	 * @throws WXException
	 */
	public function getUserInfo()
	{
		//获取access token 和 open id
		$para = $this->getAccessToken();

		$query = [
			'access_token' => $para['access_token'],
			'openid' => $para['openid']
		];

		$url = 'https://api.weixin.qq.com/sns/userinfo?' . http_build_query($query);

		$userInfo = json_decode(file_get_contents($url), true);

		if( !is_array($userInfo)){
			throw new WXException(28002);
		}

		if( key_exists('errcode', $userInfo))
			throw new WXException([
				'code' =>$userInfo['errcode'], 'msg' => $userInfo['errmsg']
			]);

		return $userInfo;
	}
}