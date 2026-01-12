<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 12-ott-2010
 * Time: 16.01.50
 * To change this template use File | Settings | File Templates.
 */

class Model_Order_Repository
{
    /**
     * Orders mysql mapper
     *
     * @var Model_Order_Mapper
     */
    protected $_orderMapper;

    public function __construct()
    {
        $this->_orderMapper = new Model_Order_Mapper();
    }

    public function find($id)
    {
        $order = $this->_orderMapper->find($id);
        $momentsRepo = Maco_Model_Repository_Factory::getRepository('moment');
        $order->moments = $momentsRepo->findByOffer($order->id_offer);
        return $order;
    }

    public function findCode($id)
    {
        $db = $this->_orderMapper->getDbAdapter();
        $select = $db->select()->from('orders', 'code_order')
                ->where('order_id = ?', $id);
        $data = $db->fetchOne($select);
        return $data;
    }

    public function findOfferId($id)
    {
        $db = $this->_orderMapper->getDbAdapter();
        $select = $db->select()->from('orders', 'id_offer')
                ->where('order_id', $id);
        return $db->fetchOne($select);
    }

    public function save($order)
    {
        return $this->_orderMapper->save($order);
    }

    public function saveFromData($data, $prefix = '')
    {

    }

    public function exportOffers($sort = false, $dir = false, $search = array())
    {
        /*
        $auth = Zend_Auth::getInstance()->getIdentity();

        $db = $this->_offerMapper->getDbAdapter();

        $select = $db->select();

        $select->from('offers', array('offer_id', 'date_offer', 'code_offer', 'date_end'))
	        ->joinLeft(array('c1' => 'companies'), 'c1.company_id = offers.id_company', array('cliente' => 'ragione_sociale'))
            ->joinLeft(array('ci' => 'companies_internals'), 'ci.id_company = c1.company_id and ci.id_internal = ' . $auth->internal_id, 'id_office')
	        ->joinLeft(array('c2' => 'companies'), 'c2.company_id = offers.id_promotore', array('promotore' => 'ragione_sociale'))
	        ->joinLeft(array('s' => 'services'), 's.service_id = offers.id_service', array('service' => 's.name'))
	        ->joinLeft(array('ss' => 'subservices'), 'ss.subservice_id = offers.id_subservice', array('subservice' => 'ss.name', 'subservicecode' => 'ss.cod'))
	        ->joinLeft(array('u' => 'users'), 'u.user_id = offers.id_rco', array('rco' => 'u.username'))
	        ->joinLeft(array('os' => 'offer_status'), 'os.offer_status_id = offers.id_status', array('status' => 'os.name'))
			->where('offers.active = 1');

        // forse è superflua?
        $select->where('internal_code = ?', strtolower($auth->internal_abbr));

        if($auth->office_id)
        {
            $select->where('ci.id_office = ?', $auth->office_id);
        }

        */

        include(LIBRARY_PATH . '/Tbs/tbs_class.php');
        include(LIBRARY_PATH . '/Tbs/plugins/tbs_plugin_opentbs.php');

        $filesMapper = new Model_FilesMapper();

        $template_name = 'orders.xlsx';

        /*
        if(!$filesMapper->pathExists($filesMapper->getTemplatePath(false) . $template_name))
        {
            $this->_helper->getHelper('FlashMessenger')->addMessage('Il file di template per l\'esportazione dell\'offerta non esiste.');
            $this->_redirect('orders/detail/id/' . $offer_id);
        }
        */

        $tbs = new clsTinyButStrong; // new instance of TBS
        $tbs->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin

        $tbs->LoadTemplate($filesMapper->getTemplatePath() . $template_name);

        $orders = $this->getOrders($sort, $dir, $search);

        $orders_data = array();
        foreach($orders as $order)
        {
            $orders_data[] = array(
                'id' => utf8_decode($order['order_id']),
                'codice' => $order['code_order'],
                'rali_date' => Maco_Utils_DbDate::fromDb($order['rali_date'], Maco_Utils_DbDate::DBDATE_DATE),
                'date_completed' => Maco_Utils_DbDate::fromDb($order['date_completed'], Maco_Utils_DbDate::DBDATE_DATE),
                'cliente' => utf8_decode($order['cliente']),
                'promotore' => utf8_decode($order['partner']),
                'servizio' => utf8_decode($order['service']),
                'sottoservizio' => utf8_decode($order['subservice']),
                'sal' => ($order['sal']),
                'rc' => utf8_decode(str_replace('<hr />', ", ", $order['rcs'])),
                'stato' => utf8_decode($order['status']),
            );
        }
        $tbs->MergeBlock('o', $orders_data);

        $file_name = 'commesse.xlsx';
        $tbs->Show(OPENTBS_DOWNLOAD, $file_name);
        exit;
    }


