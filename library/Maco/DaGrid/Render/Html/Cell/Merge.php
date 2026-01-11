<?php

class Maco_DaGrid_Render_Html_Cell_Merge extends Maco_DaGrid_Render_Html_Renderer
{
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
        
        $this->_fields = $column->getOption('fields', array($column->getField()));
        
        $this->_defaultTpl = 'cells/merge.phtml';
    }
    
    public function getValue()
    {
        $values = array();
        foreach($this->_fields as $f)
        {
            $values[] = $this->_source->getCellByFieldName($f);
        }
        $ret = implode(' ', $values);
        return $ret;
    }
}
