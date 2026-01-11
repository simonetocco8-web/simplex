<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 8-ott-2010
 * Time: 15.06.10
 * To change this template use File | Settings | File Templates.
 */

class Model_Address extends Maco_Model_Abstract
{
    protected $address_id;

    protected $created_by;

    protected $date_created;

    protected $modified_by;

    protected $date_modified;

    protected $via;

    protected $numero;

    protected $cap;

    protected $localita;

    protected $provincia;

    protected $regione;

    protected $description;

    protected $id_company;

    protected $id_contact;

    public function getCleanAddress()
    {
        $address = '';
        if($this->via != '')
        {
            $address .= $this->via;
        }
        if($this->numero != '')
        {
            $address .= ' ' . $this->numero;
        }
        if($address != '')
        {
            $address .= "\n";
        }
        if($this->cap != '')
        {
            $address .= $this->cap . ' - ';
        }
        if($this->localita != '')
        {
            $address .= $this->localita;
        }
        if($this->provincia != '')
        {
            $address .= ' (' . $this->provincia . ')';
        }
        if($this->regione != '')
        {
            $address .= ' ' . $this->regione;
        }

        return $address;
    }

    public function getFirstPartAddress()
    {
        $address = '';
        if($this->via != '')
        {
            $address .= $this->via;
        }
        if($this->numero != '')
        {
            $address .= ', ' . $this->numero;
        }
        return $address;
    }

    public function getLastPartAddress()
    {
        $address = '';
        if($this->cap != '')
        {
            $address .= $this->cap . ' ';
        }
        if($this->localita != '')
        {
            $address .= $this->localita;
        }
        if($this->provincia != '')
        {
            $provincia = $this->provincia;
            $db = Zend_Registry::get('dbAdapter');
            $prov = $db->fetchOne('select abbr from province where nome = ' . $db->quote($this->provincia));
            if($prov)
            {
                $provincia = $prov;
            }
            $address .= ' (' . $provincia . ')';
        }

        return $address;
    }
}
