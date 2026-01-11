<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 11-ott-2010
 * Time: 14.18.38
 * To change this template use File | Settings | File Templates.
 */

class Model_Website_Validator extends Maco_Model_Validator_Abstract
{
    protected $_validators = array(
        'website_id' => array(
            'allowEmpty' => true
        ),
        'url' => array(
            'presence' => 'required'
        ),
        'description' => array(
            'allowEmpty' => true
        ),
        'id_company' => array(
            'presence' => 'required'
	    )
    );

    protected $_filters = array(
        '*' => 'StringTrim',
    );
}
