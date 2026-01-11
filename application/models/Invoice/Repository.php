<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 7-set-2010
 * Time: 11.16.13
 * To change this template use File | Settings | File Templates.
 */

class Model_Invoice_Repository
{
    /**
     * Invoices mysql mapper
     *
     * @var Model_Invoice_Mapper
     */
    protected $_invoiceMapper;

    public function __construct()
    {
        $this->_invoiceMapper = new Model_Invoice_Mapper();
    }

    public function getNewInvoice()
    {
        $new = new Model_Invoice();
        return $new;
    }

    public function find($id)
    {
        $item = $this->_invoiceMapper->find($id);
        return $item;
    }

    public function save($item)
    {
        return $this->_invoiceMapper->save($item);
    }

    public function delete($id)
    {
        return $this->_invoiceMapper->delete($id);
    }

    public function saveFromData($data, $prefix = '')
    {
        $invoice = new Model_Invoice();
        $invoice->setValidatorAndFilter(new Model_Invoice_Validator());
        $invoice->setData($data, $prefix);

        $new = $invoice->invoice_id == '';

        if($new)
        {
            $invoice->status = 0;
        }

        $db = $this->_invoiceMapper->getDbAdapter();
        $select = $db->select();

        $aut = Zend_Auth::getInstance()->getIdentity();

        $invoice->id_internal = $aut->internal_id;

        if(isset($data['code_invoice']) && trim($data['code_invoice']) != '')
        {
            $invoice->code_invoice = $data['code_invoice'];
        }
        else
        {
            $select->from('invoices', 'code_invoice')
                ->where('id_internal = ?', $aut->internal_id)
                ->where('code_invoice is not null and code_invoice <> \'\'')
                ->order('date_created DESC')
                ->limit(1, 0);
            $last_code = $db->fetchOne($select);
            $last_code_parts = explode('-', $last_code);
            $year = date('Y');
            $invoice->code_invoice = $year . '-' . $aut->internal_abbr . '-';

            if(empty($last_code_parts) || $last_code_parts[0] != $year)
            {
                $invoice->code_invoice .= '1';
            }
            else
            {
                $invoice->code_invoice .= $last_code_parts[2] + 1;
            }
        }

        $importo = 0;
        foreach($data['importo-rata'] as $rata)
        {
            $importo += $rata;
        }

        // AGGIUNGERE TRASFERTA (non iva) E VARIE (iva)

        $invoice->importo = $importo;

        $invoice->date_end = $data['date-rata'][count($data['date-rata']) - 1];

        unset($select);
        $select = $db->select();
        $invoice->id_company = $db->fetchOne('select id_company from offers, moments where offer_id = moments.id_offer and moment_id = ' . $data['moment_id'][0]);

        if($invoice->isValid())
        {
            Maco_Model_TransactionManager::beginTransaction();
            try
            {
                $invoice_id = $this->_invoiceMapper->save($invoice);

                // 1. save data to the moments
                $moments_repo = Maco_Model_Repository_Factory::getRepository('moment');
                foreach($data['moment_id'] as $moment_id)
                {
                    $moment = $moments_repo->find($moment_id);
                    if(!$moment)
                    {
                        throw new Exception('no moment for this id');
                    }
                    $moment->id_invoice = $invoice_id;
                    $moment->i_prezzo = $data['importo_' . $moment_id];
                    $moment->i_sconto = $data['sconto_' . $moment_id];
                    $moment->i_iva = $data['iva_' . $moment_id];

                    $moment->setValidatorAndFilter(new Model_Moment_Validator());

                    if(!$moment->isValid())
                    {
                        throw new Exception('invalid moment data');
                    }
                    $moments_repo->save($moment);
                }

                // 2. save tranches
                $tranches_repo = Maco_Model_Repository_Factory::getRepository('tranche');

                $db->delete('tranches', 'id_invoice = ' . $db->quote($invoice->invoice_id));

                foreach($data['date-rata'] as $k => $d)
                {
                    //$tranches[$k]['date_expected'] = $d;
                    //$tranches[$k]['importo'] = $data['importo-rata'][$k];

                    $tranche = array(
                        'date_expected' => $d,
                        'importo' => $data['importo-rata'][$k],
                        'id_invoice' => $invoice_id
                    );

                    if( ! $tranches_repo->saveFromData($tranche) )
                    {
                        throw new Exception('invalid tranche data');
                    }
                }

                Maco_Model_TransactionManager::commit();
                return $invoice_id;
            }
            catch(Exception $e)
            {
                Maco_Model_TransactionManager::rollback();
                return array($e->getMessage());
            }
        }
        else
        {
            return $invoice->getInvalidMessages();
        }
    }

