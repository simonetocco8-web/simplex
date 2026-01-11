<?php

class DashboardController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
        $ajaxContext = $this->_helper->getHelper('AjaxContext');

        $ajaxContext->addActionContext('recap', 'html')
                    //->addActionContext('tasks', 'json')
                    ->initContext();
    }

    public function indexAction()
    {

        //$messagesModel = new Model_MessagesMapper();

        $user = Zend_Auth::getInstance()->getIdentity();

        $user_id = $user->user_id;
        $user_role_id = (int) $user->id_role;
        $user_internal = $user->internal_id;

        // $this->view->messages = $messagesModel->getMessagesForUser($user_id);

        $dalist = $this->_request->getParam('dalist', false);

        if($dalist)
        {
            switch($dalist)
            {
                case 'offers':
                    $this->_offersFor($user_id);
                    break;
                case 'orders':
                    $this->_ordersFor($user_id);
                    break;
                case 'moments':
                    $this->_momentsFor($user_id);
                    break;
                case 'messages':
                    break;
                case 'tasks':
                    $this->_tasksFor($user_id);
                    break;
            }
        }
        else
        {
            $this->commonStuff($user_id);
        }

        /*
		switch($user_role_id)
		{
			case 1:
				// superadmin
				$this->dashBoardForAdmin($user_id);
				break;
			case 2:
				// rco
				$this->dashboardForRco($user_id);
				break;
				// dtg
			case 3:
				$this->dashboardForDtg($user_id);
				break;
		}
        */
    }

    protected function commonStuff($user_id)
    {
        $this->_tasksFor($user_id);

        $this->_offersFor($user_id);

        $this->_ordersFor($user_id);

        if(Zend_Auth::getInstance()->getIdentity()->user_object->has_permission('administration', 'view'))
        {
            $this->_momentsFor($user_id);
        }

        $this->_messagesFor($user_id);

        $this->_infosFor($user_id);

        $this->render('common');
    }

    protected function _messagesFor($user_id)
    {
        $messages_repo = Maco_Model_Repository_Factory::getRepository('message');

        $messages = $messages_repo->getMessages('date_created', 'DESC', array('to' => $user_id));

        $this->view->has_messages = !empty($messages);

        $todo = $important = $notifications = array();

        foreach($messages as $message)
        {
            if(stripos($message['title'], 'nuovo impegno') !== FALSE)
            {
                $todo[] = $message;
                continue;
            }

            if($message['type'] == 1)
            {
                $important[] = $message;
                continue;
            }

            $notifications[] = $message;
        }

        $this->view->todo_array = $todo;
        $this->view->important_array = $important;
        $this->view->notifications_array = $notifications;
    }

    protected function _infosFor($user_id)
    {
        $messages_repo = Maco_Model_Repository_Factory::getRepository('message');

        $todos = $messages_repo->getMessages('message_id', 'DESC', array(
            'to' => $user_id,
            'type' => '1',
        )/*, 10*/);
        $notifications = $messages_repo->getMessages('message_id', 'DESC', array(
            'to' => $user_id,
            'type' => 0,
        ), 5);

        foreach($todos as $key => $message)
        {
            if(stripos($message['title'], 'nuovo impegno') !== FALSE)
            {
                // no tasks in this list
                unset($todos[$key]);
            }
        }

        $this->view->new_todo_array = $todos;
        $this->view->new_notifications_array = $notifications;
    }

    protected function _tasksFor_OLD($user_id)
    {
        $this->view->id_who = Zend_Auth::getInstance()->getIdentity()->user_id;
        /*
        $tasksModel = new Model_TasksMapper();
        $tasks = $tasksModel->fetch(array('where' => array('id_who' => $user_id, 'when' => 'SETTIMANA_AND_OLD', 'done' => '0')));

        $this->view->tasks_array = $tasks;
        */
    }

    protected function _tasksFor($user_id)
    {
        $sort = 'when';
        $dir = 'ASC';

        $repo = Maco_Model_Repository_Factory::getRepository('task');

        $perpage = $this->_request->getParam('perpage', Zend_Registry::getInstance()->get('config')->entries_per_page);

        $tasks = $repo->getTasks($sort, $dir, array('id_who' => $user_id, 'done' => '0'), $perpage);

        $g = new Maco_DaGrid_Grid();
        $g->addColumns(array(
            array(
                'label' => '#',
                'field' => 'task_id',
                'class' => 'link', // class -> link set renderer -> link
                'sortable' => false,
                'options' => array(
                    'linksData' => array(
                        'id' => 'task_id'
                    ),
                    'base' => '/tasks/detail'
                )
            ),
            array(
                'label' => 'Chi',
                'field' => 'who',
                'class' => 'link', // class -> link set renderer -> link
                'sortable' => false,
                'options' => array(
                    'linksData' => array(
                        'id' => 'id_who'
                    ),
                    'base' => '/admin/users/task/detail'
                )
            ),
            array(
                'field' => 'what',
                'label' => 'Cosa',
                'sortable' => false,
                'path_to_template' => APPLICATION_PATH . '/Simplex/DaGrid/scripts/',
                'renderer' => 'task_what',
            ),
            array(
                'field' => 'when',
                'label' => 'quando',
                'renderer' => 'datetime',
                'sortable' => false,
            ),
            array(
                'label' => 'Azienda',
                'field' => 'company',
                'class' => 'link', // class -> link set renderer -> link
                'sortable' => false,
                'options' => array(
                    'linksData' => array(
                        'id' => 'id_company'
                    ),
                    'base' => '/companies/detail'
                )
            ),
            array(
                'field' => 'subject',
                'label' => 'Contatto',
                'sortable' => false,
            ),
            array(
                'field' => 'subject_data',
                'label' => 'dati',
                'sortable' => false,
            )
        ));

        $r = new Maco_DaGrid_Render_Html();
        //$r->with_export = true;
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));

        $r->withFastSearch = false;
        //        $r->setId('users');
        $r->setTrIdPrefix('man_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($tasks);
        $g->setRenderer($r);
        $g->setSource($s);

        //$g->setPaginator(true);
        $g->setRowsPerPage($perpage);
        $g->setId('tasks');

        if($this->_request->isXmlHttpRequest())
        {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy();
            exit;
        }
        else
        {
            $this->view->tasks = $g;
        }
    }

    protected function _offersFor($user_id)
    {
        /** @var $repo Model_Offer_Repository */
        $repo = Maco_Model_Repository_Factory::getRepository('offer');
        $db = Zend_Registry::get('dbAdapter');
        $where_id = $db->quote($user_id);

        $where = $_POST;
        $where['owner'] = $where_id;
        $where['id_status'] = array(1, 2, 3);
        /*
        $where = '(offers.created_by = ' . $where_id .
            ' OR id_rco = ' . $where_id .
            //' OR offers.id_segnalato_da = ' . $where_id .
            ') AND id_status < 4 ';
        */
        $perpage = $this->_request->getParam('perpage', Zend_Registry::getInstance()->get('config')->entries_per_page);

        $offers = $repo->getOffers('offers.date_offer', 'DESC', $where, 0, null, $perpage);

        $g = new Maco_DaGrid_Grid();
        $g->setSearchable(false);
        $g->addColumns(array(
            array(
                'label' => 'Codice',
                'field' => 'code_offer',
                'class' => 'link', // class -> link set renderer -> link
                'sortable' => false,
                'options' => array(
                    'linksData' => array(
                        'id' => 'offer_id'
                    ),
                    'base' => '/offers/detail',
                    'separator' => '-',
                )
            ),
            array(
                'field' => 'date_offer',
                'sortable' => false,
                'label' => 'Data Offerta',
                'renderer' => 'datetime'
            ),
            array(
                'field' => 'date_end',
                'sortable' => false,
                'label' => 'Data Scadenza',
                'renderer' => 'datetime'
            ),
            array(
                'sortable' => false,
                'field' => 'cliente'
            ),
            array(
                'sortable' => false,
                'field' => 'partner',
            ),
            array(
                'field' => 'service',
                'sortable' => false,
                'label' => 'Servizio',
            ),
            array(
                'field' => 'subservice',
                'sortable' => false,
                'label' => 'Servizio Specifico'
            ),
            /*
array(
 'field' => 'rco',
 'sortable' => false,
),
array(
 'field' => array('snome', 'scognome'),
 'sortable' => false,
 'label' => 'Segnalato Da',
),
            */
            array(
                'field' => 'status',
                'label' => 'Stato',
                'sortable' => false,
            ),
        ));

        $r = new Maco_DaGrid_Render_Html();
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        //        $r->setId('users');
        $r->setTrIdPrefix('man_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($offers);
        $g->setRenderer($r);
        $g->setSource($s);
        //$g->setPaginator(true);
        $g->setRowsPerPage($perpage);
        $g->setId('offers');

        if($this->_request->isXmlHttpRequest())
        {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy();
            exit;
        }
        else
        {
            $this->view->offers = $g;
        }
    }

    protected function _ordersFor($user_id)
    {
        /** @var $repo Model_Order_Repository */
        $repo = Maco_Model_Repository_Factory::getRepository('order');
        $db = Zend_Registry::get('dbAdapter');
        $where_id = $db->quote($user_id);
        $where = $_POST;
        /*
        $where = '(orders.created_by = ' . $where_id .
            ' or id_dtg = ' . $where_id .
            ') and orders.id_status = 1';
        */
        $where['id_status'] = array('1', '2');
        $where['owner'] = $where_id;

        $perpage = $this->_request->getParam('perpage', Zend_Registry::getInstance()->get('config')->entries_per_page);

        $orders = $repo->getOrders('orders.date_created', 'DESC', $where, null, $perpage);

        $j = new Maco_DaGrid_Grid();
        $j->setSearchable(false);
        $j->addColumns(array(
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
                ),
                'searchable' => false,
                'sortable' => false
            ),
            array(
                'label' => 'Offerta',
                'field' => 'code_offer',
                'class' => 'link', // class -> link set renderer -> link
                'sortable' => false,
                'options' => array(
                    'linksData' => array(
                        'id' => 'id_ord'
                    ),
                    'base' => '/offers/detail',
                    'separator' => '-',
                )
            ),
            array(
                'field' => 'cliente',
                'sortable' => false
            ),
            array(
                'field' => 'service',
                'label' => 'Servizio',
                'sortable' => false
            ),
            array(
                'field' => 'subservice',
                'label' => 'Servizio Specifico',
                'sortable' => false
            ),
            array(
                'field' => 'dtg',
                'sortable' => false
            ),
            array(
                'field' => 'status',
                'label' => 'Stato',
                'sortable' => false
            ),
        ));

        $r = new Maco_DaGrid_Render_Html();
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        //        $r->setId('users');
        $r->setTrIdPrefix('man_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($orders);
        $j->setRenderer($r);
        $j->setSource($s);
        //$g->setPaginator(true);
        $j->setRowsPerPage($perpage);
        $j->setId('orders');

        if($this->_request->isXmlHttpRequest())
        {
            $this->_helper->viewRenderer->setNoRender();
            $j->deploy();
            exit;
        }
        else
        {
            $this->view->orders = $j;
        }
    }

    protected function _momentsFor($user_id)
    {
        $model = new Model_Administration();

        $options['moment-done'] = TRUE;
        $options['without-invoice'] = true;
        $options['search'] = array();
        $perpage = $this->_request->getParam('perpage', Zend_Registry::getInstance()->get('config')->entries_per_page);
        $options['perpage'] = $perpage;
        $moments = $model->getMoments($options);

        $g = new Maco_DaGrid_Grid();
        $g->setSearchable(false);
        $g->addColumns(array(
            array(
                'label' => 'Commessa',
                'field' => 'code_order',
                'options' => array(
                    'separator' => '-',
                ),
                'sortable' => false
            ),
            array(
                'label' => 'Offerta',
                'field' => 'code_offer',
                'options' => array(
                    'separator' => '-',
                ),
                'sortable' => false
            ),
            array(
                'field' => 'ragione_sociale',
                'label' => 'Azienda',
                'sortable' => false
            ),
            array(
                'field' => 'service',
                'label' => 'Servizio',
                'sortable' => false
            ),
            array(
                'field' => 'subservice',
                'label' => 'Servizio Specifico',
                'sortable' => false
            ),
            array(
                'field' => 'tipologia',
                'label' => 'Momento di Fatturazione',
                'sortable' => false
            ),
            array(
                'field' => 'expected_date',
                'label' => 'Data',
                'renderer' => 'datetime',
                'search' => 'datetime',
                'sortable' => false
            ),
            array(
                'field' => 'importo',
                'sortable' => false
            ),
        ));

        $r = new Maco_DaGrid_Render_Html();
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        //        $r->setId('users');
        $r->setTrIdPrefix('man_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($moments);
        $g->setRenderer($r);
        $g->setSource($s);
        //$g->setPaginator(true);
        $g->setRowsPerPage($perpage);
        $g->setId('moments');

        if($this->_request->isXmlHttpRequest())
        {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy();
            exit;
        }
        else
        {
            $this->view->moments = $g;
        }
    }

    public function dashboardForAdmin($user_id)
    {
        $repo = Maco_Model_Repository_Factory::getRepository('offer');
        $offers = $repo->getOffers(null, null, null, 0, 5);

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
                    'base' => '/offers/detail',
                    'separator' => '-',
                )
            ),
            array(
                'field' => 'date_offer',
                'label' => 'Data Offerta'
            ),
            array(
                'field' => 'cliente'
            ),
            array(
                'field' => 'partner',
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
                'field' => array('snome', 'scognome'),
                'label' => 'Segnalato Da',
            ),
            array(
                'field' => 'status',
                'label' => 'Stato',
            ),
        ));

        $r = new Maco_DaGrid_Render_Html();
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        //        $r->setId('users');
        $r->setTrIdPrefix('man_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($offers);
        $g->setRenderer($r);
        $g->setSource($s);
        //$g->setPaginator(true);
        $g->setRowsPerPage($this->_request->getParam('perpage', 20));
        $g->setId('offers');

        $this->view->offers = $g;

        if($this->_request->isXmlHttpRequest())
        {
            //            $this->_helper->viewRenderer->setNoRender();
            //          $g->deploy();
            exit;
        }

        unset($offers);
        unset($repo);

        $repo = Maco_Model_Repository_Factory::getRepository('order');
        $orders = $repo->getOrders(null, null, null, 0, 5);

        $j = new Maco_DaGrid_Grid();
        $j->addColumns(array(
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
            array(
                'label' => 'Offerta',
                'field' => 'code_offer',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'id_ord'
                    ),
                    'base' => '/offers/detail',
                    'separator' => '-',
                )
            ),
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
                'field' => 'dtg',
            ),
            array(
                'field' => 'status',
                'label' => 'Stato',
            ),
        ));

        $r = new Maco_DaGrid_Render_Html();
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        //        $r->setId('users');
        $r->setTrIdPrefix('man_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($orders);
        $j->setRenderer($r);
        $j->setSource($s);
        //$g->setPaginator(true);
        $j->setRowsPerPage($this->_request->getParam('perpage', 20));
        $j->setId('orders');

        $this->view->orders = $j;

        if($this->_request->isXmlHttpRequest())
        {
            //            $this->_helper->viewRenderer->setNoRender();
            //          $g->deploy();
            exit;
        }


        $admModel = new Model_Admin();

        $this->render('admin');
    }

    public function dashboardForRco($user_id)
    {
        $repo = Maco_Model_Repository_Factory::getRepository('offer');
        $offers = $repo->getOffers(null, null, array('id_rco' => $user_id), 0, 5);

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
                    'base' => '/offers/detail',
                    'separator' => '-',
                )
            ),
            array(
                'field' => 'date_offer',
                'label' => 'Data Offerta'
            ),
            array(
                'field' => 'cliente'
            ),
            array(
                'field' => 'partner',
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
                'field' => array('snome', 'scognome'),
                'label' => 'Segnalato Da',
            ),
            array(
                'field' => 'status',
                'label' => 'Stato',
            ),
        ));

        $r = new Maco_DaGrid_Render_Html();
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        //        $r->setId('users');
        $r->setTrIdPrefix('man_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($offers);
        $g->setRenderer($r);
        $g->setSource($s);
        //$g->setPaginator(true);
        $g->setRowsPerPage($this->_request->getParam('perpage', 20));
        $g->setId('offers');

        $this->view->offers = $g;

        if($this->_request->isXmlHttpRequest())
        {
            //            $this->_helper->viewRenderer->setNoRender();
            //          $g->deploy();
            exit;
        }

        unset($offers);
        unset($repo);



        $repo = Maco_Model_Repository_Factory::getRepository('order');
        $orders = $repo->getOrders(null, null, array('id_rco' => $user_id), 0, 5);


        $j = new Maco_DaGrid_Grid();
        $j->addColumns(array(
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
            array(
                'label' => 'Offerta',
                'field' => 'code_offer',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'id_ord'
                    ),
                    'base' => '/offers/detail',
                    'separator' => '-',
                )
            ),
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
                'field' => 'dtg',
            ),
            array(
                'field' => 'status',
                'label' => 'Stato',
            ),
        ));

        $r = new Maco_DaGrid_Render_Html();
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        //        $r->setId('users');
        $r->setTrIdPrefix('man_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($orders);
        $j->setRenderer($r);
        $j->setSource($s);
        //$g->setPaginator(true);
        $j->setRowsPerPage($this->_request->getParam('perpage', 20));
        $j->setId('orders');

        $this->view->orders = $j;

        if($this->_request->isXmlHttpRequest())
        {
            //            $this->_helper->viewRenderer->setNoRender();
            //          $g->deploy();
            exit;
        }

        $this->render('rco');

    }

    public function dashboardForDtg($user_id)
    {
        $offersModel = new Model_OffersMapper();

        $ordersModel = new Model_OrdersMapper();

        $orders = $ordersModel->getList(null, null, 'id_dtg = ' . $user_id, 5);

        $j = new Maco_DaGrid_Grid();
        $j->addColumns(array(
            array(
                'label' => 'Commessa',
                'field' => 'code_order',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'id'
                    ),
                    'base' => '/orders/detail',
                    'separator' => '-',
                )
            ),
            array(
                'label' => 'Offerta',
                'field' => 'code_offer',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'id_ord'
                    ),
                    'base' => '/offers/detail',
                    'separator' => '-',
                )
            ),
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
                'field' => 'dtg',
            ),
            array(
                'field' => 'status',
                'label' => 'Stato',
            ),
        ));

        $r = new Maco_DaGrid_Render_Html();
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        //        $r->setId('users');
        $r->setTrIdPrefix('man_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($orders);
        $j->setRenderer($r);
        $j->setSource($s);
        //$g->setPaginator(true);
        $j->setRowsPerPage($this->_request->getParam('perpage', 20));
        $j->setId('orders');

        $this->view->orders = $j;

        if($this->_request->isXmlHttpRequest())
        {
            //            $this->_helper->viewRenderer->setNoRender();
            //          $g->deploy();
            exit;
        }
        /*
        $messagesModel = new Model_MessagesMapper();

        $messages = $messagesModel->getMessagesForUser($user_id);

        $this->view->messages = $messages;
        */
        $this->render('dtg');

    }

    public function recapAction()
    {
        if(!isset($_GET['period']))
        {
            $lastDay = date("t/m/Y", strtotime("first day of this month"));
            $firstDay = date("01/m/Y", strtotime("first day of this month"));
            $_GET['period'] = $firstDay . ' - ' . $lastDay;
        }

        $_POST['period'] = $_GET['period'];


        $days = explode(' - ', $_POST['period']);
        $day = Maco_Utils_DbDate::toDb($days[0]);
        $this->view->thisMonth = date("F", strtotime($day));
        $this->view->thisYear = date("Y", strtotime($day));

        $timeNextMonth = strtotime($day . ' + 1 month');
        $nextMonthDay = date('Y-m-d', $timeNextMonth);
        $this->view->nextMonth = date("F", strtotime($nextMonthDay));
        $this->view->nextYear = date("Y", strtotime($nextMonthDay));
        $lastDay = date("t/m/Y", $timeNextMonth);
        $firstDay = date("01/m/Y", $timeNextMonth);
        $this->view->nextUrlQuery = http_build_query(array('period' => $firstDay . ' - ' . $lastDay));

        $timePrevMonth = strtotime($day . ' - 1 month');
        $prevMonthDay = date('Y-m-d', $timePrevMonth);
        $this->view->prevMonth = date("F", strtotime($prevMonthDay));
        $this->view->prevYear = date("Y", strtotime($prevMonthDay));
        $lastDay = date("t/m/Y", $timePrevMonth);
        $firstDay = date("01/m/Y", $timePrevMonth);
        $this->view->prevUrlQuery = http_build_query(array('period' => $firstDay . ' - ' . $lastDay));

        $user_id = Zend_Auth::getInstance()->getIdentity()->user_id;

        $repo = Maco_Model_Repository_Factory::getRepository('user');
        $user = $repo->find($user_id);

        $repo = Maco_Model_Repository_Factory::getRepository('offer');

        // Offerte aggiudicate in cui l’Utente risulta RCO
        $where_1 = array(
            'id_rco' => array($user_id),
            'id_status' => array(4), // aggiudicata
        );

        $date_offer_where = array();
        $date_offer_where['date_accepted'] = $_POST['period'];

        $where_1 = array_merge($_GET, $where_1, $date_offer_where); // TOSDADS

        $totals = $repo->getTotals($where_1);
        $this->view->tot_offers_rco = $totals['total'];
        $this->view->num_offers_rco = $totals['count'];

        // PRODUZIONE

        $repo = Maco_Model_Repository_Factory::getRepository('order');

        // Commesse assegnate RC

        $rali_date_where = array();
        $rali_date_where['date_assigned'] = $_POST['period'];

        $where_1 = array(
            'rc' => array($user->username . ' - ' . $user->contact->nome . ' ' . $user->contact->cognome),
            'id_status' => array(2), // assegnata
        );
        $where_1 = array_merge($_POST, $where_1, $rali_date_where); // NO DATA

        $totals = $repo->getTotals($where_1);
        $this->view->tot_orders_rc_ass = $totals['total'];
        $this->view->num_orders_rc_ass = $totals['count'];

        // Commesse completate RC

        $rali_date_where = array();
        $rali_date_where['date_completed'] = $_POST['period'];

        $where_1 = array(
            'rc' => array($user->username . ' - ' . $user->contact->nome . ' ' . $user->contact->cognome),
            'id_status' => array(3), // completata
        );
        $where_1 = array_merge($_POST, $where_1, $rali_date_where); // TOSDADS

        $totals = $repo->getTotals($where_1);
        $this->view->tot_orders_rc_com = $totals['total'];
        $this->view->num_orders_rc_com = $totals['count'];

        // Commesse completate RCO
        $where_1 = array(
            'id_rco' => array($user->user_id),
            'id_status' => array(3), // completata
        );
        $where_1 = array_merge($_POST, $where_1, $rali_date_where); // TOSDADS

        $totals = $repo->getTotals($where_1);
        $this->view->tot_orders_rco_com = $totals['total'];
        $this->view->num_orders_rco_com = $totals['count'];

        // SDM

        $repo = Maco_Model_Repository_Factory::getRepository('sdm');

        // creatore - autore
        $where_1 = array('count' => true, 'created_by' => array($user_id), 'date_problem' => $_POST['period']);
        $total = $repo->fetch($where_1);
        $this->view->sdm_creator = $total;
    }
}