    public function createFromOffer($id_offer, $id_dtg)
    {
        $offerRepo = Maco_Model_Repository_Factory::getRepository('offer');
        $offer = $offerRepo->findWithDependenciesById($id_offer);

        $db = $this->_orderMapper->getDbAdapter();
        $existingOrderId = $db->fetchOne('select order_id from orders where id_offer = ?', $id_offer);
        if($existingOrderId)
        {
            return array('commessa già creata per questa offerta');
        }

        $order = new Model_Order();
        $order->setValidatorAndFilter(new Model_Order_Validator());

        $data = array(
            'id_offer' => $id_offer,
            'id_dtg' => $id_dtg,
            'id_status' => 1, // todo: 1 = aperta
            'year' => date('Y'),
            'internal_code' => $offer->internal_code
        );

        $order->setData($data);

        $aut = Zend_Auth::getInstance()->getIdentity();

        $select = $db->select();
        $select->from('orders', array('year', 'id_order'))
                ->where('internal_code = ?', $aut->internal_abbr)
                ->order('year DESC')
                ->order('id_order DESC')
                ->limit(1, 0);
        $vals = $db->fetchRow($select);
        if($order->year > $vals['year'])
        {
            $order->id_order = 1;
        }
        else
        {
            $order->id_order = $vals['id_order'] + 1;
        }

        $order->code_order = implode('-', array($order->internal_code, $order->year, $order->id_order));

        // globale commessa dai momenti della offerta
        $tot_hm = 0;
        $tot_ni = 0;
        $tot_no = 0;
        $tot_hm_h = 0;
        foreach($offer->moments as $m)
        {
            /*
            if($m['moment_id'] == $id)
            {
                $tot_ni += $data['n_incontri'];
                $tot_no += $data['n_ore_studio'];
                $tot_hm += $data['valore_g_uomo'];
                $tot_hm_h += $m->getImportoScontato() / $data['valore_g_uomo'];
            }
            else
            */
            {
                $tot_ni += $m['p_n_incontri'];
                $tot_no += $m['p_ore_studio'];
                if($m['p_valore_g_uomo'] != '' && $m['p_valore_g_uomo'] != 0)
                {
                    $tot_hm += $m['p_valore_g_uomo'];
                    $tot_hm_h += $m->getImportoScontato() / $m['p_valore_g_uomo'];
                }
            }
        }
        $order->n_incontri = $tot_ni;
        $order->n_ore_studio =  $tot_no;
        $count = count($offer->moments);
        if($tot_hm_h != 0)
        {
            $order->valore_g_uomo = $offer->total / $tot_hm_h;
        }

        $order->setValidatorAndFilter(new Model_Order_Validator());

        if($order->isValid())
        {
            Maco_Model_TransactionManager::beginTransaction();

            try
            {
                $order_id = $this->_orderMapper->save($order);

                $companyRepo = Maco_Model_Repository_Factory::getRepository('company');

                $company = $companyRepo->find($offer->id_company);

                //$company->status = 1; //todo: stato cliente -> hardcoded
                $company->is_cliente = 2;
                $res = $companyRepo->validateAndSave($company);

                if($res)
                {
                    $message_repo = Maco_Model_Repository_Factory::getRepository('message');
                    $cf = Zend_Controller_Front::getInstance();
                    $base_url = /* $cf->getRequest()->getScheme()
                    . '://'
                    . $cf->getRequest()->getHttpHost()
                    . */ $cf->getBaseUrl();

                    $message_title = 'Pianificare ed assegnare la commessa: ' . $order->code_order;
                    $message_body = 'E\' stata creata una nuova commessa (<a href="' . $base_url . '/orders/detail/id/' . $order_id .'"><b>'
                            . $order->code_order . '</b></a>) e la tua utenza &egrave; stata impostata come DTG!';

                    $id_dtg = $order->id_dtg;
                    $message_repo->send($id_dtg, $message_title, $message_body, Model_Message_Types::TODO, null, $order_id . '-DTG-PLAN', true);

                    $uid = $offer->id_offer . '-RCO-RALI';
                    $toRemoveID = $offer->id_rco;
                    $message_repo->deleteByToAndUid($toRemoveID, $uid);

                    return Maco_Model_TransactionManager::commit();
                }
                else
                {
                    Maco_Model_TransactionManager::rollback();
                    return array('errori nella richiesta');
                }
            }
            catch(Exception $e)
            {
                Maco_Model_TransactionManager::rollback();
                return array($e->getMessage());
            }
        }
        else
        {
            return $order->getInvalidMessages();
        }
    }

    public function findWithDependenciesById($id)
    {
        return $this->findWithDependencies(array('orders.order_id' => $id));
    }

    public function findWithDependenciesByIdOffer($id_offer)
    {
        return $this->findWithDependencies(array('orders.id_offer' => $id_offer));
    }

