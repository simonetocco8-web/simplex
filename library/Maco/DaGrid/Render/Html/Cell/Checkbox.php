<?php

class Maco_DaGrid_Render_Html_Cell_Checkbox extends Maco_DaGrid_Render_Html_Renderer
{    
    protected $_cb_prefix = 'cb_';
    protected $_cb_name = 'cb';
    protected $_cb_class = 'cb';
    
    public function setSource(&$source)
    {
        $this->_source = $source;
    }

    public function setColumns($columns)
    {
        $this->_columns = $columns;
    }

    public function setColumn($column)
    {        
        $this->_column = $column;
        $this->_cb_name = $column->getOption('cb_name', 'cb');
        $this->_cb_class = $column->getOption('cb_class', 'cb');
        $this->_defaultTpl = 'cells/checkbox.phtml';
    }
    
    public function getCheckboxClass()
    {
        return $this->_cb_class;
    }
    
    public function getValue()
    {
        return 1;
    }
    
    public function getName()
    {
        return $this->_cb_name;
    }
    
    public function getCheckboxId()
    {
        $field = $this->_column->getField();
        $separator = $this->_column->getOption('separator', $this->_separator); 
        
        
        $ret = $this->_cb_prefix . $this->_source->getCellByFieldName($field);
        
        return $ret;
    }
}
