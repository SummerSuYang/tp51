<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/5/29 0029 14:39
|--------------------------------------------------------------------------
|                                            扫码登录的配置文件
|--------------------------------------------------------------------------
*/

return [
	'default' => [
		'type' => 'weChat',
		'appId' => env('WX_OPEN_APP_ID', 'DEFAULT'),
		'appSecret' => env('WX_OPEN_APP_SECRET', 'DEFAULT'),
		//已经绑定的前端路由url
		'bindWebUrl' => 'http://'.env('WEB_DOMAIN','DEFAULT').'/welcome',
		//未绑定的前端路由url
		'unbindWebUrl' => 'http://'.env('WEB_DOMAIN','DEFAULT').'/account/login',
	],
];