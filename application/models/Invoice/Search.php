<?php

class Model_Invoice_Search extends Maco_Model_Search_Abstract
{
    protected $_search = array(
        'code_invoice' => array(
            'label' => 'Fattura',
            'name' => 'code_invoice',
            'type' => 'text',
            'multiple' => true
        ),
        'status' => array(
            'label' =>'Stato',
            'name' => 'status[]',
            'type' => 'select',
            'source' => 'array',
            'array' => array(
                '' => '',
                '1' => 'Chiusa',
                '0' => 'Aperta',
            ),
            'multiple' => true
        ),
        'type' => array(
            'label' =>'Tipo',
            'name' => 'type[]',
            'type' => 'select',
            'source' => 'array',
            'array' => array(
                '0' => 'Fattura',
                '1' => 'Nota Credito',
            ),
            'multiple' => true
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

        'date_invoice' => array(
            'label' =>'Data Emissione',
            'name' => 'date_invoice',
            'type' => 'date',
            'max' => false,
            'min' => false,
            'multiple' => false
        ),
        'date_end' => array(
            'label' =>'Data Scadenza',
            'name' => 'date_end',
            'type' => 'date',
            'max' => false,
            'min' => false,
            'multiple' => false
        ),

        'importo' => array(
            'label' =>'Importo',
            'name' => 'importo',
            'id' => 'importo',
            'type' => 'numeric_range',
            'max' => 100000,
            'min' => 0,
            'step' => 100,
        ),
        'id_pagamento' => array(
            'label' =>'Pagamento',
            'name' => 'id_pagamento[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'pagamenti',
            'table_id' => 'pagamento_id',
            'table_field' => 'name',
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
