<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 12-ott-2010
 * Time: 15.53.28
 * To change this template use File | Settings | File Templates.
 */

class Model_Order extends Maco_Model_Abstract
{
    protected $order_id;

    protected $created_by;

    protected $date_created;

    protected $modified_by;

    protected $date_modified;

    protected $id_offer;
    
    protected $id_order;
    
    protected $internal_code;
    
    protected $code_order;

    protected $year;

    protected $rali_date;

    protected $valore_g_uomo;
    protected $ore_hm;
    protected $gg_hm;

    protected $n_incontri;

    protected $n_ore_studio;

    protected $id_dtg;
    protected $dtg_name;

    protected $date_chiusura_richiesta;

    protected $id_status;
    protected $status_name;

    protected $note;
    protected $note_da_offerta;

    protected $offer;

    protected $rcos;
    
    protected $note_pianificazione;
    
    protected $note_consuntivo;
    
    protected $ente;

    protected $sal;

    protected $of_internal; //???


    public function getStatusColor(){
        switch($this->id_status){
            case 1:
                return 'd9db00';
            case 2:
                return 'ffd24d';
            case 3:
                return '36d900';
            case 4:
                return 'ff9673';
            case 5:
                return 'ff3333';
        }

        return 'c0c0c0';
    }
}
