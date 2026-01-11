<?php

class Model_User_Search_Report extends Maco_Model_Search_Abstract
{
    protected $_search = array(
        'period' => array(
            'label' =>'Periodo',
            'name' => 'period',
            'type' => 'date',
            'max' => false,
            'min' => false,
            'multiple' => false
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
}