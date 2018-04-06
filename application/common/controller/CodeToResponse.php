<?php

namespace app\common\controller;

/*
|--------------------------------------------------------------------------
|                                      成功请求的通用返回
|--------------------------------------------------------------------------
*/
class CodeToResponse
{
    protected static $responseMsg = [
        //通用返回
        1000 => [200, 'ok'],

        /*用户认证 start*/
        1001 => [200, '登陆成功'],
        1002 => [200, '注销成功'],
        1003 => [202, '修改密码成功'],
        1004 => [202, '用户修改账户信息成功'],
        1005 => [202, '找回密码成功'],
        /*用户认证 end*/

        //创建成功
        1100 => [201, '新建成功',],

        //获取成功
        1201 => [200, '获取成功'],

        //更新成功
        1301 => [202, '更新成功'],

        //删除成功
        1401 => [200, '删除成功'],
    ];

    public static function show($key)
    {
        $returnMsg = [200, 'ok'];

        if(array_key_exists($key, self::$responseMsg)){
            $returnMsg = self::$responseMsg[$key];
        }

        return $returnMsg;
    }
}