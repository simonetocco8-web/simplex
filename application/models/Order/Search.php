<?php

class Model_Order_Search extends Maco_Model_Search_Abstract
{
    protected $_search = array(
        'code_order' => array(
            'label' => 'Codice Commessa',
            'name' => 'code_order',
            'type' => 'text',
            'multiple' => true
        ),
        'id_status' => array(
            'label' =>'Stato',
            'name' => 'id_status[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'order_status',
            'table_id' => 'order_status_id',
            'table_field' => 'name',
            'multiple' => true
        ),
        'rali_date' => array(
            'label' =>'Data R.A.L.I.',
            'name' => 'rali_date',
            'type' => 'date',
            'max' => false,
            'min' => false,
            'multiple' => false
        ),
        'date_chiusura_richiesta' => array(
            'label' =>'Data Chiusura Richiesta',
            'name' => 'date_chiusura_richiesta',
            'type' => 'date',
            'max' => false,
            'min' => false,
            'multiple' => false
        ),
        'date_completed' => array(
            'label' =>'Data Chiusura Commessa',
            'name' => 'date_completed',
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
            'url' => 'orders/companies',
            'multiple' => true
        ),
        'id_promotore' => array(
            'label' =>'Promotore',
            'name' => 'id_promotore',
            'field' => 'ragione_sociale',
            'type' => 'autocomplete',
            'source' => 'url',
            'url' => 'companies/tbl/is_promotore/1',
            'multiple' => true
        ),
        'id_service' => array(
            'label' =>'Servizio',
            'name' => 'id_service[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'services',
            'table_id' => 'service_id',
            'table_field' => 'name',
            'multiple' => true
        ),
        'id_subservice' => array(
            'label' => 'Sotto-servizio',
            'name' => 'id_subservice[]',
            'type' => 'offers_subservices',
            'multiple' => true
        ),
        'id_ea' => array(
            'label' =>'EA',
            'name' => 'id_ea[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'ea',
            'table_id' => 'ea_id',
            'table_field' => 'name',
            'multiple' => true
        ),
        'ente' => array(
            'label' =>'Ente',
            'name' => 'ente[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'orders',
            'table_id' => 'ente',
            'table_field' => 'ente',
            'multiple' => true
        ),
        /*
        'ente' => array(
            'label' =>'Ente',
            'name' => 'ente',
            'field' => 'ente',
            'type' => 'autocomplete',
            'source' => 'url',
            'url' => 'orders/enti',
            'multiple' => true
        ),
        */
        /*
        'id_subservice' => array(
            'label' =>'Sotto-servizio',
            'name' => 'id_subservice[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'subservices',
            'table_id' => 'subservice_id',
            'table_field' => 'name',
            'depends_on' => 'id_service',
            'depends_name' => 'id_service',
            'multiple' => true
        ),
        */
        'rco' => array(
            'label' =>'Responsabile Commerciale',
            'name' => 'id_rco[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'users',
            'table_id' => 'user_id',
            'table_field' => 'username',
            'multiple' => true
        ),
        'id_dtg' => array(
            'label' =>'DTG',
            'name' => 'id_dtg[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'users',
            'table_id' => 'user_id',
            'table_field' => 'username',
            'multiple' => true
        ),
       	'rc' => array(
            'label' => 'RC',
            'name' => 'rc[]',
            'type' => 'users_rcs',
            'multiple' => true
        ),
        'importo' => array(
            'label' => 'Importo',
            'name' => 'importo',
            'id' => 'importo',
            'type' => 'numeric_range',
            'min' => 0,
            'max' => 100000,
            'step' => 100,
        ),
	);       
                                                                   
    
    public function getSearchArray($options = array())
    {
        $user = Zend_Auth::getInstance()->getIdentity()->user_object;

        if(!$user->has_permission('orders', 'view_budget'))
        {
            unset($this->_search['importo']);
        }

        return $this->_search;
    }
}
