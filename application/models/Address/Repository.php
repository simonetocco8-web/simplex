<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 8-ott-2010
 * Time: 15.11.28
 * To change this template use File | Settings | File Templates.
 */

class Model_Address_Repository
{
    /**
     * Users mysql mapper
     *
     * @var Model_Address_Mapper
     */
    protected $_addressMapper;

    public function __construct()
    {
        $this->_addressMapper = new Model_Address_Mapper();
    }

    public function getNewAddress()
    {
        $new = new Model_Address();
        return $new;
    }

    public function find($id)
    {
        $item = $this->_addressMapper->find($id);
        return $item;
    }

    public function save($item)
    {
        return $this->_addressMapper->save($item);
    }

    public function findByContact($id_contact)
    {
        return $this->_addressMapper->fetch(array('id_contact' => $id_contact));
    }

    public function findByCompany($id_company)
    {
        return $this->_addressMapper->fetch(array('id_company' => $id_company));
    }

    public function delete($id)
    {
        return $this->_addressMapper->delete($id);
    }
    
     public function getFromData($data, $prefix = '')
    {
        $address = new Model_Address();
        $address->setValidatorAndFilter(new Model_Address_Validator());
        
        $data['regione'] = $this->_findRegioneFromProvincia($data['provincia']);
        
        $address->setData($data, $prefix);
        $address->isValid();
        return $address;
    }

    public function saveFromData($data, $prefix = '')
    {
        $address = new Model_Address();
        $address->setValidatorAndFilter(new Model_Address_Validator());
        
        $data['regione'] = $this->_findRegioneFromProvincia($data['provincia']);
        
        $address->setData($data, $prefix);
        if($address->isValid())
        {
            Maco_Model_TransactionManager::beginTransaction();

            try
            {
                $address_id = $this->_addressMapper->save($address);

                Maco_Model_TransactionManager::commit();
                return $address_id;
            }
            catch(Exception $e)
            {
                Maco_Model_TransactionManager::rollback();
                return array($e->getMessage());
            }
        }
        else
        {
            return $address->getInvalidMessages();
        }
    }
    
    protected function _findRegioneFromProvincia($provincia)
    {
        $db = $this->_addressMapper->getDbAdapter();
        
        return $db->fetchOne('select r.nome from regioni r, province p where p.nome = ' 
            . $db->quote($provincia) . ' and p.id_regione = r.regione_id');
    }
}
