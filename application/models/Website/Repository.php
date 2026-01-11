<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 11-ott-2010
 * Time: 14.16.27
 * To change this template use File | Settings | File Templates.
 */

class Model_Website_Repository
{
    /**
     * Users mysql mapper
     *
     * @var Model_Website_Mapper
     */
    protected $_websiteMapper;

    public function __construct()
    {
        $this->_websiteMapper = new Model_Website_Mapper();
    }

    public function getNewWebsite()
    {
        $new = new Model_Website();
        return $new;
    }

    public function find($id)
    {
        $item = $this->_websiteMapper->find($id);
        return $item;
    }

    public function save($item)
    {
        return $this->_websiteMapper->save($item);
    }

    public function delete($id)
    {
        return $this->_websiteMapper->delete($id);
    }

    public function findByCompany($id_contact)
    {
        return $this->_websiteMapper->fetch(array('id_company' => $id_contact));
    }
    
    public function getFromData($data, $prefix = '')
    {
        $ws = new Model_Website();
        $ws->setValidatorAndFilter(new Model_Website_Validator());
        $ws->setData($data, $prefix);
        $ws->isValid();
        return $ws;
    }

    public function saveFromData($data, $prefix = '')
    {
        $ws = new Model_Website();
        $ws->setValidatorAndFilter(new Model_Website_Validator());
        $ws->setData($data, $prefix);
        if($ws->isValid())
        {
            Maco_Model_TransactionManager::beginTransaction();

            try
            {
                $ws_id = $this->_websiteMapper->save($ws);

                Maco_Model_TransactionManager::commit();
                return $ws_id;
            }
            catch(Exception $e)
            {
                Maco_Model_TransactionManager::rollback();
                return array($e->getMessage());
            }
        }
        else
        {
            return $ws->getInvalidMessages();
        }
    }
}
