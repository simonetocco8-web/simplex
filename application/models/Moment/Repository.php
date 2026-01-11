<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 12-ott-2010
 * Time: 13.17.50
 * To change this template use File | Settings | File Templates.
 */

class Model_Moment_Repository
{
    /**
     * Moments mysql mapper
     *
     * @var Model_Moment_Mapper
     */
    protected $_momentMapper;

    public function __construct()
    {
        $this->_momentMapper = new Model_Moment_Mapper();
    }

    public function getNewMoment()
    {
        $offer = new Model_Moment();
        return $offer;
    }

    public function find($id)
    {
        $moment = $this->_momentMapper->find($id);
        return $moment;
    }

    public function delete($moment_id)
    {
        return $this->_momentMapper->delete($moment_id);
    }
    
    public function findWithDependencies($id)
    {
        $moment = $this->_momentMapper->find($id);
        
        $ordersRepo = Maco_Model_Repository_Factory::getRepository('order');
        $moment->order = $ordersRepo->findWithDependenciesByIdOffer($moment->id_offer);
        unset($ordersRepo);
        
        $invoicesRepo = Maco_Model_Repository_Factory::getRepository('invoice');
        
        return $moment;
    }

    public function save($moment)
    {
        return $this->_momentMapper->save($moment);
    }

    public function getMoments($sort = 'username', $dir = 'ASC', $search = array())
    {
        $db = $this->_momentMapper->getDbAdapter();

        $select = $db->select();

        /* SELECT */

        $select->order($sort . ' ' . $dir);

        return $db->fetchAll($select);
    }

    public function getMomentsIdForInvoice($id_invoice)
    {
        $db = $this->_momentMapper->getDbAdapter();

        return $db->fetchCol('select moment_id from moments where id_invoice = ' . $db->quote($id_invoice));
    }

    public function findByOffer($id_offer)
    {
        return $this->_momentMapper->fetch(array('id_offer' => $id_offer));
    }
    
    public function findByInvoice($id_invoice)
    {
        return $this->_momentMapper->fetch(array('id_invoice' => $id_invoice));
    }

    public function saveFromData($data, $prefix = '')
    {
        if(isset($data['moment_id']) && $data['moment_id'] != '')
        {
            $moment = $this->find($data['moment_id']);
            if(!isset($data['done']))
            {
                $data['done'] = $moment->done;
            }
        }
        else
        {
            $moment = new Model_Moment();
            if(!isset($data['done']))
            {
                $data['done'] = 0;
            }
        }
        $moment->setValidatorAndFilter(new Model_Moment_Validator());
        $moment->setData($data, $prefix);
        if($moment->isValid())
        {
            Maco_Model_TransactionManager::beginTransaction();

            try
            {
                $moment_id = $this->_momentMapper->save($moment);

                Maco_Model_TransactionManager::commit();
                return $moment_id;
            }
            catch(Exception $e)
            {
                Maco_Model_TransactionManager::rollback();
                return array($e->getMessage());
            }
        }
        else
        {
            return $moment->getInvalidMessages();
        }
    }
    
    public function getFromData($data, $prefix = '')
    {
        $moment = new Model_Moment();
        $moment->setValidatorAndFilter(new Model_Moment_Validator());
        $moment->setData($data, $prefix);
        $moment->isValid();
        
        return $moment;
    }
}
