<?php

class Model_Administration
{
    public function getMoments($options = array())
    {
        $db = $this->_getDbAdapter();

        $select = $db->select();

        $auth = Zend_Auth::getInstance()->getIdentity();

        $select->from('moments', array('moment_id', 'importo', 'tipologia', 'fatturato', 'expected_date', 'c_ore_studio', 'c_ore_azienda', 'c_ore_certificazione', new Zend_Db_Expr('sum(c_ore_azienda + c_ore_certificazione + c_ore_studio) as c_ore_total'), 'c_n_incontri', 'c_n_km', 'c_ore_viaggio', 'c_costo_km', new Zend_Db_Expr('FORMAT((importo / (c_ore_azienda + c_ore_certificazione + c_ore_studio)), 2) as importo_per_ora')))
                ->joinLeft('offers', 'offer_id = moments.id_offer', array('code_offer', 'offer_id'))
                ->joinLeft('orders', 'orders.id_offer = offer_id', array('code_order', 'order_id'))
                ->joinLeft('companies', 'offers.id_company = company_id', array('ragione_sociale', 'company_id'))
                ->joinLeft(array('s' => 'services'), 's.service_id = offers.id_service', array('service' => 's.name'))
                ->join(array('si' => 'service_internal'), 'si.id_service = offers.id_service and si.id_internal = ' . $db->quote($auth->internal_id))
                ->joinLeft(array('ss' => 'subservices'), 'ss.subservice_id = offers.id_subservice', array('subservice' => 'ss.name', 'subservicecode' => 'ss.cod'))
                ->join(array('ssi' => 'subservice_internal'), 'ssi.id_subservice = offers.id_subservice and ssi.id_internal = ' . $db->quote($auth->internal_id))
                ->where('orders.order_id is not null')
        //->where('fatturazione = 1')
        //->where('date_done is not null')
        //->where('date_done <> \'0000-00-00\'')
                ->where('done = 1')
                ->group('moments.moment_id')
                ->where('offers.internal_code = ' . $db->quote(strtolower($auth->internal_abbr)));

        if(!isset($options['_order']))
        {
            $select->order('orders.year ASC')
                    ->order('orders.id_order ASC')
                    ->order('moments.index');
        }
        else
        {
            $select->order($options['_order']);
        }

        $search = isset($options['search']) ? $options['search'] : array();

        foreach($search as $f => $v)
        {
            $select->where($f . ' = ?', $v);
        }

        if(isset($options['without-invoice']))
        {
            if($options['without-invoice'])
            {
                $select->where('id_invoice is null or id_invoice = 0');
            }
            else
            {
                $select->where('id_invoice is not null and id_invoice <> 0');
            }

        }

        if(isset($options['moment-done']))
        {
            if($options['moment-done'])
            {
                //$select->where('date_done is not null and date_done <> \'0000-00-00\'');
                $select->where('done = 1');
            }
            else
            {
                //$select->where('date_done is null or date_done = \'0000-00-00\'');
                $select->where('done = 0');
            }
        }

        if(isset($_GET['ragione_sociale']))
        {
            $select->where('ragione_sociale like \'%' . $_GET['ragione_sociale'] . '%\'');
        }
        if(isset($_GET['service']))
        {
            $select->where('s.name like \'%' . $_GET['service'] . '%\'');
        }
        if(isset($_GET['subservice']))
        {
            $select->where('ss.name like \'%' . $_GET['subservice'] . '%\'');
        }
        if(isset($_GET['tipologia']))
        {
            $select->where('tipologia like \'%' . $_GET['tipologia'] . '%\'');
        }

        if (isset($_GET['id_company']) && !empty($_GET['id_company']))
        {
            $idcparts = explode(',', $_GET['id_company']);
            $wh = '';
            foreach($idcparts as $id_company)
            {
                if($id_company != '')
                {
                    $wh .= 'offers.id_company = ' . $db->quote($id_company) . ' OR ';
                }
            }
            if($wh != '')
            {
                $wh = substr($wh, 0, -4);
                $select->where($wh);
            }
        }

        if (isset($_GET['date_done']) && $_GET['date_done'] != '')
        {
            $parts = explode('-', $_GET['date_done']);

            if(count($parts) == 2)
            {
                $select->where('date_done >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_done <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('date_done = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
            }
        }

        if (isset($_GET['stato']) && !empty($_GET['stato']))
        {
            $w = '';
            foreach($_GET['stato'] as $t)
            {
                if($t == 'working')
                {
                    $w .= 'moments.done = 0 or ';
                }
                if($t == 'completed')
                {
                    $w .= 'moments.done = 1 or ';
                }
                if($t == 'invoiced')
                {
                    $w .= '(moments.id_invoice is not null and moments.id_invoice <> 0) or ';
                }
            }
            if($w != '')
            {
                $w = substr($w, 0, -3);
                $select->where($w);
            }
        }

        if (isset($_GET['id_service']) && !empty($_GET['id_service']))
        {
            $select->where('offers.id_service = ' . implode(' or offers.id_service = ', $_GET['id_service']));
        }

        if (isset($_GET['id_subservice']) && !empty($_GET['id_subservice']))
        {
            $select->where('offers.id_subservice = ' . implode(' or offers.id_subservice = ', $_GET['id_subservice']));
        }


        if (isset($_GET['id_rco']) && !empty($_GET['id_rco']))
        {
            $select->where('offers.id_rco = ' . implode(' or offers.id_rco = ', $_GET['id_rco']));
        }

        if (isset($_GET['id_dtg']) && !empty($_GET['id_dtg']))
        {
            $select->where('orders.id_dtg = ' . implode(' or orders.id_dtg = ', $_GET['id_dtg']));
        }

        if (isset($_GET['rc']) && !empty($_GET['rc']))
        {
            //$select->where(' exists (select orders_rcos.id_order from orders_rcos where orders.order_id = orders_rcos.id_order and orders_rcos.rco like ' . $db->quote('%' . $search['rc'] . '%') . ')');
            /*$where = ' exists (select orders_rcos.id_order from orders_rcos where orders.order_id = orders_rcos.id_order and (';
            foreach($search['rc'] as $rc)
            {
                $where .= 'orders_rcos.rco = ' . $db->quote($rc) . ' or ';
            }
            $where = substr($where, 0, -3) . '))';
            */
            $where = ' exists (select orders_rcos.id_order from orders_rcos where orders.order_id = orders_rcos.id_order and orders_rcos.rco in (';
            foreach($_GET['rc'] as $rc)
            {
                $where .= $db->quote($rc) . ', ';
            }
            $where = substr($where, 0, -2) . '))';
            $select->where($where);
        }



        if(isset($options['perpage']) && $options['perpage'])
        {
            $values = Zend_Paginator::factory($select);
            $values->setItemCountPerPage((int)$options['perpage']);
            $values->setCurrentPageNumber(isset($_GET['page']) ? $_GET['page'] : 1);
        }
        else
        {
            $values = $db->fetchAll($select);
        }

        return $values;
    }

    public function getTotal($options, $with_count = false)
    {
        $db = $this->_getDbAdapter();

        $select = $db->select();

        $auth = Zend_Auth::getInstance()->getIdentity();

        if($with_count)
        {
            $select->from('moments', array(new Zend_Db_Expr('sum(importo) as total'), new Zend_Db_Expr('count(distinct offers.offer_id) as count')));
        }
        else
        {
            $select->from('moments', array('total' => new Zend_Db_Expr('sum(importo)')));
        }

        $select->joinLeft('offers', 'offer_id = moments.id_offer', array())
                ->joinLeft('orders', 'orders.id_offer = offer_id', array())
                ->joinLeft('companies', 'offers.id_company = company_id', array())
                ->joinLeft(array('s' => 'services'), 's.service_id = offers.id_service', array())
                ->join(array('si' => 'service_internal'), 'si.id_service = offers.id_service and si.id_internal = ' . $db->quote($auth->internal_id))
                ->joinLeft(array('ss' => 'subservices'), 'ss.subservice_id = offers.id_subservice', array())
                ->join(array('ssi' => 'subservice_internal'), 'ssi.id_subservice = offers.id_subservice and ssi.id_internal = ' . $db->quote($auth->internal_id))
                ->where('orders.order_id is not null')
        //->where('fatturazione = 1')
        //->where('date_done is not null')
        //->where('date_done <> \'0000-00-00\'')
                ->where('done = 1')
                ->where('offers.internal_code = ' . $db->quote(strtolower($auth->internal_abbr)));

        $search = isset($options['search']) ? $options['search'] : array();

        foreach($search as $f => $v)
        {
            $select->where($f . ' = ?', $v);
        }

        if(isset($options['without-invoice']))
        {
            if($options['without-invoice'])
            {
                $select->where('id_invoice is null or id_invoice = 0');
            }
            else
            {
                $select->where('id_invoice is not null and id_invoice <> 0');
            }

        }

        if(isset($options['moment-done']))
        {
            if($options['moment-done'])
            {
                //$select->where('date_done is not null and date_done <> \'0000-00-00\'');
                $select->where('done = 1');
            }
            else
            {
                //$select->where('date_done is null or date_done = \'0000-00-00\'');
                $select->where('done = 0');
            }
        }

        if(isset($_GET['ragione_sociale']))
        {
            $select->where('ragione_sociale like \'%' . $_GET['ragione_sociale'] . '%\'');
        }
        if(isset($_GET['service']))
        {
            $select->where('s.name like \'%' . $_GET['service'] . '%\'');
        }
        if(isset($_GET['subservice']))
        {
            $select->where('ss.name like \'%' . $_GET['subservice'] . '%\'');
        }
        if(isset($_GET['tipologia']))
        {
            $select->where('tipologia like \'%' . $_GET['tipologia'] . '%\'');
        }

        if (isset($_GET['id_company']) && !empty($_GET['id_company']))
        {
            $idcparts = explode(',', $_GET['id_company']);
            $wh = '';
            foreach($idcparts as $id_company)
            {
                if($id_company != '')
                {
                    $wh .= 'offers.id_company = ' . $db->quote($id_company) . ' OR ';
                }
            }
            if($wh != '')
            {
                $wh = substr($wh, 0, -4);
                $select->where($wh);
            }
        }

        if (isset($_GET['date_done']) && $_GET['date_done'] != '')
        {
            $parts = explode('-', $_GET['date_done']);

            if(count($parts) == 2)
            {
                $select->where('date_done >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_done <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('date_done = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
            }
        }

        if (isset($_GET['stato']) && !empty($_GET['stato']))
        {
            $w = '';
            foreach($_GET['stato'] as $t)
            {
                if($t == 'working')
                {
                    $w .= 'moments.done = 0 or ';
                }
                if($t == 'completed')
                {
                    $w .= 'moments.done = 1 or ';
                }
                if($t == 'invoiced')
                {
                    $w .= '(moments.id_invoice is not null and moments.id_invoice <> 0) or ';
                }
            }
            if($w != '')
            {
                $w = substr($w, 0, -3);
                $select->where($w);
            }
        }

        if (isset($_GET['id_service']) && !empty($_GET['id_service']))
        {
            $select->where('offers.id_service = ' . implode(' or offers.id_service = ', $_GET['id_service']));
        }

        if (isset($_GET['id_subservice']) && !empty($_GET['id_subservice']))
        {
            $select->where('offers.id_subservice = ' . implode(' or offers.id_subservice = ', $_GET['id_subservice']));
        }

        if (isset($_GET['id_rco']) && !empty($_GET['id_rco']))
        {
            $select->where('offers.id_rco = ' . implode(' or offers.id_rco = ', $_GET['id_rco']));
        }

        if (isset($_GET['id_dtg']) && !empty($_GET['id_dtg']))
        {
            $select->where('orders.id_dtg = ' . implode(' or orders.id_dtg = ', $_GET['id_dtg']));
        }

        if (isset($_GET['rc']) && !empty($_GET['rc']))
        {
            //$select->where(' exists (select orders_rcos.id_order from orders_rcos where orders.order_id = orders_rcos.id_order and orders_rcos.rco like ' . $db->quote('%' . $search['rc'] . '%') . ')');
            /*$where = ' exists (select orders_rcos.id_order from orders_rcos where orders.order_id = orders_rcos.id_order and (';
            foreach($search['rc'] as $rc)
            {
                $where .= 'orders_rcos.rco = ' . $db->quote($rc) . ' or ';
            }
            $where = substr($where, 0, -3) . '))';
            */
            $where = ' exists (select orders_rcos.id_order from orders_rcos where orders.order_id = orders_rcos.id_order and orders_rcos.rco in (';
            foreach($_GET['rc'] as $rc)
            {
                $where .= $db->quote($rc) . ', ';
            }
            $where = substr($where, 0, -2) . '))';
            $select->where($where);
        }

        if($with_count)
        {
            return $db->fetchRow($select);
        }

        return $db->fetchOne($select);
    }

    public function doFattura($id)
    {
        $db = $this->_getDbAdapter();

        $data = array(
            'fatturato' => 1,
            'date_fatturato' => date('Y-m-d')
        );

        return $db->update('moments', $data, 'id = ' . $db->quote($id));
    }

    public function close($id)
    {
        $db = $this->_getDbAdapter();

        $data = array(
            'closed' => 1,
            'date_closed' => date('Y-m-d')
        );

        return $db->update('moments', $data, 'id = ' . $db->quote($id));
    }

    public function getDetail($id)
    {
        $db = $this->_getDbAdapter();

        $select = $db->select();

        $select->from('moments', '*')
                ->joinLeft('offers', 'offer_id = moments.id_offer', array('of_sconto' => 'sconto', 'code_offer'))
                ->joinLeft('orders', 'orders.id_offer = offer_id', array('rali_date', 'valore_g_uomo', 'code_order'))
                ->joinLeft('companies', 'offers.id_company = company_id', array('ragione_sociale', 'partita_iva', 'cf', 'iban'))
                ->joinLeft(array('s' => 'services'), 'service_id = offers.id_service', array('service' => 's.name'))
                ->joinLeft(array('ss' => 'subservices'), 'ss.subservice_id = offers.id_subservice', array('subservice' => 'ss.name', 'subservicecode' => 'ss.cod'))
                ->where('moments.moment_id = ?', $id);

        $values = $db->fetchRow($select);

        return $values;
    }

    /**
     * Returns the db adapter
     *
     * @return Zend_Db_Adapter_Abstract
     */
    protected function _getDbAdapter()
    {
        return Zend_Registry::get('dbAdapter');
    }
}
