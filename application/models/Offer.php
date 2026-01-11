<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 12-ott-2010
 * Time: 11.37.30
 * To change this template use File | Settings | File Templates.
 */

class Model_Offer extends Maco_Model_Abstract
{
    protected $offer_id;

    protected $created_by;

    protected $date_created;

    protected $modified_by;

    protected $date_modified;

    protected $deleted;

    protected $code_offer;
    
    protected $internal_code;

    protected $year;

    protected $id_offer;

    protected $revision;

    protected $id_status;
    protected $status_name;

    protected $id_company;
    protected $company;

    protected $id_service;
    protected $service_name;
    protected $service_code;

    protected $id_subservice;
    protected $subservice_name;
    protected $subservice_code;

    protected $luogo;
    
    protected $id_promotore;
    protected $promotore_name;

    protected $promotore_percent;
    protected $promotore_value;
    protected $promotore_value_flag;

    protected $date_offer;

    protected $validita;

    protected $date_end;

    protected $date_sent;

    protected $date_accepted;

    protected $subject;

    protected $note;

    protected $scadenze;

    protected $id_company_contact;
    protected $company_contact_name;

    protected $id_interest;
    protected $interest_name;

    protected $sconto;
    
    protected $id_pagamento;
    protected $pagamento_name;

    protected $id_rco;
    protected $rco_name;

    protected $segnalato_da;

    protected $id_approver;

    protected $active;

    protected $id_order;

    protected $offer_importo;

    protected $moments = array();

    protected $total;
    protected $total_raw;

    protected $revisions;
}
