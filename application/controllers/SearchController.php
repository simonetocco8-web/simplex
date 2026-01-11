<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marcello
 * Date: 20/05/13
 * Time: 17.26
 * To change this template use File | Settings | File Templates.
 */

class SearchController extends Zend_Controller_Action
{
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('index', 'html')
                    ->addActionContext('companies', 'html')
                    ->addActionContext('contacts', 'html')
                    ->addActionContext('offers', 'html')
                    ->addActionContext('orders', 'html')
                    ->addActionContext('users', 'html')
            ->initContext();
    }

    public function indexAction()
    {
        $query = $this->_request->getParam('q', false);
        if(!$query)
        {
            throw new Exception('query value missing');
        }
        $this->view->query = $query;

        // 1. companies
        $this->companiesAction();
        // 2. contacts
        $this->contactsAction();
        // 3. offers
        $this->offersAction();
        // 4. orders
        $this->ordersAction();
        // 5. sdm
        // 6. users
        $this->usersAction();
    }

    protected function companiesAction()
    {
        $query = $this->_request->getParam('q', false);
        if(!$query)
        {
            throw new Exception('query value missing');
        }
        $repo = Maco_Model_Repository_Factory::getRepository('company');

        $excluded = $this->_request->getParam('excluded', 0);

        $sort = $this->_request->getParam('_s', 'ragionesociale');
        $dir = $this->_request->getParam('_d', 'ASC');

        $search = array_merge($_GET, array('ragione_sociale' => $query));

        $perpage = $this->_request->getParam('perpage', 5);

        if($this->_request->isXmlHttpRequest())
        {
            $companies = $repo->getCompanies($sort, $dir, $search, $excluded, $perpage);
        }
        else
        {
            $companies = array();
        }

        //$companies = $companiesModel->getCompanies($sort, $dir, $search);

        $g = new Maco_DaGrid_Grid();
        $g->addColumns(array(
            array(
                'field' => 'company_id',
                'label' => 'Cod.',
                'search' => FALSE,
            ),
            array(
                'label' => 'Ragione Sociale',
                'field' => 'ragionesociale',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'company_id'
                    ),
                    'base' => '/companies/detail'
                )
            ),
            /*
			array(
                'field' => 'stato',
            ),
            */
            array(
                'field' => 'iscliente',
                'label' => 'Cliente',
                'path_to_template' => APPLICATION_PATH . '/Simplex/DaGrid/scripts/',
                'renderer' => 'cliente_company',
                'search' => 'cliente_company'
            ),
            /*
            array(
                'field' => 'iscliente',
                'label' => 'Pot. Cliente',
                'renderer' => 'bool',
                'search' => 'bool'
            ),
            */
            array(
                'field' => 'ispromotore',
                'label' => 'Promotore',
                'renderer' => 'bool',
                'search' => 'bool'
            ),
            array(
                'field' => 'isfornitore',
                'label' => 'Fornitore',
                'renderer' => 'bool',
                'search' => 'bool'
            ),
            array(
                'field' => 'ispartner',
                'label' => 'Partner',
                'renderer' => 'bool',
                'search' => 'bool'
            ),
            array(
                'field' => 'addresses',
                'label' => 'Sede',
            ),
        ));

        $r = new Maco_DaGrid_Render_Html();
        $r->with_export = false;
        $r->withFastSearch = false;
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        //        $r->setId('users');
        $r->setTrIdPrefix('man_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($companies);
        $g->setRenderer($r);
        $g->setSource($s);
        //$g->setPaginator(true);
        $g->setRowsPerPage($perpage);
        $g->setId('companies');

        $this->view->company_grid = $g;

        if(!$this->_request->isXmlHttpRequest())
        {
            $g->setRawUri('search/companies');
        }

        if($this->_request->isXmlHttpRequest())
        {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy();
        }
    }

    public function contactsAction()
    {
        $query = $this->_request->getParam('q', false);
        if(!$query)
        {
            throw new Exception('query value missing');
        }
        $repo = Maco_Model_Repository_Factory::getRepository('contact');

        $sort = $this->_request->getParam('_s', 'cognome');
        $dir = $this->_request->getParam('_d', 'ASC');

        $search = array_merge($_GET, array('cognome' => $query));

        $perpage = $this->_request->getParam('perpage', 5);

        if($this->_request->isXmlHttpRequest())
        {
            $contacts = $repo->getContacts($sort, $dir, $search);
        }
        else
        {
            $contacts = array();
        }

        $g = new Maco_DaGrid_Grid();
        $g->setSearchable(false);
        $g->addColumns(array(
            array(
                'field' => 'contact_title',
                'label' => 'titolo',
            ),
            array(
                'label' => 'Cognome',
                'field' => 'cognome',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'id_company',
                        'idc' => 'contact_id'
                    ),
                    'base' => '/companies/detailcontact'
                )
            ),
            array(
                'field' => 'nome',
            ),
            array(
                'field' => 'description',
                'label' => 'descrizione',
            ),
            array(
                'field' => 'telephones',
                'label' => 'telefono',
                'renderer' => 'array-br',
            ),
            array(
                'field' => 'mails',
                'label' => 'e-mail',
                'renderer' => 'array-br',
            )
        ));

        $r = new Maco_DaGrid_Render_Html();
        $r->setView($this->view);
        $r->withFastSearch = false;
        $r->with_export = false;
        $r->addClasses(array('fluid', 'listview'));
        //        $r->setId('users');
        $r->setTrIdPrefix('man_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($contacts);
        $g->setRenderer($r);
        $g->setSource($s);
        //$g->setPaginator(true);
        $g->setRowsPerPage($perpage);
        $g->setId('contacts');

        $this->view->contact_grid = $g;

        if(!$this->_request->isXmlHttpRequest())
        {
            $g->setRawUri('search/contacts');
        }

        if($this->_request->isXmlHttpRequest())
        {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy();
        }
    }

    public function offersAction()
    {
        $query = $this->_request->getParam('q', false);
        if(!$query)
        {
            throw new Exception('query value missing');
        }

        $sort = $this->_request->getParam('_s', false);
        $dir = $this->_request->getParam('_d', 'ASC');

        $search = array_merge($_GET, array('ragione_sociale' => $query));

        /** @var $repo Model_Offer_Repository */
        $repo = Maco_Model_Repository_Factory::getRepository('offer');

        $deleted = $this->_request->getParam('deleted', 0);

        $perpage = $this->_request->getParam('perpage', 5);


        if($this->_request->isXmlHttpRequest())
        {

            $offers = $repo->getOffers($sort, $dir, $search, $deleted, null, $perpage);
            $totals = $repo->getTotals($search, $deleted);
        }
        else
        {
            $offers = array();
            $totals = array('total' => 0, 'to_promotors' => 0);
        }

        $g = new Maco_DaGrid_Grid();
        $g->addColumns(array(
            array(
                'label' => 'Codice',
                'field' => 'code_offer',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'offer_id'
                    ),
                    'base' => '/offers/detail'
                )
            ),
            array(
                'field' => 'date_offer',
                'label' => 'Data Offerta',
                'renderer' => 'datetime',
                'search' => false,
            ),
            array(
                'field' => 'cliente'
            ),
            array(
                'field' => 'promotore',
            ),
            array(
                'field' => 'service',
                'label' => 'Servizio',
            ),
            array(
                'field' => 'subservice',
                'label' => 'Servizio Specifico'
            ),
            array(
                'field' => 'rco',
            ),
            array(
                'field' => 'status',
                'label' => 'Stato',
            ),
        ));

        $r = new Maco_DaGrid_Render_Html();
        $r->with_export = false;
        $r->withFastSearch = false;
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));

        $r->offers_total = $totals['total'];
        $r->offers_to_promotors = $totals['to_promotors'];

        $r->withFastSearch = false;
        //        $r->setId('users');
        $r->setTrIdPrefix('man_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($offers);
        $g->setRenderer($r);
        $g->setSource($s);
        //$g->setPaginator(true);
        $g->setRowsPerPage($perpage);
        $g->setId('offers');

        $this->view->offer_grid = $g;

        if(!$this->_request->isXmlHttpRequest())
        {
            $g->setRawUri('search/offers');
        }

        if($this->_request->isXmlHttpRequest())
        {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy('offers.phtml');
        }
    }

    public function ordersAction()
    {
        $query = $this->_request->getParam('q', false);
        if(!$query)
        {
            throw new Exception('query value missing');
        }

        /** @var $repo Model_Order_Repository */
        $repo = Maco_Model_Repository_Factory::getRepository('order');

        $sort = $this->_request->getParam('_s', false);
        $dir = $this->_request->getParam('_d', 'ASC');

        // se non abbiamo fatto una ricerca di default facciamo vedere solo le
        // commesse aperte
        //if(empty($_POST) || !isset($_POST['id_status']) /*|| empty($_POST['id_status'])*/)
        //{
        //    $_POST['id_status'] = array('1', '2');
        //}

        $search = array_merge($_GET, array('ragione_sociale' => $query));

        $perpage = $this->_request->getParam('perpage', 5);


        if($this->_request->isXmlHttpRequest())
        {
            $orders = $repo->getOrders($sort, $dir, $search, null, $perpage);
            $totals = $repo->getTotals($search);
        }
        else
        {
            $orders = array();
            $totals = array('total' => 0, 'to_promotors' => 0);
        }

        $g = new Maco_DaGrid_Grid();
        $g->addColumns(array(
            array(
                'label' => 'Commessa',
                'field' => 'code_order',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'order_id'
                    ),
                    'base' => '/orders/detail',
                    'separator' => '-',
                )
            ),
            /*
            array(
                'label' => 'Offerta',
            	'field' => 'code_offer',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'id_off'
                    ),
                    'base' => '/offers/detail',
                    'separator' => '-',
                )
            ),
            */
            array(
                'field' => 'cliente'
            ),
            array(
                'field' => 'service',
                'label' => 'Servizio',
            ),
            array(
                'field' => 'subservice',
                'label' => 'Servizio Specifico'
            ),
            array(
                'field' => 'categoria_name',
                'label' => 'Settore',
            ),
            array(
                'field' => 'rcs',
                'label' => 'RC',
            ),
            array(
                'field' => 'date_chiusura_richiesta',
                'label' => 'Data Chiusura Richiesta',
                'renderer' => 'datetime',
                'search' => false,
            ),
            /*
            array(
                'field' => 'dtg',
            ),
            */
            array(
                'field' => 'sal',
                'label' => 'SAL',
            ),
            array(
                'field' => 'status',
                'label' => 'Stato',
            ),
        ));

        $r = new Maco_DaGrid_Render_Html();
        $r->with_export = false;
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        //        $r->setId('users');
        $r->withFastSearch = false;
        $r->setTrIdPrefix('man_');

        $r->offers_total = $totals['total'];
        $r->offers_to_promotors = $totals['to_promotors'];

        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($orders);
        $g->setRenderer($r);
        $g->setSource($s);
        //$g->setPaginator(true);
        $g->setRowsPerPage($perpage);
        $g->setId('orders');

        $this->view->order_grid = $g;

        if(!$this->_request->isXmlHttpRequest())
        {
            $g->setRawUri('search/orders');
        }

        if($this->_request->isXmlHttpRequest())
        {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy('offers.phtml');
        }
    }

    public function usersAction()
    {
        $query = $this->_request->getParam('q', false);
        if(!$query)
        {
            throw new Exception('query value missing');
        }
        $repo = Maco_Model_Repository_Factory::getRepository('user');

        $sort = $this->_request->getParam('_s', 'username');
        $dir = $this->_request->getParam('_d', 'ASC');

        if(trim($sort) == '')
        {
            $sort = 'username';
        }
        if(trim($dir) == '')
        {
            $dir = 'ASC';
        }

        $search = array_merge($_GET, array('cognome' => $query));

        $perpage = $this->_request->getParam('perpage', 5);

        if($this->_request->isXmlHttpRequest())
        {
            $users = $repo->getUsers($sort, $dir, $search, 0, $perpage);
        }
        else
        {
            $users = array();
        }

        $g = new Maco_DaGrid_Grid();
        $g->addColumns(array(
            array(
                'label' => 'Nome Utente',
                'field' => 'username',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'user_id'
                    ),
                    'base' => '/users/detail'
                )
            ),
            array(
                'field' => 'nome'
            ),
            array(
                'field' => 'cognome'
            ),
            array(
                'label' => 'Attivo',
                'field' => 'active',
                'renderer' => 'bool',
                'search' => 'bool'
            ),
        ));

        $r = new Maco_DaGrid_Render_Html();
        $r->with_export = false;
        $r->withFastSearch = false;
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        //        $r->setId('users');
        $r->setTrIdPrefix('user_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($users);
        $g->setRenderer($r);
        $g->setSource($s);
        //$g->setPaginator(true);
        $g->setRowsPerPage($perpage);
        $g->setId('users');

        $this->view->user_grid = $g;

        if(!$this->_request->isXmlHttpRequest())
        {
            $g->setRawUri('search/users');
        }

        if ($this->_request->isXmlHttpRequest()) {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy();
        }
    }
}