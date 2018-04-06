<?php
/**
 * User: suyang
 * Date: 2018/3/14 0014
 * Time: 15:51
 */

namespace app\common\service;

/*
|--------------------------------------------------------------------------
|                                  用于存储当前登录用户的信息
|--------------------------------------------------------------------------
*/
class LoginUser
{
    protected static $user = [];

    public static function set($key, $value='')
    {
        if(is_array($key)) self::$user = $key;
        else self::$user[$key] = $value;

        return;
    }

    public static function get($key = '')
    {
        if(empty($key)) return self::$user;
        if(is_string($key) || is_numeric($key)){
            if(key_exists($key, self::$user))
                return self::$user[$key];
            else return null;
        }
        else return null;
    }
}