<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 12-ott-2010
 * Time: 13.11.05
 */

class Model_Payment_Validator extends Maco_Model_Validator_Abstract
{
    protected $_validators = array(
        'payment_id' => array(
            'allowEmpty' => true
        ),
        'id_tranche' => array(
            'presence' => 'required',
            'Int'
        ),
        'importo' => array(
            'presence' => 'required',
        ),
        'date_done' => array(
            'allowEmpty' => true
        ),
        'note' => array(
            'allowEmpty' => true
        ),
    );

    protected $_filters = array(
        '*' => 'StringTrim',
        'date_done' => 'DateTime',
    );
}
