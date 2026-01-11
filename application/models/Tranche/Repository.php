<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 7-set-2010
 * Time: 11.16.13
 * To change this template use File | Settings | File Templates.
 */

class Model_Tranche_Repository
{
    /**
     * Invoices mysql mapper
     *
     * @var Model_Invoice_Mapper
     */
    protected $_trancheMapper;

    public function __construct()
    {
        $this->_trancheMapper = new Model_Tranche_Mapper();
    }

    public function getNewInvoice()
    {
        $new = new Model_Invoice();
        return $new;
    }

    public function find($id)
    {
        $item = $this->_trancheMapper->find($id);
        return $item;
    }

    public function save($item)
    {
        return $this->_trancheMapper->save($item);
    }

    public function delete($id)
    {
        return $this->_trancheMapper->delete($id);
    }
    
    public function findByInvoice($id_invoice)
    {
        $db = $this->_trancheMapper->getDbAdapter();
        $select = $db->select();
        
        $select->from('tranches', '*')
            ->where('id_invoice = ?', $id_invoice);
        
        return $db->fetchAll($select);
    }

    public function saveFromData($data, $prefix = '')
    {
        $tranche = new Model_Tranche();
        $tranche->setValidatorAndFilter(new Model_Tranche_Validator());
        $tranche->setData($data, $prefix);
        if($tranche->isValid())
        {
            Maco_Model_TransactionManager::beginTransaction();

            try
            {
                $tranche_id = $this->_trancheMapper->save($tranche);

                Maco_Model_TransactionManager::commit();
                return $tranche_id;
            }
            catch(Exception $e)
            {
                Maco_Model_TransactionManager::rollback();
                return array($e->getMessage());
            }
        }
        else
        {
            return $tranche->getInvalidMessages();
        }
    }
                       
    public function getTranches($options)
    {
        $db = $this->_trancheMapper->getDbAdapter();

        $select = $db->select();

        $select->from('tranches', array('tranche_id', 'importo', 'date_expected', 'status', 'pagato'))
            ->joinLeft('invoices', 'invoice_id = id_invoice', array('code_invoice'));
            
        $auth = Zend_Auth::getInstance()->getIdentity();
        $select->where('id_internal = ?', strtolower($auth->internal_id));

        if(isset($options['count']) && $options['count'])
        {
            //$select->order('offers.date_modified desc');
            $select->order('offers.date_created desc');
            $select->limit($options['count'], 0);
        }
        else
        {
            if(isset($options['sort']) && $options['sort'])
            {
                $select->order($options['sort'] . ' ' . $options['dir']);
            }
            else
            {
                $select->order('date_expected asc');
            }
        }
        
        if(isset($options['search']))
        {
            if(is_array($options['search']) && !empty($options['search']))
            {
            }
            else if(is_string($options['search']))
            {
                $select->where($search);
            }
        }
        
        if(isset($options['per_page']) && $options['per_page'] !== NULL)
        {
            $tranches = Zend_Paginator::factory($select);
            $tranches->setItemCountPerPage($per_page);
            $tranches->setCurrentPageNumber(isset($options['page']) ? $options['page'] : 1);        
        }
        else
        {
            $tranches = $db->fetchAll($select);
        }

        return $tranches;
    }
    
    public function findWithDependenciesById($id)
    {
        $db = $this->_trancheMapper->getDbAdapter();

        $select = $db->select();
        
        $select->from('tranches', '*')->where('tranche_id = ?', $id);
        
        $data = $db->fetchRow($select);
        
        $tranche = new Model_Tranche();
        
        $tranche->setData($data);
        
        $invoices_repo = Maco_Model_Repository_Factory::getRepository('invoice');
        
        $tranche->invoice = $invoices_repo->findWithDependenciesById($tranche->id_invoice);
        
        $payments_repo = Maco_Model_Repository_Factory::getRepository('payment');
        
        $tranche->payments = $payments_repo->findByIdTranche((int) $id);
         
        return $tranche;
    }
}
