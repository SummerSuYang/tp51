<?php
/*
|--------------------------------------------------------------------------
| Creator: Su Yang
| Date: 2018/6/14 0014 14:14
|--------------------------------------------------------------------------
|                                                 基础缓存类
|--------------------------------------------------------------------------
*/

namespace app\common\cache;
use think\facade\Cache;

class CacheService
{
	public static $prefix = '';
	public static $ttl = 3600;

	public static function set($identifier = '', $value = '', $serialize = false )
	{
		$key = static::getKey($identifier);

		if($serialize){
			$value = serialize($value);
		}

		Cache::set($key, $value, static::$ttl);
	}

	public static function get($identifier = '', $unserialize = false)
	{
		$key =static::getKey($identifier);

		if(Cache::has($key)){
			$value = Cache::get($key);

			if($unserialize){
				return unserialize($value);
			}
			else{
				return $value;
			}
		}
		else{
			return null;
		}
	}

	public static function delete($identifier = '')
	{
		$key = static::getKey($identifier);

		if(Cache::has($key)){
			Cache::rm($key);
		}
	}

	public static function getKey($identifier)
	{
		return md5($identifier.static::$prefix);
	}
}