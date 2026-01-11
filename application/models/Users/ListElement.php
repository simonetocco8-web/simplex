<?php

class Admin_Model_Users_ListElement
{
	/**
	 * User Id
	 *
	 * @var mixed
	 */
	protected $_id;

	/**
	 * Username of this user
	 *
	 * @var mixed
	 */
	protected $_username;

	/**
	 * Name of this user
	 *
	 * @var string
	 */
	protected $_name;

	/**
	 * Last name of this user
	 *
	 * @var mixed
	 */
	protected $_lastname;

	/**
	 * Role description of this user
	 *
	 * @var string
	 */
	protected $_role;

	/**
	 * Constructor
	 *
	 * @param array $values
	 * @return Default_Model_TelephoneNumbers
	 */
	public function __construct($values = array())
	{
		foreach($values as $key => $value)
		{

		}
	}

	/**
	 * Set the id
	 *
	 * @param mixed $id
	 * @return Admin_Model_Users_ListElement
	 */
	public function setId($id)
	{
		$this->_id = $id;
		return $this;
	}

	/**
	 * Returns user id
	 *
	 * @return mixed
	 */
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * Set the user name of this user
	 *
	 * @param string $username
	 * @return Admin_Model_Users_ListElement
	 */
	public function setUsername($username)
	{
		$this->_username = $username;
		return $this;
	}

	/**
	 * Get the username
	 *
	 * @return string
	 */
	public function getUsername()
	{
		return $this->_username;
	}

	/**
	 * Set the name of this user
	 *
	 * @param string $name
	 * @return Admin_Model_Users_ListElement
	 */
	public function setName($name)
	{
		$this->_name = $name;
		return $this;
	}

	/**
	 * Get the name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Set the last name of this user
	 *
	 * @param string $lastname
	 * @return Admin_Model_Users_ListElement
	 */
	public function setLastname($lastname)
	{
		$this->_lastname = $lastname;
		return $this;
	}

	/**
	 * Get the last name
	 *
	 * @return string
	 */
	public function getLastname()
	{
		return $this->_lastname;
	}

	/**
	 * Set the role decription of this user
	 *
	 * @param string $role
	 * @return Admin_Model_Users_ListElement
	 */
	public function setRole($role)
	{
		$this->_role = $role;
		return $this;
	}

	/**
	 * Get the role description of this user
	 *
	 * @return string
	 */
	public function getRole()
	{
		return $this->_role;
	}

	public function populateFromArray()
	{
		foreach($values as $key => $value)
		{

		}
	}
}
