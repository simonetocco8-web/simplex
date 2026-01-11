<?php


class TasksController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('done', 'html')
                ->addActionContext('done', 'json')
                ->addActionContext('postpone', 'json')
                ->addActionContext('postpone', 'html')
                ->addActionContext('list', 'html')
                ->addActionContext('where', 'html')
                ->addActionContext('agenda', 'html')
                ->addActionContext('table', 'html')
                ->addActionContext('calendar', 'json')
                ->addActionContext('calendar', 'html')
                ->initContext();
    }

    public function indexAction()
    {
        $this->_forward('table', null, null, array(
            'id_who' => array(Zend_Auth::getInstance()->getIdentity()->user_id),
            'done' => '0',
            '_default' => 1)
        );
    }

    public function postponeAction()
    {
        $save = $this->_request->getParam('save', false);

        $id = $this->_request->getParam('id', false);

        if(!$id)
        {
            throw new Zend_Controller_Action_Exception('page not found', 404);
        }

        $repo = new Model_Task_Repository();
        $task = $repo->find($id);

        if($save)
        {
            $task->setValidatorAndFilter(new Model_Task_Validator());
            $task->when = $this->_request->getParam('when');

            if($task->isValid())
            {
                $result = $repo->save($task);
                $this->view->result = $result;
                $this->view->message = 'aggiornamento eseguito';
            }
            else
            {
                $this->view->result = false;
                $this->view->message = 'errori durante l\'operazione';
            }
        }
        else
        {
            $this->view->data = $task;
        }
    }

    public function calendarAction()
    {
        $repo = new Model_Task_Repository();

        $search = array(
            'end' => $this->_request->getParam('end'),
            'start' => $this->_request->getParam('start'),
        );

        if($id_who = $this->_request->getParam('id_who', false))
        {
            $search['id_who'] = $id_who;
        }

        //     $search += $_POST;

        // maybe it's better
        $search = $this->_request->getParams();

        $tasks = $repo->getTasks(false, false, $search);
        $tasks = json_encode(Maco_Utils_FullCalendar::formatTasks($tasks));

        echo $tasks;
        exit;
    }

    public function agendaAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('task');

        $week = $this->_request->getParam('week', 0);

        if(date('N', time()) == 1)
        {
            $this->view->refdate = new DateTime('today');
            $ddate = new DateTime('today');
        }
        else
        {
            $this->view->refdate = new DateTime('last monday');
            $ddate = new DateTime('last monday');
        }

        $date_interval = new DateInterval('P' . abs($week) . 'W');
        if($week > 0)
        {
            $this->view->refdate->add($date_interval);
            $ddate->add($date_interval);
        }
        elseif($week < 0)
        {
            $this->view->refdate->sub($date_interval);
            $ddate->sub($date_interval);
        }
        $this->view->lastdate = $ddate->add(new DateInterval('P6D'));

        $search = array(
            'when' => $this->view->refdate->format('d/m/Y') . ' - ' .  $this->view->lastdate->format('d/m/Y'),
            'what' => array('3', '5')
        );
        $this->view->tasks = $repo->getTasks('when', 'asc', $search);
        $this->view->week = $week;

        if(!$this->_request->isXmlHttpRequest())
        {
            $this->view->agenda_active = true;
        }
    }

    public function tableAction()
    {
        $sort = $this->_request->getParam('_s', 'when');
        $dir = $this->_request->getParam('_d', 'ASC');

        // forziamo quest $_get
        if(empty($_GET) && $this->_request->getParam('_default', false) === 1)
        {
            $_GET['id_who'] = $this->_request->getParam('id_who');
            $_GET['done'] = $this->_request->getParam('done');
        }

        $search = $_GET;

        $repo = Maco_Model_Repository_Factory::getRepository('task');

        $perpage = $this->_request->getParam('perpage', Zend_Registry::getInstance()->get('config')->entries_per_page);

        if(isset($_GET['_export']) && $_GET['_export'] == 1)
        {
            $repo->exportTasks($sort, $dir, $search);
            exit;
        }

        if($this->_request->isXmlHttpRequest() || $this->_request->getParam('_default', false) === 1)
        {
            $tasks = $repo->getTasks($sort, $dir, $search, $perpage);
        }
        else
        {
            $tasks = array();
        }

        $g = $this->_getListObj();

        // search elements
        $searchRepo = new Model_Task_Search();

        $r = new Maco_DaGrid_Render_Html();
        $r->with_export = true;
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));

        $r->withFastSearch = false;
        //        $r->setId('users');
        $r->setTrIdPrefix('man_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($tasks);
        $g->setRenderer($r);
        $g->setSource($s);
        $g->setAdvancedSearch($searchRepo);
        //$g->setPaginator(true);
        $g->setRowsPerPage($perpage);
        $g->setId('tasks');

        $this->view->dag = $g;

        if($this->_request->isXmlHttpRequest())
        {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy();
        }
    }

    public function listAction()
    {
        // forziamo quest $_post
        if(empty($_POST))
        {
            $_POST['what'] = array(3, 5);
        }

        // search elements
        $searchRepo = new Model_Task_Search();
        $searchRepo->unsetWhen();
        $this->view->search = $searchRepo;

        $this->render('fullcalendar');
        return;
        // OLD - no calendar

        $this->view->tasks_active = true;

        $sort = $this->_request->getParam('_s', false);
        $dir = $this->_request->getParam('_d', 'ASC');

        $repo = Maco_Model_Repository_Factory::getRepository('task');

        $search = $_POST;

        $perpage = $this->_request->getParam('perpage', Zend_Registry::getInstance()->get('config')->entries_per_page);

        if($this->_request->isXmlHttpRequest())
        {
            $tasks = $repo->getTasks($sort, $dir, $search, $perpage);
        }
        else
        {
            $tasks = array();
        }
        /*
                $this->view->per_page = $perpage;

                // search elements
                $searchRepo = new Model_Task_Search();
                $this->view->searchElements = $searchRepo->getSearchArray();
        */

        $g = $this->_getListObj();

        // search elements
        $searchRepo = new Model_Task_Search();

        $r = new Maco_DaGrid_Render_Html();
        $r->with_export = true;
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));

        $r->withFastSearch = false;
        //        $r->setId('users');
        $r->setTrIdPrefix('man_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($tasks);
        $g->setRenderer($r);
        $g->setSource($s);
        $g->setAdvancedSearch($searchRepo);
        //$g->setPaginator(true);
        $g->setRowsPerPage($perpage);
        $g->setId('tasks');

        $this->view->dag = $g;

        if($this->_request->isXmlHttpRequest())
        {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy();
        }
        else
        {
            $this->render('dalist');
        }
    }

    protected function _getListObj()
    {
        $g = new Maco_DaGrid_Grid();
        $g->addColumns(array(
            array(
                'label' => '#',
                'field' => 'task_id',
                'class' => 'link', // class -> link set renderer -> link
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
                'path_to_template' => APPLICATION_PATH . '/Simplex/DaGrid/scripts/',
                'renderer' => 'task_what',
            ),
            array(
                'field' => 'when',
                'label' => 'quando',
                'renderer' => 'datetime'
            ),
            array(
                'label' => 'Azienda',
                'field' => 'company',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'id_company'
                    ),
                    'base' => '/companies/detail'
                )
            ),
            array(
                'field' => 'subject',
                'label' => 'Contatto'
            ),
            array(
                'field' => 'subject_data',
                'label' => 'dati'
            ),
            array(
                'field' => 'done',
                'label' => 'Eseguito',
                'renderer' => 'bool',
                'search' => 'bool'
            ),
        ));

        return $g;
    }

    public function editAction()
    {
        $this->view->contentTitle = 'Nuovo Impegno';

        $util = new Maco_Html_Utils();

        $userModel = new Model_UsersMapper();
        $users = $userModel->getAllActiveUsers();
        $users = $util->parseDbRowsForSelectElement($users, 'user_id', 'username', array('nome', 'cognome'));
        $this->view->users = array('' => '') + $users;

        $dummy_task = new Model_Task();
        $whats = $dummy_task->getWhats();
        // non vogliamo far creare un task "parlato con"
        unset($whats[4]);
        $parent_whats = $whats;
        unset($whats[7]);
        $this->view->whats = array('' => '') + $whats;
        $this->view->parent_whats = array('' => '') + $parent_whats;

        $id = $this->_request->getParam('id', false);

        $repo = Maco_Model_Repository_Factory::getRepository('task');
        $model = new Model_TasksMapper();
        $this->view->error = $this->_request->getParam('error', false);

        $gen_task = null;
        $gen_deps = null;
        $task = false;
        $id_company = false;

        if($id_gen = $this->_request->getParam('idp'))
        {
            $gen_task = $repo->find($id_gen);
            $gen_deps = $model->getDependencesFor($id_gen);
            $id_company = $gen_task->id_company;
        }
        else
        {
            $gen_task = $repo->getNewTask();
        }
        $action = $this->_request->getParam('w');

        if($action == 'd')
        {
            $parent_task = $repo->find($gen_task->id_parent);
            $this->view->parent_task = $parent_task;
            $task = $gen_task;
        }
        else
        {
            $this->view->parent_task = $gen_task;
        }

        if($id)
        {
            $task = $repo->find($id);
            if($task->id_parent)
            {
                $gen_task = $repo->find($task->id_parent);
                $this->view->parent_task = $gen_task;
            }
            $this->view->data = $task;
        }
        else
        {
            if(!$task)
            {
                $task = $repo->getNewTask();
            }

            $id_subject = $this->_request->getParam('id_subject', false);

            if($id_subject)
            {
                $task->id_subject = $id_subject;
                //$contactsModel = new Model_ContactsMapper();
                //$comp = $contactsModel->getDetail($id_receiver);

                $contactRepo = Maco_Model_Repository_Factory::getRepository('contact');
                $contact = $contactRepo->find($id_subject);

                if($contact->contact_id)
                {
                    $this->view->subject = $contact['nome'] . ' ' . $contact['cognome'];
                }

                //$id_company = $contactsModel->findCompanyIdForContact($id_receiver);
                $id_company = $contact->id_company;
            }

            $id_offer = false;
            $offer = false;

            $id_offer = $this->_request->getParam('id_offer', false);

            if($id_offer)
            {
                $task->id_offer = $id_offer;

                $offerRepo = Maco_Model_Repository_Factory::getRepository('offer');
                $offer = $offerRepo->find($id_offer);

                $this->view->offer = $offer->code_offer;
                $task->id_offer = $id_offer;

                $id_company = $offer->id_company;
                $base_url = Zend_Controller_Front::getInstance()->getBaseUrl();
                $this->view->contentTitle .= ' per l\'offerta <a href="' . $base_url . '/offers/detail/id/' . $offer->offer_id . '">' . $offer->code_offer . '</a>';
            }

            if(!$id_company)
            {
                $id_company = $this->_request->getParam('id_company', false);
            }

            if($id_company)
            {
                $task->id_company = $id_company;

                $companyRepo = Maco_Model_Repository_Factory::getRepository('company');
                $company = $companyRepo->find($id_company);

                //$companyModel = new Model_CompaniesMapper();
                //$comp = $companyModel->getDetail($id_company);

                $this->view->company = $company['ragione_sociale'];
                $task->id_company = $id_company;
                $base_url = Zend_Controller_Front::getInstance()->getBaseUrl();
                $this->view->contentTitle .= ', <a href="' . $base_url . '/companies/detail/id/' . $id_company . '">' . $company['ragione_sociale'] . '</a>';
            }

            // proponiamo come who QUESTO utente
            $user_id = Zend_Auth::getInstance()->getIdentity()->user_id;
            $this->view->this_user_data = $userModel->getUserWithContactData($user_id);

            $this->view->data = $task;
        }
    }

    public function saveAction()
    {
        /*
        if(((int)$_POST['id_receiver']) == 0)
        {
            // TODO: SE inseriamo un contatto che non esiste
         
            $contactsModel = new Model_ContactsMapper();

            $data = array(
                'cognome' => $_POST['id_receiver']
            );

            switch($_POST['what'])
            {
                case 1:
                    $data['telephones'][] = array('id' => '', 'number' => $_POST['telephone_number']);
                    break;
                case 2:
                    $data['mails'][] = array('mail' => $_POST['email']);
                    break;
                case 3:
                    $data['addresses'][] = array('localita' => $_POST['address']);
                    break;
            }

            $id_contact = $contactsModel->save($data);
            
            $_POST['id_receiver'] = $id_contact;

            if(((int)$_POST['id_company']) != 0)
            {
                $id_company = (int) $_POST['id_company'];
                $db = Zend_Registry::get('dbAdapter');

                $db->insert('contacts_companies', array(
                    'id_contact' => $id_contact,
                    'id_company' => $id_company
                ));
            }
        }
        */

        $repo = new Model_Task_Repository();

        $id_parent = $_POST['id_parent'];

        if($id_p = $this->_request->getParam('p_task_id', false))
        {
            $id_parent = $id_p;
        }
        if($this->_request->getParam('p_id_who', false))
        {
            if($id_parent)
            {
                $parent_task = $repo->find($id_parent);
            }
            else
            {
                $parent_task = new Model_Task();
            }

            $parent_task->setValidatorAndFilter(new Model_Task_Validator());
            $parent_task->id_who = $_POST['p_id_who'];
            $parent_task->when = $_POST['p_when'];
            $parent_task->what = isset($_POST['p_what']) ? $_POST['p_what'] : 4;
            $parent_task->id_company = $_POST['id_company'];

            $p_subject = $_POST['p_subject'];
            if((int) $p_subject)
            {
                $contact_repo = Maco_Model_Repository_Factory::getRepository('contact');
                $p_subject_data = $contact_repo->find($p_subject);
                $parent_task->id_subject = $_POST['p_subject'];
                $parent_task->subject = $p_subject_data['nome'] . ' ' . $p_subject_data['cognome'];
            }
            else
            {
                $parent_task->subject = $_POST['p_subject'];
            }

            $parent_task->done = 1;
            $parent_task->date_when = $_POST['p_when'];
            $parent_task->date_done = $_POST['p_when'];

            if($parent_task->isValid())
            {
                $id_parent = $repo->save($parent_task);
            }
            else
            {
                foreach($parent_task->getInvalidMessages() as $cont)
                {
                    foreach($cont as $msg)
                    {
                        $this->_helper->getHelper('FlashMessenger')->addMessage($msg);
                    }
                }

                $this->_redirect('tasks/edit/id_company/' . $_POST['id_company']);
            }
        }

        if(isset($_POST['task_id']) && !empty($_POST['task_id']))
        {
            $task = $repo->find($_POST['task_id']);
        }
        else
        {
            $task = new Model_Task();
        }

        $task->setValidatorAndFilter(new Model_Task_Validator());
        $data = $_POST;

        $data['id_parent'] = $id_parent;

        switch($_POST['what'])
        {
            case 1:
                $data['subject_data'] = $_POST['telephone_number'];
                break;
            case 2:
                $data['subject_data'] = $_POST['email'];
                break;
            case 3:
            case 5:
                $data['subject_data'] = $_POST['address'];
                break;
            default:
                $data['subject_data'] = $_POST['subject_data'];
                break;
        }

        $subject = $_POST['subject'];
        if((int) $subject)
        {
            $contact_repo = Maco_Model_Repository_Factory::getRepository('contact');
            $subject_data = $contact_repo->find($subject);
            $data['id_subject'] = $_POST['subject'];
            $data['subject'] = $subject_data['nome'] . ' ' . $subject_data['cognome'];
        }
        else
        {
            $data['subject'] = $_POST['subject'];
        }

        //$data['type'] = (isset($_POST['type_1']) ? 1 : 0) | (isset($_POST['type_2']) ? 2 : 0) | (isset($_POST['type_3']) ? 4 : 0);

        $data_when = Maco_Utils_DbDate::toDb($data['when']);
        if($data['when'] != '' && ($data['time_expected_hour'] != '' || $data['time_expected_minute'] != ''))
        {
            $date = new DateTime($data_when);
            $interval_string = '';
            // controlliamo se � un intero o se dobbiamo calcolare i minuti

            $h = $data['time_expected_hour'];
            $m = $data['time_expected_minute'];

            //$h = floor($data['time_expected']);
            //$dec = (float)$data['time_expected'] - $h;
            //$m = 60 * $dec;
            $interval_string = 'PT' . $h . 'H' . $m . 'M';
            $date = $date->add(new DateInterval($interval_string));
            $data['finishs'] = $date->format('Y-m-d H:i:s');

            // TODO: aggiungere controllo concomitanza
            /*
            $repo = new Model_TasksMapper();
            $where = array('when' => $data['finishs'], 'finishs' => $data_when, 'id_who' => $data['id_who']);
            $tasks = $repo->fetch(array('where' => $where));
            if(!empty($tasks))
            {
                $this->_helper->getHelper('FlashMessenger')->addMessage('Utente gi&agrave; impegnato nel periodo inserito');

                //
                $this->_redirect('tasks/edit/error/1/id_company/' . $data['id_company']);
            }
            */
        }

        if($this->_request->getParam('with_auto', false))
        {
            $data['with_auto'] = 1;
        }
        if($this->_request->getParam('when_flexible', false))
        {
            $data['when_flexible'] = 1;
        }

        $time_expected_value = Maco_Utils_Time::toValue($data['time_expected_hour'], $data['time_expected_minute']);
        $data['time_expected'] = $time_expected_value;

        unset($data['task_id'], $data['time_expected_hour'], $data['time_expected_minute']);

        $task->setData($data);
        if($task->isValid())
        {
            $repo = new Model_Task_Repository();

            $id = $repo->save($task);

            // send a message to the rco for this offer
            $message_repo = Maco_Model_Repository_Factory::getRepository('message');
            $message_title = 'Nuovo impegno alle ' . Maco_Utils_DbDate::fromDb($task->when);
            $task->task_id = $id;
            $message_body = $task->getFormatted();
            $message_repo->send($task->id_who, $message_title, $message_body, Model_Message_Types::TODO);

            $this->_redirect('tasks/detail/id/' . $id);
        }
        else
        {
            foreach($task->getInvalidMessages() as $cont)
            {
                foreach($cont as $msg)
                {
                    $this->_helper->getHelper('FlashMessenger')->addMessage($msg);
                }
            }

            $this->_redirect('tasks/edit/id_company/' . $_POST['id_company']);
        }
    }

    public function doneAction()
    {
        $save = $this->_request->getParam('save', false);

        $id = $this->_request->getParam('id', false);

        if(!$id)
        {
            throw new Zend_Controller_Action_Exception('page not found', 404);
        }

        $repo = new Model_Task_Repository();
        $task = $repo->find($id);

        if($save)
        {
            $task->setValidatorAndFilter(new Model_Task_Validator());
            if($this->_request->getParam('cancel_task', false))
            {
                $task->done = -1;
            }
            else
            {
                $task->done = 1;
            }
            $task->note = $this->_request->getParam('note_done', '');
            if($task->done)
            {
                $task->date_done = date('d-m-Y h:i');
            }

            if($task->isValid())
            {
                $result = $repo->save($task);

                $this->view->result = $result;

                $this->view->message = 'aggiornamento eseguito';
            }
            else
            {
                $this->view->result = false;
                $this->view->message = 'errori durante l\'operazione';
            }
        }
        else
        {
            $this->view->data = $task;
        }
    }

    public function detailAction()
    {
        $id = $this->_request->getParam('id', false);

        if(!$id)
        {
            throw new Exception('Id missing', 500);
        }

        $repo = Maco_Model_Repository_Factory::getRepository('task');

        $task = $repo->find($id);

        // TODO: brutto -> da rifare con la nuova metodologia
        $mod = new Model_TasksMapper();

        $this->view->deps = $mod->getDependencesFor($id);

        $this->view->data = $task;

        if($id_parent = $task->id_parent)
        {
            $parent_task = $repo->find($id_parent);

            // TODO: brutto -> da rifare con la nuova metodologia
            $mod = new Model_TasksMapper();

            $this->view->parent_deps = $mod->getDependencesFor($id_parent);

            $this->view->parent_data = $parent_task;
        }

        // impegni figli
        $sort = $this->_request->getParam('_s', false);
        $dir = $this->_request->getParam('_d', 'ASC');

        $perpage = $this->_request->getParam('perpage', Zend_Registry::getInstance()->get('config')->entries_per_page);

        $search = array('id_parent' => $id);

        $tasks = $repo->getTasks($sort, $dir, $search, $perpage);

        $g = $this->_getListObj();

        $r = new Maco_DaGrid_Render_Html();
        $r->with_export = false;
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

        $this->view->dag = $g;

        if($this->_request->isXmlHttpRequest())
        {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy();
        }
    }

    public function whereAction()
    {
        $mapper = new Model_TasksMapper();

        $options = array();

        $options['where']['when'] = 'FUTURI';
        $options['where']['what'] = 3;

        if(isset($_POST['address'])) $options['where']['address'] = $_POST['address'];
        if(isset($_POST['who'])) $options['where']['who'] = $_POST['who'];
        if(isset($_POST['when']) && $_POST['when'] != '') $options['where']['when'] = $_POST['when'];


        $tasks = $mapper->fetch($options);

        $g = new Maco_DaGrid_Grid();
        $g->addColumns(array(
            array(
                'label' => 'Chi',
                'field' => 'who',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'task_id'
                    ),
                    'base' => '/tasks/detail'
                )
            ),
            array(
                'field' => 'when',
                'label' => 'quando',
                'renderer' => 'datetime'
            ),
            array(
                'field' => 'finishs',
                'label' => 'fine',
                'renderer' => 'datetime'
            ),
            array(
                'field' => 'address',
                'label' => 'dove',
            ),
        ));

        $r = new Maco_DaGrid_Render_Html();
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        //        $r->setId('users');
        $r->setTrIdPrefix('task_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($tasks);
        $g->setRenderer($r);
        $g->setSource($s);
        //$g->setPaginator(true);
        $g->setRowsPerPage($this->_request->getParam('perpage', 20));
        $g->setId('tasks');

        $this->view->dag = $g;

        if($this->_request->isXmlHttpRequest())
        {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy();
        }
    }
}
