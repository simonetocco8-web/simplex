<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 12-ott-2010
 * Time: 13.11.05
 * To change this template use File | Settings | File Templates.
 */

class Model_Moment_Validator extends Maco_Model_Validator_Abstract
{
    protected $_validators = array(
        'moment_id' => array(
            'allowEmpty' => true
        ),
        'id_offer' => array(
            'presence' => 'required',
            'Int'
        ),
        'index' => array(
            'allowEmpty' => true
        ),
        'importo' => array(
            'presence' => 'required',
	    ),
        'importo_real' => array(
            'allowEmpty' => true
	    ),
        'tipologia' => array(
            'presence' => 'required',
	    ),
        'expected_date' => array(
            'allowEmpty' => true
	    ),
        'fatturazione' => array(
            'allowEmpty' => true,
            'Int'
	    ),
        'fatturato' => array(
            'allowEmpty' => true,
            'Int'
	    ),
        'date_fatturato' => array(
            'allowEmpty' => true,
	    ),
        'done' => array(
            'allowEmpty' => true,
            'Int'
	    ),
        'date_done' => array(
            'allowEmpty' => true
	    ),
        'closed' => array(
            'allowEmpty' => true,
            'Int'
	    ),
        'date_closed' => array(
            'allowEmpty' => true
	    ),
        'p_valore_g_uomo' => array(
            'allowEmpty' => true,
			'Float'
        ),
        'p_n_incontri' => array(
            'allowEmpty' => true
        ),
        'p_ore_studio' => array(
            'allowEmpty' => true
        ),
        'o_ore_previste' => array(
            'allowEmpty' => true
        ),
        'c_ore_studio' => array(
            'allowEmpty' => true
	    ),
        'c_ore_azienda' => array(
            'allowEmpty' => true
	    ),
        'c_ore_certificazione' => array(
            'allowEmpty' => true
	    ),
        'c_sal' => array(
            'allowEmpty' => true
	    ),
        'c_n_incontri' => array(
            'allowEmpty' => true
	    ),
        'c_ore_viaggio' => array(
            'allowEmpty' => true
	    ),
        'c_n_km' => array(
            'allowEmpty' => true
	    ),
        'c_costo_km' => array(
            'allowEmpty' => true
	    ),
        'c_certificato_ente' => array(
            'allowEmpty' => true
	    ),
        'c_certificato_data' => array(
            'allowEmpty' => true
	    ),
        'c_note' => array(
            'allowEmpty' => true
	    ),
        'c_riesame_produzione' => array(
            'allowEmpty' => true
	    ),
        'c_riesame_amministrazione' => array(
            'allowEmpty' => true
	    ),
        'c_riesame_direzione' => array(
            'allowEmpty' => true
	    ),
        'c_pl_importo' => array(
            'allowEmpty' => true
	    ),
        'c_pl_note' => array(
            'allowEmpty' => true
	    ),
        'id_invoice' => array(
            'allowEmpty' => true,
            'Int'
        ),
        'i_prezzo' => array(
            'allowEmpty' => true
        ),
        'i_sconto' => array(
            'allowEmpty' => true
        ),
        'i_iva' => array(
            'allowEmpty' => true
        ),
    );

    protected $_filters = array(
     //   '*' => 'StringTrim',
        'expected_date' => 'DateTime',
        'date_done' => 'DateTime',
        'date_fatturato' => 'DateTime',
        'date_closed' => 'DateTime',
        'c_certificato_data' => 'DateTime',
    );
}
