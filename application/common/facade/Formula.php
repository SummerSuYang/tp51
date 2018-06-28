<?php
/*
|--------------------------------------------------------------------------
| Creator: Su Yang
| Date: 2018/6/15 0015 10:44
|--------------------------------------------------------------------------
|                                                      说明
|--------------------------------------------------------------------------
*/

namespace app\common\facade;

use think\Facade;
class Formula extends Facade
{
	protected static function getFacadeClass()
	{
		return 'app\common\service\Formula';
	}
}