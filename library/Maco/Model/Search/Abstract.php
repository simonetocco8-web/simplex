<?php
  
class Maco_Model_Search_Abstract
{
    protected $_search = array();
    
    protected $_utils = false;
    
    public function render()
    {
        $out = '';
        $search = $this->getSearchArray();
        foreach($search as $key => $element)
        {
            $out .= $this->_buildElement($key, $element);
        }
        return $out;
    }
    
    protected function _buildElement($key, $element)
    {
        if($element['type'] != 'hidden')
        {
            $out = '<div class="search-element"><div class="search-element-label">' . $element['label'] . '</div><div class="search-element-field">';
        }
        else
        {
            $out = '';
        }
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
            case 'autocomplete':
                $out .= $this->_autocompleteElement($key, $element);
                break;
            case 'hidden':
                $out .= $this->_hiddenElement($key, $element);
                break;
                    
            // spuri
            case 'users_rcs':
                $out .= $this->_users_rcs($key, $element);
                break;
            case 'offers_subservices':
                $out .= $this->_offers_subservices($key, $element);
                break;
        }
        if($element['type'] != 'hidden')
        {
            $out .= '</div></div>';
        }
        
        return $out;
    }

    protected function _hiddenElement($key, $element)
    {
        $id = (isset($element['id'])) ? 'id="' . $element['id'] . '"' : '';
        $name = 'name="' . $element['name'] . '"';
        $value = 'value="' . $element['value'] . '"';
        $out = '<input type="hidden" ' . $id . ' ' . $name . ' ' . $value . ' />';
        return $out;
    }
    
    protected function _autocompleteElement($key, $element)
    {
        $val = isset($_GET[$element['name']]) ? $_GET[$element['name']] : '';
        $class ="search-autocomplete u:'" . $element['url'] . "' i:'" . $key . "'";
        $id = (isset($element['id'])) ? 'id="' . $element['id'] . '"' : '';
        if(isset($element['field']) && $element['field'] != '')
        {
            $class .= ' f:\'' . $element['field'] . '\'';
        }
        $class .= ((isset($element['multiple']) && $element['multiple']) ? ' m:1' : 'm:0');
        $out = '<input type="text" class="' . $class . '" ' . $id . ' name="' . $element['name'] . '" value="' . $val . '" />';
        if($id != '')
        {
            $out .= '';
            //$out .= '<script type="text/javascript">_autocomplete.' . $element['id'] . ' = \'' . $val . '\'</script>';
        }
        return $out;
    }
    protected function _textElement($key, $element)
    {
        $val = isset($_GET[$element['name']]) ? $_GET[$element['name']] : '';
        $id = (isset($element['id'])) ? 'id="' . $element['id'] . '"' : '';
        $out = '<input type="text" class="search-text" ' . $id . ' name="' . $element['name'] . '" value="' . $val .'" />';
        return $out;
    }
    protected function _dateElement($key, $element)
    {
        $val = isset($_GET[$element['name']]) ? $_GET[$element['name']] : '';
        $id = (isset($element['id'])) ? 'id="' . $element['id'] . '"' : '';
        $out = '<input type="text" ' . $id . ' name="' . $element['name'] . '" class="search-text date-range" value="' . $val .'" />';
        return $out;
    }
    protected function _selectElement($key, $element)
    {
        $id = (isset($element['id'])) ? 'id="' . $element['id'] . '"' : '';
        $out = '<select ' . $id . ' name="' . $element['name'] . '" ';
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
                    $where = (isset($element['where'])) ? $element['where'] : null;
                    $vals = array('' => '') + $utils->getArrayForSelectElementSimple($element['table_name'], $element['table_id'], $element['table_field'], $where);
                    $this->_tables_cache[$element['table_name']] = $vals;
                }
                $vals = $this->_tables_cache[$element['table_name']];
                break;
        }
        if(!$element['multiple'])
        {
            $val = isset($_GET[$element['name']]) ? $_GET[$element['name']] : '';
        }
        else
        {
            $key = substr($element['name'], 0, -2);
            $val = isset($_GET[$key]) ? $_GET[$key] : '';
        }
        
        foreach($vals as $k => $v)
        {
            $out .= '<option value="' . $k . '" '; 
            if(!$element['multiple'])
            {
                $out .= (($val == $k && $val != '') ? 'selected="selected"' : '');
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

    protected function _users_rcs($key, $element)
    {
        $id = (isset($element['id'])) ? 'id="' . $element['id'] . '"' : '';
        $out = '<select ' . $id . ' name="' . $element['name'] . '" ';
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
        $users_repo = Maco_Model_Repository_Factory::getRepository('user');
        $rcs = $users_repo->getUsersOfType('RC');

        $db = Zend_Registry::get('dbAdapter');
        $rcs_esistenti = $db->fetchCol('select distinct rco from orders_rcos');

        $rcs_to_add = array();
        foreach($rcs_esistenti as $es)
        {
            $already_in = false;
            foreach($rcs as $rc)
            {
                if($rc['username'] . ' - ' . $rc['nome'] . ' ' . $rc['cognome'] == $es)
                {
                    $already_in = true;
                    break;
                }
            }
            if(!$already_in)
            {
                $rcs_to_add[$es] = $es;
            }
        }

        $util = new Maco_Html_Utils();
        $rcs_real = $util->parseDbRowsForSelectElement($rcs, 'username', 'username', array('nome', 'cognome'));

        if(!$element['multiple'])
        {
            $val = isset($_GET[$element['name']]) ? $_GET[$element['name']] : '';
        }
        else
        {
            //$key = substr($element['name'], 0, -2);
            $val = isset($_GET[$key]) ? $_GET[$key] : '';
        }

        $out .= '<optgroup label="Utenti del Sistema">';
        foreach($rcs as $rc)
        {
            $value = $rc['username'] . ' - ' . $rc['nome'] . ' ' . $rc['cognome'];
            $out .= '<option value="' . $value . '" ';
            if(!$element['multiple'])
            {
                $out .= ($val == $value ? 'selected="selected"' : '');
            }
            else
            {
                $out .= (is_array($val) && in_array($value, $val) ? 'selected="selected"' : '');
            }
            $out .= '>' . $value . '</option>';
        }
        /*
        foreach($rcs_real as $k => $v)
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
        */
        $out .= '</optgroup>';

        $out .= '<optgroup label="Esterni">';
        foreach($rcs_to_add as $k => $v)
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
        $out .= '</optgroup>';

        $out .= '</select>';
        return $out;
    }

    protected function _offers_subservices($key, $element)
    {
        $id = (isset($element['id'])) ? 'id="' . $element['id'] . '"' : '';
        $out = '<select ' . $id . ' name="' . $element['name'] . '" ';
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

        $db = Zend_Registry::get('dbAdapter');
        $user = Zend_Auth::getInstance()->getIdentity();

        $subservices = $db->fetchAll('select ss.subservice_id, ss.name as subservice_name, ss.id_service, s.name  as service_name
                from subservices ss left join services s on ss.id_service = s.service_id
                join subservice_internal ssi on ss.subservice_id = ssi.id_subservice and ssi.id_internal = ' . $db->quote($user->internal_id) . '
                join service_internal si on s.service_id = si.id_service and si.id_internal = ' . $db->quote($user->internal_id) . '
                order by s.name asc, ss.name asc');

        if(!$element['multiple'])
        {
            $val = isset($_GET[$element['name']]) ? $_GET[$element['name']] : '';
        }
        else
        {
            $key = substr($element['name'], 0, -2);
            $val = isset($_GET[$key]) ? $_GET[$key] : '';
        }

        $last_service_id = -1;
        foreach($subservices as $subservice)
        {
            if($subservice['id_service'] != $last_service_id)
            {
                if($last_service_id != -1)
                {
                    $out .= '</optgroup>';
                }
                $out .= '<optgroup id="service_' . $subservice['id_service'] . '" label="'. $subservice['service_name'] . '">';
                $last_service_id = $subservice['id_service'];
            }

            $out .= '<option value="' . $subservice['subservice_id'] . '" ';
            if(!$element['multiple'])
            {
                $out .= ($val == $subservice['subservice_id'] ? 'selected="selected"' : '');
            }
            else
            {
                $out .= (is_array($val) && in_array($subservice['subservice_id'], $val) ? 'selected="selected"' : '');
            }
            $out .= '>' . $subservice['subservice_name'] . '</option>';
        }

        $out .= '</optgroup>';

        $out .= '</select>';
        return $out;
    }

    protected function _rangeElement($key, $element)
    {
        $val = isset($_GET[$element['name']]) ? $_GET[$element['name']] : '';
        $id = (isset($element['id'])) ? 'id="' . $element['id'] . '"' : '';
        $divid = $element['id'] . '_slider_div';
        $out = '<input type="text" class="numeric-range search-text" name="' . $element['name'] . '" value="' . $val .'" ' . $id . ' /><div id="' . $divid . '" ';
        $min = isset($element['min']) ? $element['min'] : 0;
        $max = isset($element['max']) ? $element['max'] : 100;
        $step = isset($element['step']) ? $element['step'] : 1;
        $parts = explode('-', $val);
        $vmin = $parts[0];
        $vmax = isset($parts[1]) ? $parts[1] : null;
        $out .= 'data-min="' . $min . '" ';
        $out .= 'data-max="' . $max . '" ';
        $out .= 'data-step="' . $step . '" ';
        $out .= 'data-vmin="' . $vmin . '" ';
        $out .= 'data-vmax="' . $vmax . '" ';
        $out .= '></div>';
        return $out;
    }
    
    protected function _getUtils()
    {
        if(!$this->_utils)
        {
            $this->_utils = new Model_Common();
        }
        return $this->_utils;
    }
    
     public function getSearchArray($options = array())
    {
        return $this->_search;
    }
}