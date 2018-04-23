<?php

namespace app\lib\exception;

use app\lib\enum\ExceptionEnum;
use think\Exception;

class CommonException extends Exception
{
    // HTTP 状态码
    protected $code = 400;
    // 错误消息
    protected $msg = 'BAD REQUEST OR INVALID PARAMETERS';
    // 错误码 10000
    protected $errorCode = ExceptionEnum::BASE;
    // 自定义的错误集
    protected $errorMsg = [];

    /**
     * 确保传递进来的错误体包含以下三个属性，且可支持仅覆盖其中一个属性：
     * code -
     * msg -
     * errorCode -
     *
     * BaseException constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        if(is_int($params)){
            $params = $this->show((int)$params);
        }
        if (!is_array($params)) {
            return ;
        }

        if (array_key_exists('code', $params)) {
            $this->code = isPositiveInteger($params['code']) ? $params['code'] : $this->code;
        }

        if (array_key_exists('msg', $params)) {
            $this->msg = $params['msg'];
        }

        if (array_key_exists('errorCode', $params)) {
            $this->errorCode = isPositiveInteger($params['errorCode']) ? $params['errorCode'] : $this->errorCode;
        }
    }

    public function __set($name, $value)
    {
        if (!in_array($name, ['code', 'msg', 'errorCode'])) {
            throw new Exception('Params Exception');
        }

        if (in_array($name, ['code', 'errorCode'])) {
            // code & errorCode 必须是正整数
            if (!isPositiveInteger($value)) {
                throw new Exception('Params Exception');
            }
        }

        $this->$name = $value;
    }

    public function __get($name)
    {
        if (!in_array($name, ['code', 'msg', 'errorCode'])) {
            throw new Exception('Params Exception');
        }

        return $this->$name;
    }

    /**
     * @param $errorCode
     * @return array|mixed
     * 通过errorCode 获取http code 和msg
     */
    public function show($errorCode)
    {
        if(!array_key_exists($errorCode, $this->errorMsg)){
            return [];
        }

        $returnError = $this->errorMsg[$errorCode];
        $returnError['errorCode'] = $errorCode;

        return $returnError;
    }
}