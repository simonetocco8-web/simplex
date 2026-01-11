<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 01-dec-2010
 * Time: 11.04.09 
 */

class Model_Tranche extends Maco_Model_Abstract
{
    protected $tranche_id;
    
    protected $created_by;
    
    protected $date_created;

    protected $modified_by;

    protected $date_modified;
    
    protected $id_invoice;
    protected $invoice;

    protected $importo;
    protected $pagato;
    
    protected $date_expected;
    
    protected $date_done;
    
    protected $note;
    
    protected $status;
    
    protected $payments = array();
    
    /**
    * Calcola e ritorna l'importo da pagare per questa tranchce
    * 
    * @return float
    */
    public function getImportoDaPagare()
    {
        return $this->importo - $this->pagato;
    }
    
    public static $statusDescriptions = array(
        '0' => 'Da pagare',
        '1' => 'Pagato Parzialmente',
        '2' => 'Pagato'
    );
    
    /**
    * Ritorna la descrizione dello stato per lo stato passato
    * 
    * @return float
    */
    public function getStatoDescriptionByStatusId($status_code)
    {
        if(array_key_exists($status_code, self::$statusDescriptions))
        {
            return self::$statusDescriptions[$status_code];
        }
        return 'sconosciuto';
    }
    
    public static function getStatues()
    {
        return self::$statusDescriptions;
    }
}

