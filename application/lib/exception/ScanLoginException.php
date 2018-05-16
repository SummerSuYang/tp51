<?php
/**
 * User: suyang
 * Date: 2018/3/17 0017
 * Time: 15:42
 */

namespace app\lib\exception;
use app\lib\enum\ExceptionEnum;
use app\lib\exception\BaseException;

class ScanLoginException extends CommonException
{
    // HTTP 状态码
    protected $code = 400;
    // 错误消息
    protected $msg = 'SCAN LOGIN EXCEPTION';
    // 错误码 10000
    protected $errorCode = ExceptionEnum::SCAN_LOGIN;
    // 自定义的错误集
    protected $errorMsg = [
        18001 => [
            'code' => 400,
            'msg' => '缺少配置信息'
        ],

        18002 => [
            'code' => 400,
            'msg' => '无效的应用场景'
        ],

        18003 => [
            'code' => 400,
            'msg' => '暂不支持的扫码方式'
        ],

        18004 => [
            'code' => 400,
            'msg' => '配置中缺少存储第三方用户信息的模型'
        ],
    ];
}