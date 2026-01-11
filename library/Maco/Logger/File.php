<?php

class Maco_Logger_File
{
	/**
	 * The logger object
	 *
	 * @var Zend_Log
	 */
	protected $_logger;

	/**
	 * File Logger object
	 *
	 * @var Maco_Logger_File
	 */
	protected static $_fileLogger = null;

	public static function getInstance()
	{
		if(self::$_fileLogger === null)
		{
			self::$_fileLogger = new self();
		}
		return self::$_fileLogger;
	}

	/**
	 * put your comment there...
	 *
	 * @return Zend_Log
	 */
	public function getLog()
	{
		return $this->_logger;
	}

	protected function __construct()
	{
		$this->_logger = Zend_Registry::get('log');
	}

	/**
	 * Send an info message to the log file
	 *
	 * @param string $message
	 */
	public static function info($message)
	{
		self::getInstance()->getLog()->info($message);
	}
}
