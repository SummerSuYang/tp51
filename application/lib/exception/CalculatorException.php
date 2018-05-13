<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/5/11 0011 16:06
|--------------------------------------------------------------------------
|                                                      说明
|--------------------------------------------------------------------------
*/

namespace app\lib\exception;


use app\lib\enum\ExceptionEnum;

class CalculatorException extends CommonException
{
    // HTTP 状态码
    protected $code = 400;
    // 错误消息
    protected $msg = 'CALCULATOR EXCEPTION';
    // 错误码 16000
    protected $errorCode = ExceptionEnum::CALCULATOR;
    // 自定义的错误集
    protected $errorMsg = [
        16001 => [
            'code' => 400,
            'msg' => '小数点保留位数设置失败'
        ],

        16002 => [
            'code' => 400,
            'msg' => '检测到了计算数为空'
        ],

        16003 => [
            'code' => 400,
            'msg' => '检测到了除数为0'
        ],

        16004 => [
            'code' => 400,
            'msg' => '未知算数运算符'
        ],

        16005 => [
            'code' => 400,
            'msg' => '字符串中包含非数字字符'
        ],
    ];
}