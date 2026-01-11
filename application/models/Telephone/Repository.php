<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 8-ott-2010
 * Time: 14.50.58
 * To change this template use File | Settings | File Templates.
 */

class Model_Telephone_Repository
{
    /**
     * Users mysql mapper
     *
     * @var Model_User_Mapper
     */
    protected $_telephoneMapper;

    public function __construct()
    {
        $this->_telephoneMapper = new Model_Telephone_Mapper();
    }

    public function delete($id)
    {
        return $this->_telephoneMapper->delete($id);
    }

    public function getNewTelephone()
    {
        $telephone = new Model_Telephone();
        return $telephone;
    }

    public function find($id)
    {
        $telephone = $this->_telephoneMapper->find($id);
        return $telephone;
    }

    public function save($telephone)
    {
        return $this->_telephoneMapper->save($telephone);
    }

    public function findByContact($id_contact)
    {
        return $this->_telephoneMapper->fetch(array('id_contact' => $id_contact));
    }

    public function findByCompany($id_company)
    {
        return $this->_telephoneMapper->fetch(array('id_company' => $id_company));
    }

    public function findByCompanyAndContact($id_company, $id_contact = null)
    {
        $where = array(
            'id_company' => $id_company
        );
        if($id_contact)
        {
            $where['id_contact'] = $id_contact;
        }
        
        return $this->_telephoneMapper->fetch($where);
    }

    public function getFromData($data, $prefix = '')
    {
        $telephone = new Model_Telephone();
        $telephone->setValidatorAndFilter(new Model_Telephone_Validator());
        $telephone->setData($data, $prefix);
        $telephone->isValid();
        return $telephone;
    }

    public function saveFromData($data, $prefix = '')
    {
        $telephone = new Model_Telephone();
        $telephone->setValidatorAndFilter(new Model_Telephone_Validator());
        $telephone->setData($data, $prefix);
        if($telephone->isValid())
        {
            Maco_Model_TransactionManager::beginTransaction();

            try
            {
                $telephone_id = $this->_telephoneMapper->save($telephone);

                Maco_Model_TransactionManager::commit();
                return $telephone_id;
            }
            catch(Exception $e)
            {
                Maco_Model_TransactionManager::rollback();
                return array($e->getMessage());
            }
        }
        else
        {
            return $telephone->getInvalidMessages();
        }
    }
}
