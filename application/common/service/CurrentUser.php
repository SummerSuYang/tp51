<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/4/8 0008 17:14
|--------------------------------------------------------------------------
|                                          存储当前登录用户的信息
|--------------------------------------------------------------------------
*/

namespace app\common\service;

use app\common\model\CommonModel;

class CurrentUser
{
    //微信授权之后的access_token
    protected static $WXAccessToken;
    //用户模型
    protected static $account = null;

    /**
     * @return null
     * 返回用户的model
     */
    public static function getAccount()
    {
        if(is_null(static::$account)){
            static::$account = JWTAuth::getAccount();
        }

        return static::$account;
    }

    /**
     * @return mixed
     * 返回调用我们自己网站的token
     */
    public static function getToken()
    {
        return JWTAuth::getReturnToken();
    }

    /**
     * @param $token
     * 存放微信授权的access token
     */
    public static function setWXAccessToken($token)
    {
        static::$WXAccessToken = $token;
    }

    /**
     * @return mixed
     * 返回微信授权的access token
     */
    public static function getWXAccessToken()
    {
        return static::$WXAccessToken;
    }

    /**
     * @param $property
     * @return null
     * 返回用户的某一个属性值
     */
    public static function getAccountAttribute($attribute)
    {
        $account = static::getAccount();

        if($account instanceof CommonModel){
            if(property_exists($account, $attribute))
                return $account->{$attribute};
            else return null;
        }

        if(is_array($account) && key_exists($attribute, $account)) {
            return $account[$attribute];
        }

        return null;
    }
}