<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 7-set-2010
 * Time: 11.08.41
 * To change this template use File | Settings | File Templates.
 */

class Model_Company_Repository
{
    /**
     * Companies mysql mapper
     *
     * @var Model_Company_Mapper
     */
    protected $_companyMapper;

    /**
     * Mails mysql mapper
     *
     * @var Model_Mail_Mapper
     */
    protected $_mailMapper;

    public function __construct()
    {
        $this->_companyMapper = new Model_Company_Mapper();
    }

    public function getNewCompany()
    {
        $new = new Model_Company();

        $mailsRepo = Maco_Model_Repository_Factory::getRepository('mail');
        $telephonesRepo = Maco_Model_Repository_Factory::getRepository('telephone');
        $addressesRepo = Maco_Model_Repository_Factory::getRepository('address');
        $websiteRepo = Maco_Model_Repository_Factory::getRepository('website');

        $new->mails = array($mailsRepo->getNewMail());
        $new->telephones = array($telephonesRepo->getNewTelephone());
        $new->addresses = array($addressesRepo->getNewAddress());
        $new->websites = array($websiteRepo->getNewWebsite());
        return $new;
    }

    public function find($id)
    {
        $item = $this->_companyMapper->find($id);

        $mailsRepo = Maco_Model_Repository_Factory::getRepository('mail');
        $telephonesRepo = Maco_Model_Repository_Factory::getRepository('telephone');
        $addressesRepo = Maco_Model_Repository_Factory::getRepository('address');
        $websitesRepo = Maco_Model_Repository_Factory::getRepository('website');

        $item->mails = $mailsRepo->findByCompany($id);
        if (count($item->mails) == 0)
        {
            $item->mails = array($mailsRepo->getNewMail());
        }
        $item->telephones = $telephonesRepo->findByCompany($id);
        if (count($item->telephones) == 0)
        {
            $item->telephones = array($telephonesRepo->getNewTelephone());
        }
        $item->addresses = $addressesRepo->findByCompany($id);
        if (count($item->addresses) == 0)
        {
            $item->addresses = array($addressesRepo->getNewAddress());
        }
        $item->websites = $websitesRepo->findByCompany($id);
        if (count($item->websites) == 0)
        {
            $item->websites = array($websitesRepo->getNewWebsite());
        }

        return $item;
    }

    public function save($item)
    {
        return $this->_companyMapper->save($item);
    }

    public function saveWithId($item)
    {
        return $this->_companyMapper->saveWithId($item);
    }

    public function validateAndSave($item)
    {
        $item->setValidatorAndFilter(new Model_Company_Validator());
        if($item->isValid())
        {
            $company_id = (int) $this->_companyMapper->save($item);
            return $company_id;
        }
        else
        {
            return false;
        }
    }

