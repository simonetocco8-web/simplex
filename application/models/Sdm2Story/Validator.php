<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marcello
 * Date: 26/04/13
 * Time: 16.59
 * To change this template use File | Settings | File Templates.
 */

class Model_Sdm2Story_Validator extends Maco_Model_Validator_Abstract
{
    protected $_validators = array(
        'sdm_story_id' => array(
            'allowEmpty' => 'required',
            'Int',
        ),
        'id_sdm' => array(
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
        'active' => array(
            'Int',
            'allowEmpty' => true,
        ),
        'id_status' => array(
            'Int',
            'allowEmpty' => true
        ),
        'text1' => array(
            'allowEmpty' => true
        ),
        'text2' => array(
            'allowEmpty' => true
        ),
        'date1' => array(
            'allowEmpty' => true
        ),
        'date2' => array(
            'allowEmpty' => true
        ),
        'id_user' => array(
            'Int',
            'allowEmpty' => true
        ),
    );

    protected $_filters = array(
        '*' => 'stringTrim',
    );
}
