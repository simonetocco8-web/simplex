<?php

class Simplex_Importer_Config
{
    protected $_config_path;
    protected $_config;
    
    protected $_autosave = false;
    
    public function __construct($autosave = false)
    {
        $this->_config_path = APPLICATION_PATH . '/configs/importer.ini';        
        $this->_config = new Zend_Config_Ini($this->_config_path, APPLICATION_ENV, array('allowModifications' => true));
        $this->_autosave = $autosave;
    }
    
    public function save()
    {
        $this->_writeConfig();
    }
    
    protected function _writeConfig()
    {
        // Write the config file
        $writer = new Zend_Config_Writer_Ini(array('config'   => $this->_config,
                                           'filename' => $this->_config_path));
        $writer->write();
    }
    
    public function __get($name)
    {
        return ($this->_config->$name) ?: false;
    }
    
    public function __set($name, $val)
    {
        $this->_config->$name = $val;
        
        if($this->_autosave)
        {
            $this->_writeConfig();
        }
    }
}
