<?php
/**
 * author      : Gavin <liyn2007@qq.com>
 * createTime  : 2017/8/16 09:50
 * description : 自定义异常处理handle类
 */

namespace app\lib\exception;

use Exception;
use think\facade\Config;
use think\exception\Handle;
use think\facade\Request;

class ExceptionHandler extends Handle
{
    // HTTP 状态码
    private $code;
    // 错误消息
    private $msg;
    // 错误码
    private $errorCode;
    // 当前访问URL
    private $currentUrl;

    public function render(Exception $e)
    {
        if ($e instanceof CommonException) {
            // 接受自定义的错误体信息
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errorCode = $e->errorCode;
        } else {
            if(Config::get('custom_exception_handle')) {
                // 如果不是自定义的错误体，抛出内部错误500
                $this->code = 500;
                $this->msg = 'INTERNAL SERVER ERROR';
                $this->errorCode = 999;
            }else {
                return parent::render($e); // 抛出原来的异常
            }
        }

        $this->currentUrl = Request::url(); // 获取当前URL地址

        $return = [
            'msg' => $this->msg,
            'err_code' => $this->errorCode,
            'request_url' => $this->currentUrl
        ];

        return json($return, $this->code);
    }
}