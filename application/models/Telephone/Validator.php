<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 8-ott-2010
 * Time: 14.46.46
 * To change this template use File | Settings | File Templates.
 */

class Model_Telephone_Validator extends Maco_Model_Validator_Abstract
{
    protected $_validators = array(
        'telephone_id' => array(
            'allowEmpty' => true
	    ),/*
        'created_by' => array(
            'allowEmpty' => 'required',
            'Int',
	    ),
        'date_created' => array(
            'allowEmpty' => 'required',
	    ),
        'modified_by' => array(
            'allowEmpty' => 'required',
            'Int',
	    ),
        'date_modified' => array(
            'allowEmpty' => 'required',
	    ),*/
        'number' => array(
            'presence' => 'required'
	    ),
	    'description' => array(
            'allowEmpty' => true
	    ),
	    'id_company' => array(
            'allowEmpty' => true
	    ),
        'id_contact' => array(
            'allowEmpty' => true
	    ),
	);

	protected $_filters = array(
        '*' => 'StringTrim',
	);
}
