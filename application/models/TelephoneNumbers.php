<?php

class Default_Model_TelephoneNumbers
{
	/**
	 * Telephone Id
	 *
	 * @var mixed
	 */
	protected $_id;

	/**
	 * Id of the user that create the telephone number
	 *
	 * @var mixed
	 */
	protected $_created_by;

	/**
	 * Date of creation
	 *
	 * @var string
	 */
	protected $_date_created;

	/**
	 * Id of the user that last modified the telephone number
	 *
	 * @var mixed
	 */
	protected $_modified_by;

	/**
	 * Date of last modification
	 *
	 * @var string
	 */
	protected $_date_modified;

	/**
	 * Telephone or Fax Number
	 *
	 * @var string
	 */
	protected $_number;

	/**
	 * Flag for telephone number. true if fax, false if telephone, null unknown
	 *
	 * @var bool
	 */
	protected $_isFax = false;

	/**
	 * Description
	 *
	 * @var string
	 */
	protected $_description;

	/**
	 * Constructor
	 *
	 * @param array $values
	 * @return Default_Model_TelephoneNumbers
	 */
	public function __construct($values = array())
	{
	}

	/**
	 * Set the id
	 *
	 * @param mixed $id
	 * @return Default_Model_TelephoneNumbers
	 */
	public function setId($id)
	{
		$this->_id = $id;
		return $this;
	}

	/**
	 * Returns telephone number id
	 *
	 * @return mixed
	 */
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * Set the id of the creator
	 *
	 * @param mixed $id
	 * @return Default_Model_TelephoneNumbers
	 */
	public function setCreateb_by($id)
	{
		$this->_created_by = $id;
		return $this;
	}

	/**
	 * Returns id of the creator
	 *
	 * @return mixed
	 */
	public function getCreated_by()
	{
		return $this->_created_by;
	}

	/**
	 * Set the date of the creation
	 *
	 * @param string $date
	 * @return Default_Model_TelephoneNumbers
	 */
	public function setDate_created($date)
	{
		$this->_date_created = $date;
		return $this;
	}

	/**
	 * Returns the date of creation
	 *
	 * @return mixed
	 */
	public function getDate_created()
	{
		return $this->_date_created;
	}

	/**
	 * Set the id of the last modifier
	 *
	 * @param mixed $id
	 * @return Default_Model_TelephoneNumbers
	 */
	public function setModified_by($id)
	{
		$this->_modified_by = $id;
		return $this;
	}

	/**
	 * Returns id of the last modifier
	 *
	 * @return mixed
	 */
	public function getModified_by()
	{
		return $this->_modified_by;
	}

	/**
	 * Set the date of the last modification
	 *
	 * @param string $date
	 * @return Default_Model_TelephoneNumbers
	 */
	public function setDate_modified($date)
	{
		$this->_date_modified = $date;
		return $this;
	}

	/**
	 * Returns the date of last modification
	 *
	 * @return mixed
	 */
	public function getDate_modified()
	{
		return $this->_date_modified;
	}

	/**
	 * Set the number
	 *
	 * @param string $number
	 * @return Default_Model_TelephoneNumbers
	 */
	public function setNumber($number)
	{
		$this->_number = $number;
		return $this;
	}

	/**
	 * Returns telephone number
	 *
	 * @return string
	 */
	public function getNumber()
	{
		return $this->_number;
	}

	/**
	 * Set the flag for fax
	 *
	 * @param bool $flag
	 * @return Default_Model_TelephoneNumbers
	 */
	public function setIsFax($flag)
	{
		$this->_isFax = $flag;
		return $this;
	}

	/**
	 * Returns true if is fax, false if is telephone, null if unknown
	 *
	 * @return bool
	 */
	public function getIsFax()
	{
		return $this->_isFax;
	}

	/**
	 * Set the description
	 *
	 * @param string $description
	 * @return Default_Model_TelephoneNumbers
	 */
	public function setDescription($description)
	{
		$this->_description = $description;
		return $this;
	}

	/**
	 * Returns telephone number description
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->_description;
	}
}