    public function exportCompanies($sort = false, $dir = false, $search = array(), $excluded = null)
    {
        include(LIBRARY_PATH . '/Tbs/tbs_class.php');
        include(LIBRARY_PATH . '/Tbs/plugins/tbs_plugin_opentbs.php');

        $filesMapper = new Model_FilesMapper();

        $template_name = 'companies.xlsx';

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

        $db = $this->_companyMapper->getDbAdapter();

        $select = $db->select();

        $user = Zend_Auth::getInstance()->getIdentity();

        $select->from('companies', array(
            'company_id', 'ragionesociale' => 'ragione_sociale',
            'ispartner' => 'is_partner', 'iscliente' => 'is_cliente',
            'isfornitore' => 'is_fornitore', 'ispromotore' => 'is_promotore',
            'partita_iva', 'segnalato_da', 'prodotti', 'note',
            'numbers' => new Zend_Db_Expr("group_concat(DISTINCT concat_ws(' ', telephones.description, telephones.number) SEPARATOR ' --- ')"),
            'mails' => new Zend_Db_Expr("group_concat(DISTINCT concat_ws(' ', mails.description, mails.mail) SEPARATOR ' --- ')"),
            'addresses' => new Zend_Db_Expr("group_concat(DISTINCT concat_ws(' ', addresses.description, addresses.via, addresses.numero, addresses.cap, addresses.localita, addresses.provincia) SEPARATOR ' --- ')")
        ))
                ->joinLeft(array('u1' => 'users'), 'u1.user_id = companies.rco', array('rco_name' => 'u1.username'))
                ->joinLeft('companies_internals', 'companies_internals.id_company = company_id', array())
                ->joinLeft('categories', 'categories.category_id = companies.categoria', array('categoria_name' => 'categories.name'))
                ->joinLeft('ea', 'ea.ea_id = companies.ea', array('ea_name' => 'name'))
                ->joinLeft('organici_medi', 'organico_medio_id = companies.organico_medio', array('organico_medio_name' => 'organici_medi.name'))
                ->joinLeft('fatturati', 'fatturato_id = companies.fatturato', array('fatturato_name' => 'fatturati.name'))
                ->joinLeft('conosciuto_come', 'conosciuto_come_id = companies.conosciuto_come', array('conosciuto_come_name' => 'conosciuto_come.name'))
                ->joinLeft('telephones', 'telephones.id_company = companies.company_id', array())
                ->joinLeft('addresses', 'addresses.id_company = companies.company_id', array())
                ->joinLeft('mails', 'mails.id_company = companies.company_id', array())
                ->group('company_id')
                ->where('id_internal = ?', $user->internal_id);

        if($user->office_id)
        {
            $select->where('id_office = ?', $user->office_id);
        }

        $select->where('companies.deleted = ?', $excluded);

        if ($sort)
        {
            $select->order($sort . ' ' . $dir);
        }
        else
        {
            $select->order('ragione_sociale ASC');
        }

        if (isset($search['ragione_sociale']) && $search['ragione_sociale'] != '')
        {
            $select->where('ragione_sociale like ' . $db->quote('%' . $search['ragione_sociale'] . '%'));
        }
        else
        {
            if (isset($search['ragionesociale']) && $search['ragionesociale'] != '')
            {
                $select->where('ragione_sociale like ' . $db->quote('%' . $search['ragionesociale'] . '%'));
            }
        }
        if (isset($search['cf']) && $search['cf'] != '')
        {
            $select->where('cf like ' . $db->quote('%' . $search['cf'] . '%'));
        }
        if (isset($search['partita_iva']) && $search['partita_iva'] != '')
        {
            $select->where('partita_iva like ' . $db->quote('%' . $search['partita_iva'] . '%'));
        }
        if (isset($search['iban']) && $search['iban'] != '')
        {
            $select->where('iban like ' . $db->quote('%' . $search['iban'] . '%'));
        }

        if (isset($search['rco']) && !empty($search['rco']))
        {
            $select->where('rco = ' . implode(' or rco = ', $search['rco']));
        }

        if (isset($search['segnalato_da']) && $search['segnalato_da'] != '')
        {
            $select->where('segnalato_da like ' . $db->quote('%' . $search['segnalato_da'] . '%'));
        }

        if (isset($search['categoria']) && !empty($search['categoria']))
        {
            $select->where('categoria = ' . implode(' or categoria = ', $search['categoria']));
        }

        if (isset($search['ea']) && !empty($search['ea']))
        {
            $select->where('ea = ' . implode(' or ea = ', $search['ea']));
        }

        if (isset($search['organico_medio']) && !empty($search['organico_medio']))
        {
            $select->where('organico_medio = ' . implode(' or organico_medio = ', $search['organico_medio']));
        }

        if (isset($search['fatturato']) && !empty($search['fatturato']))
        {
            $select->where('fatturato = ' . implode(' or fatturato = ', $search['fatturato']));
        }

        if (isset($search['conosciuto_come']) && !empty($search['conosciuto_come']))
        {
            $select->where('conosciuto_come = ' . implode(' or conosciuto_come = ', $search['conosciuto_come']));
        }

        if (isset($search['cname']) && $search['cname'] != '')
        {
            $select->where('categories.name like ' . $db->quote('%' . $search['cname'] . '%'));
        }

        if (isset($search['status']) && !empty($search['status']))
        {
            $select->where('status = ' . implode(' or status = ', $search['status']));
        }
        {
            if (isset($search['stato']) && $search['stato'] != '')
            {
                $select->where('status.name like ' . $db->quote('%' . $search['stato'] . '%'));
            }
        }
        /*
        if (isset($search['id_promotore']) && !empty($search['id_promotore']))
        {
            $wh = '';
            foreach($search['id_promotore'] as $id_company)
            {
                if($id_company != '')
                {
                    $wh .= 'companies.id_promotore = ' . $db->quote($id_company) . ' OR ';
                }
            }
            if($wh != '')
            {
                $wh = substr($wh, 0, -4);
                $select->where($wh);
            }
        }
        */
        if (isset($search['id_promotore']) && !empty($search['id_promotore']))
        {
            $idpparts = explode(',', $search['id_promotore']);
            $wh = '';
            foreach($idpparts as $id_company)
            {
                if($id_company != '')
                {
                    $wh .= 'companies.id_promotore = ' . $db->quote($id_company) . ' OR ';
                }
            }
            if($wh != '')
            {
                $wh = substr($wh, 0, -4);
                $select->where($wh);
            }
        }

        if (isset($search['date_created']) && $search['date_created'] != '')
        {
            $parts = explode('-', $search['date_created']);

            if(count($parts) == 2)
            {
                $select->where('companies.date_created >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and companies.date_created <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('companies.date_created = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
            }
        }

        if (isset($search['id_office']) && $search['id_office'] != '')
        {
            $select->where('id_office = ?', $search['id_office']);
        }

        /*
        if (isset($search['is_partner']) && $search['is_partner'] != '')
        {
            $select->where('is_partner like ' . $db->quote('%' . $search['is_partner'] . '%'));
        }
        */
        if (isset($search['tipologia']) && !empty($search['tipologia']))
        {
            $w = '';
            foreach($search['tipologia'] as $t)
            {
                if($t == 'is_partner')
                {
                    $w .= 'is_partner = 1 or ';
                }
                if($t == 'is_fornitore')
                {
                    $w .= 'is_fornitore = 1 or ';
                }
                if($t == 'is_promotore')
                {
                    $w .= 'is_promotore = 1 or ';
                }
                if($t == 'is_cliente1')
                {
                    $w .= 'is_cliente = 1 or ';
                }
                if($t == 'is_cliente2')
                {
                    $w .= 'is_cliente = 2 or ';
                }
            }
            if($w != '')
            {
                $w = substr($w, 0, -3);
                $select->where($w);
            }
        }
        else
        {
            if (isset($search['ispartner']) && $search['ispartner'] != '')
            {
                $select->where('is_partner like ' . $db->quote('%' . $search['ispartner'] . '%'));
            }
            if (isset($search['ispromotore']) && $search['ispromotore'] != '')
            {
                $select->where('is_promotore like ' . $db->quote('%' . $search['ispromotore'] . '%'));
            }
            if (isset($search['iscliente']) && $search['iscliente'] != '')
            {
                $select->where('is_cliente like ' . $db->quote('%' . $search['iscliente'] . '%'));
            }
            if (isset($search['isfornitore']) && $search['isfornitore'] != '')
            {
                $select->where('is_fornitore like ' . $db->quote('%' . $search['isfornitore'] . '%'));
            }
        }

        if (isset($search['promotore_percent']) && $search['promotore_percent'] != '')
        {
            $parts = explode('-', $search['promotore_percent']);
            if(count($parts) == 2)
            {
                $select->where('promotore_percent >= ' . $parts[0] . ' and promotore_percent <= ' . $parts[1]);
            }
            else
            {
                $select->where('promotore_percent = ' . $parts[0]);
            }
        }

        // filtro regione - provincia
        $query_in = '';
        if (isset($search['regione']) && !empty($search['regione']))
        {
            $query_in = "a2.regione = '" . implode("' or a2.regione = '", $search['regione']) . "'";
        }
        if (isset($search['provincia']) && !empty($search['provincia']))
        {
            $query_in .= (($query_in != '') ? ' or ' : '') . "a2.provincia = '" . implode("' or a2.provincia = '", $search['provincia']) . "'";
        }
        if($query_in != '')
        {
            $select->where('company_id in (select a2.id_company from addresses a2 where ' . $query_in . ')');
        }

        $select->order('company_id asc');

        $companies = $db->fetchAll($select);

        $companies_data = array();
        foreach($companies as $company)
        {
            $companies_data[] = array(
                'id' => utf8_decode($company['company_id']),
                'ragionesociale' => utf8_decode($company['ragionesociale']),
                'partitaiva' => $company['partita_iva'],
                'rco' => utf8_decode($company['rco_name']),
                'segnalatoda' => utf8_decode($company['segnalato_da']),
                'categoria' => utf8_decode($company['categoria_name']),
                'ea' => utf8_decode($company['ea_name']),
                'organicomedio' => utf8_decode($company['organico_medio_name']),
                'fatturato' => utf8_decode($company['fatturato_name']),
                'conosciutocome' => utf8_decode($company['conosciuto_come_name']),
                'prodotti' => utf8_decode($company['prodotti']),
                'note' => utf8_decode($company['note']),
                'telefono' => utf8_decode($company['numbers']),
                'indirizzo' => utf8_decode($company['addresses']),
                'email' => utf8_decode($company['mails']),
            );
        }
        $tbs->MergeBlock('o', $companies_data);

        $file_name = 'aziende.xlsx';
        $tbs->Show(OPENTBS_DOWNLOAD, $file_name);
        exit;
    }

    public function get_count_companies_rco($id_rco, $period = null)
    {
        $db = $this->_companyMapper->getDbAdapter();

        $select = $db->select();

        $select->from('companies', new Zend_Db_Expr('count(company_id)'))
                ->where('rco = ?', $id_rco);

        if($period)
        {
            $parts = explode('-', $period);

            if(count($parts) == 2)
            {
                $select->where('date_created >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_created <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('date_created = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
            }
        }

        return $db->fetchOne($select);
    }

    public function getCompanies($sort = null, $dir = 'ASC', $search = array(), $excluded = 0, $per_page = NULL)
    {
        $db = $this->_companyMapper->getDbAdapter();

        $select = $db->select();

        $user = Zend_Auth::getInstance()->getIdentity();

        $select->from('companies', array('company_id', 'ragionesociale' => 'ragione_sociale', 'ispartner' => 'is_partner', 'iscliente' => 'is_cliente', 'isfornitore' => 'is_fornitore', 'ispromotore' => 'is_promotore', 'partita_iva',
            'addresses' => new Zend_Db_Expr("group_concat(DISTINCT concat_ws(' ', addresses.via, addresses.numero, addresses.cap, addresses.localita, addresses.provincia) SEPARATOR ' <hr /> ')")))
        //->joinLeft('status', 'status.status_id=status', array('stato' => 'name'))
                ->joinLeft('companies_internals', 'companies_internals.id_company = company_id', array())
                ->joinLeft('addresses', 'addresses.id_company = company_id', array())
                //->joinLeft('offices', 'offices.office_id = companies_internals.id_office', array('office_id', 'office_name' => 'offices.name'))
                ->where('companies_internals.id_internal = ?', $user->internal_id)
                ->group('company_id');
        if($user->office_id)
        {
            $select->where('id_office = ?', $user->office_id);
        }

        $select->where('companies.deleted = ?', $excluded);

        if(!$user->user_object->has_permission('companies', 'view') && $user->user_object->has_permission('companies', 'view_own'))
        {
            $select->where('companies.created_by = ' . $user->user_id);
        }

        if ($sort)
        {
            $select->order($sort . ' ' . $dir);
        }
        else
        {
            $select->order('ragione_sociale ASC');
        }

        if (isset($search['ragione_sociale']) && $search['ragione_sociale'] != '')
        {
            $select->where('ragione_sociale like ' . $db->quote('%' . $search['ragione_sociale'] . '%'));
        }
        else
        {
            if (isset($search['ragionesociale']) && $search['ragionesociale'] != '')
            {
                $select->where('ragione_sociale like ' . $db->quote('%' . $search['ragionesociale'] . '%'));
            }
        }
        if (isset($search['cf']) && $search['cf'] != '')
        {
            $select->where('cf like ' . $db->quote('%' . $search['cf'] . '%'));
        }
        if (isset($search['partita_iva']) && $search['partita_iva'] != '')
        {
            $select->where('partita_iva like ' . $db->quote('%' . $search['partita_iva'] . '%'));
        }
        if (isset($search['iban']) && $search['iban'] != '')
        {
            $select->where('iban like ' . $db->quote('%' . $search['iban'] . '%'));
        }

        if (isset($search['rco']) && !empty($search['rco']))
        {
            $select->where('rco = ' . implode(' or rco = ', $search['rco']));
        }

        if (isset($search['segnalato_da']) && $search['segnalato_da'] != '')
        {
            $select->where('segnalato_da like ' . $db->quote('%' . $search['segnalato_da'] . '%'));
        }

        if (isset($search['categoria']) && !empty($search['categoria']))
        {
            $select->where('categoria = ' . implode(' or categoria = ', $search['categoria']));
        }

        if (isset($search['ea']) && !empty($search['ea']))
        {
            $select->where('ea = ' . implode(' or ea = ', $search['ea']));
        }

        if (isset($search['organico_medio']) && !empty($search['organico_medio']))
        {
            $select->where('organico_medio = ' . implode(' or organico_medio = ', $search['organico_medio']));
        }

        if (isset($search['fatturato']) && !empty($search['fatturato']))
        {
            $select->where('fatturato = ' . implode(' or fatturato = ', $search['fatturato']));
        }

        if (isset($search['conosciuto_come']) && !empty($search['conosciuto_come']))
        {
            $select->where('conosciuto_come = ' . implode(' or conosciuto_come = ', $search['conosciuto_come']));
        }

        if (isset($search['cname']) && $search['cname'] != '')
        {
            $select->where('categories.name like ' . $db->quote('%' . $search['cname'] . '%'));
        }

        if (isset($search['status']) && !empty($search['status']))
        {
            $select->where('status = ' . implode(' or status = ', $search['status']));
        }
        {
            if (isset($search['stato']) && $search['stato'] != '')
            {
                $select->where('status.name like ' . $db->quote('%' . $search['stato'] . '%'));
            }
        }

        if (isset($search['id_promotore']) && !empty($search['id_promotore']))
        {
            $idpparts = explode(',', $search['id_promotore']);
            $wh = '';
            foreach($idpparts as $id_company)
            {
                if($id_company != '')
                {
                    $wh .= 'companies.id_promotore = ' . $db->quote($id_company) . ' OR ';
                }
            }
            if($wh != '')
            {
                $wh = substr($wh, 0, -4);
                $select->where($wh);
            }
        }

        if (isset($search['date_created']) && $search['date_created'] != '')
        {
            $parts = explode('-', $search['date_created']);

            if(count($parts) == 2)
            {
                $select->where('companies.date_created >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and companies.date_created <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('companies.date_created = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
            }
        }

        if (isset($search['addresses']) && $search['addresses'] != '')
        {
            //addresses.via, addresses.numero, addresses.cap, addresses.localita, addresses.provincia
            $to_search = $db->quote($search['addresses']);
            $addr_where = 'addresses.via = ' . $to_search . ' or addresses.numero = ' . $to_search
                    . ' or addresses.cap = ' . $to_search . ' or addresses.localita = ' . $to_search
                    . ' or addresses.provincia = ' . $to_search;
            $select->where($addr_where);
        }

        /*
        if (isset($search['is_partner']) && $search['is_partner'] != '')
        {
            $select->where('is_partner like ' . $db->quote('%' . $search['is_partner'] . '%'));
        }
        */
        if (isset($search['tipologia']) && !empty($search['tipologia']))
        {
            $w = '';
            foreach($search['tipologia'] as $t)
            {
                if($t == 'is_partner')
                {
                    $w .= 'is_partner = 1 or ';
                }
                if($t == 'is_fornitore')
                {
                    $w .= 'is_fornitore = 1 or ';
                }
                if($t == 'is_promotore')
                {
                    $w .= 'is_promotore = 1 or ';
                }
                if($t == 'is_cliente1')
                {
                    $w .= 'is_cliente = 1 or ';
                }
                if($t == 'is_cliente2')
                {
                    $w .= 'is_cliente = 2 or ';
                }
            }
            if($w != '')
            {
                $w = substr($w, 0, -3);
                $select->where($w);
            }
        }
        else
        {
            if (isset($search['ispartner']) && $search['ispartner'] != '')
            {
                $select->where('is_partner like ' . $db->quote('%' . $search['ispartner'] . '%'));
            }
            if (isset($search['ispromotore']) && $search['ispromotore'] != '')
            {
                $select->where('is_promotore like ' . $db->quote('%' . $search['ispromotore'] . '%'));
            }
            if (isset($search['iscliente']) && $search['iscliente'] != '')
            {
                $select->where('is_cliente like ' . $db->quote('%' . $search['iscliente'] . '%'));
            }
            if (isset($search['isfornitore']) && $search['isfornitore'] != '')
            {
                $select->where('is_fornitore like ' . $db->quote('%' . $search['isfornitore'] . '%'));
            }
        }

        if (isset($search['promotore_percent']) && $search['promotore_percent'] != '')
        {
            $parts = explode('-', $search['promotore_percent']);
            if(count($parts) == 2)
            {
                $select->where('promotore_percent >= ' . $parts[0] . ' and promotore_percent <= ' . $parts[1]);
            }
            else
            {
                $select->where('promotore_percent = ' . $parts[0]);
            }
        }

        // filtro regione - provincia - cap
        $query_in = '';
        if (isset($search['regione']) && !empty($search['regione']))
        {
            $query_in = "a2.regione = '" . implode("' or a2.regione = '", $search['regione']) . "'";
        }
        if (isset($search['provincia']) && !empty($search['provincia']))
        {
            $query_in .= (($query_in != '') ? ' or ' : '') . "a2.provincia = '" . implode("' or a2.provincia = '", $search['provincia']) . "'";
        }
        if (isset($search['cap']) && !empty($search['cap']))
        {
            $caps = explode(',', $search['cap']);
            $query_in .= (($query_in != '') ? ' or ' : '') . "a2.cap = '" . implode("' or a2.cap = '", $caps) . "'";
        }
        if($query_in != '')
        {
            $select->where('company_id in (select a2.id_company from addresses a2 where ' . $query_in . ')');
        }


        if($per_page !== NULL)
        {
            $values = Zend_Paginator::factory($select);
            $values->setItemCountPerPage($per_page);
            $values->setCurrentPageNumber(isset($search['page']) ? $search['page'] : 1);
        }
        else
        {
            $values = $db->fetchAll($select);
        }

        return $values;
    }

    public function findWithDependencies(array $where = array(), $checkInternals = true)
    {
        $user = Zend_Auth::getInstance()->getIdentity();
        $db = $this->_companyMapper->getDbAdapter();

        $select = $db->select();

        $select->from('companies', array(
            'created_by',
            'date_created',
            'ragione_sociale',
            'cf',
            'partita_iva',
            'pec',
            'iban',
            'segnalato_da',
            'conosciuto_come',
            'categoria',
            'ea',
            'status',
            'rco',
            'organico_medio',
            'fatturato',
            'conosciuto_come',
            'company_id',
            'is_partner',
            'is_promotore',
            'is_cliente',
            'is_fornitore',
            'id_promotore',
            'promotore_percent',
            'deleted',
            'note',
            'prodotti',
        ))
                ->joinLeft('status', 'status.status_id = companies.status', array('status_name' => 'status.name'))
                ->joinLeft(array('u1' => 'users'), 'u1.user_id = companies.rco', array('rco_name' => 'u1.username'))
        //->joinLeft(array('u2' => 'users'), 'u2.user_id = companies.id_segnalato_da', array('segnalato_da_name' => 'u2.username'))
                ->joinLeft('categories', 'categories.category_id = companies.categoria', array('categoria_name' => 'categories.name'))
                ->joinLeft('ea', 'ea.ea_id = companies.ea', array('ea_name' => 'name'))
                ->joinLeft('organici_medi', 'organico_medio_id = companies.organico_medio', array('organico_medio_name' => 'organici_medi.name'))
                ->joinLeft('fatturati', 'fatturato_id = companies.fatturato', array('fatturato_name' => 'fatturati.name'))
                ->joinLeft('conosciuto_come', 'conosciuto_come_id = companies.conosciuto_come', array('conosciuto_come_name' => 'conosciuto_come.name'));

        if($checkInternals)
        {
            $select->joinLeft('companies_internals', 'companies_internals.id_company = company_id', array())
                    ->joinLeft('offices', 'offices.office_id = companies_internals.id_office', array('office' => 'name', 'office_id'))
                    ->where('companies_internals.id_internal = ?', $user->internal_id);
        }

        foreach ($where as $f => $v)
        {
            $select->where($f . ' = ?', $v);
        }

        $data = $db->fetchRow($select);

        if($data['id_promotore'] ?? null)
        {
            unset($select);
            $select = $db->select();
            $select->from('companies', 'ragione_sociale')
                    ->where('company_id = ?', $data['id_promotore']);
            $c = $db->fetchOne($select);

            $data['promotore_name'] = $c;
        }

        $company = $this->getNewCompany();

        if(isset($data['company_id']) ?? null)
        {
            $company = new Model_Company();
            $company->setData($data);

            $addressRepo = Maco_Model_Repository_Factory::getRepository('address');
            $company->addresses = $addressRepo->findByCompany($company->company_id);
            unset($addressRepo);

            $telephoneRepo = Maco_Model_Repository_Factory::getRepository('telephone');
            $company->telephones = $telephoneRepo->findByCompany($company->company_id);
            unset($telephoneRepo);

            $mailsRepo = Maco_Model_Repository_Factory::getRepository('mail');
            $company->mails = $mailsRepo->findByCompany($company->company_id);
            unset($mailsRepo);

            $websiteRepo = Maco_Model_Repository_Factory::getRepository('website');
            $company->websites = $websiteRepo->findByCompany($company->company_id);
            unset($websiteRepo);

            unset($select);
            $select = $db->select();
            // TODO: LE internals meritano un modello???
            $select->from('companies_internals', array())
                    ->joinLeft('internals', 'id_internal = internal_id', array('internal_id', 'full_name', 'abbr'))
                    ->where('id_company = ?', $company->company_id);
            $company->internals = $db->fetchAll($select);
        }

        return $company;
    }

    public function setOffice($id_company, $id_office)
    {
        $db = $this->_companyMapper->getDbAdapter();
        $id_internal = Zend_Auth::getInstance()->getIdentity()->internal_id;
        $db->update(
            'companies_internals',
            array(
                'id_office' => $id_office
            ),
                'id_company = ' . $id_company . ' and id_internal = ' . $id_internal
        );

        return $db->fetchOne('select name from offices where office_id = ' . $id_office);
    }

    public function setInternal($company_id, $internal_id, $office_id = false)
    {
        $db = $this->_companyMapper->getDbAdapter();
        $ints = $db->fetchCol('select id_internal from companies_internals where id_company = ' . $db->quote($company_id));
        if(!in_array($internal_id, $ints))
        {
            $data = array(
                'id_company' => $company_id,
                'id_internal' => $internal_id
            );

            if($office_id)
            {
                $data['id_office'] = $office_id;
            }

            return $db->insert('companies_internals', $data);
        }
        return true;
    }

    public function findByPartitaIva($pi)
    {
        $where = array('companies.partita_iva' => $pi);
        return $this->findWithDependencies($where, false);
    }

    public function findById($id)
    {
        $where = array('companies.company_id' => $id);
        return $this->findWithDependencies($where);
    }

    public function getPartners()
    {

    }

    public function getFromData($data, $prefix = '')
    {
        $company = new Model_Company();
        $company->setValidatorAndFilter(new Model_Company_Validator());
        $company->setData($data, $prefix);
        $company->isValid();

        $utils = new Maco_Input_Utils();
        // la utils toglie il prefisso

        $telephoneRepo = Maco_Model_Repository_Factory::getRepository('telephone');
        $telephonesData = $utils->formatDataForMultipleFields(array('telephone_id', 'number', 'description'), $prefix . 'telephones_', $data);
        if(!empty($telephonesData))
        {
            $telephones = array();
            foreach ($telephonesData as $telephone)
            {
                $telephones[] = $telephoneRepo->getFromData($telephone);
            }
            $company->telephones = $telephones;
        }
        else
        {
            $company->telephones = array($telephoneRepo->getNewTelephone());
        }

        $mailRepo = Maco_Model_Repository_Factory::getRepository('mail');
        $mailsData = $utils->formatDataForMultipleFields(array('mail_id', 'mail', 'description'), $prefix . 'mails_', $data);
        if(!empty($mailsData))
        {
            $mails = array();
            foreach ($mailsData as $mail)
            {
                $mails[] = $mailRepo->getFromData($mail);

            }
            $company->mails = $mails;
        }
        else
        {
            $company->mails = array($mailRepo->getNewMail());
        }

        $addressRepo = Maco_Model_Repository_Factory::getRepository('address');
        $addressesData = $utils->formatDataForMultipleFields(array('address_id', 'via', 'numero', 'cap', 'localita', 'provincia', 'description'), $prefix . 'addresses_', $data);
        if(!empty($addressesData))
        {
            $addresses = array();
            foreach ($addressesData as $address)
            {
                $addresses[] = $addressRepo->getFromData($address);
            }
            $company->addresses = $addresses;
        }
        else
        {
            $company->addresses = array($addressRepo->getNewAddress());
        }

        $websitesRepo = Maco_Model_Repository_Factory::getRepository('website');
        $websitesData = $utils->formatDataForMultipleFields(array('website_id', 'url', 'description'), $prefix . 'websites_', $data);
        if(!empty($websitesData))
        {
            $websites = array();
            foreach ($websitesData as $website)
            {
                $websites[] = $websitesRepo->getFromData($website);
            }
            $company->websites = $websites;
        }
        else
        {
            $company->websites = array($websitesRepo->getNewWebSite());
        }

        return $company;
    }

    public function saveFromData($data, $prefix = '')
    {
        $company = new Model_Company();
        $company->setValidatorAndFilter(new Model_Company_Validator());
        $company->setData($data, $prefix);
        $company->iban = strtoupper($company->iban);

        $company->is_fornitore = ($company->is_fornitore === NULL) ? 0 : $company->is_fornitore;
        $company->is_partner = ($company->is_partner === NULL) ? 0 : $company->is_partner;
        $company->is_promotore = ($company->is_promotore === NULL) ? 0 : $company->is_promotore;
        $company->is_cliente = ($company->is_cliente === NULL) ? 0 : $company->is_cliente;

        if ($company->isValid())
        {
            Maco_Model_TransactionManager::beginTransaction();

            try
            {
                $company_id = (int) $this->_companyMapper->save($company);

                $company_edited = $this->find($company_id);

                $utils = new Maco_Input_Utils();
                // la utils toglie il prefisso

                $telephoneRepo = Maco_Model_Repository_Factory::getRepository('telephone');
                $telephonesData = $utils->formatDataForMultipleFields(array('telephone_id', 'number', 'description'), $prefix . 'telephones_', $data);
                foreach ($telephonesData as $telephone)
                {
                    $telephone['id_company'] = $company_id;
                    if (is_array($res = $telephoneRepo->saveFromData($telephone)))
                    {
                        Maco_Model_TransactionManager::rollback();
                        return $res;
                    }
                }
                foreach ($company_edited->telephones as $old)
                {
                    $in = false;
                    foreach ($telephonesData as $telephone)
                    {
                        if ($old->telephone_id == $telephone['telephone_id'])
                        {
                            $in = true;
                        }
                    }
                    if (!$in)
                    {
                        $telephoneRepo->delete($old->telephone_id);
                    }
                }
                unset($telephoneRepo, $telephonesData);

                $mailRepo = Maco_Model_Repository_Factory::getRepository('mail');
                $mailsData = $utils->formatDataForMultipleFields(array('mail_id', 'mail', 'description'), $prefix . 'mails_', $data);
                foreach ($mailsData as $mail)
                {
                    $mail['id_company'] = $company_id;
                    if (is_array($res = $mailRepo->saveFromData($mail)))
                    {
                        Maco_Model_TransactionManager::rollback();
                        return $res;
                    }
                }
                foreach ($company_edited->mails as $old)
                {
                    $in = false;
                    foreach ($mailsData as $mail)
                    {
                        if ($old->mail_id == $mail['mail_id'])
                        {
                            $in = true;
                        }
                    }
                    if (!$in)
                    {
                        $mailRepo->delete($old->mail_id);
                    }
                }
                unset($mailRepo, $mailsData);

                $addressRepo = Maco_Model_Repository_Factory::getRepository('address');
                $addressesData = $utils->formatDataForMultipleFields(array('address_id', 'via', 'numero', 'cap', 'localita', 'provincia', 'description'), $prefix . 'addresses_', $data);
                foreach ($addressesData as $address)
                {
                    $address['id_company'] = $company_id;
                    if (is_array($res = $addressRepo->saveFromData($address)))
                    {
                        Maco_Model_TransactionManager::rollback();
                        return $res;
                    }
                }
                foreach ($company_edited->addresses as $old)
                {
                    $in = false;
                    foreach ($addressesData as $address)
                    {
                        if ($old->address_id == $address['address_id'])
                        {
                            $in = true;
                        }
                    }
                    if (!$in)
                    {
                        $addressRepo->delete($old->address_id);
                    }
                }
                unset($addressRepo, $addressesData);

                $websitesRepo = Maco_Model_Repository_Factory::getRepository('website');
                $websitesData = $utils->formatDataForMultipleFields(array('website_id', 'url', 'description'), $prefix . 'websites_', $data);
                foreach ($websitesData as $website)
                {
                    $website['id_company'] = $company_id;
                    if (is_array($res = $websitesRepo->saveFromData($website)))
                    {
                        Maco_Model_TransactionManager::rollback();
                        return $res;
                    }
                }
                foreach ($company_edited->websites as $old)
                {
                    $in = false;
                    foreach ($websitesData as $website)
                    {
                        if ($old->website_id == $website['website_id'])
                        {
                            $in = true;
                        }
                    }
                    if (!$in)
                    {
                        $websitesRepo->delete($old->website_id);
                    }
                }
                unset($websitesRepo, $websitesRepo);

                $this->setInternal($company_id, $data['internal_id'], Zend_Auth::getInstance()->getIdentity()->office_id);

                Maco_Model_TransactionManager::commit();
                return $company_id;
            }
            catch (Exception $e)
            {
                Maco_Model_TransactionManager::rollback();
                return array($e->getMessage());
            }
        }
        else
        {
            return $company->getInvalidMessages();
        }

        // fist the contact
        $contactRepo = Maco_Model_Repository_Factory::getRepository('contact');
        $res = $contactRepo->saveFromData($data, 'contacts_');
        if (is_array($res))
        {
            // no good
            return $res;
        }
    }
}
