<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 7-set-2010
 * Time: 11.15.59
 * To change this template use File | Settings | File Templates.
 */

class Model_Mail_Validator extends Maco_Model_Validator_Abstract
{
    protected $_validators = array(
        'mail_id' => array(
            'allowEmpty' => true
        ),
        'mail' => array(
            'EmailAddress',
            'presence' => 'required',
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
