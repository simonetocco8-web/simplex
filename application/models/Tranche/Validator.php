<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 12-ott-2010
 * Time: 13.11.05
 */

class Model_Tranche_Validator extends Maco_Model_Validator_Abstract
{
    protected $_validators = array(
        'tranche_id' => array(
            'allowEmpty' => true
        ),
        'id_invoice' => array(
            'presence' => 'required',
            'Int'
        ),
        'importo' => array(
            'presence' => 'required',
        ),
        'pagato' => array(
            'allowEmpty' => true
        ),
        'date_expected' => array(
            'presence' => 'required',
        ),
        'date_done' => array(
            'allowEmpty' => true
        ),
        'note' => array(
            'allowEmpty' => true
        ),
        'status' => array(
            'allowEmpty' => true,
            'Int'
        ),
    
    );

    protected $_filters = array(
        'tranche_id' => 'StringTrim',
        'id_invoice' => 'StringTrim',
        'importo' => 'StringTrim',
        'pagato' => 'StringTrim',
        'date_expected' => 'StringTrim',
        'date_done' => 'StringTrim',
        'note' => 'StringTrim',
        'status' => 'StringTrim',
        'date_expected' => 'DateTime',
        'date_done' => 'DateTime',
    );
}
