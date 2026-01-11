<?php

class CommonController extends Zend_Controller_Action
{
	public function init()
	{
		$ajaxContext = $this->_helper->getHelper('AjaxContext')
            ->addActionContext('dd', 'json')
		    ->addActionContext('tbl', 'json')
		    ->addActionContext('address', 'json')
            ->addActionContext('dependentselect', 'html')
		    ->initContext();
	}
	public function ddAction()
	{
		$db = Zend_Registry::get('dbAdapter');

		$q = $this->_request->getParam('q');

		$values = $db->fetchAll('select id, nomeprovincia as text from province where nomeprovincia like \'' . $q . '%\'');

		echo json_encode($values);
		exit;
	}

    public function addressAction()
    {
        $search = $this->_request->getParam('search', false);

        if(!$search)
        {
            echo json_encode(array());
            exit;
        }

        $model = new Model_Common();
        $values = $model->getAddress($search);

        $response = array();

        foreach($values as $v)
        {
            if($this->_request->getParam('pair', false))
            {
                $response[] = array($v['cap'], $v['cap'] . ' ' . $v['localita'] . ' (' . $v['provincia'] . ')');
            }
            else
            {
                $response[] = array($v['provincia'], $v['cap'] . ' ' . $v['localita'] . ', ' . $v['provincia'], array(
                    'cap' => $v['cap'],
                    'localita' => $v['localita'],
                    'provincia' => $v['provincia'],
                ));
            }
        }

        echo json_encode($response);
        exit;
    }
    
    public function dependentselectAction()
    {
        $model = new Model_Common();
        
        // TODO: supponiamo che sia sempre un intero
        $value = (int) $this->_request->getParam('value', 0);
        $self_table = $this->_request->getParam('self_table');
        $parent_field = $this->_request->getParam('parent_field');
        $self_label_field = $this->_request->getParam('self_label_field');
        
        $self_field = $this->_request->getParam('self_field');
        $this->view->selectItems = $model->getArrayForSelectElementSimple($self_table, $parent_field, $self_label_field, $self_field . ' = ' . $value);
    }
    
    public function tblAction()
    {
        
        $where_value = $this->_request->getParam('where_value', FALSE);
        
        if($where_value === FALSE)
        {
            echo json_encode(array());
            exit;
        }
        
        $where_field = $this->_request->getParam('where_field');
        $field = $this->_request->getParam('field');
        $label = $this->_request->getParam('label', false);
        $table = $this->_request->getParam('table');
        
        $db = Zend_Registry::get('dbAdapter');
        
        $select = $db->select();
        
        $select->from($table, ($label ? array($field, $label) : $field))
            ->where($where_field . ' = ?', $where_value);
        
        $values = $db->fetchAll($select);
        
        $response = array();
        
        foreach($values as $v)
        {
            $response[] = ($label) ? array($v[$field], $v[$label]) : array($v[$field], $v[$field]);
        }
        
        echo json_encode($response);
        exit;
    } 
    
    public function usersAction()
    {
        $db = Zend_Registry::get('dbAdapter');
        
        $select = $db->select();

        $user = Zend_Auth::getInstance()->getIdentity();

        $select->from('users', 'username')
            ->joinLeft('contacts', 'contacts.contact_id = users.id_contact', array('nome', 'cognome'))

            ->joinLeft('users_internals', 'users_internals.id_user = user_id', array())
            ->where('users_internals.id_internal = ?', $user->internal_id)

            ->where('users.deleted = 0 and active = 1')
            ->order('username ASC');

        $type = $this->_request->getParam('type', false);
        if($type)
        {
            $users_repo = Maco_Model_Repository_Factory::getRepository('user');
            $type_where = $users_repo->getTypeWhere(strtoupper($type));
            $select->where($type_where);
        }

        $values = $db->fetchAll($select);
        
        $response = array();
        
        foreach($values as $v)
        {
            $response[] = array(
                $v['username'] . ' - ' . $v['nome'] . ' ' . $v['cognome'],
                $v['username'] . ' - ' . $v['nome'] . ' ' . $v['cognome']
            );
        }
        
        echo json_encode($response);
        exit;
    }
    
    public function companiesAction()
    {
        $db = Zend_Registry::get('dbAdapter');
        
        $select = $db->select();
        
        $select->from('companies', array('ragione_sociale', 'company_id'))
            ->where('deleted = 0')
            ->order('ragione_sociale ASC');
        
        $type = $this->_request->getParam('type', false);       
        switch($type)
        {
            case 'promotori':
                $select->where('is_promotore = 1');
                break;
        }
        
        $values = $db->fetchAll($select);
        
        $response = array();
        
        foreach($values as $v)
        {
            $response[] = array($v['company_id'], $v['ragione_sociale']);
        }
        
        echo json_encode($response);
        exit;
    }

}