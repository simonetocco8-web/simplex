<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 8-ott-2010
 * Time: 14.59.25
 * To change this template use File | Settings | File Templates.
 */

class Model_Contact_Validator extends Maco_Model_Validator_Abstract
{
    protected $_validators = array(
        'contact_id' => array(
            'allowEmpty' => true
	    ),/*
        'created_by' => array(
            'allowEmpty' => true,
            'Int',
	    ),
        'date_created' => array(
            'allowEmpty' => true,
	    ),
        'modified_by' => array(
            'allowEmpty' => true,
            'Int',
	    ),
        'date_modified' => array(
            'allowEmpty' => true,
	    ),
	    'deleted' => array(
            'allowEmpty' => true,
	    ),*/
        'id_contact_title' => array(
            'allowEmpty' => true
        ),
        'nome' => array(
            'allowEmpty' => true
	    ),
        'cognome' => array(
            'presence' => 'required'
	    ),
	    'description' => array(
	        'allowEmpty' => true
	    ),
	    'id_company' => array(
            'allowEmpty' => true
	    ),
	);

	protected $_filters = array(
        '*' => 'StringTrim',
	);
}
