<?php

namespace App\Services;

class Helper
{

	public static $developmentMode;

	public static $standardizedDateFormat = 'Y-m-d';
	public static $standardizedDateTimeFormat = 'Y-m-d H:i:s';

	public static $generalError = "Ne pare rău, dar am întâmpinat o problemă. Vă rugăm să reîncercați.";
	public static $failStatusCodeException = 400;
	public static $successStatusCodeRequests = 200;

	public static function getDevelopmentMode()
	{
		return config('environment.developmentMode');
	}

	public static function getAppName()
	{
		return config('environment.appName');
	}

	public static function cleanData($text)
	{
		return trim(strip_tags(stripslashes($text)));
	}

	public static function cleanNumber($number)
	{
		return (int)trim(strip_tags(stripslashes($number)));
	}
}
