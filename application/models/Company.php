<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 7-set-2010
 * Time: 11.04.09
 * To change this template use File | Settings | File Templates.
 */

class Model_Company extends Maco_Model_Abstract
{
    protected $company_id;

    protected $created_by;

    protected $date_created;

    protected $modified_by;

    protected $date_modified;

    protected $deleted;

    protected $ragione_sociale;

    protected $cf;

    protected $partita_iva;

    protected $pec;

    protected $iban;

    protected $status;
    protected $status_name;

    protected $rco;
    protected $rco_name;

    protected $segnalato_da;

    protected $categoria;
    protected $categoria_name;

    protected $ea;
    protected $ea_name;

    protected $organico_medio;
    protected $organico_medio_name;

    protected $fatturato;
    protected $fatturato_name;

    protected $conosciuto_come;
    protected $conosciuto_come_name;

    /**
    * Questo flag pu� essere 0 => non cliente, 1 => potenziale cliente, 2 => cliente
    * 
    * @var mixed
    */
    protected $is_cliente;
    
    protected $is_promotore;
    protected $promotore_percent;
    
    protected $is_partner;
    
    protected $is_fornitore;
    
    protected $id_promotore;
    protected $promotore_name;
    
    protected $note;
    
    protected $prodotti;

    protected $addresses = array();

    protected $telephones = array();

    protected $mails = array();

    protected $contacts = array();

    protected $websites = array();
    
    protected $internals = array();

    protected $office_id;
    protected $office;

     public static $clienteOptions = array(
        '1' => 'Potenziale',
        '2' => 'Si',
        '0' => 'No'
    );
    
    public static function getClienteOptions()
    {
        return self::$clienteOptions;
    }
    
    /**
    * Ritorna la descrizione dello stato per lo stato passato
    * 
    * @return float
    */
    public function getClienteDescriptionByFlagValue($cliente_flag)
    {
        if(array_key_exists($cliente_flag, self::$clienteOptions))
        {
            return self::$clienteOptions[$cliente_flag];
        }
        return 'sconosciuto';
    }
    
    /**
    * Ritorna la tipologia azienda formattata per bene
    * 
    * @return string
    */
    public function getTipologia()
    {
        $sep = '';
        $out = '';
        if($this['is_cliente'] == 1) 
        {
            $sep = ' - ';
            $out .= 'Potenziale Cliente';
        }
        elseif($this['is_cliente'] == 2)
        {
            $sep = ' - ';
            $out .= '<b>Cliente</b>';
        }
        if($this['is_fornitore'])
        {
            $out .=  $sep . 'Fornitore'; 
            $sep = ' - ';
        }
        if($this['is_partner'])
        { 
            $out .= $sep . 'Partner';
        }
        if($this['is_promotore'])
        { 
            $out .= $sep . 'Promotore <span class="info">(default ' . $this['promotore_percent'] . '%)</span>'; 
        }
        
        return $out;
    }
}
