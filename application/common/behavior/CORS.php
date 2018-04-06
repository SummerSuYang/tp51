<?php
/**
 * author      : Gavin <liyn2007@qq.com>
 * createTime  : 2017/9/1 11:27
 * description :
 */

namespace app\common\behavior;


class CORS
{
    // 处理跨域问题 ps: 容易产生安全问题，需要确认是否所有来源都可访问接口
    public function appInit($params)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: token, Origin, X-Requested-With, Content-Type, Accept');
        /**
         * CORS请求时，XMLHttpRequest对象的getResponseHeader()方法只能拿到6个基本字段：
         * Cache-Control、Content-Language、Content-Type、Expires、Last-Modified、Pragma。
         * 如果想拿到其他字段，就必须在Access-Control-Expose-Headers里面指定。
         */
        header('Access-Control-Expose-Headers: token, version');
        header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT');
        header('Access-Control-Max-Age: 3600'); // 指定本次预检请求的有效期，单位为秒
        if (request()->isOptions()) {
            exit();
        }
    }
}