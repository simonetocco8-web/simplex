<?php
/**
* Helper to build a nice looking button (button or link)
*/
class Zend_View_Helper_SearchWrapper extends Zend_View_Helper_Abstract 
{ 
    /**
    * Cache for the select table values
    * 
    * @var array
    */
    protected $_tables_cache = array();
    
    /**
    * My Utils Instance
    */
    protected $_utils;
    
    /**
    * Return the search wrapper with all the search elements given
    * 
    * @param array the element's array
    * @param array optional array of css classes
    * append string options id for the search wrapper
    * 
    * @return string
    */
    public function searchWrapper($form_action, $form_name, $form_id, $target_div, $elements, $id = null, $classes = array())
    { 
        $out = '<div ';
        if($id)
        {
            $out .= 'id="' . $id . '" ';
        }
        
        $classes = array_merge($classes, array('search-wrapper'));
        $out .= 'class="' . implode(' ', $classes) . '">';        
        
        $out .= '<h3>Filtri di Ricerca</h3>';
        
        $out .= '<form action="' . $form_action . '" name="' . $form_name . '" id="' . $form_id . '" rel="' . $target_div . '" method="POST" class="search-form">';
        
        foreach($elements as $key => $element)
        {
            $out .= $this->_buildElement($key, $element);
        }
        
        $out .= '<div style="clear:both"></div><div class="search-element"><div class="search-element-label">&nbsp;</div><div class="search-element-field"><input type="reset" value="annulla" /> <input type="submit" value="cerca" /></div></div>';
        
        $out .= '</form></div>';

        return $out;
    } 
    
    protected function _buildElement($key, $element)
    {
        $out = '<div class="search-element"><div class="search-element-label">' . $element['label'] . '</div><div class="search-element-field">';
        switch($element['type'])
        {
            case 'text':
                $out .= $this->_textElement($key, $element);
                break;
            case 'select':
                $out .= $this->_selectElement($key, $element);
                break;
            case 'numeric_range':
                $out .= $this->_rangeElement($key, $element);
                break;
            case 'date':
                $out .= $this->_dateElement($key, $element);
                break;
        }
        
        $out .= '</div></div>';
        
        return $out;
    }
    
    protected function _textElement($key, $element)
    {
        $val = isset($_POST[$element['name']]) ? $_POST[$element['name']] : '';
        $out = '<input type="text" class="search-text" name="' . $element['name'] . '" value="' . $val .'" />';
        return $out;
    }
    protected function _dateElement($key, $element)
    {
        $out = '<input type="text"  name="' . $element['name'] . '" class="search-text date-range" />';
        return $out;
    }
    protected function _selectElement($key, $element)
    {
        $out = '<select name="' . $element['name'] . '" ';
        if($element['multiple'])
        {
            $out .= ' multiple="multiple" class="multiple-select"';
        }
        else
        {
            $out .= ' class="input"';
        }
        $out .= '>';
        
        $vals = array();
        switch($element['source'])
        {
            case 'array':
                $vals = $element['array'];
                break;
            case 'table':
                if(!isset($this->_tables_cache[$element['table_name']]))
                {
                    $utils = $this->_getUtils();
                    $vals = array('' => '') + $utils->getArrayForSelectElementSimple($element['table_name'], $element['table_id'], $element['table_field']);
                    $this->_tables_cache[$element['table_name']] = $vals;
                }
                $vals = $this->_tables_cache[$element['table_name']];
                break;
        }
        if(!$element['multiple'])
        {
            $val = isset($_POST[$element['name']]) ? $_POST[$element['name']] : '';
        }
        else
        {
            $key = substr($element['name'], 0, -2);
            $val = isset($_POST[$key]) ? $_POST[$key] : '';
        }

        foreach($vals as $k => $v)
        {
            $out .= '<option value="' . $k . '" '; 
            if(!$element['multiple'])
            {
                $out .= ($val == $k ? 'selected="selected"' : '');
            }
            else
            {
                $out .= (is_array($val) && in_array($k, $val) ? 'selected="selected"' : '');
            }
            $out .= '>' . $v . '</option>';
        }
        $out .= '</select>';
        return $out;
    }
    protected function _rangeElement($key, $element)
    {
        $out = '<input type="text" class="search-text" name="' . $element['name'] . '" />';
        return $out;
    }

    /**
     * @return Model_Common
     */
    protected function _getUtils()
    {
        if(!$this->_utils)
        {
            $this->_utils = new Model_Common();
        }
        return $this->_utils;
    }
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
} 