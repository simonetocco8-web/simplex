<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 12-ott-2010
 * Time: 12.41.31
 * To change this template use File | Settings | File Templates.
 */

class Model_Offer_Repository
{
    /**
     * Offers mysql mapper
     *
     * @var Model_Offer_Mapper
     */
    protected $_offerMapper;

    public function __construct()
    {
        $this->_offerMapper = new Model_Offer_Mapper();
    }

    public function getNewOffer()
    {
        $offer = new Model_Offer();

        $momentsRepo = Maco_Model_Repository_Factory::getRepository('moment');
        $offer->moments = array($momentsRepo->getNewMoment());

        return $offer;
    }

    public function findCode($id)
    {
        $db = $this->_offerMapper->getDbAdapter();
        $select = $db->select()->from('offers', array('code_offer'))
                ->where('offer_id = ?', $id);
        $data = $db->fetchOne($select);
        return $data;
    }

    public function findCompanyId($id)
    {
        $db = $this->_offerMapper->getDbAdapter();
        $select = $db->select()->from('offers', 'id_company')
                ->where('offer_id', $id);
        return $db->fetchOne($select);
    }

    public function find($id)
    {
        $offer = $this->_offerMapper->find($id);
        $momentsRepo = Maco_Model_Repository_Factory::getRepository('moment');
        $offer->moments = $momentsRepo->findByOffer($offer->offer_id);
        return $offer;
    }

    public function findWithDependenciesById($id)
    {
        return $this->findWithDependencies(array('offer_id' => $id));
    }

    public function findWithDependenciesByIdOfferAndRevision($id_offer, $year, $revision)
    {
        return $this->findWithDependencies(array('offers.id_offer' => $id_offer, 'revision' => $revision, 'offers.year' => $year));
    }

    public function findWithDependencies($where)
    {
        $auth = Zend_Auth::getInstance()->getIdentity();

        $db = $this->_offerMapper->getDbAdapter();

        $select = $db->select();

        $select->from('offers')
                ->joinLeft('services', 'service_id = offers.id_service', array('service_name' => 'services.name', 'service_code' => 'services.cod'))
                ->joinLeft('subservices', 'subservice_id = offers.id_subservice', array('subservice_name' => 'subservices.name', 'subservice_code' => 'subservices.cod'))
                ->joinLeft('interests_levels', 'interests_level_id = offers.id_interest', array('interest_name' => 'interests_levels.name'))
                ->joinLeft(array('c2' => 'companies'), 'c2.company_id = offers.id_promotore', array('promotore_name' => 'c2.ragione_sociale'))
        //->joinLeft(array('u1' => 'users'), 'u1.user_id = offers.id_segnalato_da', array('segnalato_da_name' => 'u1.username'))
                ->joinLeft(array('co2' => 'contacts'), 'co2.contact_id = offers.id_company_contact', array('company_contact_name' => new Zend_Db_Expr('concat_ws(\' \', co2.nome, co2.cognome)')))
                ->joinLeft(array('u2' => 'users'), 'u2.user_id = offers.id_rco', array('rco_name' => 'u2.username'))
                ->joinLeft('offer_status', 'offer_status_id = offers.id_status', array('status_name' => 'offer_status.name'))
                ->joinLeft('pagamenti', 'pagamento_id = offers.id_pagamento', array('pagamento_name' => 'pagamenti.name'))
                ->joinLeft('orders', 'orders.id_offer = offer_id', array('id_order' => 'orders.order_id'))
                ->joinLeft(array('ci' => 'companies_internals'), 'ci.id_company = offers.id_company and offers.internal_code = ' . $db->quote(strtolower($auth->internal_abbr)), 'id_office');

        if($auth->office_id)
        {
            $select->where('id_office = ?', $auth->office_id);
        }

        foreach($where as $f => $v)
        {
            $select->where($f . ' = ?', $v);
        }

        $data = $db->fetchRow($select);

        if(!$data)
        {
            return null;
        }

        $offer = new Model_Offer();
        $offer->setData($data);

        $companiesRepo = Maco_Model_Repository_Factory::getRepository('company');

        $offer->company = $companiesRepo->findById($offer->id_company);

        $momentsRepo = Maco_Model_Repository_Factory::getRepository('moment');

        $moments = $momentsRepo->findByOffer($offer->offer_id);

        if(empty($moments))
        {
            $offer->moments = array($momentsRepo->getNewMoment());
            $offer->total = 0;
        }
        else
        {
            $offer->moments = $moments;
            $tot = 0;
            $totraw = 0;
            foreach($moments as $m)
            {
                $p = $m['importo'];
                $totraw += $p;
                if($offer['sconto'] != '' && $offer['sconto'] != 0)
                {
                    $p = $p - ($p * $offer['sconto'] / 100);
                }

                $tot += $p;
            }
            $offer->total = $tot;
            $offer->total_raw = $totraw;
        }

        unset($select);
        $select = $db->select();

        $select->from('offers', 'revision')
                ->where('offers.internal_code = ?', $offer->internal_code)
                ->where('offers.id_offer = ?', $offer->id_offer)
                ->where('offers.year = ?', $offer->year);

        $offer->revisions = $db->fetchCol($select);

        return $offer;
    }

