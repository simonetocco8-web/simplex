<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 12-ott-2010
 * Time: 15.56.43
 * To change this template use File | Settings | File Templates.
 */

class Model_Order_Validator extends Maco_Model_Validator_Abstract
{   
    protected $_validators = array(
        'order_id' => array(
            'allowEmpty' => true,
            'Int',
	    ),
        'created_by' => array(
            'allowEmpty' => true,
            'Int',
	    ),
        'date_created' => array(
            'allowEmpty' => true,
	    ),
        'modified_by' => array(
            'allowEmpty' => true,
            'Int',
	    ),
        'date_modified' => array(
            'allowEmpty' => true,
	    ),
        'id_offer' => array(
            'presence' => 'required',
            'Int'
	    ),
        'id_order' => array(
            'presence' => 'required',
        ),
        'internal_code' => array(
            'presence' => 'required',
        ),
        'code_order' => array(
            'presence' => 'required',
        ),
        'year' => array(
            'allowEmpty' => true,
            'Int',
	    ),
        'rali_date' => array(
            'allowEmpty' => true,
	    ),
        'valore_g_uomo' => array(
            'allowEmpty' => true,
	    ),
        'n_incontri' => array(
            'allowEmpty' => true,
	    ),
        'n_ore_studio' => array(
            'allowEmpty' => true,
	    ),
        'id_dtg' => array(
            'allowEmpty' => true,
            'Int',
	    ),
        'id_status' => array(
            'presence' => 'required',
            'Int'
	    ),
       'date_chiusura_richiesta' => array(
            'allowEmpty' => true,
	    ),
        'note' => array(
            'allowEmpty' => true,
	    ),
         'note_pianificazione' => array(
            'allowEmpty' => true,
        ),
        'note_consuntivo' => array(
            'allowEmpty' => true,
        ),
        'ente' => array(
            'allowEmpty' => true,
        ),
        'sal' => array(
            'allowEmpty' => true,
            'Int',
        ),
    );

    protected $_filters = array(
       // '*' => 'stringTrim',
        'rali_date' => 'DateTime',
        'date_chiusura_richiesta' => 'DateTime',
    );
}
