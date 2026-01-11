<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 6-set-2010
 * Time: 11.31.01
 */
 
class Model_Sdm_Validator extends Maco_Model_Validator_Abstract
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
        'year' => array(
            'Int',
            'allowEmpty' => true,
        ),
        'id_status' => array(
            'Int',
            'allowEmpty' => true
        ),
        'problem' => array(
            'presence' => 'required',
        ),
        'date_problem' => array(
            'presence' => 'required',
        ),
        'cause' => array(
            'allowEmpty' => true
        ),
        'area' => array(
            'allowEmpty' => true
        ),
        'id_responsible' => array(
            'Int',
            'presence' => 'required',
        ),
        'date_feedback' => array(
            'allowEmpty' => true
        ),
        'date_set_responsible' => array(
            'allowEmpty' => true
        ),
        'treatment' => array(
            'allowEmpty' => true
        ),
        'id_solver' => array(
            'allowEmpty' => true,
            'Int'
        ),
        'date_expected_resolution' => array(
            'allowEmpty' => true
        ),
        'date_set_solver' => array(
            'allowEmpty' => true
        ),
        'resolution' => array(
            'allowEmpty' => true
        ),
        'date_resolution' => array(
            'allowEmpty' => true
        ),
        'verification' => array(
            'allowEmpty' => true
        ),
        'date_verification' => array(
            'allowEmpty' => true
        ),
    );

    protected $_filters = array(
        '*' => 'stringTrim',
        'date_problem' => 'DateTime',
        'date_feedback' => 'DateTime',
        'date_set_responsible' => 'DateTime',
        'date_expected_resolution' => 'DateTime',
        'date_set_solver' => 'DateTime',
        'date_resolution' => 'DateTime',
        'date_verification' => 'DateTime',
    );
}
