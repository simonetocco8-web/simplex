<?php

class Model_Offer_Search extends Maco_Model_Search_Abstract
{
    protected $_search = array(
        'code_offer' => array(
            'label' => 'Codice Offerta',
            'name' => 'code_offer',
            'type' => 'text',
            'multiple' => true
        ),
        'id_status' => array(
            'label' =>'Stato Offerta',
            'name' => 'id_status[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'offer_status',
            'table_id' => 'offer_status_id',
            'table_field' => 'name',
            'multiple' => true
        ),
        'id_order_status' => array(
            'label' =>'Stato Commessa',
            'name' => 'id_order_status[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'order_status',
            'table_id' => 'order_status_id',
            'table_field' => 'name',
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
        /*
        'id_company_contact' => array(
            'label' =>'Contatto Azienda',        
            'name' => 'id_company_contact[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'contacts',
            'table_id' => 'contact_id',
            'table_field' => array('nome', 'cognome'),
            'multiple' => true
        ),
        */
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
        'id_rco' => array(
            'label' =>'RCO',
            'name' => 'id_rco[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'users',
            'table_id' => 'user_id',
            'table_field' => 'username',
            'multiple' => true
        ),
        'segnalato_da' => array(
            'label' => 'Segnalato da',
            'name' => 'segnalato_da',
            'type' => 'text',
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
        'promotore_percent' => array(
            'label' =>'Percentuale Partner',
            'name' => 'promotore_percent',
            'id' => 'promotore_percent',
            'type' => 'numeric_range',
            'max' => 100,
            'min' => 0,
            'step' => 1,
        ),
        'id_interest' => array(
            'label' =>'Interesse',
            'name' => 'id_interest[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'interests_levels',
            'table_id' => 'interests_level_id',
            'table_field' => 'name',
            'multiple' => true
        ),
        'date_offer' => array(
            'label' =>'Data Offerta',
            'name' => 'date_offer',
            'type' => 'date',
            'max' => false,
            'min' => false,
            'multiple' => false
        ),
        'date_accepted' => array(
            'label' =>'Data Aggiudicazione',
            'name' => 'date_accepted',
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
        /*
        'date_sent' => array(
            'label' =>'Data Offerta',
            'name' => 'date_offer',
            'type' => 'date',
            'max' => false,
            'min' => false,
            'multiple' => false
        ),
        'date_accepted' => array(
            'label' =>'Data Scadenza',
            'name' => 'date_end',
            'type' => 'date',
            'max' => false,
            'min' => false,
            'multiple' => false
        ),
        */
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
    );
}
