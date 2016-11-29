<?php namespace Util;

class Arr
{
    /**
     * @param array $arr
     * @param string $key
     * @param null $default
     * @return null
     */
	public static function get($arr, $key, $default = null)
	{
		if (is_array($arr) && array_key_exists($key, $arr))
		{
			return $arr[$key];
		}

		return $default;
	}

}