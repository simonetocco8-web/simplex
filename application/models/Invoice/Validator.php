<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 12-ott-2010
 * Time: 13.11.05
 * To change this template use File | Settings | File Templates.
 */

class Model_Invoice_Validator extends Maco_Model_Validator_Abstract
{
    protected $_validators = array(
        'invoice_id' => array(
            'allowEmpty' => true
        ),
        'code_invoice' => array(
            'presence' => 'required',
        ),
        'id_internal' => array(
            'presence' => 'required',
        ),
        'id_company' => array(
            'presence' => 'required',
        ),
        'date_invoice' => array(
            'presence' => 'required',
        ),
        'date_end' => array(
            'presence' => 'required',
        ),
        'importo' => array(
            'presence' => 'required',
        ),
        'id_tipo_pagamento' => array(
            'presence' => 'required',
            'Int'
        ),
        'id_iban' => array(
            'presence' => 'required',
            'Int'
        ),
        'note' => array(
            'allowEmpty' => true
        ),
        'status' => array(
            'allowEmpty' => true,
            'Int'
        ),
        'type' => array(
            'allowEmpty' => true,
            'Int'
        ),
        'trasferta' => array(
            'allowEmpty' => true,
        ),
        'varie' => array(
            'allowEmpty' => true,
        ),
        'varie_iva' => array(
            'allowEmpty' => true,
        ),
    );

    protected $_filters = array(
        'invoice_id' => 'StringTrim',
        'code_invoice' => 'StringTrim',
        'id_internal' => 'StringTrim',
        'id_company' => 'StringTrim',
        'date_invoice' => 'StringTrim',
        'date_end' => 'StringTrim',
        'importo' => 'StringTrim',
        'trasferta' => 'StringTrim',
        'varie' => 'StringTrim',
        'varie_iva' => 'StringTrim',
        'id_tipo_pagamento' => 'StringTrim',
        'note' => 'StringTrim',
        'status' => 'StringTrim',
        'type' => 'StringTrim',

        'date_invoice' => 'DateTime',
        'date_end' => 'DateTime',
    );
}