    //public function findWithDependencies($where)


    public function findWithDependencies($where)
    {
        $db = $this->_orderMapper->getDbAdapter();

        $select = $db->select();

        $select->from('orders')
                ->joinLeft(array('u2' => 'users'), 'u2.user_id = orders.id_dtg', array('dtg_name' => 'u2.username'))
                ->joinLeft('order_status', 'order_status_id = orders.id_status', array('status_name' => 'order_status.name'));

        foreach($where as $f => $v)
        {
            $select->where($f . ' = ?', $v);
        }

        //    ->where('orders.order_id = ?', $id);

        $orderData = $db->fetchRow($select);

        $order = new Model_Order();
        $order->setData($orderData);

        $offerRepo = Maco_Model_Repository_Factory::getRepository('offer');

        $order->offer = $offerRepo->findWithDependenciesById($order->id_offer);

        if(!$order->offer)
        {
            return false;
        }

        unset($select);
        $select = $db->select();
        // TODO: id_rco to rco
        $select->from('orders_rcos', array('rco', 'note', 'date_assigned', 'incarico', 'date_incarico'))
                ->order('index asc')
                ->where('id_order = ?', $order->order_id);

        $rcos = $db->fetchAll($select);
        // TODO: id_rco to rco
        if(empty($rcos))
        {
            $rcos = array(
                array(
                    'rco' => '',
                    'note' => '',
                    'date_assigned' => ''
                )
            );
        }

        $order->rcos = $rcos;

        return $order;
    }

    public function changeStatus($id_order, $new_status, $other_data = array())
    {
        $db = $this->_orderMapper->getDbAdapter();

        $data = array_merge($other_data, array(
            'id_status' => $new_status
        ));

        return $db->update(
            'orders',
            $data,
                'order_id = ' . $db->quote($id_order)
        );
    }

    public function setAssigned($id_order, $rco)
    {
        $db = $this->_orderMapper->getDbAdapter();

        $res = $db->update(
            'orders_rcos',
            array(
                'incarico' => 1,
                'date_incarico' => date('Y-m-d')
            ),
            'id_order = ' . $db->quote($id_order) . ' and rco = ' . $db->quote($rco)
        );

        if($res)
        {
            $message_repo = Maco_Model_Repository_Factory::getRepository('message');
            $cf = Zend_Controller_Front::getInstance();
            $base_url = /* $cf->getRequest()->getScheme()
                    . '://'
                    . $cf->getRequest()->getHttpHost()
                    . */ $cf->getBaseUrl();
            $db = $this->_orderMapper->getDbAdapter();
            $order_data = $db->fetchRow('select code_order, id_dtg from orders where order_id = ' . $db->quote($id_order));

            // 1. info to dtg
            $message_title = 'Commessa presa in carico: ' . $order_data['code_order'];
            $message_body = 'La commessa <a href="' . $base_url . '/orders/detail/id/' . $id_order . '"><b>' . $order_data['code_order'] . '</b></a> &egrave; stata presa in carico da ' . $rco;
            $message_repo->send($order_data['id_dtg'], $message_title, $message_body, Model_Message_Types::INFO);

            // 2. remove dafare to rco
            $username = explode(' - ', $rco);
            $username = $username[0];
            $id_user_rco = $db->fetchOne('select user_id from users where username = ' . $db->quote($username));
            if($id_user_rco)
            {
                $uid = $id_order . '-RC-WORK';
                $toRemoveID = $id_user_rco;
                $message_repo->deleteByToAndUid($toRemoveID, $uid);
            }
        }

        return $res;
    }

    public function cancelOrder($id_order)
    {
        $order = $this->find($id_order);

        if(/*$order->id_status != 3 && */ $order->id_status != 5)
        {
            return $this->changeStatus($id_order, 5, array(
                'date_cancelled' => date('Y-m-d'),
                'date_suspended' => new Zend_Db_Expr('null'),
            ));
        }
        return false;
    }

    public function suspendOrder($id_order)
    {
        $order = $this->find($id_order);

        if($order->id_status != 3 && $order->id_status != 4 && $order->id->id_status != 5)
        {
            return $this->changeStatus($id_order, 4, array(
                'date_suspended' => date('Y-m-d'),
                'date_cancelled' => new Zend_Db_Expr('null'),
            ));
        }
        return false;
    }

    public function resumeOrder($id_order)
    {
        $order = $this->findWithDependenciesById($id_order);

        $toClose = true;
        foreach($order->offer->moments as $moment)
        {
            if($moment->done != 1)
            {
                $toClose = false;
                break;
            }
        }

        $other_data = array(
            'date_suspended' => new Zend_Db_Expr('null'),
            'date_cancelled' => new Zend_Db_Expr('null'),
        );

        if($toClose)
        {
            return $this->changeStatus($id_order, 3, $other_data);
        }

        $db = $this->_orderMapper->getDbAdapter();
        $rcos = $db->fetchAll('select * from orders_rcos where id_order = ?', $id_order);
        $new_status = 1;
        if(count($rcos) > 0)
        {
            $new_status = 2;
        }

        return $this->changeStatus($id_order, $new_status, $other_data);
    }

