<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 6-set-2010
 * Time: 11.31.01
 */
 
class Model_Task_Validator extends Maco_Model_Validator_Abstract
{
    protected $_validators = array(
        'task_id' => array(
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
        'id_parent' => array(
            'allowEmpty' => 'required',
            'Int',
        ),
        'id_who' => array(
            'presence' => 'required',
            'Int',
	    ),
        'id_subject' => array(
            'Int',
            'allowEmpty' => true
	    ),
        'subject' => array(
            'allowEmpty' => true
	    ),
        'id_company' => array(
            'Int',
            'allowEmpty' => 'required'
	    ),
        'what' => array(
            'Int',
            'presence' => 'required'
	    ),
        'subject_data' => array(
            'allowEmpty' => true
        ),
        'with_auto' => array(
            'allowEmpty' => true
        ),
        'when' => array(
            'allowEmpty' => true,
        ),
        'when_flexible' => array(
            'allowEmpty' => true
        ),
        'sector' => array(
            'Int',
            'allowEmpty' => true
        ),
        'finishs' => array(
            'allowEmpty' => true,
        ),
        'time_expected' => array(
            'ValidFloat',
            'allowEmpty' => true,
	    ),
        'done' => array(
            'allowEmpty' => true,
            'Int',
        ),
        'date_done' => array(
            'allowEmpty' => true,
        ),
        'info' => array(
            'allowEmpty' => true,
        ),
        'note' => array(
            'allowEmpty' => true
        ),
        'id_offer' => array(
            'allowEmpty' => true,
            'Int',
        ),
    );

    protected $_filters = array(
        //'*' => 'stringTrim',
        'when' => 'DateTime',
        'date_done' => 'DateTime',
        //'time_expected' => 'localizedToNormalized'
    );
}
