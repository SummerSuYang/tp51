<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/5/13 0013 14:25
|--------------------------------------------------------------------------
|                                                      说明
|--------------------------------------------------------------------------
*/

namespace app\test\controller;

use app\common\service\Formula;
use think\Controller;

class Test extends Controller
{
    public function test()
    {
        $str = 'ID113*ID113+ID114+10000*ID115*0.03+ID111*45.2';
        $value = [
            111 => 0.11,
            112 => 4.35,
            113 => 50.64,
            114 => 10000.45,
            115 => 40
        ];

        $obj = new Formula();
        //$bool = $obj->calculate($str, $value);
        $bool = $obj->calculate($str, $value);
        dump($bool);die;
    }
}