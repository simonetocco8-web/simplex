<?php

class Model_Task_Search extends Maco_Model_Search_Abstract
{
    protected $_search = array(
        'id_who' => array(
            'label' =>'Chi',
            'name' => 'id_who[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'users',
            'table_id' => 'user_id',
            'table_field' => 'username',
            'multiple' => true
        ),
        'what' => array(
            'label' =>'Cosa',
            'name' => 'what[]',
            'type' => 'select',
            'source' => 'array',
            'sectors' => array(),
            'multiple' => true
        ),
        'when' => array(
            'label' =>'Quando',
            'name' => 'when',
            'type' => 'date',
            'max' => false,
            'min' => false,
            'multiple' => false
        ),
        'id_company' => array(
            'label' =>'Azienda',
            'name' => 'id_company',
            'field' => 'ragione_sociale',
            'type' => 'autocomplete',
            'source' => 'url',
            'url' => 'offers/companies',
            'multiple' => true
        ),
        /*
        'id_receiver' => array(
            'label' =>'Contatto',        
            'name' => 'id_contact[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'contacts',
            'table_id' => 'contact_id',
            'table_field' => array('nome', 'cognome'),
            'multiple' => true
        ),
        'id_receiver' => array(
            'label' =>'Contatto',
            'name' => 'id_contact[]',
            'field' => 'ragione_sociale',
            'type' => 'autocomplete',
            'source' => 'url',
            'url' => 'offers/companies',
            'multiple' => true
        ),
        */
        'sector' => array(
            'label' =>'Settore',
            'name' => 'sector[]',
            'type' => 'select',
            'source' => 'array',
            'array' =>  array(),
            'multiple' => true
        ),
        'where' => array(
            'label' => 'Luogo',
            'name' => 'where',
            'type' => 'text',
            'multiple' => true
        ),
        'done' => array(
            'label' =>'Eseguito',
            'name' => 'done',
            'type' => 'select',
            'source' => 'array',
            'array' => array(
                '' => '',
                '1' => 'si',
                '0' => 'no',
                '-1' => 'annullato'
            ),
            'multiple' => false
        ),
    );

    public function __construct()
    {
        $auth = Zend_Auth::getInstance()->getIdentity();

        if(!$auth->user_object->has_permission('tasks', 'view')
                && $auth->user_object->has_permission('tasks', 'view_own'))
        {
            $this->unsetWho();
        }
    }

    public function setCompany($id_company)
    {
        $this->_search['id_company'] = array(
            'type' => 'hidden',
            'name' => 'id_company',
            'value' => $id_company,
        );
    }

    public function unsetWhen()
    {
        unset($this->_search['when']);
    }

    public function unsetWho()
    {
        unset($this->_search['id_who']);
    }

    public function getSearchArray($options = array())
    {
        $task = new Model_Task();
        $this->_search['sector']['array'] = array('' => '') + $task->getSectors();
        $whats = $task->getWhats();
        // no "parlato con"
        unset($whats[4]);
        $this->_search['what']['array'] = array('' => '') + $whats;
        return $this->_search;
    }
}
