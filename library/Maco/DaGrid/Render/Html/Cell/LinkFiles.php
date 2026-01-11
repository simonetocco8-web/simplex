<?php

class Maco_DaGrid_Render_Html_Cell_LinkFiles extends Maco_DaGrid_Render_Html_Cell_Link
{
    public function setColumn($column)
    {
        $this->_column = $column;
        $this->_linksData = $column->getOption('linksData', array());
        $this->_base = $column->getOption('base', '/');

        $this->_field = $column->getField();


        $this->_img = $column->getOption('img', false);
        $this->_title = $column->getOption('title', '');

        $this->_defaultTpl = 'cells/linkFiles.phtml';
    }
}
