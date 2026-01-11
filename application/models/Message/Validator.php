<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 8-ott-2010
 * Time: 14.59.25
 * To change this template use File | Settings | File Templates.
 */

class Model_Message_Validator extends Maco_Model_Validator_Abstract
{
    protected $_validators = array(
        'message_id' => array(
            'allowEmpty' => true
        ),
        'created_by' => array(
            'allowEmpty' => true,
            'Int',
        ),
        'to' => array(
            'presence' => 'required',
            'Int',
        ),
        'title' => array(
            'presence' => 'required'
        ),
        'body' => array(
            'presence' => 'required'
        ),
        'read' => array(
            'allowEmpty' => true
        ),
        'dashboard' => array(
            'allowEmpty' => true,
			'Int',
        ),
        'type' => array(
            'allowEmpty' => true
        ),
        'uid' => array(
            'allowEmpty' => true,
        ),
        'no_delete' => array(
            'allowEmpty' => true,
        ),
        'deleted' => array(
            'allowEmpty' => true,
        ),
        'id_internal' => array(
            'presence' => 'required',
        )
    );

    protected $_filters = array(
        '*' => 'StringTrim',
    );
}