    public function getNewOrder()
    {
        $order = new Model_Order();

        return $order;
    }

    public function getCompaniesWithOrders($search)
    {
        $db = $this->_orderMapper->getDbAdapter();
        $auth = Zend_Auth::getInstance()->getIdentity();

        return $db->fetchAll(
            'select distinct company_id, ragione_sociale from orders ' .
                    'left join offers on orders.id_offer = offers.offer_id ' .
                    'left join companies on company_id = offers.id_company ' .
                    'left join companies_internals on companies_internals.id_company = company_id ' .
                    'where id_internal = ' . $auth->internal_id . ' and ' .
                    'ragione_sociale like ' . $db->quote('%' . $search . '%')
        );
    }


    public function getOrders($sort = null, $dir = 'ASC', $search = array(), $count = null, $per_page = NULL)
    {
        $auth = Zend_Auth::getInstance()->getIdentity();
        $db = $this->_orderMapper->getDbAdapter();

        $select = $db->select();

        $select->from('orders', array('order_id', 'or_year' => 'year', 'code_order', 'date_chiusura_richiesta', 'sal', 'rali_date', 'date_completed',
            'rcs' => new Zend_Db_Expr("group_concat(DISTINCT orders_rcos.rco SEPARATOR ' <hr /> ')"),
        ))
                ->joinLeft('orders_rcos', 'orders.order_id = orders_rcos.id_order', array())
                ->joinLeft('offers', 'offer_id = orders.id_offer', array('id_off' => 'offer_id', 'id_offer', 'revision', 'of_year' => 'year', 'date_offer', 'of_internal' => 'internal_code', 'code_offer', 'id_promotore'))
                ->joinLeft(array('c1' => 'companies'), 'c1.company_id = offers.id_company', array('cliente' => 'ragione_sociale'))
                ->joinLeft(array('ci' => 'companies_internals'), 'ci.id_company = c1.company_id and ci.id_internal = ' . $auth->internal_id, 'id_office')
                ->joinLeft(array('ca' => 'categories'), 'c1.categoria = ca.category_id', array('categoria_name' =>'ca.name'))
                ->joinLeft(array('c2' => 'companies'), 'c2.company_id = offers.id_promotore', array('partner' => 'ragione_sociale'))
                ->joinLeft(array('s' => 'services'), 's.service_id = offers.id_service', array('service' => 's.name'))
                ->join(array('si' => 'service_internal'), 'si.id_service = offers.id_service and si.id_internal = ' . $db->quote($auth->internal_id))
                ->joinLeft(array('ss' => 'subservices'), 'ss.subservice_id = offers.id_subservice', array('subservice' => 'ss.name', 'subservicecode' => 'ss.cod'))
                ->join(array('ssi' => 'subservice_internal'), 'ssi.id_subservice = offers.id_subservice and ssi.id_internal = ' . $db->quote($auth->internal_id))
        //->joinLeft(array('u2' => 'users'), 'u2.user_id = orders.id_dtg', array('dtg' => 'u2.username'))
        //->joinLeft(array('u3' => 'users'), 'u3.user_id = offers.id_segnalato_da', array('snome' => 'u3.username'))
                ->joinLeft(array('ors' => 'order_status'), 'ors.order_status_id = orders.id_status', array('status' => 'ors.name'))
                ->group('order_id')
        ;

        $select->where('offers.internal_code = ?', strtolower($auth->internal_abbr));

        if($auth->office_id)
        {
            $select->where('ci.id_office = ?', $auth->office_id);
        }

        if(!$count)
        {
            if($sort)
            {
                $select->order($sort . ' ' . $dir);
            }
            else
            {
                $select->order('of_internal ASC');
                $select->order('or_year ASC');
                $select->order('order_id ASC');
            }
        }
        else
        {
            $select->order('orders.date_modified desc');
            $select->order('orders.date_created desc');
            $select->limit($count, 0);
        }

        if(is_array($search) && !empty($search))
        {
            if (isset($search['code_order']) && $search['code_order'] != '')
            {
                $select->where('code_order like ' . $db->quote('%' . $search['code_order'] . '%'));
            }
            else
            {
                if (isset($search['codeorder']) && $search['codeorder'] != '')
                {
                    $select->where('code_order like ' . $db->quote('%' . $search['codeorder'] . '%'));
                }
            }

            if(isset($search['owner']) && $search['owner'] != '')
            {
                // todo: fake -> giusto con lo username
                $_rc = $auth->username . ' - ';
                $select->where('orders.created_by = ' . $search['owner'] . ' or id_dtg = ' . $search['owner'] . ' or exists (select orders_rcos.id_order from orders_rcos where orders.order_id = orders_rcos.id_order and orders_rcos.rco like \'' . $_rc . '%\')');
            }

            if (isset($search['code_offer']) && $search['code_offer'] != '')
            {
                $select->where('code_offer like ' . $db->quote('%' . $search['code_offer'] . '%'));
            }
            else
            {
                if (isset($search['codeoffer']) && $search['codeoffer'] != '')
                {
                    $select->where('code_offer like ' . $db->quote('%' . $search['codeoffer'] . '%'));
                }
            }

            if(isset($search['importo']) && $search['importo'] != '')
            {
                $parts = explode('-', $search['importo']);
                if(count($parts) == 1)
                {
                    //$select->where('(select sum(importo) as simporto from moments mm where mm.id_offer = offer_id) = ' . $db->quote($parts[0]));
                    $select->where('offers.offer_importo = ' . $db->quote($parts[0]));
                }
                elseif(count($parts) == 2)
                {
                    $from = $parts[0];
                    $to = $parts[1];
                    $select->where('offers.offer_importo between ' . $from . ' and ' . $to);
                }
            }

            if (isset($search['ragione_sociale']) && !empty($search['ragione_sociale']))
            {
                $select->where('c1.ragione_sociale like ' . $db->quote('%' . $search['ragione_sociale'] . '%'));
            }

            if (isset($search['categoria_name']) && $search['categoria_name'] != '')
            {
                $select->where('ca.name like \'%' . $search['categoria_name'] . '%\'');
            }

            if (isset($search['sal']) && $search['sal'] != '')
            {
                $select->where('sal = ' . $db->quote($search['sal']));
            }

            if (isset($search['id_status']) && !empty($search['id_status']))
            {
                $select->where('orders.id_status = ' . implode(' or orders.id_status = ', $search['id_status']));
            }
            else
            {
                if (isset($search['status']) && $search['status'] != '')
                {
                    $select->where('ors.name like ' . $db->quote('%' . $search['status'] . '%'));
                }
            }

            if (isset($search['rali_date']) && $search['rali_date'] != '')
            {
                $parts = explode('-', $search['rali_date']);

                if(count($parts) == 2)
                {
                    $select->where('rali_date >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and rali_date <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
                }
                else
                {
                    $select->where('rali_date = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
                }
            }

            if (isset($search['date_chiusura_richiesta']) && $search['date_chiusura_richiesta'] != '')
            {
                $parts = explode('-', $search['date_chiusura_richiesta']);

                if(count($parts) == 2)
                {
                    $select->where('date_chiusura_richiesta >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_chiusura_richiesta <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
                }
                else
                {
                    $select->where('date_chiusura_richiesta = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
                }
            }

            if (isset($search['date_completed']) && $search['date_completed'] != '')
            {
                $parts = explode('-', $search['date_completed']);

                if(count($parts) == 2)
                {
                    $select->where('date_completed >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_completed <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
                }
                else
                {
                    $select->where('date_completed = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
                }
            }

            if (isset($search['ente']) && !empty($search['ente']))
            {
                // ENTE SELECT MULTI
                $enti = $search['ente'];
                // ENTE AUTOCOMPLETE
                //$enti = explode(',', $search['ente']);

                $wh = '';
                foreach($enti as $ente)
                {
                    if($ente != '')
                    {
                        $wh .= 'orders.ente = ' . $db->quote($ente) . ' OR ';
                    }
                }
                if($wh != '')
                {
                    $wh = substr($wh, 0, -4);
                    $select->where($wh);
                }
            }

            if (isset($search['id_promotore']) && !empty($search['id_promotore']))
            {
                $idcparts = explode(',', $search['id_promotore']);
                $wh = '';
                foreach($idcparts as $id_company)
                {
                    if($id_company != '')
                    {
                        $wh .= 'offers.id_promotore = ' . $db->quote($id_company) . ' OR ';
                    }
                }
                if($wh != '')
                {
                    $wh = substr($wh, 0, -4);
                    $select->where($wh);
                }
            }

            if (isset($search['id_company']) && !empty($search['id_company']))
            {
                $idcparts = explode(',', $search['id_company']);
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
                //$select->where('offers.id_company = ' . implode(' or offers.id_company = ', $search['id_company']));
            }
            else
            {
                if (isset($search['cliente']) && $search['cliente'] != '')
                {
                    $select->where('c1.ragione_sociale like ' . $db->quote('%' . $search['cliente'] . '%'));
                }
            }

            if (isset($search['id_service']) && !empty($search['id_service']))
            {
                $select->where('offers.id_service = ' . implode(' or offers.id_service = ', $search['id_service']));
            }
            else
            {
                if (isset($search['service']) && $search['service'] != '')
                {
                    $select->where('s.name like ' . $db->quote('%' . $search['service'] . '%'));
                }
            }

            if (isset($search['id_ea']) && !empty($search['id_ea']))
            {
                $select->where('c1.ea = ' . implode(' or c1.ea = ', $search['id_ea']));
            }

            if (isset($search['id_subservice']) && !empty($search['id_subservice']))
            {
                $select->where('offers.id_subservice = ' . implode(' or offers.id_subservice = ', $search['id_subservice']));
            }
            else
            {
                if (isset($search['subservice']) && $search['subservice'] != '')
                {
                    $select->where('ss.name like ' . $db->quote('%' . $search['subservice'] . '%'));
                }
            }

            if (isset($search['id_rco']) && !empty($search['id_rco']))
            {
                $select->where('offers.id_rco = ' . implode(' or offers.id_rco = ', $search['id_rco']));
            }

            if (isset($search['id_dtg']) && !empty($search['id_dtg']))
            {
                $select->where('orders.id_dtg = ' . implode(' or orders.id_dtg = ', $search['id_dtg']));
            }
            else
            {
                if (isset($search['dtg']) && $search['dtg'] != '')
                {
                    $select->where('u2.username like ' . $db->quote('%' . $search['dtg'] . '%'));
                }
            }
            if (isset($search['rc']) && !empty($search['rc']))
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
                foreach($search['rc'] as $rc)
                {
                    $where .= $db->quote($rc) . ', ';
                }
                $where = substr($where, 0, -2) . '))';
                $select->where($where);
            }

            $user = Zend_Auth::getInstance()->getIdentity();
            if(!$user->user_object->has_permission('orders', 'view') && $user->user_object->has_permission('orders', 'view_own'))
            {
                $select->where(' exists (select orders_rcos.id_order from orders_rcos where orders.order_id = orders_rcos.id_order and orders_rcos.rco = ' . $db->quote($user->username . ' - ' . $user->user_object->contact->nome . ' ' . $user->user_object->contact->cognome) . ')');
            }
        }
        else if(is_string($search))
        {
            $select->where($search);
        }

        if($per_page !== NULL)
        {
            $orders = Zend_Paginator::factory($select);
            $orders->setItemCountPerPage($per_page);
            $orders->setCurrentPageNumber(isset($search['page']) ? $search['page'] : 1);
        }
        else
        {
            $orders = $db->fetchAll($select);
        }

        return $orders;
    }

    public function getTotals($search = array())
    {
        $auth = Zend_Auth::getInstance()->getIdentity();
        $db = $this->_orderMapper->getDbAdapter();

        $select = $db->select();

        $select->from('offers', array('total' => 'sum(offers.offer_importo)', 'to_promotors' => new Zend_Db_Expr('sum(offers.promotore_value)')))
            ->joinLeft('orders', 'orders.id_offer = offers.offer_id', array(new Zend_Db_Expr('count(distinct orders.order_id) as count')))
        //$select->from('moments', array(new Zend_Db_Expr('sum(importo) as total'), new Zend_Db_Expr('sum(importo * offers.promotore_percent / 100) as to_promotors'), new Zend_Db_Expr('count(distinct orders.order_id) as count')))
                //->joinLeft('orders', 'orders.id_offer = moments.id_offer', array(new Zend_Db_Expr('count(distinct orders.order_id) as count')))
                //->joinLeft('offers', 'offer_id = orders.id_offer', array())
                ->joinLeft(array('c1' => 'companies'), 'c1.company_id = offers.id_company', array())
                ->joinLeft(array('ci' => 'companies_internals'), 'ci.id_company = c1.company_id and ci.id_internal = ' . $auth->internal_id, array())
                ->joinLeft(array('ca' => 'categories'), 'c1.categoria = ca.category_id', array())
                ->joinLeft(array('c2' => 'companies'), 'c2.company_id = offers.id_promotore', array('partner' => 'ragione_sociale'))
                ->joinLeft(array('s' => 'services'), 's.service_id = offers.id_service', array())
                ->join(array('si' => 'service_internal'), 'si.id_service = offers.id_service and si.id_internal = ' . $db->quote($auth->internal_id))
                ->joinLeft(array('ss' => 'subservices'), 'ss.subservice_id = offers.id_subservice', array())
                ->join(array('ssi' => 'subservice_internal'), 'ssi.id_subservice = offers.id_subservice and ssi.id_internal = ' . $db->quote($auth->internal_id))
        //->joinLeft(array('u2' => 'users'), 'u2.user_id = orders.id_dtg', array('dtg' => 'u2.username'))
        //->joinLeft(array('u3' => 'users'), 'u3.user_id = offers.id_segnalato_da', array('snome' => 'u3.username'))
                ->joinLeft(array('ors' => 'order_status'), 'ors.order_status_id = orders.id_status', array());

        $select->where('offers.internal_code = ?', strtolower($auth->internal_abbr));

        if($auth->office_id)
        {
            $select->where('ci.id_office = ?', $auth->office_id);
        }

        if(is_array($search) && !empty($search))
        {
            if (isset($search['code_order']) && $search['code_order'] != '')
            {
                $select->where('code_order like ' . $db->quote('%' . $search['code_order'] . '%'));
            }
            else
            {
                if (isset($search['codeorder']) && $search['codeorder'] != '')
                {
                    $select->where('code_order like ' . $db->quote('%' . $search['codeorder'] . '%'));
                }
            }

            if(isset($search['owner']) && $search['owner'] != '')
            {
                // todo: fake -> giusto con lo username
                $_rc = $auth->username . ' - ';
                $select->where('orders.created_by = ' . $search['owner'] . ' or id_dtg = ' . $search['owner'] . ' or exists (select orders_rcos.id_order from orders_rcos where orders.order_id = orders_rcos.id_order and orders_rcos.rco like \'' . $_rc . '%\')');
            }

            if (isset($search['code_offer']) && $search['code_offer'] != '')
            {
                $select->where('code_offer like ' . $db->quote('%' . $search['code_offer'] . '%'));
            }
            else
            {
                if (isset($search['codeoffer']) && $search['codeoffer'] != '')
                {
                    $select->where('code_offer like ' . $db->quote('%' . $search['codeoffer'] . '%'));
                }
            }

            if(isset($search['importo']) && $search['importo'] != '')
            {
                $parts = explode('-', $search['importo']);
                if(count($parts) == 1)
                {
                    //$select->where('(select sum(importo) as simporto from moments mm where mm.id_offer = offer_id) = ' . $db->quote($parts[0]));
                    $select->where('offers.offer_importo = ' . $db->quote($parts[0]));
                }
                elseif(count($parts) == 2)
                {
                    $from = $parts[0];
                    $to = $parts[1];
                    //$select->where('(select sum(importo) as simporto from moments mm where mm.id_offer = offer_id) between ' . $from . ' and ' . $to);
                    //$select->where('order_id in (select oo3.order_id from orders oo3 where (select sum(importo) as simporto from moments mm where mm.id_offer = oo3.id_offer) between ' . $from . ' and ' . $to . ')');
                    $select->where('order_id in (select oo3.order_id from orders oo3 where (select of3.offer_importo from offers of3 where oo3.id_offer = of3.offer_id) between ' . $from . ' and ' . $to . ')');
                }
            }


            if (isset($search['ragione_sociale']) && !empty($search['ragione_sociale']))
            {
                $select->where('c1.ragione_sociale like ' . $db->quote('%' . $search['ragione_sociale'] . '%'));
            }

            if (isset($search['categoria_name']) && $search['categoria_name'] != '')
            {
                $select->where('ca.name like \'%' . $search['categoria_name'] . '%\'');
            }

            if (isset($search['sal']) && $search['sal'] != '')
            {
                $select->where('sal = ' . $db->quote($search['sal']));
            }

            if (isset($search['id_status']) && !empty($search['id_status']))
            {
                $select->where('orders.id_status = ' . implode(' or orders.id_status = ', $search['id_status']));
            }
            else
            {
                if (isset($search['status']) && $search['status'] != '')
                {
                    $select->where('ors.name like ' . $db->quote('%' . $search['status'] . '%'));
                }
            }

            if (isset($search['rali_date']) && $search['rali_date'] != '')
            {
                $parts = explode('-', $search['rali_date']);

                if(count($parts) == 2)
                {
                    $select->where('rali_date >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and rali_date <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
                }
                else
                {
                    $select->where('rali_date = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
                }
            }

            if (isset($search['date_chiusura_richiesta']) && $search['date_chiusura_richiesta'] != '')
            {
                $parts = explode('-', $search['date_chiusura_richiesta']);

                if(count($parts) == 2)
                {
                    $select->where('date_chiusura_richiesta >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_chiusura_richiesta <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
                }
                else
                {
                    $select->where('date_chiusura_richiesta = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
                }
            }

            if (isset($search['date_completed']) && $search['date_completed'] != '')
            {
                $parts = explode('-', $search['date_completed']);

                if(count($parts) == 2)
                {
                    $select->where('date_completed >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_completed <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
                }
                else
                {
                    $select->where('date_completed = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
                }
            }

            if (isset($search['date_cancelled']) && $search['date_cancelled'] != '')
            {
                $parts = explode('-', $search['date_cancelled']);

                if(count($parts) == 2)
                {
                    $select->where('date_cancelled >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_cancelled <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
                }
                else
                {
                    $select->where('date_cancelled = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
                }
            }

            if (isset($search['date_suspended']) && $search['date_suspended'] != '')
            {
                $parts = explode('-', $search['date_suspended']);

                if(count($parts) == 2)
                {
                    $select->where('date_suspended >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_suspended <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
                }
                else
                {
                    $select->where('date_suspended = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
                }
            }

            if (isset($search['id_promotore']) && !empty($search['id_promotore']))
            {
                $idcparts = explode(',', $search['id_promotore']);
                $wh = '';
                foreach($idcparts as $id_company)
                {
                    if($id_company != '')
                    {
                        $wh .= 'offers.id_promotore = ' . $db->quote($id_company) . ' OR ';
                    }
                }
                if($wh != '')
                {
                    $wh = substr($wh, 0, -4);
                    $select->where($wh);
                }
            }

            if (isset($search['ente']) && !empty($search['ente']))
            {
                // ENTE SELECT MULTI
                $enti = $search['ente'];
                // ENTE AUTOCOMPLETE
                //$enti = explode(',', $search['ente']);

                $wh = '';
                foreach($enti as $ente)
                {
                    if($ente != '')
                    {
                        $wh .= 'orders.ente = ' . $db->quote($ente) . ' OR ';
                    }
                }
                if($wh != '')
                {
                    $wh = substr($wh, 0, -4);
                    $select->where($wh);
                }
            }

            if (isset($search['id_company']) && !empty($search['id_company']))
            {
                $idcparts = explode(',', $search['id_company']);
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
                //$select->where('offers.id_company = ' . implode(' or offers.id_company = ', $search['id_company']));
            }
            else
            {
                if (isset($search['cliente']) && $search['cliente'] != '')
                {
                    $select->where('c1.ragione_sociale like ' . $db->quote('%' . $search['cliente'] . '%'));
                }
            }

            if (isset($search['id_service']) && !empty($search['id_service']))
            {
                $select->where('offers.id_service = ' . implode(' or offers.id_service = ', $search['id_service']));
            }
            else
            {
                if (isset($search['service']) && $search['service'] != '')
                {
                    $select->where('s.name like ' . $db->quote('%' . $search['service'] . '%'));
                }
            }

            if (isset($search['id_subservice']) && !empty($search['id_subservice']))
            {
                $select->where('offers.id_subservice = ' . implode(' or offers.id_subservice = ', $search['id_subservice']));
            }
            else
            {
                if (isset($search['subservice']) && $search['subservice'] != '')
                {
                    $select->where('ss.name like ' . $db->quote('%' . $search['subservice'] . '%'));
                }
            }

            if (isset($search['id_ea']) && !empty($search['id_ea']))
            {
                $select->where('c1.ea = ' . implode(' or c1.ea = ', $search['id_ea']));
            }

            if (isset($search['id_rco']) && !empty($search['id_rco']))
            {
                $select->where('offers.id_rco = ' . implode(' or offers.id_rco = ', $search['id_rco']));
            }

            if (isset($search['id_dtg']) && !empty($search['id_dtg']))
            {
                $select->where('orders.id_dtg = ' . implode(' or orders.id_dtg = ', $search['id_dtg']));
            }
            else
            {
                if (isset($search['dtg']) && $search['dtg'] != '')
                {
                    $select->where('u2.username like ' . $db->quote('%' . $search['dtg'] . '%'));
                }
            }
            if (isset($search['rc']) && !empty($search['rc']))
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
                foreach($search['rc'] as $rc)
                {
                    $where .= $db->quote($rc) . ', ';
                }
                $where = substr($where, 0, -2) . ')';

                if (isset($search['date_assigned']) && $search['date_assigned'] != '')
                {
                    $parts = explode('-', $search['date_assigned']);

                    $where .= ' and ';

                    if(count($parts) == 2)
                    {
                        $where .= 'orders_rcos.date_assigned >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and orders_rcos.date_assigned <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1])));
                    }
                    else
                    {
                        $where .= 'orders_rcos.date_assigned = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0])));
                    }
                }

                $where .= ')';
                $select->where($where);
            }

            $user = Zend_Auth::getInstance()->getIdentity();
            if(!$user->user_object->has_permission('orders', 'view') && $user->user_object->has_permission('orders', 'view_own'))
            {
                $select->where(' exists (select orders_rcos.id_order from orders_rcos where orders.order_id = orders_rcos.id_order and orders_rcos.rco = ' . $db->quote($user->username . ' - ' . $user->user_object->contact->nome . ' ' . $user->user_object->contact->cognome) . ')');
            }
        }
        else if(is_string($search))
        {
            $select->where($search);
        }

        return $db->fetchRow($select);
    }
}
