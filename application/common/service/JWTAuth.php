<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/3/28 0028 17:24
|--------------------------------------------------------------------------
|                                             JWT认证服务
|--------------------------------------------------------------------------
|checkAuth 负责认证，会根据配置need_nonce_str自动选择是否刷新。可
|以传入一个scene来设定应用场景
|
|generateToken 负责生成token，第一个参数是数据对象（不能是数组），
|第二个参数是唯一标识用户的“键”，默认是id
|
|getReturnToken返回将要返回的token值。如果是刷新操作就返回新生成
|的token，反之直接将请求token返回
|--------------------------------------------------------------------------
*/

namespace app\common\service;

use app\lib\exception\TokenException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Request;

class JWTAuth
{
    //用户有没有通过认证
    protected static $passAuth = false;
    //从前端带过来的token
    protected static $requestToken;
    //刷新后的token
    protected static $returnToken;
    //存储已经认证过的用户信息
    protected static $account;
    //应用场景
    protected static $scene;

    /**
     * @throws TokenException
     */
    public static function handle($scene = '')
    {
        //验证场景
        if(!empty($scene)) self::setScene($scene);

        //如果有随机串就说明是刷新的反之仅仅需要认证
        if(self::needNonce()) {
            self::refresh();
        }
        else {
            self::authenticate();
            //不需要刷新，返回的token就是请求的token
            self::$returnToken = self::$requestToken;
        }
    }

    /**
     * @param string $scene
     * @throws TokenException
     * 选择应用场景
     */
    public static function setScene($scene = '')
    {
        if(empty($scene)){
            throw new TokenException(12010);
        }
        self::$scene = $scene;
    }

    /**
     * @param $payload
     * @throws TokenException
     */
    protected static function resolveAccount($payload)
    {
        if( !key_exists('uid', $payload) || !isPositiveInteger($payload['uid'])) {
            throw new TokenException(12007);
        }

        //获取account的方法需要具体的实现
        self::$account = (self::getModel())::get($payload['uid']);

        if(empty(self::$account)) {
            throw new TokenException(12011);
        }
    }

    /**
     * @return mixed
     */
    protected static function getTokenFromRequest()
    {
        return Request::header('token');
    }

    /**
     * @param $account
     * @return string
     * @throws TokenException
     */
    public static function generateToken($account)
    {
        $payload = self::createPayload($account);

        self::$returnToken = JWT::encode($payload, self::getSecretKey());

        return self::$returnToken;
    }

    /**
     * @param $account
     * @return mixed
     * @throws TokenException
     */
    protected static function createPayload($account)
    {
        if( !key_exists('id', $account)) {
            throw new TokenException(12008);
        }
        $payload['uid'] = $account['id'];
        // 过期时间 = 当前请求时间 + token过期时间
        $payload['exp'] = Request::time() + self::getExpireIn();
        if (self::needNonce()) {
            // 需要随机字符串
            $payload['nonce'] = createUniqidNonceStr();
        }

        return $payload;
    }

    /**
     * @return array
     * @throws TokenException
     */
    protected static function verifyToken()
    {
        if (empty(self::$requestToken = self::getTokenFromRequest()))
                throw new TokenException(12001);

        try {
            $payLoad = (array)JWT::decode(self::$requestToken, self::getSecretKey(), ['HS256']);
        }
        catch (ExpiredException $e) {
            //过期了
            throw new TokenException(12003);
        }
        catch (SignatureInvalidException $e) {
            //签名认证失败
            throw new TokenException(12005);
        }
        catch (BeforeValidException $e) {
            //token被设置了nbf，现在还不能使用
            throw new TokenException(12002);
        }
        catch (\UnexpectedValueException $e) {
            //header 中缺少必要的声明或者加密算法不被接收或者payload为空
            throw new TokenException(12004);
        }

        return $payLoad;
    }

    /**
     * @param array $payload
     * @param bool $markAsExpired
     * @throws TokenException
     */
    protected static function verifyNonce($payload = [], $markAsExpired = true)
    {
        if( !key_exists('nonce', $payload)){
            throw new TokenException(12004);
        }

        $nonceStr = $payload['nonce'];

        if (Cache::has($nonceStr)) { // 存在表示已用过
            throw new TokenException(12003);
        } else {
            if($markAsExpired) {
                // 标识已使用
                Cache::set($nonceStr, 1, self::getExpireIn());
            }
        }
    }

    /**
     * @throws TokenException
     */
    protected static function authenticate()
    {
        //验证token
        $payload = self::verifyToken();

        //验证随机串
        if(self::needNonce()) {
            self::verifyNonce($payload);
        }

        //检查并存储用户信息
        self::resolveAccount($payload);

        //当且仅当上面三步都完成以后才会标记“认证成功”
       self::$passAuth = true;
    }

    /**
     * @throws TokenException
     */
    protected static function refresh()
    {
        //前面没有认证需要先认证
        if(self::$passAuth === false) {
            self::authenticate();
        }

        self::generateToken((self::$account)->toArray());
    }

    /**
     * @return mixed
     * 返回将要返回的token
     */
   public static function getReturnToken()
   {
        return self::$returnToken;
   }

    /**
     * @return mixed
     * 返回前端请求的token
     */
   public static function getRequestToken()
   {
       return self::$requestToken;
   }

    /**
     * @return mixed
     * 返回登录成功用户的信息
     */
   public static function getAccount()
   {
       return self::$account;
   }

    /**
     * @return mixed
     * @throws TokenException
     */
    protected static function getSecretKey()
    {
       if(empty($secretKey = Config::get("token.".self::$scene.".secret_key"))){
           throw new TokenException(12009);
       }

       return$secretKey;
    }

    /**
     * @return int
     */
    protected static function getExpireIn()
    {
        if(empty($expireIn = Config::get("token.".self::$scene.".expires_in"))){
            return 3600;
        }

        return (int)$expireIn;
    }

    /**
     * @return mixed
     */
    protected static function needNonce()
    {
        if(empty($needNonce = Config::get("token.".self::$scene.".need_nonce_str"))){
            return false;
        }

        return $needNonce;
    }

    /**
     * @return mixed
     * @throws TokenException
     */
    protected static function getModel()
    {
        if(empty($model = Config::get("token.".self::$scene.".model"))) {
            throw new TokenException(12009);
        }

        return $model;
    }
}
