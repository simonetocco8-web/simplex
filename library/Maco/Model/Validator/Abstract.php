<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 6-set-2010
 * Time: 11.32.17
 */
 
class Maco_Model_Validator_Abstract
{
    /**
     * Array of filters
     *
     * @var array
     */
    protected $_filters = array();

    /**
     * Array of validators
     *
     * @var array
     */
    protected $_validators = array();

    /**
     * @var Zend_Filter_Input
     */
    protected $_input;

    /**
     * Returns true if the model is valid
     *
     * @return bool
     */
    public function isValid()
    {
        if(!isset($this->_input))
        {
            $options = array('filterNamespace' => 'Maco_Input_Filter',
                 'validatorNamespace' => 'Maco_Input_Validate');
            $this->_input = new Zend_Filter_Input($this->_filters, $this->_validators, null, $options);
        }

        if($this->_input->hasInvalid() || $this->_input->hasMissing())
        {
            return false;
        }

        return true;
    }

    public function getMessages()
    {
        return $this->_input->getMessages();
    }

    public function setData($data)
    {
        if(!isset($this->_input))
        {
            $options = array('filterNamespace' => 'Maco_Input_Filter',
                 'validatorNamespace' => 'Maco_Input_Validate');
            $this->_input = new Zend_Filter_Input($this->_filters, $this->_validators, null, $options);
        }

        $this->_input->setData($data);
    }

    public function getValid()
    {
        return $this->_input;
    }
}
