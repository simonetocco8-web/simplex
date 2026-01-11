<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 8-ott-2010
 * Time: 15.08.12
 * To change this template use File | Settings | File Templates.
 */

class Model_Address_Validator extends Maco_Model_Validator_Abstract
{
    protected $_validators = array(
        'address_id' => array(
            'allowEmpty' => true
	    ),
        /*
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
        'via' => array(
            'allowEmpty' => true
	    ),
        'numero' => array(
            'allowEmpty' => true
	    ),
        'cap' => array(
            'presence' => 'required'
	    ),
        'localita' => array(
            'presence' => 'required'
	    ),
        'provincia' => array(
            'allowEmpty' => true
            //'presence' => 'required'
	    ),
        'regione' => array(
            'allowEmpty' => true
            //'presence' => 'required'
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