    public function saveNotaCredito($data, $prefix = '')
    {
        $invoice = new Model_Invoice();
        $invoice->setValidatorAndFilter(new Model_Invoice_Validator());
        $invoice->setData($data, $prefix);

        $invoice->status = 0;
        $invoice->type = 1;

        $invoice->importo = - $invoice->importo;

        $db = $this->_invoiceMapper->getDbAdapter();
        $select = $db->select();

        $aut = Zend_Auth::getInstance()->getIdentity();

        $invoice->id_internal = $aut->internal_id;

        $select->from('invoices', 'code_invoice')
                ->where('id_internal = ?', $aut->internal_id)
                ->where('code_invoice is not null and code_invoice <> \'\'')
                ->order('date_created DESC')
                ->limit(1, 0);
        $last_code = $db->fetchOne($select);
        $last_code_parts = explode('-', $last_code);
        $year = date('Y');
        $invoice->code_invoice = $year . '-' . $aut->internal_abbr . '-';

        if(empty($last_code_parts) || $last_code_parts[0] != $year)
        {
            $invoice->code_invoice .= '1';
        }
        else
        {
            $invoice->code_invoice .= $last_code_parts[2] + 1;
        }

        $invoice->code_invoice .= '-' . 'NC';

        unset($select);

        if($invoice->isValid())
        {
            Maco_Model_TransactionManager::beginTransaction();
            try
            {
                $invoice_id = $this->_invoiceMapper->save($invoice);

                Maco_Model_TransactionManager::commit();
                return $invoice_id;
            }
            catch(Exception $e)
            {
                Maco_Model_TransactionManager::rollback();
                return array($e->getMessage());
            }
        }
        else
        {
            return $mail->getInvalidMessages();
        }
    }

    public function pagaNotaCredito($data)
    {
        $invoice = $this->find($data['invoice_id']);

        if($invoice->status == 1)
        {
            return array('nota credito gi&agrave; pagata!');
        }

        unset($data['invoice_id']);
        $invoice->setValidatorAndFilter(new Model_Invoice_Validator());

        $invoice->setData($data);
        $invoice->status = 1;

        if($invoice->isValid())
        {
            try
            {
                $invoice_id = $this->_invoiceMapper->save($invoice);
                return $invoice_id;
            }
            catch(Exception $e)
            {
                return array($e->getMessage());
            }
        }
        else
        {
            return $mail->getInvalidMessages();
        }
    }

