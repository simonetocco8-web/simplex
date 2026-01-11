<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 01-dec-2010
 * Time: 11.04.09 
 */

class Model_Invoice extends Maco_Model_Abstract
{
    protected $invoice_id;
    
    protected $created_by;
    
    protected $date_created;

    protected $modified_by;

    protected $date_modified;
    
    protected $id_internal;
    
    protected $id_company;

    protected $code_invoice;

    protected $date_invoice;
    
    protected $date_end;

    protected $importo;

    protected $id_tipo_pagamento;
    protected $tipo_pagamento_name;

    protected $id_iban;
    protected $iban_name;
    protected $iban;
    protected $bank;

    protected $note;
    
    protected $status;
    
    protected $type;

    protected $trasferta;
    protected $varie;
    protected $varie_iva;

    protected $company;
    protected $order;
    
    protected $tranches = array();
    
    protected $moments = array();
}

