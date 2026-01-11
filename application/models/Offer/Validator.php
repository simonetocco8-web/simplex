<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 12-ott-2010
 * Time: 12.26.42
 * To change this template use File | Settings | File Templates.
 */

class Model_Offer_Validator extends Maco_Model_Validator_Abstract
{
    protected $_validators = array(
        'offer_id' => array(
            'allowEmpty' => true,
            'Int',
	    ),
        'deleted' => array(
            'allowEmpty' => true,
            'Int'
	    ),
        'internal_code' => array(
            'allowEmpty' => true,
	    ),
        'year' => array(
            'allowEmpty' => true,
            'Int',
	    ),
        'id_offer' => array(
            'allowEmpty' => true,
            'Int'
	    ),
        'code_offer' => array(
            'presence' => 'required',
        ),
        'revision' => array(
            'allowEmpty' => true,
            'Int',
	    ),
        'id_status' => array(
            'allowEmpty' => true,
            'Int',
	    ),
        'id_company' => array(
            'presence' => 'required',
            'Int',
	    ),
        'id_service' => array(
            'presence' => 'required',
            'Int',
	    ),
        'id_subservice' => array(
            'presence' => 'required',
            'Int',
	    ),
        'luogo' => array(
            'allowEmpty' => true,
	    ),
        'id_promotore' => array(
            'allowEmpty' => true,
            'Int',
	    ),
        'promotore_percent' => array(
            'allowEmpty' => true,
	    ),
        'promotore_value' => array(
            'allowEmpty' => true,
        ),
        'promotore_value_flag' => array(
            'allowEmpty' => true,
        ),
        'date_offer' => array(
            'presence' => 'required',
	    ),
        'validita' => array(
            'presence' => 'required',
	    ),
        'date_end' => array(
            'presence' => 'required',
	    ),
        'date_sent' => array(
            'allowEmpty' => true,            
	    ),
        'date_accepted' => array(
            'allowEmpty' => true,
	    ),
        'subject' => array(
            'allowEmpty' => true,
	    ),
        'note' => array(
            'allowEmpty' => true,
            //'allowEmpty' => true
	    ),
        'scadenze' => array(
            'allowEmpty' => true
	    ),
        'offer_importo' => array(
            'allowEmpty' => true
        ),
        'id_company_contact' => array(
            'allowEmpty' => true,
            'Int'
        ),
        'id_interest' => array(
            'presence' => 'required',
            'Int'
        ),
        'sconto' => array(
            'allowEmpty' => true,
			'Float'
	    ),
        'id_pagamento' => array(
            'presence' => 'required',
            'Int'
        ),
        'id_rco' => array(
            'presence' => 'required',
            'Int'
	    ),
        'segnalato_da' => array(
            'allowEmpty' => true,
        ),
        'active' => array(
            'allowEmpty' => true,
        ),
    );

    protected $_filters = array(
     //   '*' => 'stringTrim',
        'date_offer' => 'DateTime',
        'date_end' => 'DateTime',
        'date_sent' => 'DateTime',
        'date_accepted' => 'DateTime',
    );
}
