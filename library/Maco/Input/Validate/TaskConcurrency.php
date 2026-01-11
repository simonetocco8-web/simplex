<?php
    class Maco_Input_Validate_TaskConcurrency extends Zend_Validate_Abstract
    {
        const BUSY = 'userBusy';
   
        protected $_messageTemplates = array(
            self::BUSY   => "Utente gi&agrave; impegnato nel periodo inserito",
        );
   
        public function isValid($value)
        {
            return true;
        }
   }