<?php
/**
 * User: suyang
 * Date: 2018/3/17 0017
 * Time: 15:42
 */

namespace app\lib\exception;
use app\lib\enum\ExceptionEnum;

class AuthException extends CommonException
{
    // HTTP 状态码
    protected $code = 400;
    // 错误消息
    protected $msg = 'AUTH EXCEPTION';
    // 错误码 10000
    protected $errorCode = ExceptionEnum::AUTH;
    // 自定义的错误集
    protected $errorMsg = [
        11001 => [
            'code' => 400,
            'msg' => '该账户不存在'
        ],

        11002 => [
            'code' => 400,
            'msg' => '账户或密码错误'
        ],

        11003 => [
            'code' => 403,
            'msg' => '账户无此权限'
        ],
    ];
}