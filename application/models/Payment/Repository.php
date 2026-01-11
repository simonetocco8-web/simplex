<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 7-set-2010
 * Time: 11.16.13
 * To change this template use File | Settings | File Templates.
 */

class Model_Payment_Repository
{
    /**
     * Invoices mysql mapper
     *
     * @var Model_Invoice_Mapper
     */
    protected $_paymentMapper;

    public function __construct()
    {
        $this->_paymentMapper = new Model_Payment_Mapper();
    }

    public function getNewPayment()
    {
        $new = new Model_Payment();
        return $new;
    }

    public function find($id)
    {
        $item = $this->_paymentMapper->find($id);
        return $item;
    }

    public function save($item)
    {
        return $this->_paymentMapper->save($item);
    }

    public function delete($id)
    {
        return $this->_paymentMapper->delete($id);
    }

    /**
     * SAves the ne payment che for the tranche status and for the invoice status
     *
     * @param mixed $data
     * @param mixed $prefix
     */
    public function saveFromData($data, $prefix = '')
    {
        $payment = NULL;
        $payment_old_importo = 0;
        if(!empty($data['payment_id']))
        {
            $payment = $this->find($data['payment_id']);
            $payment_old_importo = $payment->importo();
        }
        else
        {
            $payment = new Model_Payment();
        }

        $payment->setValidatorAndFilter(new Model_Payment_Validator());
        $payment->setData($data, $prefix);
        if($payment->isValid())
        {
            $tranche_repo = Maco_Model_Repository_Factory::getRepository('tranche');
            $tranche = $tranche_repo->findWithDependenciesById($data['id_tranche']);
            $tranche->setValidatorAndFilter(new Model_Tranche_Validator());
            $importo_da_pagare = $tranche->getImportoDaPagare() + $payment_old_importo;
            $data['importo'] = (double) $data['importo'];
            if( bccomp($data['importo'], $importo_da_pagare, 2) == 1)
            {
                return array('L\'importo inserito &egrave; maggiore dell\'importo totale da pagare');
            }
            if($data['importo'] <= 0)
            {
                return array('L\'importo inserito deve essere maggiore di 0');
            }

            Maco_Model_TransactionManager::beginTransaction();

            try
            {
                $payment_id = $this->_paymentMapper->save($payment);

                $tranche->pagato += $payment->importo;

                $check_invoice = false;

                if(bccomp($payment->importo, $importo_da_pagare, 2) == 0)
                {
                    // chiudiamo la tranche - code = 2
                    $tranche->status = 2;
                    // controlliamo se la fattura � da chiudere
                    $check_invoice = true;
                }
                else
                {
                    // chiudiamo la tranche - code = 2
                    $tranche->status = 1;
                }

                if( ! $tranche->isValid() || ! $tranche_id = $tranche_repo->save($tranche))
                {
                    Maco_Model_TransactionManager::rollback();
                    return array('Problemi nell\'aggiornamento del momento di pagamento!');
                }

                if($check_invoice)
                {
                    $invoice_repo = Maco_Model_Repository_Factory::getRepository('invoice');

                    if(!$invoice_repo->updateStatusByInvoiceId($tranche->id_invoice))
                    {
                        Maco_Model_TransactionManager::rollback();
                        return array('Problemi nell\'aggiornamento dello stato della fattura!');
                    }
                }

                Maco_Model_TransactionManager::commit();
                return $payment_id;
            }
            catch(Exception $e)
            {
                Maco_Model_TransactionManager::rollback();
                return array($e->getMessage());
            }
        }
        else
        {
            return $payment->getInvalidMessages();
        }
    }

    public function findByIdTranche($id_tranche)
    {
        return $this->_paymentMapper->fetch(array('id_tranche' => $id_tranche));
    }

    public function getTotal($options)
    {
        $db = $this->_get_db();
        $select = $db->select();
        $select->from('payments', array('incasso' => new Zend_Db_Expr('sum(payments.importo)')))
                ->joinLeft('tranches', 'tranche_id = payments.id_tranche', array())
                ->joinLeft('invoices', 'invoice_id = tranches.id_invoice', array())
                ->joinLeft('offers', '1 = 1', array())
                ->joinLeft('orders', 'offer_id = orders.id_offer', array())
                ->where('offer_id in (select m.id_offer from moments m where m.id_invoice = invoice_id)');

        $search = isset($options['search']) ? $options['search'] : array();

        if(isset($search['id_rco']) && !empty($search['id_rco']))
        {
            $select->where('offers.id_rco = ' . implode(' or offers.id_rco = ', $search['id_rco']));
        }

        if(isset($search['id_dtg']) && !empty($search['id_dtg']))
        {
            $select->where('orders.id_dtg = ' . implode(' or orders.id_dtg = ', $search['id_dtg']));
        }

        if (isset($search['rc']) && !empty($search['rc']))
        {
            $where = ' exists (select orders_rcos.id_order from orders_rcos where orders.order_id = orders_rcos.id_order and orders_rcos.rco in (';
            foreach($search['rc'] as $rc)
            {
                $where .= $db->quote($rc) . ', ';
            }
            $where = substr($where, 0, -2) . '))';
            $select->where($where);
        }

        if (isset($search['date_done']) && $search['date_done'] != '')
        {
            $parts = explode('-', $search['date_done']);

            if(count($parts) == 2)
            {
                $select->where('payments.date_done >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and payments.date_done <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('payments.date_done = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
            }
        }


        return $db->fetchOne($select);
    }

    /**
     * Returns the db adapter
     *
     * @return Zend_Db_Adapter_Abstract
     */
    protected function _get_db()
    {
        return Zend_Registry::get('dbAdapter');
    }
}
