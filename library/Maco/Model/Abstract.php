<?php

/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 6-set-2010
 * Time: 11.32.17
 */

class Maco_Model_Abstract implements ArrayAccess /* , Countable, IteratorAggregate */
{
    private $_strict = false;

    /**
     * The validator
     *
     * @var Maco_Model_Validator_Abstract
     */
    private $_validator;

    /**
     * @var bool
     */
    private $_validateDone = false;

    private $_validateResult = false;

    public function setData($data, $prefix = '')
    {
        foreach($data as $field => $value)
        {
            $realField = substr($field, strlen($prefix));
			$this->$realField = $value;
        }
    }

    public function __set($name, $value)
    {
        if(property_exists($this, $name))
        {
            $this->_validateDone = false;
            $this->$name = $value;
        }
        else
        {
            if($this->_strict)
            {
                throw new Exception('field ' . $name . ' doesn\'t exists for ' . __CLASS__ . ' class');
            }
        }
    }

    public function __get($name)
    {
        if(property_exists($this, $name))
        {
            return $this->$name;
        }
        else
        {
            if($this->_strict)
            {
                throw new Exception('field ' . $name . ' doesn\'t exists for ' . __CLASS__ . ' class');
            }
        }
    }
   
    /* ARRAYACCESS */

    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    public function offsetGet($offset)
    {
        if($this->offsetExists($offset))
        {
            return $this->$offset;
        }
        else
        {
            if($this->_strict)
            {
                throw new Exception('field ' . $offset . ' doesn\'t exists for ' . __CLASS__ . ' class');
            }
        }
    }

    public function offsetSet($offset, $value)
    {
        if($this->offsetExists($offset))
        {
            $this->_validateDone = false;
            $this->$offset = $value;
        }
        else
        {
            if($this->_strict)
            {
                throw new Exception('field ' . $offset . ' doesn\'t exists for ' . __CLASS__ . ' class');
            }
        }
    }

    public function offsetUnset($offset)
    {
        if($this->offsetExists($offset))
        {
            $this->_validateDone = false;
            $this->$offset = null;
        }
        else
        {
            if($this->_strict)
            {
                throw new Exception('field ' . $offset . ' doesn\'t exists for ' . __CLASS__ . ' class');
            }
        }
    }

    public function setValidatorAndFilter($validator)
    {
        $this->_validator = $validator;
    }

    public function isValid()
    {
        if(!$this->_validateDone)
        {
            if(!isset($this->_validator))
            {
                throw new Exception('No validator set for model ' . __CLASS__);
            }

            $this->_validator->setData($this->toArray());

            $this->_validateResult = $this->_validator->isValid();

            if($this->_validateResult)
            {
                $this->_validateDone = true;
                $this->_validate();
            }

            return $this->_validateResult;
        }
    }

    protected function _validate()
    {
        $valid = $this->_validator->getValid();
        
        $ref = new ReflectionClass($this);
        $props = $ref->getProperties(ReflectionProperty::IS_PROTECTED);

        foreach($props as $prop)
        {
            $name = $prop->getName();
            $this->$name = $valid->$name;
        }
    }

    public function getValid()
    {
        if($this->_validateDone && $this->_validateResult)
        {
            return $this->_validator->getValid()->getUnescaped();
            return $this->toArray();
        }
        else
            return array();
    }

    public function getInvalidMessages()
    {
        return $this->_validator->getMessages();
    }

    public function toArray()
    {
        $ref = new ReflectionClass($this);
        $props = $ref->getProperties(ReflectionProperty::IS_PROTECTED);

        $array = array();

        foreach($props as $prop)
        {
            $name = $prop->getName();
            $array[$name] = $this->$name;
        }

        return $array;
    }

    /*

    public function count()
    {
        $ref = new ReflectionClass($this);
        $props = $ref->getProperties(ReflectionProperty::IS_PROTECTED);

        return count($props) - 2;
    }

    public function getIterator()
    {

    }

     */
}