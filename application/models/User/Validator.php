<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 7-ott-2010
 * Time: 17.08.19
 * To change this template use File | Settings | File Templates.
 */

class Model_User_Validator extends Maco_Model_Validator_Abstract
{
    protected $_validators = array(
        'user_id' => array(
            'allowEmpty' => true,
            'Int',
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
	    ),*/
        'deleted' => array(
            'allowEmpty' => true,
            'Int'
	    ),
        'username' => array(
            'presence' => 'required',
	    ),
        'password' => array(
            'allowEmpty' => true,
	    ),
        'password_salt' => array(
            'allowEmpty' => true,
	    ),
        'id_role' => array(
            'presence' => 'required',
            'Int',
	    ),
        'active' => array(
            'Int',
            'allowEmpty' => true,
	    ),
        'id_contact' => array(
            'Int',
            'allowEmpty' => true,
	    ),
    );

    protected $_filters = array(
        //'*' => 'stringTrim',
        //'when' => 'DateTime',
        //'date_done' => 'DateTime'
    );
}