    public function getInvoices($sort = false, $dir = false, $search = array(), $count = null, $per_page = null)
    {
        $auth = Zend_Auth::getInstance()->getIdentity();

        $db = $this->_invoiceMapper->getDbAdapter();
        $select = $db->select();

        $select->from('invoices', array('code_invoice', 'date_invoice', 'date_end', 'importo', 'invoice_id', 'status'))
                ->joinLeft('offers', '1 = 1', array())
                ->where('offer_id in (select m.id_offer from moments m where m.id_invoice = invoice_id)')
                ->joinLeft('companies', 'company_id = invoices.id_company', array('ragione_sociale'))
                ->join(array('si' => 'service_internal'), 'si.id_service = offers.id_service and si.id_internal = ' . $db->quote($auth->internal_id))
                ->join(array('ssi' => 'subservice_internal'), 'ssi.id_subservice = offers.id_subservice and ssi.id_internal = ' . $db->quote($auth->internal_id))
        ;

        $auth = Zend_Auth::getInstance()->getIdentity();
        $select->where('invoices.id_internal = ?', $auth->internal_id);

        if($count)
        {
            //$select->order('offers.date_modified desc');
            $select->order('invoices.date_invoice asc');
            $select->limit($count, 0);
        }
        else
        {
            if($sort)
            {
                $select->order($sort . ' ' . $dir);
            }
            else
            {
                $select->order('invoices.date_invoice asc');
            }
        }

        if(is_array($search))
        {
            if (isset($search['code_invoice']) && $search['code_invoice'] != '')
            {
                $select->where('code_invoice like ' . $db->quote('%' . $search['code_invoice'] . '%'));
            }

            if (isset($search['status']) && !empty($search['status']))
            {
                $select->where('invoices.status = ' . implode(' or invoices.status = ', $search['status']));
            }

            if (isset($search['type']) && !empty($search['type']))
            {
                $select->where('type = ' . implode(' or type = ', $search['type']));
            }

            if (isset($search['id_company']) && !empty($search['id_company']))
            {
                $select->where('id_company = ' . implode(' or id_company = ', $search['id_company']));
            }

            if (isset($search['id_service']) && !empty($search['id_service']))
            {
                $select->where('offers.id_service = ' . implode(' or offers.id_service = ', $search['id_service']));
            }

            if (isset($search['id_subservice']) && !empty($search['id_subservice']))
            {
                $select->where('id_subservice = ' . implode(' or id_subservice = ', $search['id_subservice']));
            }


            if (isset($search['date_invoice']) && $search['date_invoice'] != '')
            {
                $parts = explode('-', $search['date_invoice']);

                if(count($parts) == 2)
                {
                    $select->where('date_invoice >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_invoice <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
                }
                else
                {
                    $select->where('date_invoice = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
                }
            }

            if (isset($search['date_end']) && $search['date_end'] != '')
            {
                $parts = explode('-', $search['date_end']);
                if(count($parts) == 2)
                {
                    $select->where('date_end >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_end <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
                }
                else
                {
                    $select->where('date_end = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
                }
            }

            if (isset($search['importo']) && $search['importo'] != '')
            {
                $parts = explode('-', $search['importo']);
                if(count($parts) == 2)
                {
                    $select->where('importo >= ' . $parts[0] . ' and importo <= ' . $parts[1]);
                }
                else
                {
                    $select->where('importo = ' . $parts[0]);
                }
            }

            if (isset($search['id_tipo_pagamento']) && !empty($search['id_tipo_pagamento']))
            {
                $select->where('id_tipo_pagamento = ' . implode(' or id_tipo_pagamento = ', $search['id_tipo_pagamento']));
            }
        }
        else if(is_string($search))
        {
            $select->where($search);
        }

        if($per_page !== NULL)
        {
            $invoices = Zend_Paginator::factory($select);
            $invoices->setItemCountPerPage($per_page);
            $invoices->setCurrentPageNumber(isset($search['page']) ? $search['page'] : 1);
        }
        else
        {
            $invoices = $db->fetchAll($select);
        }

        return $invoices;
    }

    public function findWithDependenciesById($id)
    {
        $db = $this->_invoiceMapper->getDbAdapter();

        $select = $db->select();

        $select->from('invoices', '*')
                ->join('pagamenti', 'pagamento_id = id_tipo_pagamento', array('tipo_pagamento_name' => 'pagamenti.name'))
                ->join('ibans', 'iban_id = id_iban', array('iban', 'bank', 'iban_name' => new Zend_Db_Expr('concat_ws(\' - \', bank, iban)')))
                ->where('invoice_id = ?', $id);

        $data = $db->fetchRow($select);

        $invoice = new Model_Invoice();

        $invoice->setData($data);

        if($invoice->type == 0)
        {
            $moments_repo = Maco_Model_Repository_Factory::getRepository('moment');
            $invoice->moments = $moments_repo->findByInvoice((int) $id);

            $tranches_repo = Maco_Model_Repository_Factory::getRepository('tranche');
            $invoice->tranches = $tranches_repo->findByInvoice($id);

            $orders_repo = Maco_Model_Repository_Factory::getRepository('order');
            $invoice->order = $orders_repo->findWithDependenciesByIdOffer($invoice->moments[0]->id_offer);
        }
        else
        {
            $companies_repo = Maco_Model_Repository_Factory::getRepository('company');
            $invoice->company = $companies_repo->find($invoice->id_company);
        }

        return $invoice;
    }

    /**
     * Check that all the tranches are payed. If so set the status to 1 elsewhere to 0
     *
     * @param int $id_invoice
     */
    public function updateStatusByInvoiceId($id_invoice)
    {
        $invoice = $this->findWithDependenciesById($id_invoice);
        $invoice->setValidatorAndFilter(new Model_Invoice_Validator());

        $new_status = 1;

        foreach($invoice->tranches as $t)
        {
            $timp2 = $t['status'];
            if($t['status'] != 2)
            {
                $new_status = 0;
                break;
            }
        }

        $invoice->status = $new_status;

        if(! $invoice->isValid())
        {
            return false;
        }

        /*
        if($new_status == 1)
        {
            // settiamo lo stato della commessa a fatturato
            
            $moments_repo = Maco_Model_Repository_Factory::getRepository('moment');
            $moments = $moments_repo->findByInvoice((int) $id_invoice);
            
            $orders_repo = Maco_Model_Repository_Factory::getRepository('order');
            $order = $orders_repo->findWithDependenciesByIdOffer($moments[0]->id_offer);
            
            $order->setValidatorAndFilter(new Model_Order_Validator());
            $order->id_status = 3; // fatturata
            if(!$order->isValid())
            {
                return false;
            }
            
            $orders_repo->save($order);
        }
        */

        return $this->save($invoice);
    }
}