    public function exportOffers($sort = false, $dir = false, $search = array(), $deleted = null)
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

        $template_name = 'offers.xlsx';

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

        $offers = $this->getOffers($sort, $dir, $search, $deleted);

        $offers_data = array();
        foreach($offers as $offer)
        {
            $offers_data[] = array(
                'id' => utf8_decode($offer['offer_id']),
                'codice' => utf8_decode($offer['code_offer']),
                'data' => Maco_Utils_DbDate::fromDb($offer['date_offer']),
                'cliente' => utf8_decode($offer['cliente']),
                'servizio' => utf8_decode($offer['service']),
                'sottoservizio' => utf8_decode($offer['subservice']),
                'rco' => utf8_decode($offer['rco']),
                'importo' => number_format($offer['importo'], 2, ',', '.'),
                'al_partner' => number_format($offer['al_partner'], 2, ',', '.'),
                'stato' => utf8_decode($offer['status']),
            );
        }
        $tbs->MergeBlock('o', $offers_data);

        $file_name = 'offerte.xlsx';
        $tbs->Show(OPENTBS_DOWNLOAD, $file_name);
        exit;
    }

    public function getRevisionsStory($internal_code, $id_offer, $year)
    {
        $db = $this->_offerMapper->getDbAdapter();

        $select = $db->select();

        $select->from('offers', array('revision', 'date_created', 'created_by', 'date_modified', 'modified_by', 'active', 'code_offer'))
                ->joinLeft(array('u1' => 'users'), 'u1.user_id = offers.created_by', array('cusername' => 'username'))
                ->joinLeft(array('u2' => 'users'), 'u2.user_id = offers.modified_by', array('musername' => 'username'))
                ->where('internal_code = ?', $internal_code)
                ->where('id_offer = ?', $id_offer)
                ->where('year = ?', $year);

        return $db->fetchAll($select);
    }

    public function save($offer)
    {
		return $this->_offerMapper->save($offer);
    }

    public function getOffers($sort = false, $dir = false, $search = array(), $deleted = null, $count = null, $per_page = NULL)
    {
        $auth = Zend_Auth::getInstance()->getIdentity();

        $db = $this->_offerMapper->getDbAdapter();

        $select = $db->select();

        $select->from('offers', array('offer_id', 'date_offer', 'code_offer', 'date_end', 'importo' => 'offer_importo', 'al_partner' => new Zend_Db_Expr('offers.promotore_value')))
                ->joinLeft(array('c1' => 'companies'), 'c1.company_id = offers.id_company', array('cliente' => 'ragione_sociale'))
                ->joinLeft(array('ci' => 'companies_internals'), 'ci.id_company = c1.company_id and ci.id_internal = ' . $auth->internal_id, 'id_office')
                ->joinLeft(array('c2' => 'companies'), 'c2.company_id = offers.id_promotore', array('promotore' => 'ragione_sociale'))
                ->joinLeft(array('s' => 'services'), 's.service_id = offers.id_service', array('service' => 's.name'))
                ->join(array('si' => 'service_internal'), 'si.id_service = offers.id_service and si.id_internal = ' . $db->quote($auth->internal_id))
                ->joinLeft(array('ss' => 'subservices'), 'ss.subservice_id = offers.id_subservice', array('subservice' => 'ss.name', 'subservicecode' => 'ss.cod'))
                ->join(array('ssi' => 'subservice_internal'), 'ssi.id_subservice = offers.id_subservice and ssi.id_internal = ' . $db->quote($auth->internal_id))
                ->joinLeft(array('u' => 'users'), 'u.user_id = offers.id_rco', array('rco' => 'u.username'))
                ->joinLeft(array('os' => 'offer_status'), 'os.offer_status_id = offers.id_status', array('status' => 'os.name'))

                //->joinLeft(array('m' => 'moments'), 'm.id_offer = offers.offer_id', array('importo' => new Zend_Db_Expr('sum(m.importo)'), 'al_partner' => new Zend_Db_Expr('sum(m.importo) * offers.promotore_percent / 100')))
                ->group('offers.offer_id')

                ->where('offers.active = 1');


        $select->where('internal_code = ?', strtolower($auth->internal_abbr));

        if($auth->office_id)
        {
            $select->where('ci.id_office = ?', $auth->office_id);
        }

        if(!$auth->user_object->has_permission('offers', 'view') && $auth->user_object->has_permission('offers', 'view_own'))
        {
            $select->where('offers.created_by = ' . $auth->user_id);
        }

        if($count)
        {
            //$select->order('offers.date_modified desc');
            $select->order('offers.date_created desc');
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
                $select->order('offers.code_offer asc');
            }
        }

        if(is_array($search) && !empty($search))
        {
            if (isset($search['code_offer']) && $search['code_offer'] != '')
            {
                $select->where('code_offer like ' . $db->quote('%' . $search['code_offer'] . '%'));
            }

            if(isset($search['owner']) && $search['owner'] != '')
            {
                $select->where('offers.created_by = ' . $search['owner'] . ' OR id_rco = ' . $search['owner']);
            }

            if (isset($search['id_status']) && !empty($search['id_status']))
            {
                $select->where('id_status = ' . implode(' or id_status = ', $search['id_status']));
            }

            if (isset($search['id_order_status']) && !empty($search['id_order_status']))
            {
                $select->where('exists (select order_id from orders as ord where ord.id_offer = offer_id and (ord.id_status = ' . implode(' or ord.id_status = ', $search['id_order_status']) . '))');
                //$select->where('id_status = ' . implode(' or id_status = ', $search['id_status']));
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
            }
            else
            {
                if (isset($search['cliente']) && $search['cliente'] != '')
                {
                    $select->where('cliente  like ' . $db->quote('%' . $search['cliente'] . '%'));
                }
            }

            if (isset($search['ragione_sociale']) && !empty($search['ragione_sociale']))
            {
                $select->where('c1.ragione_sociale like ' . $db->quote('%' . $search['ragione_sociale'] . '%'));
            }

            if (isset($search['id_service']) && !empty($search['id_service']))
            {
                $select->where('offers.id_service = ' . implode(' or offers.id_service = ', $search['id_service']));
            }

            if (isset($search['id_subservice']) && !empty($search['id_subservice']))
            {
                $select->where('offers.id_subservice = ' . implode(' or offers.id_subservice = ', $search['id_subservice']));
            }

            if (isset($search['luogo']) && $search['luogo'] != '')
            {
                $select->where('luogo like ' . $db->quote('%' . $search['luogo'] . '%'));
            }

            if (isset($search['subject']) && $search['subject'] != '')
            {
                $select->where('subject like ' . $db->quote('%' . $search['subject'] . '%'));
            }

            if (isset($search['id_rco']) && !empty($search['id_rco']))
            {
                $select->where('id_rco = ' . implode(' or id_rco = ', $search['id_rco']));
            }

            if (isset($search['segnalato_da']) && !empty($search['segnalato_da']))
            {
                $select->where('offers.segnalato_da like ' . $db->quote('%' . $search['segnalato_da'] . '%'));
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

            if (isset($search['promotore_percent']) && $search['promotore_percent'] != '')
            {
                $parts = explode('-', $search['promotore_percent']);
                if(count($parts) == 2)
                {
                    $select->where('offers.promotore_percent >= ' . $parts[0] . ' and offers.promotore_percent <= ' . $parts[1]);
                }
                else
                {
                    $select->where('offers.promotore_percent = ' . $parts[0]);
                }
            }

            if (isset($search['id_interest']) && !empty($search['id_interest']))
            {
                $select->where('id_interest = ' . implode(' or id_interest = ', $search['id_interest']));
            }

            if (isset($search['sconto']) && $search['sconto'] != '')
            {
                $parts = explode('-', $search['sconto']);
                if(count($parts) == 2)
                {
                    $select->where('sconto >= ' . $parts[0] . ' and sconto <= ' . $parts[1]);
                }
                else
                {
                    $select->where('sconto = ' . $parts[0]);
                }
            }

            if (isset($search['id_pagamento']) && !empty($search['id_pagamento']))
            {
                $select->where('id_pagamento = ' . implode(' or id_pagamento = ', $search['id_pagamento']));
            }

            if (isset($search['date_offer']) && $search['date_offer'] != '')
            {
                $parts = explode('-', $search['date_offer']);

                if(count($parts) == 2)
                {
                    $select->where('date_offer >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_offer <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
                }
                else
                {
                    $select->where('date_offer = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
                }
            }

            if (isset($search['date_accepted']) && $search['date_accepted'] != '')
            {
                $parts = explode('-', $search['date_accepted']);

                if(count($parts) == 2)
                {
                    $select->where('date_accepted >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_accepted <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
                }
                else
                {
                    $select->where('date_accepted = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
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
        }
        else if(is_string($search))
        {
            $select->where($search);
        }

        if($per_page !== NULL)
        {
            $offers = Zend_Paginator::factory($select);
            $offers->setItemCountPerPage($per_page);
            $offers->setCurrentPageNumber(isset($search['page']) ? $search['page'] : 1);
        }
        else
        {
            $offers = $db->fetchAll($select);
        }

        return $offers;
    }

    public function getTotals($search = array(), $deleted = null)
    {
        $auth = Zend_Auth::getInstance()->getIdentity();

        $db = $this->_offerMapper->getDbAdapter();

        $select = $db->select();

        $select->from('offers', array('total' => 'sum(offers.offer_importo)', 'to_promotors' => new Zend_Db_Expr('sum(offers.promotore_value)'), 'count' => new Zend_Db_Expr('count(distinct offers.offer_id)')))
        //$select->from('moments', array(new Zend_Db_Expr('sum(importo) as total'), new Zend_Db_Expr('sum(importo * offers.promotore_percent / 100) as to_promotors'), new Zend_Db_Expr('count(distinct offers.offer_id) as count')))
          //      ->joinLeft('offers', 'offers.offer_id = moments.id_offer', array())
                ->joinLeft(array('c1' => 'companies'), 'c1.company_id = offers.id_company', array())
                ->joinLeft(array('ci' => 'companies_internals'), 'ci.id_company = c1.company_id and ci.id_internal = ' . $auth->internal_id, array())
                ->joinLeft(array('c2' => 'companies'), 'c2.company_id = offers.id_promotore', array())
                ->joinLeft(array('s' => 'services'), 's.service_id = offers.id_service', array())
                ->join(array('si' => 'service_internal'), 'si.id_service = offers.id_service and si.id_internal = ' . $db->quote($auth->internal_id))
                ->joinLeft(array('ss' => 'subservices'), 'ss.subservice_id = offers.id_subservice', array())
                ->join(array('ssi' => 'subservice_internal'), 'ssi.id_subservice = offers.id_subservice and ssi.id_internal = ' . $db->quote($auth->internal_id))
                ->joinLeft(array('u' => 'users'), 'u.user_id = offers.id_rco', array())
                ->joinLeft(array('os' => 'offer_status'), 'os.offer_status_id = offers.id_status', array())
                ->where('offers.active = 1');

        $select->where('internal_code = ?', strtolower($auth->internal_abbr));

        if($auth->office_id)
        {
            $select->where('ci.id_office = ?', $auth->office_id);
        }

        if(!$auth->user_object->has_permission('offers', 'view') && $auth->user_object->has_permission('offers', 'view_own'))
        {
            $select->where('offers.created_by = ' . $auth->user_id);
        }

        if(is_array($search) && !empty($search))
        {
            if (isset($search['code_offer']) && $search['code_offer'] != '')
            {
                $select->where('code_offer like ' . $db->quote('%' . $search['code_offer'] . '%'));
            }

            if(isset($search['owner']) && $search['owner'] != '')
            {
                $select->where('offers.created_by = ' . $search['owner'] . ' OR id_rco = ' . $search['owner']);
            }

            if (isset($search['id_status']) && !empty($search['id_status']))
            {
                $select->where('id_status = ' . implode(' or id_status = ', $search['id_status']));
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
            }
            else
            {
                if (isset($search['cliente']) && $search['cliente'] != '')
                {
                    $select->where('cliente  like ' . $db->quote('%' . $search['cliente'] . '%'));
                }
            }

            if (isset($search['ragione_sociale']) && !empty($search['ragione_sociale']))
            {
                $select->where('c1.ragione_sociale like ' . $db->quote('%' . $search['ragione_sociale'] . '%'));
            }

            if (isset($search['id_service']) && !empty($search['id_service']))
            {
                $select->where('offers.id_service = ' . implode(' or offers.id_service = ', $search['id_service']));
            }

            if (isset($search['id_subservice']) && !empty($search['id_subservice']))
            {
                $select->where('offers.id_subservice = ' . implode(' or offers.id_subservice = ', $search['id_subservice']));
            }

            if (isset($search['luogo']) && $search['luogo'] != '')
            {
                $select->where('luogo like ' . $db->quote('%' . $search['luogo'] . '%'));
            }

            if (isset($search['subject']) && $search['subject'] != '')
            {
                $select->where('subject like ' . $db->quote('%' . $search['subject'] . '%'));
            }

            if (isset($search['id_approver']) && !empty($search['id_approver']))
            {
                $select->where('id_approver = ' . implode(' or id_approver = ', $search['id_approver']));
            }

            if (isset($search['id_rco']) && !empty($search['id_rco']))
            {
                $select->where('id_rco = ' . implode(' or id_rco = ', $search['id_rco']));
            }

            if (isset($search['segnalato_da']) && !empty($search['segnalato_da']))
            {
                $select->where('offers.segnalato_da like ' . $db->quote('%' . $search['segnalato_da'] . '%'));
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

            if (isset($search['promotore_percent']) && $search['promotore_percent'] != '')
            {
                $parts = explode('-', $search['promotore_percent']);
                if(count($parts) == 2)
                {
                    $select->where('offers.promotore_percent >= ' . $parts[0] . ' and offers.promotore_percent <= ' . $parts[1]);
                }
                else
                {
                    $select->where('offers.promotore_percent = ' . $parts[0]);
                }
            }

            if (isset($search['id_interest']) && !empty($search['id_interest']))
            {
                $select->where('id_interest = ' . implode(' or id_interest = ', $search['id_interest']));
            }

            if (isset($search['sconto']) && $search['sconto'] != '')
            {
                $parts = explode('-', $search['sconto']);
                if(count($parts) == 2)
                {
                    $select->where('sconto >= ' . $parts[0] . ' and sconto <= ' . $parts[1]);
                }
                else
                {
                    $select->where('sconto = ' . $parts[0]);
                }
            }

            if (isset($search['id_pagamento']) && !empty($search['id_pagamento']))
            {
                $select->where('id_pagamento = ' . implode(' or id_pagamento = ', $search['id_pagamento']));
            }

            if (isset($search['date_offer']) && $search['date_offer'] != '')
            {
                $parts = explode('-', $search['date_offer']);

                if(count($parts) == 2)
                {
                    $select->where('date_offer >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_offer <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
                }
                else
                {
                    $select->where('date_offer = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
                }
            }

            if (isset($search['date_accepted']) && $search['date_accepted'] != '')
            {
                $parts = explode('-', $search['date_accepted']);

                if(count($parts) == 2)
                {
                    $select->where('date_accepted >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_accepted <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
                }
                else
                {
                    $select->where('date_accepted = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
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
        }
        else if(is_string($search))
        {
            $select->where($search);
        }

        return $db->fetchRow($select);
    }

    public function getCompaniesWithOffers($search)
    {
        $db = $this->_offerMapper->getDbAdapter();
        $auth = Zend_Auth::getInstance()->getIdentity();

        return $db->fetchAll(
            'select distinct company_id, ragione_sociale from offers ' .
                    'left join companies on company_id = offers.id_company ' .
                    'left join companies_internals on companies_internals.id_company = company_id ' .
                    'where id_internal = ' . $auth->internal_id . ' and ' .
                    'ragione_sociale like ' . $db->quote('%' . $search . '%')
        );
    }

    public function saveFromData($data, $prefix = '')
    {
		if(!isset($data['promotore_value_flag']))
        {
            $data['promotore_value'] = 0;
            $data['promotore_percent'] = 0;
        }
        elseif($data['promotore_value_flag'] == 'P')
        {
            $data['promotore_value'] = 0;
        }
        elseif($data['promotore_value_flag'] == 'V')
        {
            $data['promotore_percent'] = 0;
        }
		if($data['sconto']=='')
		{
			$data['sconto'] = 0;
		}
		
	/*	if($data['p_valore_g_uomo']=='')
		{
			$data['p_valore_g_uomo'] = 0;
		} */
		
		if(!isset($data['p_valore_g_uomo']))
		{
			$data['p_valore_g_uomo'] = 0;
		}
		
        if(isset($data['offer_id']) && $data['offer_id'])
        {
            $offer = $this->find($data['offer_id']);
            if(!$offer)
            {
                throw new Exception('offer to be edited not found!');
            }
        }
        else
        {
            $offer = new Model_Offer();
        }

        $offer->setValidatorAndFilter(new Model_Offer_Validator());
        $offer->setData($data, $prefix);

        $aut = Zend_Auth::getInstance()->getIdentity();

        $create_code = false;

        $offer_edit = false;
        $offer_new_revision = false;
        $message_title = '';
        $message_body = '';

        $cf = Zend_Controller_Front::getInstance();
        $base_url = /* $cf->getRequest()->getScheme()
                    . '://'
                    . $cf->getRequest()->getHttpHost()
                    . */ $cf->getBaseUrl();

        if($offer->offer_id == '')
        {
            $message_title = 'Nuova Offerta ';
            $message_body = 'E\' stata creata l\'offerta <a href="' . $base_url
                    . '/offers/detail/id/{offer_id}"><b>{offer_code}</b></a> per l\'azienda <a href="'
                    . $base_url . '/companies/detail/id/{company_id}"><b>{company_name}</b></a> e '
                    . 'la tua utenza &egrave; stata impostata come RCO!';

            $create_code = true;
            $offer->year = date('Y');
            // new offer
            $offer->active = 1;
            $offer->revision = 1;

            $offer->id_status = 1;

            $db = $this->_offerMapper->getDbAdapter();
            $select = $db->select();
            $select->from('offers', array('year', 'id_offer'))
                    ->where('internal_code = ?', $aut->internal_abbr)
                    ->order('year DESC')
                    ->order('id_offer DESC')
                    ->limit(1, 0);
            $vals = $db->fetchRow($select);
            if($offer->year > $vals['year'])
            {
                $offer->id_offer = 1;
            }
            else
            {
                $offer->id_offer = $vals['id_offer'] + 1;
            }
        }
        else
        {
            $offer_edit = true;

            if(isset($data['nr']) && $data['nr'] == 1)
            {
                // nuova revisione
                $message_title = 'Nuova revisione per la offerta ';
                $message_body = 'Nuova revisione per l\'offerta <a href="' . $base_url
                        . '/offers/detail/id/{offer_id}"><b>{offer_code}</b></a> per l\'azienda <a href="'
                        . $base_url . '/companies/detail/id/{company_id}"><b>{company_name}</b></a> '
                        . 'che ti vede come RCO';

                $offer_new_revision = true;

                $create_code = true;
                $offer->offer_id = '';

                $offer->active = 1;
                $db = $this->_offerMapper->getDbAdapter();
                $select = $db->select();
                $select->from('offers', new Zend_Db_Expr('max(revision)'))
                        ->where('id_offer = ?', $offer->id_offer)
                        ->where('year = ?', $offer->year);
                $rev = $db->fetchOne($select);
                $offer->revision = $rev + 1;
                // disattivare le altre
                $db->update('offers', array('active' => 0), 'id_offer = ' . $db->quote($offer->id_offer));
            }
            else
            {
                $message_title = 'Modificata offerta ';
                $message_body = 'E\' stata modifica l\'offerta <a href="' . $base_url
                        . '/offers/detail/id/{offer_id}"><b>{offer_code}</b></a> per l\'azienda <a href="'
                        . $base_url . '/companies/detail/id/{company_id}"><b>{company_name}</b></a> che '
                        . ' ti vede come RCO';

                $db = $this->_offerMapper->getDbAdapter();
                $offer->code_offer = $db->fetchOne('select code_offer from offers where offer_id = ?', $offer->offer_id);
            }
        }

        if($create_code)
        {
            $offer->internal_code = strtolower($aut->internal_abbr);

            // code_internal - year - id_offer - cod_subservice - revision
            $services_codes = $db->fetchRow('select ss.cod as sscod, s.cod as scod from subservices ss, services s where s.service_id = ss.id_service and ss.subservice_id = ' . $offer->id_subservice);
            $offer->code_offer = $offer->internal_code . '-' . $offer->year . '-' . $offer->id_offer . '-'
                    . $services_codes['scod'] . '-' . $services_codes['sscod'] . '-' . $offer->revision;
        }

        $message_title .= $offer->code_offer;

        if($offer->isValid())
        {
            Maco_Model_TransactionManager::beginTransaction();

            try
            {
                $offer_id = (int) $this->_offerMapper->save($offer);

                $offer_edited = $this->find($offer_id);

                $utils = new Maco_Input_Utils();
                // la utils toglie il prefisso

                $total = 0;

                $momentRepo = Maco_Model_Repository_Factory::getRepository('moment');
                $momentsData = $utils->formatDataForMultipleFields(array('moment_id', 'id_offer', 'tipologia', 'importo', 'expected_date', 'p_valore_g_uomo', 'idx'), $prefix . 'moments_', $data);
                foreach($momentsData as $index => $moment)
                {
                    $empty = implode('', $moment);
                    if(!empty($empty))
                    {
                        $total += (int)$moment['importo'];

                        $moment['id_offer'] = $offer_id;
                        $moment['index'] = $index + 1;
                        $moment['fatturazione'] = (int) (isset($data['moments_fatturazione_' . $moment['idx']]) && $data['moments_fatturazione_' . $moment['idx']] == 1);
                        //$moment['done'] = 0;
                        if($data['nr'])
                        {
                            $moment['moment_id'] = '';
                        }
                        if(is_array($res = $momentRepo->saveFromData($moment)))
                        {
                            Maco_Model_TransactionManager::rollback();
                            return $res;
                        }
                    }
                }

                $db->update('offers', array('offer_importo' => $total), 'offer_id = ' . $offer_id);

                if($offer_edited->promotore_value_flag == 'P')
                {
                    $db->update('offers', array('promotore_value' => $total * $offer_edited->promotore_percent / 100), 'offer_id = ' . $offer_id);
                }

                foreach($offer_edited->moments as $old)
                {
                    $in = false;
                    foreach($momentsData as $moment)
                    {
                        if($old->moment_id == $moment['moment_id'])
                        {
                            $in = true;
                            break;
                        }
                    }
                    if(!$in)
                    {
                        $momentRepo->delete($old->moment_id);
                    }
                }
                unset($momentRepo, $momentsData);

                $company_repo = Maco_Model_Repository_Factory::getRepository('company');
                $company = $company_repo->find($offer->id_company);

                // send a message to the rco for this offer
                $message_repo = Maco_Model_Repository_Factory::getRepository('message');
                $message_body = str_replace(
                    array('{offer_code}', '{offer_id}', '{company_name}', '{company_id}'),
                    array($offer->code_offer, $offer_id, $company->ragione_sociale, $company->company_id),
                    $message_body
                );
                $message_repo->send($offer->id_rco, $message_title, $message_body, Model_Message_Types::INFO);


                Maco_Model_TransactionManager::commit();
                return $offer_id;
            }
            catch(Exception $e)
            {
                Maco_Model_TransactionManager::rollback();
                return array($e->getMessage());
            }
        }
        else
        {
            return $offer->getInvalidMessages();
        }

        // fist the contact
        $offerRepo = Maco_Model_Repository_Factory::getRepository('contact');
        $res = $offerRepo->saveFromData($data, 'contacts_');
        if(is_array($res))
        {
            // no good
            return $res;
        }
    }

    public function getFromData($data, $prefix = '')
    {
        $offer = new Model_Offer();
        $offer->setValidatorAndFilter(new Model_Offer_Validator());
        $offer->setData($data, $prefix);
        $offer->isValid();

        $utils = new Maco_Input_Utils();

        $momentRepo = Maco_Model_Repository_Factory::getRepository('moment');
        $momentsData = $utils->formatDataForMultipleFields(array('moment_id', 'id_offer', 'tipologia', 'importo', 'expected_date', 'fatturazione'), $prefix . 'moments_', $data);
        if(!empty($momentsData))
        {
            $moments = array();
            foreach ($momentsData as $moment)
            {
                $moments[] = $momentRepo->getFromData($moment);
            }
            $offer->moments = $moments;
        }
        else
        {
            $offer->moments = array($momentRepo->getNewMoment());
        }

        return $offer;
    }

    public function activateRevision($id_offer, $rev)
    {
        $db = $this->_offerMapper->getDbAdapter();
        $id = $db->fetchOne('select offer_id from offers where id_offer = '
                . $db->quote($id_offer) . ' and revision = '
                . $db->quote($rev));

        return $this->_activeRevision($id);
    }

    protected function _activeRevision($id)
    {
        $db = $this->_offerMapper->getDbAdapter();
        $db->beginTransaction();
        try
        {
            $id_offer = $db->fetchOne('select id_offer from offers where offer_id = ' . $db->quote($id));
            $db->update('offers', array('active' => 0), 'id_offer = ' . $id_offer);
            $db->update('offers', array('active' => 1), 'offer_id = ' . $db->quote($id));
            return $db->commit();
        }
        catch (Exception $e)
        {
            $db->rollBack();
            return false;
        }
    }

    public function setStatus($id_offer, $id_status, $id_approver = null, $hidden = null)
    {
        $db = $this->_offerMapper->getDbAdapter();
        $message_repo = Maco_Model_Repository_Factory::getRepository('message');
        $offer_data = $db->fetchRow('select id_offer, id_approver, id_rco, created_by, code_offer from offers where offer_id = ' . $db->quote($id_offer));
        $cf = Zend_Controller_Front::getInstance();
        $base_url = /* $cf->getRequest()->getScheme()
                    . '://'
                    . $cf->getRequest()->getHttpHost()
                    . */ $cf->getBaseUrl();

        // HARD CODED - NON APPROVATA messaggio al creatore
        // non approvata ID = 7
        if($id_status == 7)
        {
            $message_title = 'Revisionare offerta non approvata: ' . $offer_data['code_offer'];
            $message_body = 'L\'offerta <a href="' . $base_url . '/offers/detail/id/' . $id_offer . '"><b>' . $offer_data['code_offer'] . '</b></a> non &egrave; stata approvata!';
            $message_repo->send($offer_data['id_rco'], $message_title, $message_body, Model_Message_Types::TODO, null, $offer_data['id_offer'] . '-RCO-REVIEW', true);
        }
        elseif($id_status == 2)
        {
            $message_title = 'Inviare offerta approvata: ' . $offer_data['code_offer'];
            $message_body = 'L\'offerta <a href="' . $base_url . '/offers/detail/id/' . $id_offer . '"><b>' . $offer_data['code_offer'] . '</b></a> &egrave; stata approvata!';
            $message_repo->send($offer_data['id_rco'], $message_title, $message_body, Model_Message_Types::TODO, null, $offer_data['id_offer'] . '-RCO-SEND', true);
        }

        if($id_status == 7 || $id_status == 2)
        {
            $uid = $offer_data['id_offer'] . '-DCO-APPROVE';
            $toRemoveID = $offer_data['id_approver']; // id approver
            $message_repo->deleteByToAndUid($toRemoveID, $uid);
        }

        $data = array(
            'id_status' => (int)$id_status,
        );
        if($id_approver)
        {
            $data['id_approver'] = $id_approver;

            if(!$hidden)
            {
                //$message_title = 'Sei responsabile dell\'approvazione dell\'offerta ' . $offer_data['code_offer'];
                $message_title = 'Offerta da approvare: ' . $offer_data['code_offer'];
                $message_body = 'Sei stato segnalato come responsabile dell\'approvazione dell\'offerta <a href="' . $base_url . '/offers/detail/id/' . $id_offer . '"><b>' . $offer_data['code_offer'] . '</b></a>!';
                $message_repo->send($id_approver, $message_title, $message_body, Model_Message_Types::TODO, null, $offer_data['id_offer'] . '-DCO-APPROVE', true);

                $uid = $offer_data['id_offer'] . '-RCO-REVIEW';
                $toRemoveID = $offer_data['id_rco']; // id approver
                $message_repo->deleteByToAndUid($toRemoveID, $uid);
            }
        }

        if($id_status == 4)
        {
            $message_title = 'Creare RALI per offerta: ' . $offer_data['code_offer'];
            $message_body = 'L\'offerta  <a href="' . $base_url . '/offers/detail/id/' . $id_offer . '"><b>' . $offer_data['code_offer'] . '</b></a> &egrave; stata aggiudicata!';
            $message_repo->send($offer_data['id_rco'], $message_title, $message_body, Model_Message_Types::TODO, null, $offer_data['id_offer'] . '-RCO-RALI', true);

            $message_title = 'L\'offerta ' . $offer_data['code_offer'] . ' &egrave; stata accettata';
            $message_repo->send($id_approver, $message_title, $message_body, Model_Message_Types::INFO);
        }
        elseif($id_status == 5)
        {
            //$message_title = 'Revisionare offerta non accettata: ' . $offer_data['code_offer'];
            $message_body = 'L\'offerta  <a href="' . $base_url . '/offers/detail/id/' . $id_offer . '"><b>' . $offer_data['code_offer'] . '</b></a> non &egrave; stata accettata!';
            //$message_repo->send($offer_data['id_rco'], $message_title, $message_body, Model_Message_Types::TODO, null, $offer_data['id_offer'] . '-RCO-REVIEW', true);

            $message_title = 'L\'offerta ' . $offer_data['code_offer'] . ' non &egrave; stata accettata';
            $message_repo->send($id_approver, $message_title, $message_body, Model_Message_Types::INFO);
        }

        if($id_status == 4)
        {
            $data['date_accepted'] = date('Y-m-d');
        }
        elseif($id_status == 3)
        {
            $data['date_sent'] = date('Y-m-d');
            
			$message_title = 'L\'offerta ' . $offer_data['code_offer'] . ' &egrave; stata inviata al cliente';
            $message_body = 'L\'offerta  <a href="' . $base_url . '/offers/detail/id/' . $id_offer . '"><b>' . $offer_data['code_offer'] . '</b></a> &egrave; stata inviata al cliente!';
            $message_repo->send($offer_data['id_approver'], $message_title, $message_body, Model_Message_Types::INFO);

            $uid = $offer_data['id_offer'] . '-RCO-SEND';
            $toRemoveID = $offer_data['id_rco']; // id approver
            $message_repo->deleteByToAndUid($toRemoveID, $uid);
        }

        return $db->update('offers', $data, 'offer_id = ' . $db->quote($id_offer));
    }
}
