<?php

class Model_Moment_Search extends Maco_Model_Search_Abstract
{
    protected $_search = array(

        'id_company' => array(
            'label' =>'Azienda',
            'name' => 'id_company',
            'field' => 'ragione_sociale',
            'type' => 'autocomplete',
            'source' => 'url',
            'url' => '/offers/companies',
            'multiple' => true
        ),

        'date_done' => array(
            'label' =>'Data Chiusura Lavori',
            'name' => 'date_done',
            'type' => 'date',
            'max' => false,
            'min' => false,
            'multiple' => false
        ),

        'stato' => array(
            'label' => 'Stato',
            'name' => 'stato[]',
            'type' => 'select',
            'source' => 'array',
            'array' => array(
                'working' => 'In Lavorazione',
                'completed' => 'Completato',
                'invoiced' => 'Fatturato',
            ),
            'multiple' => true
        ),

        'id_service' => array(
            'label' =>'Servizio',
            'name' => 'id_service[]',
            'id' => 'id_service',
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

    );
    
    
    public function getSearchArray($options = array())
    {
        return $this->_search;
    }
}
