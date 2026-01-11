<?php

class Model_Sdm2_Search extends Maco_Model_Search_Abstract
{
    protected $_statuses = array();

    protected $_search = array(

        'code' => array(
            'label' => 'Progressivo',
            'name' => 'code',
            'type' => 'text',
            'multiple' => true
        ),
        'created_by' => array(
            'label' =>'Emittente',
            'name' => 'created_by[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'users',
            'table_id' => 'user_id',
            'table_field' => 'username',
            'multiple' => true
        ),
        'id_status' => array(
            'label' =>'Stato',
            'name' => 'id_status[]',
            'type' => 'select',
            'source' => 'array',
            'array' => array(),
            'multiple' => true
        ),
        'description' => array(
            'label' => 'Descrizione',
            'name' => 'description',
            'type' => 'text',
            'multiple' => true
        ),
        'date_problem' => array(
            'label' =>'Data Segnalazione',
            'name' => 'date_problem',
            'type' => 'date',
            'max' => false,
            'min' => false,
            'multiple' => false
        ),
        'cause' => array(
            'label' => 'Causa',
            'name' => 'cause',
            'type' => 'text',
            'multiple' => true
        ),
        'area' => array(
            'label' => 'Area',
            'name' => 'area',
            'type' => 'text',
            'multiple' => true
        ),
        'id_responsible' => array(
            'label' =>'Responsabile',
            'name' => 'id_responsible[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'users',
            'table_id' => 'user_id',
            'table_field' => 'username',
            'multiple' => true
        ),
        /*
        'date_set_responsible' => array(
            'label' =>'Data Responsabile',
            'name' => 'date_set_responsible',
            'type' => 'date',
            'max' => false,
            'min' => false,
            'multiple' => false
        ),
        */
        /*
        'date_feedback' => array(
            'label' =>'Data di Feedback',
            'name' => 'date_feedback',
            'type' => 'date',
            'max' => false,
            'min' => false,
            'multiple' => false
        ),
        */
        'id_solver' => array(
            'label' =>'Risolutore',
            'name' => 'id_solver[]',
            'type' => 'select',
            'source' => 'table',
            'table_name' => 'users',
            'table_id' => 'user_id',
            'table_field' => 'username',
            'multiple' => true
        ),
        'treatment' => array(
            'label' => 'Trattamento',
            'name' => 'treatment',
            'type' => 'text',
            'multiple' => true
        ),
        'date_expected_resolution' => array(
            'label' =>'Data Attesa di Risoluzione',
            'name' => 'date_expected_resolution',
            'type' => 'date',
            'max' => false,
            'min' => false,
            'multiple' => false
        ),
        /*
        'resolution' => array(
            'label' => 'Risoluzione',
            'name' => 'resolution',
            'type' => 'text',
            'multiple' => true
        ),
        */
        'date_resolution' => array(
            'label' =>'Data di Risoluzione',
            'name' => 'date_resolution',
            'type' => 'date',
            'max' => false,
            'min' => false,
            'multiple' => false
        ),

        'verification' => array(
            'label' => 'Verifica',
            'name' => 'verification',
            'type' => 'text',
            'multiple' => true
        ),
        'date_verification' => array(
            'label' =>'Data di Verifica',
            'name' => 'date_verification',
            'type' => 'date',
            'max' => false,
            'min' => false,
            'multiple' => false
        ),
	);       

    
    public function getSearchArray($options = array())
    {
        $this->_search['id_status']['array'] = Model_Sdm2::getStati();
        return $this->_search;
    }
}
