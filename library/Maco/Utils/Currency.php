<?php

class Maco_Utils_Currency
{
	public static function currency($from_Currency,$to_Currency,$amount) {
		$amount = urlencode($amount);
		$from_Currency = urlencode($from_Currency);
		$to_Currency = urlencode($to_Currency);
		$url = "http://www.google.com/ig/calculator?hl=en&q=$amount$from_Currency=?$to_Currency";
		$ch = curl_init();
		$timeout = 0;
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,  CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$rawdata = curl_exec($ch);
		curl_close($ch);
		$data = explode('"', $rawdata);
		//$data = self::fixJson($rawdata);
		$data = explode(' ', $data['3']);
		$var = $data['0'];
		return round($var,2);
	}

	private static function fixJson($string)
	{
		// (no qupte) (word) (no quote) (semicolon)
		$regex = '/(?<!")([a-zA-Z0-9_]+)(?!")(?=:)/i';
		return preg_replace($regex, '"$1"', $string);	
	}
} 