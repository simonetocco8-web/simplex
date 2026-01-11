<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 12-ott-2010
 * Time: 13.07.13
 * To change this template use File | Settings | File Templates.
 */
 

class Model_Moment extends Maco_Model_Abstract
{
    protected $moment_id;

    protected $id_offer;  
    protected $offer;  
    protected $order;  

    protected $index;
    
    protected $importo;
    
    protected $importo_real;
    
    protected $tipologia;
    
    protected $expected_date;

    protected $fatturazione;

    protected $done;

    protected $date_done;

    protected $fatturato;

    protected $closed;
    
    protected $date_closed;
    
    protected $o_ore_previste;
    
    protected $p_valore_g_uomo;
    
    protected $p_n_incontri;
    
    protected $p_ore_studio;
    
    protected $c_ore_studio;

    protected $c_ore_azienda;

    protected $c_ore_certificazione;
    
    protected $c_sal;
    
    protected $c_n_incontri;
    
    protected $c_ore_viaggio;
    
    protected $c_n_km;

    protected $c_costo_km;

    protected $c_certificato_ente;
    
    protected $c_certificato_data;

    protected $c_note;
    
    protected $c_riesame_produzione;

    protected $c_riesame_amministrazione;

    protected $c_riesame_direzione;

    protected $c_pl_importo;
    
    protected $c_pl_note;
    
    protected $id_invoice;
    
    protected $i_prezzo;
    
    protected $i_sconto;
    
    protected $i_iva;
    
    /**
    * Calcola e ritorna l'importo da consuntivo
    * 
    * @return float
    */
    public function getImportoConsuntivo()
    {
        if(!$this->order)
        {
            $repo = Maco_Model_Repository_Factory::getRepository('order');
            $this->order = $repo->findWithDependenciesByIdOffer($this->id_offer);
            if(!$this->order)
            {
                return 0;
            }
        }
        
        $ore = ($this->c_ore_studio + $this->c_ore_azienda + $this->c_ore_certificazione) * $this->order->valore_g_uomo / 8;
        $km = $this->c_n_km * $this->c_costo_km;
        $tot = $ore + $km;
        $tot += $this->c_pl_importo;
        return $tot;
    }
    
    /**
    * Calcola e ritorna l'importo  scontato da offerta
    * 
    * @return float
    */
    public function getImportoScontato()
    {
        if(!$this->order)
        {
            $repo = Maco_Model_Repository_Factory::getRepository('offer');
            $this->offer = $repo->findWithDependenciesById($this->id_offer);           
            $sconto = $this->offer->sconto;
        }
        else
        {
            $sconto = $this->order->offer->sconto;
        }
                                   
        if($sconto == '')
        {
            return $this->importo;
        }
        
        return ($this->importo - ($this->importo * $sconto / 100));
    }
    
    /**
    * Ritorna l'importo per la fattura. Propone quello da contratto 
    * se non presente quello da fattura
    * 
    * @return float
    */
    public function getImportoForInvoice()
    {
        if(!$this->i_prezzo || $this->i_prezzo == '')
        {
            return $this->getImportoScontato();
        }
        
        return $this->i_prezzo;
    }
    
    /**
    * Ritorna l'iva per la fattura. Propone 20 %
    * se non presente quello da fattura
    * 
    * @return float
    */
    public function getIvaForInvoice()
    {
        if(!$this->i_iva || $this->i_iva == '')
        {
            return 21;
        }
        
        return $this->i_iva;
    }
}