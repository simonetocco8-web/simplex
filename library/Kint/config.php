<?php

class kintSettings {

	/**
	 * @var callback
	 *
	 * @param string $file filename where the function was called
	 * @param int|NULL $line the line number in the file (not applicable when used in resource dumps)
	 */
	public static $pathDisplayCallback = "kint::_debugPath";

	public static $maxStrLength = 60;
	/** @var int max array/object levels to go deep, if zero no limits are applied */
	public static $maxLevels = 5;

	public static $enabled = TRUE;

	public static $skin = 'kint.css';


}