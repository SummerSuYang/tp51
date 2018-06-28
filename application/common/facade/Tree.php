<?php
/*
|--------------------------------------------------------------------------
| Creator: Su Yang
| Date: 2018/6/14 0014 13:53
|--------------------------------------------------------------------------
|                                                      说明
|--------------------------------------------------------------------------
*/

namespace app\common\facade;

use think\Facade;

class Tree extends Facade
{
	protected static function getFacadeClass()
	{
		return 'app\common\service\Tree';
	}
}
