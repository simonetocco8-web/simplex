<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marcello
 * Date: 26/04/13
 * Time: 16.59
 * To change this template use File | Settings | File Templates.
 */

class Model_Sdm2_Validator extends Maco_Model_Validator_Abstract
{

    protected $_validators = array(
        'sdm_id' => array(
            'allowEmpty' => 'required',
            'Int',
        ),
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
        ),
        'code' => array(
            'allowEmpty' => true,
        ),
        'internal_code' => array(
            'allowEmpty' => true,
        ),
        'year' => array(
            'Int',
            'allowEmpty' => true,
        ),
        'id_status' => array(
            'Int',
            'allowEmpty' => true
        ),
        'with_prevention' => array(
            'allowEmpty' => true
        ),
    );

    protected $_filters = array(
        '*' => 'stringTrim',
    );
}
