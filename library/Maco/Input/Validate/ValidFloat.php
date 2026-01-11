<?php
    class Maco_Input_Validate_ValidFloat extends Zend_Validate_Abstract
    {
        const INVALID   = 'floatInvalid';
        const NOT_FLOAT = 'notFloat';
   
        protected $_messageTemplates = array(
            self::INVALID   => "Invalid type given, value should be float, string, or integer",
            self::NOT_FLOAT => "'%value%' does not appear to be a float",
        );
   
        public function isValid($value)
        {
            if (!is_string($value) && !is_int($value) && !is_float($value)) {
                $this->_error(self::INVALID);
                return false;
            }
            
            if (is_float($value)) {
                return true;
            }

            if(is_string($value)){
                if($value == ((string)(float)$value)){
                    return true;
                }
            }
            
            $this->_setValue($value);
            if (!is_float($value)) 
            {
                $this->_error(self::NOT_FLOAT);
                return false;
            }
            return true;
        }
   }