<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/4/10 0010 16:20
|--------------------------------------------------------------------------
|                                              与微信有关的配置
|--------------------------------------------------------------------------
*/

return [
    'appId' => env('WX_OPEN_APP_ID', 'DEFAULT'),
    'appSecret' => env('WX_OPEN_APP_SECRET', 'DEFAULT'),
    //已经绑定的前端路由url
    'bindWebUrl' => 'http://'.env('WEB_DOMAIN','DEFAULT').'/welcome',
    //未绑定的前端路由url
    'unbindWebUrl' => 'http://'.env('WEB_DOMAIN','DEFAULT').'/account/login',
];