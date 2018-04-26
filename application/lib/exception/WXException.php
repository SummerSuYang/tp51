<?php
/**
 * User: suyang
 * Date: 2018/3/17 0017
 * Time: 15:42
 */

namespace app\lib\exception;
use app\lib\enum\ExceptionEnum;
use app\lib\exception\BaseException;

class WXException extends CommonException
{
    // HTTP 状态码
    protected $code = 400;
    // 错误消息
    protected $msg = 'WX EXCEPTION';
    // 错误码 10000
    protected $errorCode = ExceptionEnum::WX;
    // 自定义的错误集
    protected $errorMsg = [
        15001 => [
            'code' => 400,
            'msg' => '获取不到code'
        ],

        15002 => [
            'code' => 400,
            'msg' => '无法解析从微信服务器返回的数据'
        ],

        15003 => [
            'code' => 400,
            'msg' => '从微信服务器返回的数据有误'
        ],
    ];
}