<?php

class Model_Company_Search extends Maco_Model_Search_Abstract
{
    protected $_search = array(
        'ragione_sociale' => array(
            'label' => 'Ragione Sociale',
            'name' => 'ragione_sociale',
            'type' => 'text',
            'multiple' => true
        ),
        'cf' => array(
            'label' => 'Codice Fiscale',
            'name' => 'cf',
            'type' => 'text',
            'multiple' => true
        ),
        'partita_iva' => array(
            'label' => 'Partita IVA',
            'name' => 'partita_iva',
            'type' => 'text',
            'multiple' => true
        ),
        'regione' => array(
            'label' =>'Regione',        
            'name' => 'regione[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'regioni',
            'table_id' => 'nome',
            'table_field' => 'nome',
            'multiple' => true
        ),
        'provincia' => array(
            'label' =>'Provincia',        
            'name' => 'provincia[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'province',
            'table_id' => 'nome',
            'table_field' => 'nome',
            'multiple' => true
        ),

        'comune' => array(
            'label' =>'Comune',
            'name' => 'cap',
            'field' => 'comune',
            'type' => 'autocomplete',
            'source' => 'url',
            'url' => 'common/address/pair/1',
            'multiple' => true,
            'id' => 'comune'
        ),

        'id_promotore' => array(
            'label' =>'Promotore',
            'name' => 'id_promotore',
            'field' => 'ragione_sociale',
            'type' => 'autocomplete',
            'source' => 'url',
            'url' => 'common/companies/type/promotori',
            'multiple' => true,
            'id' => 'id_promotore'
        ),

        /*
        'status' => array(
            'label' =>'Stato',
            'name' => 'status[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'status',
            'table_id' => 'status_id',
            'table_field' => 'name',
            'multiple' => true
        ),
        */
        'rco' => array(
            'label' =>'RCO',        
            'name' => 'rco[]',
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
        'categoria' => array(
            'label' =>'Categoria',
            'name' => 'categoria[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'categories',
            'table_id' => 'category_id',
            'table_field' => 'name',
            'multiple' => true
        ),
        'ea' => array(
            'label' =>'EA',
            'name' => 'ea[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'ea',
            'table_id' => 'ea_id',
            'table_field' => 'name',
            'multiple' => true
        ),
        'organico_medio' => array(
            'label' =>'Organico Medio',
            'name' => 'organico_medio[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'organici_medi',
            'table_id' => 'organico_medio_id',
            'table_field' => 'name',
            'multiple' => true
        ),
        'fatturato' => array(
            'label' =>'Fatturato',
            'name' => 'fatturato[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'fatturati',
            'table_id' => 'fatturato_id',
            'table_field' => 'name',
            'multiple' => true
        ),
        'conosciuto_come' => array(
            'label' =>'Conosciuto come',
            'name' => 'conosciuto_come[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'conosciuto_come',
            'table_id' => 'conosciuto_come_id',
            'table_field' => 'name',
            'multiple' => true
        ),
        /*
        'is_partner' => array(
            'label' =>'Partner',
            'name' => 'is_partner',
            'type' => 'select',
            'source' => 'array',
            'array' => array(
                '' => '',
                '1' => 'si',
                '0' => 'no',
            ),
            'multiple' => false
        ),
        */
        'tipologia' => array(
            'label' => 'Tipologia',
            'name' => 'tipologia[]',
            'type' => 'select',
            'source' => 'array',
            'array' => array(
                'is_cliente2' => 'Cliente',
                'is_cliente1' => 'Potenziale Cliente',
                'is_promotore' => 'Promotore',
                'is_fornitore' => 'Fornitore',
                'is_partner' => 'Partner'
            ),
            'multiple' => true
        ),
        'date_created' => array(
            'label' =>'Data Creazione',
            'name' => 'date_created',
            'type' => 'date',
            'max' => false,
            'min' => false,
            'multiple' => false
        ),
        'addresses' => array(
            'label' =>'Sede',
            'name' => 'addresses',
            'type' => 'text',
            'multiple' => true
        ),
        'excluded' => array(
            'label' => 'Escluse',
            'name' => 'excluded',
            'type' => 'select',
            'source' => 'array',
            'array' => array(
                '0' => 'No',
                '1' => 'Si',
            ),
            'multiple' => false
        ),
    );
    
    
    public function getSearchArray($options = array())
    {
        $user = Zend_Auth::getInstance()->getIdentity()->user_object;

        if(!$user->has_permission('companies', 'view_excluded'))
        {
            unset($this->_search['excluded']);
        }

        //$this->_search['id_office']['where'] = 'id_internal = ' . $user->internal_id;
        return $this->_search;
    }
}
