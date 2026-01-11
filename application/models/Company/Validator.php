<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 7-set-2010
 * Time: 11.08.25
 * To change this template use File | Settings | File Templates.
 */

class Model_Company_Validator extends Maco_Model_Validator_Abstract
{
    protected $_validators = array(
        'company_id' => array(
            'allowEmpty' => true
        ),
        'deleted' => array(
            'allowEmpty' => true,
            'Int'
        ),
        'ragione_sociale' => array(
            'presence' => 'required'
        ),
        'cf' => array(
            array('Alnum', true),
            'allowEmpty' => true
        ),
        'pec' => array(
            'EmailAddress',
            'allowEmpty' => true
        ),
        'partita_iva' => array(
            array('Alnum', true),
            'presence' => 'required'
        ),
        'fatturato' => array(
            'Int',
            'presence' => 'required'
        ),
        'organico_medio' => array(
            'Int',
            'presence' => 'required'
        ),
        'iban' => array(
            array('Alnum', true),
            'allowEmpty' => true
        ),
        'status' => array(
            'allowEmpty' => true
        ),
        'segnalato_da' => array(
            'allowEmpty' => true,
        ),
        'rco' => array(
            'allowEmpty' => true,
            'Int'
        ),
        'categoria' => array(
            'allowEmpty' => true,
            'Int'
        ),
        'ea' => array(
            'allowEmpty' => true,
            'Int'
        ),
        'organico_medio' => array(
            'allowEmpty' => true,
            'Int'
        ),
        'fatturato' => array(
            'allowEmpty' => true,
            'Int'
        ),
        'conosciuto_come' => array(
            'allowEmpty' => true,
            'Int'
        ),
        'is_cliente' => array(
            'allowEmpty' => true
        ),
        'is_fornitore' => array(
            'allowEmpty' => true
        ),
        'is_partner' => array(
            'allowEmpty' => true
        ),
        'is_promotore' => array(
            'allowEmpty' => true
        ),
        'id_promotore' => array(
            'Int',
            'allowEmpty' => true
        ),
        'promotore_percent' => array(
            'allowEmpty' => true
        ),
        'note' => array(
            'allowEmpty' => true
        ),
        'prodotti' => array(
            'allowEmpty' => true
        ),
    );

    protected $_filters = array(
//        '*' => 'StringTrim'
    );
}
