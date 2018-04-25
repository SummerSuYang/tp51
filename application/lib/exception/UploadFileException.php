<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/3/27 0027 13:35
|--------------------------------------------------------------------------
|                                                      说明
|--------------------------------------------------------------------------
*/

namespace app\lib\exception;

use app\lib\enum\ExceptionEnum;

class UploadFileException extends CommonException
{
    // HTTP 状态码
    protected $code = 400;
    // 错误消息
    protected $msg = 'UPLOAD FILE EXCEPTION';
    // 错误码 10000
    protected $errorCode = ExceptionEnum::UPLOAD_FILE;
    // 自定义的错误集
    protected $errorMsg = [
        14001 => [
            'code' => 400,
            'msg' => '文件类型异常'
        ],

        14002 => [
            'code' => 400,
            'msg' => '无法获取endpoint'
        ],

        14003 => [
            'code' => 400,
            'msg' => '无法获取accessKeyId'
        ],

        14004 => [
            'code' => 400,
            'msg' => '无法获取secret'
        ],

        14005 => [
            'code' => 400,
            'msg' => '上传文件太大'
        ],

        14006 => [
            'code' => 400,
            'msg' => '上传文件缺少必要信息'
        ],

        14007 => [
            'code' => 400,
            'msg' => '无法获取bucket'
        ],
        14008 => [
            'code' => 400,
            'msg' => 'bucket不存在'
        ],
    ];

}