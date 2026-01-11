<?php

class AdminController extends Zend_Controller_Action
{
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('users', 'html')
            ->addActionContext('users', 'json')
            ->addActionContext('tbl', 'json')
            ->addActionContext('common', 'html')
            ->addActionContext('subservices', 'html')
            ->addActionContext('subservicesbyserviceforselect', 'html')
            ->addActionContext('common', 'json')
            ->initContext();
    }

    public function genericsAction()
    {
    }

    /**
     * Azioni per il sottomodulo utenti
     *
     * @throws Zend_Controller_Action_Exception
     */
    public function usersAction()
    {
        $task = $this->_request->getParam('task', 'list');

        switch ($task)
        {
            case 'list':
                $this->usersList(0);
                break;
            case 'bin':
                $this->usersList(1);
                break;
            case 'detail':
                $this->userDetail();
                break;
            case 'edit':
                $this->userEdit();
                break;
            case 'links':
                $this->userLinks();
                break;
            case 'permissions':
                $this->userPermissions();
                break;
            case 'save':
                $this->userSave();
                break;
            case 'delete':
                $this->userDelete();
                break;
            case 'active':
                $this->userActive();
                break;
            case 'cpw':
                $this->userChangePassword();
                break;
            case 'spw':
                $this->userSavePassword();
                break;
            case 'report':
                $this->userReport();
                break;
            default:
                throw new Zend_Controller_Action_Exception('Task non trovato per modulo admin, action users', 404);
                break;
        }
    }

    public function userReport()
    {
        $id = $this->_request->getParam('id', false);

        if (!$id) {
            throw new Zend_Controller_Action_Exception('Necessario id per il dettaglio utente', 404);
        }

        if(!$this->_request->isPost())
        {
            $lastDay = date("t/m/Y", strtotime("first day of previous month"));
            $firstDay = date("01/m/Y", strtotime("first day of previous month"));
            $_POST['period'] = $firstDay . ' - ' . $lastDay;
            $_GET['period'] = $firstDay . ' - ' . $lastDay;
        }

        $repo = Maco_Model_Repository_Factory::getRepository('user');

        $user = $repo->find($id);
        $this->view->user = $user;

        $searchRepo = new Model_User_Search_Report();
        $this->view->search = $searchRepo;

        // TODO: PER AZIENDA INTERNA????

        // aziende RCO
        $repo = Maco_Model_Repository_Factory::getRepository('company');
        $this->view->num_companies_rco = $repo->get_count_companies_rco(
            $id,
            isset($_POST['period']) ? $_POST['period'] : null);
        unset($repo);
        $repo = Maco_Model_Repository_Factory::getRepository('offer');

        // Offerte emesse in cui l’Utente risulta "segnalatore"

        $date_offer_where = array();
        if(isset($_POST['period']))
        {
            $date_offer_where['date_offer'] = $_POST['period'];
        }

        $where_1 = array(
            'segnalato_da' => $user->username,
            'id_status' => array(1, 3, 4, 5), // inviata, aggiudicata, non accettata
        );
        $where_1 = array_merge($_POST, $where_1, $date_offer_where); // TOSDADS

        $totals = $repo->getTotals($where_1);
        $this->view->tot_offers_1_seg = $totals['total'];
        $this->view->num_offers_1_seg = $totals['count'];

        // Offerte emesse in cui l’Utente risulta RCO
        $where_1 = array(
            'id_rco' => array($id),
            'id_status' => array(3, 4, 5), // inviata, aggiudicata, non accettata
        );
        $where_1 = array_merge($_POST, $where_1, $date_offer_where); // TOSDADS

        $totals = $repo->getTotals($where_1);
        $this->view->tot_offers_1_rco = $totals['total'];
        $this->view->num_offers_1_rco = $totals['count'];

        // Offerte emesse in cui l’Utente risulta approvatore
        $where_1 = array(
            'id_approver' => array($id),
            'id_status' => array(3, 4, 5), // inviata, aggiudicata, non accettata
        );
        $where_1 = array_merge($_POST, $where_1, $date_offer_where); // TOSDADS

        $totals = $repo->getTotals($where_1);
        $this->view->tot_offers_1_app = $totals['total'];
        $this->view->num_offers_1_app = $totals['count'];

        // Offerte aggiudicate in cui l’Utente risulta "segnalatore"
        $date_offer_where = array();
        if(isset($_POST['period']))
        {
            $date_offer_where['date_accepted'] = $_POST['period'];
        }

        $where_1 = array(
            'segnalato_da' => $user->username,
            'id_status' => array(4), // inviata, aggiudicata, non accettata
        );
        $where_1 = array_merge($_POST, $where_1, $date_offer_where); // TOSDADS

        $totals = $repo->getTotals($where_1);
        $this->view->tot_offers_2_seg = $totals['total'];
        $this->view->num_offers_2_seg = $totals['count'];

        // Offerte aggiudicate in cui l’Utente risulta RCO
        // TODO: al partner
        $where_1 = array(
            'id_rco' => array($id),
            'id_status' => array(4), // aggiudicata
        );
        $where_1 = array_merge($_POST, $where_1, $date_offer_where); // TOSDADS

        $totals = $repo->getTotals($where_1);
        $this->view->tot_offers_2_rco = $totals['total'];
        $this->view->tot_offers_2_rco_promotors = $totals['to_promotors'];
        $this->view->num_offers_2_rco = $totals['count'];

        // Offerte aggiudicate in cui l’Utente risulta approvatore
        $where_1 = array(
            'id_approver' => array($id),
            'id_status' => array(4), // inviata, aggiudicata, non accettata
        );
        $where_1 = array_merge($_POST, $where_1, $date_offer_where); // TOSDADS

        $totals = $repo->getTotals($where_1);
        $this->view->tot_offers_2_app = $totals['total'];
        $this->view->num_offers_2_app = $totals['count'];


        // PRODUZIONE

        $repo = Maco_Model_Repository_Factory::getRepository('order');

        // Commesse assegnate RC

        $rali_date_where = array();
        if(isset($_POST['period']))
        {
            $rali_date_where['date_assigned'] = $_POST['period'];
        }

        $where_1 = array(
            'rc' => array($user->username . ' - ' . $user->contact->nome . ' ' . $user->contact->cognome),
            'id_status' => array(2), // assegnata
        );
        $where_1 = array_merge($_POST, $where_1, $rali_date_where); // NO DATA

        $totals = $repo->getTotals($where_1);
        $this->view->tot_orders_2_ass = $totals['total'];
        $this->view->num_orders_2_ass = $totals['count'];

        // Commesse completate RC

        $rali_date_where = array();
        if(isset($_POST['period']))
        {
            $rali_date_where['date_completed'] = $_POST['period'];
        }

        $where_1 = array(
            'rc' => array($user->username . ' - ' . $user->contact->nome . ' ' . $user->contact->cognome),
            'id_status' => array(3), // completata
        );
        $where_1 = array_merge($_POST, $where_1, $rali_date_where); // TOSDADS

        $totals = $repo->getTotals($where_1);
        $this->view->tot_orders_3_ass = $totals['total'];
        $this->view->tot_orders_3_ass_promotors = $totals['to_promotors'];
        $this->view->num_orders_3_ass = $totals['count'];

        // Commesse completate RC
        $where_1 = array(
            'id_rco' => array($user->user_id),
            'id_status' => array(3), // completata
        );
        $where_1 = array_merge($_POST, $where_1, $rali_date_where); // TOSDADS

        $totals = $repo->getTotals($where_1);
        $this->view->tot_orders_3o_ass = $totals['total'];
        $this->view->tot_orders_3o_ass_promotors = $totals['to_promotors'];
        $this->view->num_orders_3o_ass = $totals['count'];


        // Commesse Sospese RC NO DATA
        $where_1 = array(
            'rc' => array($user->username . ' - ' . $user->contact->nome . ' ' . $user->contact->cognome),
            'id_status' => array(4), // assegnata
        );
        if(isset($_POST['period']))
        {
            $where_1['date_suspended'] = $_POST['period'];
        }
        $where_1 = array_merge($_POST, $where_1); // TOSDADS

        $totals = $repo->getTotals($where_1);
        $this->view->tot_orders_4_ass = $totals['total'];
        $this->view->num_orders_4_ass = $totals['count'];

        // Commesse Annullate RC NO DATA
        $where_1 = array(
            'rc' => array($user->username . ' - ' . $user->contact->nome . ' ' . $user->contact->cognome),
            'id_status' => array(5), // assegnata
        );
        if(isset($_POST['period']))
        {
            $where_1['date_cancelled'] = $_POST['period'];
        }
        $where_1 = array_merge($_POST, $where_1); // TOSDADS

        $totals = $repo->getTotals($where_1);
        $this->view->tot_orders_5_ass = $totals['total'];
        $this->view->num_orders_5_ass = $totals['count'];

        // Commesse DTG - COMPLETATE
        $where_1 = array(
            'id_dtg' => array($id),
            'id_status' => array(3), // completata
        );
        $where_1 = array_merge($_POST, $where_1, $rali_date_where); // TOSDADS

        $totals = $repo->getTotals($where_1);
        $this->view->tot_orders_dtg = $totals['total'];
        $this->view->num_orders_dtg = $totals['count'];

        // Momenti RC - DATA DI FATTURAZIONE
        $model = new Model_Administration();

        $old_date_done = isset($_POST['date_done']) ? $_POST['date_done'] : null;
        $_GET['date_done'] = isset($_POST['period']) ? $_POST['period'] : null;
        $old_stato = isset($_POST['stato']) ? $_POST['stato'] : null;
        $_GET['stato'] = array('completed');
        $old_rc = isset($_POST['rc']) ? $_POST['stato'] : null;
        $_GET['rc'] = array($user->username . ' - ' . $user->contact->nome . ' ' . $user->contact->cognome);

        $where_1 = array();

        $totals = $model->getTotal($where_1, true);
        $this->view->tot_moments_1_rc = $totals['total'];
        $this->view->num_moments_1_rc = $totals['count'];

        $_POST['date_done'] = $old_date_done;

        // Amministrazione

        // Momenti di fatturazione in cui l'utente risulta RC

        $where_1 = array('search' => array('fatturazione' => 1));
        $totals = $model->getTotal($where_1, true);
        $this->view->tot_moments_2_rc = $totals['total'];
        $this->view->num_moments_2_rc = $totals['count'];

        $_POST['stato'] = $old_stato;
        $_POST['rc'] = $old_rc;


        $repo = Maco_Model_Repository_Factory::getRepository('payment');

        $period = isset($_POST['period']) ? $_POST['period'] : '';

        //Incassi sui contratti di cui l'utente risulta RCO
        $where_1 = array('search' => array('id_rco' => array($id), 'date_done' => $period));
        $total = $repo->getTotal($where_1);
        $this->view->incasso_rco = $total;

        //Incassi sui contratti di cui l'utente risulta RC
        $where_1 = array('search' => array('rc' => array($user->username . ' - ' . $user->contact->nome . ' ' . $user->contact->cognome), 'date_done' => $period));
        $total = $repo->getTotal($where_1);
        $this->view->incasso_rc = $total;

        //Incassi sui contratti di cui l'utente risulta DTG
        $where_1 = array('search' => array('id_dtg' => array($id), 'date_done' => $period));
        $total = $repo->getTotal($where_1);
        $this->view->incasso_dtg = $total;


        // IMPEGNI

        $repo = Maco_Model_Repository_Factory::getRepository('task');

        // creatore
        $where_1 = array('created_by' => array($id), 'date_created' => $period);
        $total = $repo->getTasks(false, false, $where_1, NULL, true);
        $this->view->task_creator = $total;

        // responsabile
        $where_1 = array('id_who' => array($id), 'when' => $period);
        $total = $repo->getTasks(false, false, $where_1, NULL, true);
        $this->view->task_who = $total;

        // responsabile - eseguiti
        $where_1 = array('id_who' => array($id), 'date_done' => $period, 'done' => 1);
        $total = $repo->getTasks(false, false, $where_1, NULL, true);
        $this->view->task_who_done = $total;

        // responsabile - incontro
        $where_1 = array('id_who' => array($id), 'when' => $period, 'what' => array(3));
        $total = $repo->getTasks(false, false, $where_1, NULL, true);
        $this->view->task_who_incontro = $total;


        // SDM

        $repo = Maco_Model_Repository_Factory::getRepository('sdm');

        // creatore - autore
        $where_1 = array('count' => true, 'created_by' => array($id), 'date_problem' => $period);
        $total = $repo->fetch($where_1);
        $this->view->sdm_creator = $total;

        // rsq - verificatore
        $where_1 = array('count' => true, 'id_responsible' => array($id), 'date_problem' => $period);
        $total = $repo->fetch($where_1);
        $this->view->sdm_rsq = $total;

        // risolutore
        $where_1 = array('count' => true, 'id_solver' => array($id), 'date_problem' => $period);
        $total = $repo->fetch($where_1);
        $this->view->sdm_risolutore = $total;

        // responsabile trattamento
//        $where_1 = array('count' => true, 'id_solver' => array($id), 'date_problem' => $period);
        //      $total = $repo->fetch($where_1);
        $this->view->sdm_trattamento = '????';

        // RESPONABILE
        $where_1 = array('count' => true, 'responsible' => array($id), 'date_problem' => $period);
        $total = $repo->fetch($where_1);
        $this->view->sdm_responsible = $total;

        if(isset($_POST['_export']) && $_POST['_export'] == 1)
        {
            include(LIBRARY_PATH . '/Tbs/tbs_class.php');
            include(LIBRARY_PATH . '/Tbs/plugins/tbs_plugin_opentbs.php');

            $filesMapper = new Model_FilesMapper();

            $template_name = 'user-report.docx';

            $tbs = new clsTinyButStrong; // new instance of TBS
            $tbs->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin

            $tbs->LoadTemplate($filesMapper->getTemplatePath() . $template_name);

            global $username;
            global $num_companies_rco;
            global $num_offers_1_seg;
            global $tot_offers_1_seg;
            global $num_offers_1_rco;
            global $tot_offers_1_rco;
            global $num_offers_1_app;
            global $tot_offers_1_app;
            global $num_offers_2_seg;
            global $tot_offers_2_seg;
            global $num_offers_2_rco;
            global $tot_offers_2_rco;
            global $tot_offers_2_rco_promotors;
            global $num_offers_2_app;
            global $tot_offers_2_app;
            global $num_orders_2_ass;
            global $tot_orders_2_ass;
            global $num_orders_3_ass;
            global $tot_orders_3_ass;
            global $tot_orders_3_ass_promotors;
            global $num_orders_3o_ass;
            global $tot_orders_3o_ass;
            global $tot_orders_3o_ass_promotors;
            global $num_orders_4_ass;
            global $tot_orders_4_ass;
            global $num_orders_5_ass;
            global $tot_orders_5_ass;
            global $num_orders_dtg;
            global $tot_orders_dtg;
            global $num_moments_1_rc;
            global $tot_moments_1_rc;
            global $num_moments_2_rc;
            global $tot_moments_2_rc;
            global $incasso_rc;
            global $incasso_rco;
            global $incasso_dtg;
            global $task_creator;
            global $task_who;
            global $task_who_done;
            global $task_who_incontro;
            global $sdm_creator;
            global $sdm_rsq;
            global $sdm_risolutore;
            global $sdm_trattamento;
            global $sdm_responsible;
            global $periodo;
            global $service;
            global $subservice;

            if(isset($_POST['period']) && $_POST['period'] != '')
            {
                $parts = explode('-', $_POST['period']);

                if(count($parts) == 2)
                {
                    $periodo = 'da ' . $parts[0] . ' a ' . $parts[1];
                }
                else
                {
                    $periodo = $parts[0];
                }
            }
            else
            {
                $periodo = '';
            }

            $db = Zend_Registry::get('dbAdapter');
            //$db = new Zend_Db_Adapter_Mysqli();
            if(isset($_POST['id_service']) && !empty($_POST['id_service']))
            {
                $service = implode(', ', $db->fetchCol('select name from services where service_id in (' . implode(',', $_POST['id_service']) . ')'));
            }
            else
            {
                $service = '';
            }
            if(isset($_POST['id_subservice']) && !empty($_POST['id_subservice']))
            {
                $subservice = implode(', ', $db->fetchCol('select name from subservices where subservice_id in (' . implode(',', $_POST['id_subservice']) . ')'));
            }
            else
            {
                $subservice = '';
            }

            $username = utf8_decode($user->username);
            $sdm_responsible = $this->view->sdm_responsible;
            $sdm_trattamento = $this->view->sdm_trattamento;
            $sdm_risolutore = $this->view->sdm_risolutore;
            $sdm_rsq = $this->view->sdm_rsq;
            $sdm_creator = $this->view->sdm_creator;
            $task_creator = $this->view->task_creator;
            $task_who_incontro = $this->view->task_who_incontro;
            $task_who = $this->view->task_who;
            $task_who_done = $this->view->task_who_done;
            $incasso_rc = number_format($this->view->incasso_rc, 2, ',', '.');
            $incasso_rco = number_format($this->view->incasso_rco, 2, ',', '.');
            $incasso_dtg = number_format($this->view->incasso_dtg, 2, ',', '.');
            $num_moments_1_rc = $this->view->num_moments_1_rc;
            $tot_moments_1_rc = number_format($this->view->tot_moments_1_rc, 2, ',', '.');
            $num_moments_2_rc = $this->view->num_moments_2_rc;
            $tot_moments_2_rc = number_format($this->view->tot_moments_2_rc, 2, ',', '.');
            $num_orders_dtg = $this->view->num_orders_dtg;
            $tot_orders_dtg = number_format($this->view->tot_orders_dtg, 2, ',', '.');
            $num_orders_5_ass = $this->view->num_orders_5_ass;
            $tot_orders_5_ass = number_format($this->view->tot_orders_5_ass, 2, ',', '.');
            $num_orders_4_ass = $this->view->num_orders_4_ass;
            $tot_orders_4_ass = number_format($this->view->tot_orders_4_ass, 2, ',', '.');
            $num_orders_3_ass = $this->view->num_orders_3_ass;
            $tot_orders_3_ass = number_format($this->view->tot_orders_3_ass, 2, ',', '.');
            $tot_orders_3_ass_promotors = number_format($this->view->tot_orders_3_ass_promotors, 2, ',', '.');
            $num_orders_3o_ass = $this->view->num_orders_3o_ass;
            $tot_orders_3o_ass = number_format($this->view->tot_orders_3o_ass, 2, ',', '.');
            $tot_orders_3o_ass_promotors = number_format($this->view->tot_orders_3o_ass_promotors, 2, ',', '.');
            $num_orders_2_ass = $this->view->num_orders_2_ass;
            $tot_orders_2_ass = number_format($this->view->tot_orders_2_ass, 2, ',', '.');
            $num_offers_2_app = $this->view->num_offers_2_app;
            $tot_offers_2_app = number_format($this->view->tot_offers_2_app, 2, ',', '.');
            $num_companies_rco = $this->view->num_companies_rco;
            $num_offers_1_seg = $this->view->num_offers_1_seg;
            $tot_offers_1_seg = number_format($this->view->tot_offers_1_seg, 2, ',', '.');
            $num_offers_1_rco = $this->view->num_offers_1_rco;
            $tot_offers_1_rco = number_format($this->view->tot_offers_1_rco, 2, ',', '.');
            $num_offers_1_app = $this->view->num_offers_1_app;
            $tot_offers_1_app = number_format($this->view->tot_offers_1_app, 2, ',', '.');
            $num_offers_2_seg = $this->view->num_offers_2_seg;
            $tot_offers_2_seg = number_format($this->view->tot_offers_2_seg, 2, ',', '.');
            $num_offers_2_rco = $this->view->num_offers_2_rco;
            $tot_offers_2_rco = number_format($this->view->tot_offers_2_rco, 2, ',', '.');
            $tot_offers_2_rco_promotors = number_format($this->view->tot_offers_2_rco_promotors, 2, ',', '.');


            $file_name = 'report utente ' . $user->username .  '.docx';
            $tbs->Show(OPENTBS_DOWNLOAD, $file_name);
            exit;
        }

        $this->render('users/report');
    }

    public function restoreAction()
    {
        $db = Zend_Registry::get('dbAdapter');

        $config = Zend_Registry::get('config');
        $db_name = $config->resources->db->params->dbname;

        $db->query('DROP DATABASE IF EXISTS ' . $db_name);

        $db->query('CREATE DATABASE ' . $db_name);

        $db = new Zend_Db_Adapter_Mysqli($config->resources->db->params);

        $db_schema = file_get_contents(APPLICATION_PATH . '/../scripts/sql/simplex0.sql');

        // Use todays date as the date for the first incident in the system
        $db_schema = str_replace('now()',
            date("'Y-m-d H:i:s'",time()), $db_schema);

        /**
         * split by ; to get the sql statement for creating individual
         * tables.
         */
        $tables = explode(';',$db_schema);
        $last_query = '';
        try
        {
            foreach($tables as $query)
            {
                $last_query = $query;
                if(trim($query) != '')
                {
                    $result = $db->query($query);
                }
            }

            $import_config = new Simplex_Importer_Config();
            $import_config->piva_progr = 0;
            $import_config->save();
        }
        catch(Exception $e)
        {
            -dd($e);
        }

        $this->_helper->getHelper('FlashMessenger')->addMessage('sistema ripristinato ai dati iniziali!');

        $this->_redirect('/');
    }

    public function dcompAction()
    {
        $db = Zend_Registry::get('dbAdapter');
        // commesse chiuse
        $orders = $db->fetchAll('select order_id, id_offer from orders where id_status = 3 and (date_completed is null or date_completed = \'\' or date_completed = \'0000-00-00\')');
        foreach($orders as $order)
        {
            $moments = $db->fetchAll('select date_done, done from moments where id_offer = ' . $order['id_offer']);
            $date = '0000-00-00';
            $good = true;
            foreach($moments as $moment)
            {
                if($moment['done'] != 1)
                {
                    $good = false;
                    break;
                }
                else
                {
                    if($moment['date_done'] > $date)
                    {
                        $date = $moment['date_done'];
                    }
                }
            }
            if($good)
            {
                $db->update('orders', array('date_completed' => $date), 'order_id = ' . $order['order_id']);
            }
        }
        echo 'done';
        exit;
    }

    public function allservAction()
    {
        /** @var $db Zend_Db_Adapter_Mysqli */
        $db = Zend_Registry::get('dbAdapter');
        $internal_ids = $db->fetchCol('select internal_id from internals');
        $service_ids = $db->fetchCol('select service_id from services');
        $subservice_ids = $db->fetchCol('select subservice_id from subservices');

        foreach($internal_ids as $internal_id)
        {
            foreach($service_ids as $service_id)
            {
                $db->insert('service_internal', array(
                    'id_internal' => $internal_id,
                    'id_service' => $service_id
                ));
            }
            foreach($subservice_ids as $subservice_id)
            {
                $db->insert('subservice_internal', array(
                    'id_internal' => $internal_id,
                    'id_subservice' => $subservice_id
                ));
            }
        }

        echo 'done';
        exit;
    }

    public function allvbAction()
    {
        $db = Zend_Registry::get('dbAdapter');
        $user_ids = $db->fetchCol('select user_id from users');

        foreach($user_ids as $user_id)
        {
            $db->insert('users_permissions', array(
                'id_user' => $user_id,
                'resource' => 'orders',
                'action' => 'view_budget',
            ));
        }
        echo 'done';
        exit;
    }

    public function allexAction()
    {
        $db = Zend_Registry::get('dbAdapter');
        $excellentia_id = $db->fetchOne('select internal_id from internals where abbr = \'EX\'');
        if(!$excellentia_id)
        {
            echo 'no ex';
            exit;
        }
        $select = $db->select();
        $select->from('companies', 'company_id')
            ->where('company_id not in (select distinct id_company from companies_internals where id_internal = ' . $excellentia_id . ')');

        $companies = $db->fetchCol($select);
        foreach($companies as $company_id)
        {
            $db->insert('companies_internals', array(
                'id_company' => $company_id,
                'id_internal' => $excellentia_id
            ));
        }
        echo 'done';
        exit;
    }

    public function parsesdmAction()
    {
        /** @var $db Zend_Db_Adapter_Mysqli */
        $db = Zend_Registry::get('dbAdapter');

        $sdms = $db->fetchAll('select * from sdm');

        $repo = Maco_Model_Repository_Factory::getRepository('sdm2');
        $sdm_story_repo = Maco_Model_Repository_Factory::getRepository('sdm2Story');

        $db->beginTransaction();

        try
        {
            foreach($sdms as $sdm)
            {
                $newSdm = new Model_Sdm2();
                $newSdm->setValidatorAndFilter(new Model_Sdm2_Validator());

                $data = array(
                    'year' => $sdm['year'],
                    'code' => $sdm['code'],
                    'id_status' => $sdm['id_status'],
                );

                $newSdm->setData($data);

                if($newSdm->isValid())
                {
                    $sdm_id = $repo->save($newSdm);

                    $db->update('sdm2', array(
                        'date_created' => $sdm['date_created'],
                        'created_by' => $sdm['created_by'],
                        'date_modified' => $sdm['date_modified'],
                        'modified_by' => $sdm['modified_by'],
                    ), 'sdm_id = ' . $sdm_id);

                    $sdm_story = new Model_Sdm2Story();
                    $sdm_story->setValidatorAndFilter(new Model_Sdm2Story_Validator());

                    $data = array(
                        'id_sdm' => $sdm_id,
                        'active' => 1,
                        'id_status' => Model_Sdm2::STATUS_NEW,
                        'text1' => $sdm['problem'],
                        'date1' => $sdm['date_problem'],
                        'id_user' => $sdm['id_responsible']
                    );

                    $sdm_story->setData($data);

                    if($sdm_story->isValid())
                    {
                        $sdm_story_id = $sdm_story_repo->save($sdm_story);

                        $db->update('sdm_story', array(
                            'date_created' => $sdm['date_created'],
                            'created_by' => $sdm['created_by'],
                        ), 'sdm_story_id = ' . $sdm_story_id);
                    }
                    else
                    {
                        $db->rollBack();
                        echo 'errors in adding NEW story';exit;
                    }

                    if($sdm['id_status'] == 3 || $sdm['id_status'] == 4 || $sdm['id_status'] == 5)
                    {
                        // aggiungo working story
                        $sdm_story = new Model_Sdm2Story();
                        $sdm_story->setValidatorAndFilter(new Model_Sdm2Story_Validator());

                        $data = array(
                            'id_sdm' => $sdm_id,
                            'active' => 1,
                            'id_status' => Model_Sdm2::STATUS_WORKING,
                            'text1' => $sdm['cause'],
                            'text2' => $sdm['area'],
                            'date1' => $sdm['date_set_solver'],
                            'date2' => $sdm['date_expected_resolution'],
                            'id_user' => $sdm['id_solver']
                        );

                        $sdm_story->setData($data);

                        if($sdm_story->isValid())
                        {
                            $sdm_story_repo->save($sdm_story);
                        }
                        else
                        {
                            $db->rollBack();
                            echo 'errors in adding WORKING story';exit;
                        }
                    }

                    if($sdm['id_status'] == 4 || $sdm['id_status'] == 5)
                    {
                        // aggiungo working story
                        $sdm_story = new Model_Sdm2Story();
                        $sdm_story->setValidatorAndFilter(new Model_Sdm2Story_Validator());

                        $data = array(
                            'id_sdm' => $sdm_id,
                            'active' => 1,
                            'id_status' => Model_Sdm2::STATUS_SOLVED,
                            'text1' => $sdm['resolution'],
                            'date1' => $sdm['date_resolution'],
                            'date2' => $sdm['date_resolution'],
                        );

                        $sdm_story->setData($data);

                        if($sdm_story->isValid())
                        {
                            $sdm_story_id = $sdm_story_repo->save($sdm_story);

                            $db->update('sdm_story', array(
                                'created_by' => $sdm['id_solver'],
                            ), 'sdm_story_id = ' . $sdm_story_id);
                        }
                        else
                        {
                            $db->rollBack();

                            echo 'errors in adding SOLVED story';exit;
                        }
                    }

                    if($sdm['id_status'] == 5)
                    {
                        // aggiungo working story
                        $sdm_story = new Model_Sdm2Story();
                        $sdm_story->setValidatorAndFilter(new Model_Sdm2Story_Validator());

                        $data = array(
                            'id_sdm' => $sdm_id,
                            'active' => 1,
                            'id_status' => Model_Sdm2::STATUS_VERIFIED,
                            'text1' => $sdm['verification'],
                            'date1' => $sdm['date_verification'],
                            'date2' => $sdm['date_verification'],
                        );

                        $sdm_story->setData($data);

                        if($sdm_story->isValid())
                        {
                            $sdm_story_id = $sdm_story_repo->save($sdm_story);

                            $db->update('sdm_story', array(
                                'created_by' => $sdm['id_responsible'],
                            ), 'sdm_story_id = ' . $sdm_story_id);

                            $responsibles = $db->fetchAll('select * from sdm_responsibles where id_sdm = ' . $sdm_id);

                            foreach($responsibles as $resp)
                            {
                                $db->insert('sdm2_responsibles', array(
                                    'id_sdm_story' => $sdm_story_id,
                                    'id_user' => $resp['id_user'],
                                    'date_assigned' => $resp['date_assigned'],
                                    'note' => $resp['note'],
                                    'index' => $resp['index'],
                                ));
                            }
                        }
                        else
                        {
                            $db->rollBack();
                            echo 'errors in adding VERIFIED story';exit;
                        }
                    }
                }


            }

            $db->commit();
        }
        catch(Exception $e)
        {
            $db->rollBack();

            echo 'errors: ' . $e->getMessage();
            exit;
        }

        echo 'done';exit;
    }

    public function amiAction()
    {
        /** @var $db Zend_Db_Adapter_Mysqli */
        $db = Zend_Registry::get('dbAdapter');
        $id_internals = array();


        $tos = $db->fetchCol('select distinct ' . $db->quoteIdentifier('to') . ' from messages');

        foreach($tos as $to)
        {
            $id_internal = $db->fetchOne('select id_internal from users_internals where id_user = ' . $to . ' order by id_internal asc limit 1');

            if(!$id_internal)
            {
                echo 'no internal for ' . $to . '<br/>';
                continue;
            }

            $db->update('messages', array(
                'id_internal' => $id_internal,
            ), $db->quoteIdentifier('to') . ' = ' . $to);
        }

        echo 'done';exit;

        $messages = $db->fetchAll('select message_id, ' . $db->quoteIdentifier('to') . ' from messages');



        foreach($messages as $message)
        {
            if(!isset($id_internals[$message['to']]))
            {
                $id_internals[$message['to']] = $db->fetchOne('select id_internal from users_internals where id_user = ' . $message['to'] . ' order by id_internal asc limit 1');
            }

            $id_internal = $id_internals[$message['to']];

            $db->update('messages', array(
                'id_internal' => $id_internal,
            ), 'message_id = ' . $message['message_id']);
        }

        echo 'done';
        exit;
    }

    public function aiAction()
    {
        $db = Zend_Registry::get('dbAdapter');
        $subservices = $db->fetchAll('select  * from subservices');
        foreach($subservices as $subservice)
        {
            $moment_defs = $db->fetchAll('select * from moment_defs where id_service = ' . $subservice['id_service'] . ' and id_subservice = ' . $subservice['subservice_id']);
            $inizio_exists = false;
            $fine_exists = false;
            foreach($moment_defs as $moment_def)
            {
                if(strtolower($moment_def['name']) == 'apertura lavori')
                {
                    $inizio_exists = true;
                }
                if(strtolower($moment_def['name']) == 'chiusura lavori')
                {
                    $fine_exists = true;
                }
            }

            if(!$inizio_exists)
            {
                $db->insert('moment_defs', array(
                    'id_service' => $subservice['id_service'],
                    'id_subservice' => $subservice['subservice_id'],
                    'name' => 'Apertura Lavori',
                    'description' => 'Apertura Lavori',
                ));
            }
            if(!$fine_exists)
            {
                $db->insert('moment_defs', array(
                    'id_service' => $subservice['id_service'],
                    'id_subservice' => $subservice['subservice_id'],
                    'name' => 'Chiusura Lavori',
                    'description' => 'Chiusura Lavori',
                ));
            }
        }
        echo 'done';
        exit;
    }

    public function otAction()
    {
        $db = Zend_Registry::get('dbAdapter');
        $offers = $db->fetchAll('select offer_id, promotore_percent from offers');
        foreach($offers as $offer)
        {
            $moments = $db->fetchAll('select importo from moments where id_offer = ' . $offer['offer_id']);

            $total = 0;
            foreach($moments as $moment)
            {
                $total += $moment['importo'];
            }

            $promotore = 0;
            if($offer['promotore_percent'] > 0)
            {
                $promotore = $total * $offer['promotore_percent'] / 100;
            }

            $db->update('offers', array(
                'offer_importo' => $total,
                'promotore_value' => $promotore
            ), 'offer_id = ' . $offer['offer_id']);
        }
        echo 'done';
        exit;
    }

    public function ot2Action()
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('offers', array(
            'promotore_value_flag' => 'P'
        ), 'promotore_percent > 0');
        exit;
    }


    public function ssoAction()
    {
        $db = Zend_Registry::get('dbAdapter');

        $offers_data = $db->fetchAll('select offer_id, id_service, code_offer from offers where offer_id >= 7057');

        $services = array();

        foreach($offers_data as $offer)
        {
            if(!array_key_exists($offer['id_service'], $services))
            {
                $services[$offer['id_service']] = $db->fetchOne('select cod from services where service_id = ' . $offer['id_service']);
            }
            $old_code = explode('-', $offer['code_offer']);
            $old_code[3] = $services[$offer['id_service']];
            $new_code = implode('-', $old_code);
            $db->update('offers', array('code_offer' => $new_code), 'offer_id = ' . $offer['offer_id']);
        }
        echo 'done';exit;
    }

    public function importAction()
    {
        $subject = $this->_request->getParam('s', false);
        $part = $this->_request->getParam('p', false);
        if(!$subject)
        {
            throw new Exception('nothing to import');
        }

        try
        {
            $importer = new Simplex_Importer_Manager();
            $importer->setSubject($subject, $part);

            $file_name = $subject;
            if($part)
            {
                $file_name .= '-' . $part;
            }

            $file = dirname(APPLICATION_PATH) . '/data/' . $file_name . '.xlsx';
            $importer->setFile($file);

            $importer->import();
        }
        catch (Exception $e)
        {
            -dd($e);
            $this->_helper->getHelper('FlashMessenger')->addMessage('impossibile importare il file!');
        }

        exit;
        $this->_redirect('/');
    }

    public function transformAction()
    {
        $repo = new Model_FilesMapper();

        $db = Zend_Registry::get('dbAdapter');
        $companies = $db->fetchPairs('select company_id, ragione_sociale from companies');

        $path = $repo->getRepoPath() . '/aziende/';
        $dirs = scandir($path);

        $go = $this->_request->getParam('go', false);

        $done = array();
        $not_done = array();
        foreach($dirs as $dir)
        {
            if($dir != '.' && $dir != '..' && $dir != '.svn')
            {
                if($id = array_search($dir, $companies))
                {
                    if($go)
                    {
                        rename($path . '/' . $dir, $path . '/' . $id);
                    }
                    $done[] = $dir;
                }
                else
                {
                    $not_done[] = $dir;
                }
            }
        }
        d($done);
        d($not_done);
        exit;
    }

    public function translateOLDAction()
    {
        $repo = new Model_FilesMapper();

        $path = $repo->getRepoPath() . '/Partner/';
        $dirs = scandir($path);

        $go = $this->_request->getParam('go', false);

        $done = array();
        $not_done = array();
        foreach($dirs as $dir)
        {
            if($dir != '.' && $dir != '..' && $dir != '.svn' && $dir != '0')
            {
                $new_dir = ((int)$dir) + 3743;
                if($go)
                {
                    rename($path . '/' . $dir, $path . '/' . $new_dir);
                }
                $done[] = $dir . ' - ' . $new_dir;
            }
        }
        d($done);
        exit;
    }

    public function translateAction()
    {
        $repo = new Model_FilesMapper();

        $path = $repo->getRepoPath() . '/aziende/';
        $dirs = scandir($path);

        $go = $this->_request->getParam('go', false);

        $done = array();
        $not_done = array();
        foreach($dirs as $dir)
        {
            if($dir != '.' && $dir != '..' && $dir != '.svn' && $dir != '0')
            {
                $substr = substr($dir, 0, 3);
                if($substr == 'EX_' || $substr == 'AT_'){
                    continue;
                }

                $new_dir = 'EX_' . $dir;
                $newest_dir = 'AT_' . $dir;
                if($go)
                {
                    $source = $path . $dir;
                    $dest = $path . $new_dir;

                    if(!file_exists($dest)){
                        mkdir($dest);
                    }

                    foreach (
                        $iterator = new RecursiveIteratorIterator(
                            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                            RecursiveIteratorIterator::SELF_FIRST) as $item
                    ) {
                        if ($item->isDir()) {
                            mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
                        } else {
                            copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
                        }
                    }

                    rename($path . '/' . $dir, $path . '/' . $newest_dir);


                }
                $done[] = $dir . ' - ' . $new_dir;
            }
        }
        d($done);
        exit;
    }

    public function pcheckAction()
    {
        $repo = new Model_FilesMapper();

        $path_p = $repo->getRepoPath() . '/Partner/';
        $path_a = $repo->getRepoPath() . '/aziende/';
        $dirs_p = scandir($path_p);
        $dirs_a = scandir($path_a);

        $duplicate = array();

        foreach($dirs_p as $p)
        {
            if(in_array($p, $dirs_a))
            {
                $duplicate[] = $p;
            }
        }
        -dd($duplicate);
    }

    public function usersList($deleted)
    {
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

        $search = $_GET;

        $users = $repo->getUsers($sort, $dir, $search, $deleted);

        $this->view->deleted = $deleted;

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
                    'base' => '/admin/users/task/detail'
                )
            ),
            array(
                'field' => 'nome'
            ),
            array(
                'field' => 'cognome'
            ),
            array(
                'label' => 'Telefono',
                'field' => 'numbers',
            ),
            array(
                'label' => 'Email',
                'field' => 'mails',
            ),
            array(
                'label' => 'Ruolo',
                'field' => 'role_name',
            ),
            array(
                'field' => 'internals',
                'renderer' => 'array',
                'sortable' => false
            ),
            array(
                'label' => 'Attivo',
                'field' => 'active',
                'renderer' => 'bool',
                'search' => 'bool'
            ),
        ));

        $r = new Maco_DaGrid_Render_Html();
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        //        $r->setId('users');
        $r->setTrIdPrefix('user_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($users);
        $g->setRenderer($r);
        $g->setSource($s);
        //$g->setPaginator(true);
        $g->setRowsPerPage($this->_request->getParam('perpage', 10));
        $g->setId('users');

        $this->view->dag = $g;

        if ($this->_request->isXmlHttpRequest()) {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy();
        }
        else
        {
            $this->render('users/dalist');
        }
    }

    public function userEdit()
    {
        // CArico i ruoli
        $aclModel = new Model_Acl();
        $roles = $aclModel->getRolesWithParents();
        unset($aclModel);
        $this->view->roles = $roles;

        $commonModel = new Model_Common();

        $vals = array('' => '') + $commonModel->getArrayForSelectElementSimple('contact_titles', 'contact_title_id', 'name');
        $this->view->contact_titles = $vals;

        // carico le province
        //$province = $commonModel->getArrayForSelectElementSimple('province', 'id', 'nomeprovincia');
        //$this->view->province = array(0 => '') + $province;

        /*
        // carico le aziende interne
        $internals = $commonModel->fetchAllSingleTableDefault('internals');        
        $this->view->internals = $internals;
        */
        unset($commonModel);

        $id = $this->_request->getParam('id', false);

        $repo = Maco_Model_Repository_Factory::getRepository('user');

        if ($id) {
            $this->view->user = $repo->find($id);
            $this->view->contentTitle = "Modifica Utente";
        }
        else
        {
            // TODO potremmo recuperare i dati validi inseriti
            $this->view->user = $repo->getNewUser();
            $this->view->contentTitle = "Nuovo Utente";
        }

        $this->render('users/edit');
    }

    public function userLinks()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('user');

        if($this->_request->isPost())
        {
            // salviamo le informazioni
            $result = $repo->saveLinks($_POST);

            if(is_array($result))
            {
                $layout = Zend_Layout::getMvcInstance();
                $layout->flashMessages = $result;
            }
            else
            {
                $this->_helper->getHelper('FlashMessenger')->addMessage('Collegamenti utente salvati!');
                $this->_redirect('admin/users/task/detail/id/' . $result);
            }
        }

        $id = $this->_request->getParam('id', $this->_request->getParam('user_id', false));

        if(!$id)
        {
            throw new Exception('user_id not found');
        }

        $this->view->user = $repo->find($id);

        $adminModel = new Model_Admin();

        // carico le aziende interne
        $this->view->internals = $adminModel->getInternalsWithOffices();

        $internals_ids = array();
        foreach($this->view->user->internals as $i)
        {
            $internals_ids[] = $i['internal_id'];
        }
        $this->view->internals_ids = $internals_ids;

        $this->render('users/links');
    }

    public function userPermissions()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('user');

        $permissionsModel = new Model_Permissions();
        $permissions = $permissionsModel->getPermissions();
        $this->view->permissions = $permissions;

        if($this->_request->isPost())
        {
            $result = $repo->savePermissions($_POST, $permissions);

            if(is_array($result))
            {
                $layout = Zend_Layout::getMvcInstance();
                $layout->flashMessages = $result;
            }
            else
            {
                $this->_helper->getHelper('FlashMessenger')->addMessage('Permessi utente salvati!');
                $this->_redirect('admin/users/task/detail/id/' . $result);
            }
        }

        $id = $this->_request->getParam('id', $this->_request->getParam('user_id', false));

        if(!$id)
        {
            throw new Exception('user_id not found');
        }

        $this->view->user = $repo->find($id);

        $this->render('users/permissions');
    }

    public function userActive()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('user');

        $user_id = $this->_request->getParam('id', false);

        if(!$user_id)
        {
            throw new Exception('user_id not found');
        }

        $user = $repo->find($user_id);
        $user->setValidatorAndFilter(new Model_User_Validator());
        $active = $this->_request->getParam('active', 1);
        $user->active = $active;
        if($user->isValid())
        {
            $repo->save($user);
            $this->_helper->getHelper('FlashMessenger')->addMessage('Stato cambiato!');
            $this->_redirect('admin/users/task/detail/id/' . $user_id);
        }
        else
        {
            $this->_helper->getHelper('FlashMessenger')->addMessage('Impossibile effettuare l\'operazione');
            $this->_redirect('admin/users/task/detail/id/' . $user_id);
        }
    }

    public function userSave()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('user');

        $uid = (int)$this->_request->getParam('user_id');
        $new_user = $uid == 0;

        $result = $repo->saveFromData($_POST, $uid);

        if (is_array($result))
        {
            foreach ($result as $msg)
            {
                $this->_helper->getHelper('FlashMessenger')->addMessage($msg);
            }
            //$this->_helper->getHelper('FlashMessenger')->addMessage(array('data' => $data));
            if($uid != 0)
            {
                $this->_redirect('admin/users/task/edit/id/' . $uid);
            }
            else
            {
                $this->_redirect('admin/users/task/edit');
            }
        }
        else
        {
            if($new_user)
            {
                $this->_redirect('admin/users/task/links/id/' . $result);
            }
            else
            {
                $this->_redirect('admin/users/task/detail/id/' . $result);
            }
        }
    }

    public function userDetail() {
        $id = $this->_request->getParam('id', false);

        if (!$id) {
            throw new Zend_Controller_Action_Exception('Necessario id per il dettaglio utente', 404);
        }

        $repo = Maco_Model_Repository_Factory::getRepository('user');

        $this->view->user = $repo->find($id);

        $this->view->tasksList = $this->_getTasksForUser($id);

        $this->render('users/detail');
        return;
    }

    protected function _getTasksForUser($id)
    {
        $mapper = new Model_TasksMapper();

        $options = array();

        $options['where']['done'] = $this->_request->getParam('done', 0);
        $options['where']['tasks.id_who'] = $id;
        // $options['where']['done'] = 1;

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
                'field' => 'receiver',
                'label' => 'Contatto'
            ),
            array(
                'field' => 'company',
                'label' => 'azienda'
            ),
            array(
                'field' => 'when',
                'label' => 'quando',
                'renderer' => 'datetime'
            ),
            array(
                'field' => 'done',
                'label' => 'Eseguito',
                'renderer' => 'bool',
                'search' => 'bool'
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

        return $g;
    }

    public function userDelete() {
        $ids = $this->_request->getParam('id', false);

        if (!$ids) {
            throw new Zend_Controller_Action_Exception('Necessario id per l\'eliminazione utente', 404);
        }

        $repo = Maco_Model_Repository_Factory::getRepository('user');

        if (is_array($ids))
        {
            foreach ($ids as $id)
            {
                $repo->delete($id);
            }
        }
        else
        {
            $result = $repo->delete($ids);
            if(is_array($result))
            {
                $this->_helper->getHelper('FlashMessenger')->addMessage($result);
            }
        }

        $this->_redirect('admin/users/task/list');
    }

    public function userChangePassword() {
        $id = $this->_request->getParam('id', false);

        if (!$id) {
            throw new Exception('Id missing', 500);
        }
        $logged_user_id = Zend_Auth::getInstance()->getIdentity()->user_id;

        $user_repo = Maco_Model_Repository_Factory::getRepository('user');
        $this->view->logged_user = $user_repo->find($logged_user_id);

        $this->view->id = $id;
        $this->render('users/changePassword');
    }

    public function userSavePassword() {
        $model = new Model_UsersMapper();

        $result = $model->saveNewPassword($_POST);

        $this->view->result = $result[0];
        $this->view->message = $result[1];
    }

    public function indexAction()
    {
    }

    public function commonAction() {
        $item = $this->_request->getParam('item', false);

        if (!$item) {
            $this->_redirect('/admin');
        }

        $task = $this->_request->getParam('task', 'list');

        $method = 'common' . ucfirst(strtolower($task));

        if (!method_exists($this, $method)) {
            throw new Zend_Controller_Action_Exception(
                'Method not found: ' . __CLASS__ . ' &rarr; ' . $method, 404
            );
        }

        $this->$method(strtolower($item));
    }

    public function tblAction() {
        $s = $this->_request->getParam('search');
        $search = array('nome' => $s, 'cognome' => $s, 'username' => $s);

        $modelName = $this->_request->getParam('model');
        $model = null;
        switch ($modelName)
        {
            case 'users':
                $model = new Model_UsersMapper();
                break;
            default:
                echo json_encode(array());
                exit;
                break;
        }

        //$values = $model->getUsersList('cognome', 'ASC', $search, 'OR');
        $repo = Maco_Model_Repository_Factory::getRepository('user');

        $search['active'] = 1;

        $values = $repo->getUsers('username', 'asc', $search, 0);

        $result = array();

        foreach ($values as $v)
        {
            $result[] = array($v['user_id'], '<div><b>' . $v['username'] . '</b> <i>(' . $v['role_name'] . ')</i><br />' . $v['nome'] . ' ' . $v['cognome'] . '</div>', '<b>' . $v['username'] . '</b> - ' . $v['nome'] . ' ' . $v['cognome']);
        }

        echo json_encode($result);
        exit;
    }

    public function commonList($table) {
        $g = new Maco_DaGrid_Grid();

        $model = new Model_Generics();

        $perpage = $this->_request->getParam('perpage', Zend_Registry::getInstance()->get('config')->entries_per_page);

        $user_internal = false;
        if($table == 'subservices' || $table == 'services' || $table == 'moment_defs')
        {
            $user = Zend_Auth::getInstance()->getIdentity();
            $user_internal = $user->internal_id;
        }

        $data = $model->getList($table, $_GET, $perpage, $user_internal);

        $fields = $model->getFieldsForTable($table);
        $pk = $model->getPrimaryKeyForTable($table);

        $firstLink = true;
        foreach ($fields as $field => $options)
        {
            $dafield = (isset($options['depends'])) ? key($options['depends']['field']) : $field;
            if ($firstLink) {
                $g->addColumns(array(
                    array(
                        'label' => $options['label'],
                        'field' => $dafield,
                        'class' => 'link', // class -> link set renderer -> link
                        'options' => array(
                            'linksData' => array(
                                'id' => $pk
                            ),
                            'base' => '/admin/common/item/' . $table . '/task/detail'
                        )
                    )
                ));
                $firstLink = false;
            } else
            {
                $g->addColumns(array(
                    array(
                        'field' => $dafield,
                        'label' => $options['label']
                    )
                ));
            }
        }

        $g->addColumns(array(
            array(
                'label' => '',
                'field' => '',
                'class' => 'links',
                'search' => false,
                'sortable' => false,
                'options' => array(
                    'links' => array(
                        array(
                            'linkData' => array(
                                'id' => $pk,
                            ),
                            'base' => '/admin/common/item/' . $table . '/task/edit',
                            'img' => '/img/edit.png',
                            'title' => 'Modifica',
                        ),
                        array(
                            'linkData' => array(
                                'id' => $pk,
                            ),
                            'base' => '/admin/common/item/' . $table . '/task/delete',
                            'img' => '/img/delete.png',
                            'title' => 'Elimina',
                        ),
                    )
                )
            )
        ));

        $r = new Maco_DaGrid_Render_Html();
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        $r->setTrIdPrefix('tr_');

        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($data);

        $g->setRenderer($r);
        $g->setSource($s);
        //$g->setPaginator(true);
        $g->setRowsPerPage($perpage);

        $g->setId($table);

        $this->view->dag = $g;

        if ($this->_request->isXmlHttpRequest()) {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy();
        }
        else
        {
            $this->view->item = $table;
            $this->view->labels = $model->getLabelsForTable($table);
            $this->view->fields = $model->getTableInfo($table);
            $this->render('common/list');
        }

    }

    public function commonListOld($table) {
        $g = new Maco_DaGrid_Grid();

        $model = new Model_Generics();

        $fields = $model->getFieldsForTable($table);

        $firstLink = true;
        foreach ($fields as $field => $options)
        {
            if ($firstLink) {
                $g->addColumns(array(
                    array(
                        'label' => $options['label'],
                        'field' => $field,
                        'class' => 'link', // class -> link set renderer -> link
                        'options' => array(
                            'linksData' => array(
                                'id' => 'id'
                            ),
                            'base' => '/admin/common/item/' . $table . '/task/detail'
                        )
                    )));
                $firstLink = false;
            } else
            {
                $g->addColumns(array(
                    array(
                        'field' => $field,
                        'label' => $options['label']
                    )
                ));
            }
        }

        $g->addColumns(array(
            array(
                'label' => '',
                'field' => '',
                'class' => 'links',
                'search' => false,
                'sortable' => false,
                'options' => array(
                    'links' => array(
                        array(
                            'linkData' => array(
                                'id' => 'id',
                            ),
                            'base' => '/admin/common/item/' . $table . '/task/edit',
                            'img' => '/img/edit.png',
                            'title' => 'Modifica',
                        ),
                        array(
                            'linkData' => array(
                                'id' => 'id',
                            ),
                            'base' => '/admin/common/item/' . $table . '/task/delete',
                            'img' => '/img/delete.png',
                            'title' => 'Elimina',
                        ),
                    )
                )
            )
        ));

        $r = new Maco_DaGrid_Render_Html();
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        $r->setTrIdPrefix('tr_');

        $s = new Maco_DaGrid_Source_DbTable($table);

        $g->setRenderer($r);
        $g->setSource($s);
        //$g->setPaginator(true);
        $g->setRowsPerPage($this->_request->getParam('perpage', 20));

        $g->setId($table);

        $this->view->dag = $g;

        if ($this->_request->isXmlHttpRequest()) {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy();
        }
        else
        {
            $this->view->item = $table;
            $this->view->labels = $model->getLabelsForTable($table);
            $this->render('common/list');
        }
    }

    public function subservicesbyserviceforselectAction() {
        $model = new Model_Common();

        $id = (int) $this->_request->getParam('id', 0);

        $this->view->subservices = $model->getArrayForSelectElementSimple('subservices', 'subservice_id', 'name', 'id_service = ' . $id);
    }

    public function commonEdit($table)
    {
        $model = new Model_Generics();

        $fields = $model->getTableInfo($table);
        $pk = $model->getPrimaryKeyForTable($table);

        $commonModel = new Model_Common();

        foreach ($fields['fields'] as $fieldName => $options)
        {
            if (isset($options['depends'])) {
                $dtable = $options['depends']['table'];
                $fname = reset($options['depends']['field']);

                $where = null;

                if (isset($options['depends']['with-parent'])) {
                    $where = $options['depends']['with-parent']['self_field'] . ' = ' . key($fields['fields'][$options['depends']['with-parent']['self_parent_field']]['select']);
                }

                $subpk = $model->getPrimaryKeyForTable($dtable);
                $fields['fields'][$fieldName]['select'] = $commonModel->getArrayForSelectElementSimple($dtable, $subpk, $fname, $where);
            }
        }

        $this->view->fields = $fields;

        $id = $this->_request->getParam('id', false);

        if ($id) {
            if($table == 'subservices' || $table == 'services')
            {
                $user = Zend_Auth::getInstance()->getIdentity();
                $detail = $model->getDetail($table, $id, $user->internal_id);
                if(!$detail)
                {
                    throw new Zend_Acl_Exception('No access to this resource', 401);
                }
                $this->view->data = $detail;
            }
            else
            {
                $this->view->data = $model->getDetail($table, $id);
            }

            $this->view->contentTitle = "Modifica";
        }
        else
        {
            // TODO potremmo recuperare i dati validi inseriti
            $this->view->data = $model->getEmptyDetail($table);
            $this->view->contentTitle = "Nuova";
        }

        $this->view->item = $table;
        $this->view->labels = $model->getLabelsForTable($table);
        $this->view->pk = $model->getPrimaryKeyForTable($table);

        $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();

        $this->render('common/edit');

    }

    public function subservicesAction()
    {
        $task = $this->_request->getParam('task', 'list');

        $method = 'subservices' . ucfirst(strtolower($task));

        if (!method_exists($this, $method)) {
            throw new Zend_Controller_Action_Exception(
                'Method not found: ' . __CLASS__ . ' &rarr; ' . $method, 404
            );
        }

        $this->$method();
    }

    public function subservicesList()
    {
        $g = new Maco_DaGrid_Grid();

        $model = new Model_Generics();


        $user = Zend_Auth::getInstance()->getIdentity();

        $data = $model->getSubservices($_GET, $user->internal_id);

        $g->addColumns(array(
            array(
                'label' => 'nome',
                'field' => 'name',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'subservice_id'
                    ),
                    'base' => '/admin/subservices/task/detail'
                )
            ),
            array(
                'field' => 'service',
                'label' => 'servizio'
            ),
            array(
                'field' => 'cod',
                'label' => 'codice'
            ),
            array(
                'field' => 'description',
                'label' => 'descrizione'
            ),
            array(
                'label' => 'template',
                'field' => 'template',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'p' => 'path'
                    ),
                    'base' => '/files/get'
                )
            ),
        ));

        $g->addColumns(array(
            array(
                'label' => '',
                'field' => '',
                'class' => 'links',
                'search' => false,
                'sortable' => false,
                'options' => array(
                    'links' => array(
                        array(
                            'linkData' => array(
                                'id' => 'subservice_id',
                            ),
                            'base' => '/admin/subservices/task/edit',
                            'img' => '/img/edit.png',
                            'title' => 'Modifica',
                        ),
                        array(
                            'linkData' => array(
                                'id' => 'subservice_id',
                            ),
                            'base' => '/admin/subservices/task/delete',
                            'img' => '/img/delete.png',
                            'title' => 'Elimina',
                        ),
                    )
                )
            )
        ));

        $r = new Maco_DaGrid_Render_Html();
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        $r->setTrIdPrefix('tr_');

        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($data);

        $g->setRenderer($r);
        $g->setSource($s);
        //$g->setPaginator(true);
        $g->setRowsPerPage($this->_request->getParam('perpage', Zend_Registry::get('config')->entries_per_page));

        $g->setId('subservices');

        $this->view->dag = $g;

        if ($this->_request->isXmlHttpRequest()) {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy();
        }
        else
        {
            $this->view->item = 'subservices';
            $this->render('common/list');
        }

    }

    public function subservicesDetail()
    {
        $id = $this->_request->getParam('id', false);
        if (!$id) {
            throw new Zend_Controller_Action_Exception('Necessario id per il dettaglio categoria', 404);
        }

        $model = new Model_Generics();

        $user = Zend_Auth::getInstance()->getIdentity();
        $detail = $model->getSubservice($id, $user->internal_id);
        if(count($detail) == 1)
        {
            throw new Zend_Acl_Exception('No access to this resource', 401);
        }

        $this->view->data = $detail;

        $files = new Model_FilesMapper();

        $this->view->template_path = base64_encode($files->getTemplatePath(false));
        $this->view->template_name = 'template_' . $this->view->data['service_id'] . '_' . $this->view->data['subservice_id'] . '.docx';

        $this->render('subservices/detail');
    }

    public function subservicesEdit()
    {
        $table = 'subservices';

        $model = new Model_Generics();

        $fields = $model->getTableInfo($table);
        $pk = $model->getPrimaryKeyForTable($table);

        $commonModel = new Model_Common();

        foreach ($fields['fields'] as $fieldName => $options)
        {
            if (isset($options['depends']))
            {
                $dtable = $options['depends']['table'];
                $fname = reset($options['depends']['field']);

                $where = null;

                if (isset($options['depends']['with-parent'])) {
                    $where = $options['depends']['with-parent']['self_field'] . ' = ' . key($fields['fields'][$options['depends']['with-parent']['self_parent_field']]['select']);
                }

                $subpk = $model->getPrimaryKeyForTable($dtable);
                $fields['fields'][$fieldName]['select'] = $commonModel->getArrayForSelectElementSimple($dtable, $subpk, $fname, $where);
            }
        }

        $this->view->fields = $fields;

        $id = $this->_request->getParam('id', false);

        if ($id)
        {
            $user = Zend_Auth::getInstance()->getIdentity();
            $detail = $model->getDetail($table, $id, $user->internal_id);
            if(!$detail)
            {
                throw new Zend_Acl_Exception('No access to this resource', 401);
            }
            $this->view->data = $detail;
            $this->view->contentTitle = "Modifica";
        }
        else
        {
            // TODO potremmo recuperare i dati validi inseriti
            $this->view->data = $model->getEmptyDetail($table);
            $this->view->contentTitle = "Nuova";
        }

        $this->view->item = $table;
        $this->view->labels = $model->getLabelsForTable($table);
        $this->view->pk = $model->getPrimaryKeyForTable($table);

        $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();

        $this->render('common/edit');
    }

    public function commonDetail($table)
    {
        $id = $this->_request->getParam('id', false);
        if (!$id) {
            throw new Zend_Controller_Action_Exception('Necessario id per il dettaglio categoria', 404);
        }

        $model = new Model_Generics();

        $fields = $model->getTableInfo($table);

        $this->view->fields = $fields;

        $user_internal = false;
        if($table == 'subservices' || $table == 'services')
        {
            $user = Zend_Auth::getInstance()->getIdentity();
            $user_internal = $user->internal_id;
        }

        $detail = $model->getDetail($table, $id, $user_internal);

        if(!$detail)
        {
            throw new Zend_Acl_Exception('No access to this resource', 401);
        }

        $this->view->data = $detail;

        $this->view->item = $table;

        $this->view->pk = $model->getPrimaryKeyForTable($table);

        $this->render('common/detail');
    }

    public function commonSave($table) {
        $model = new Model_Generics();

        $data = $_POST;

        $user_internal = false;
        if($table == 'subservices' || $table == 'services')
        {
            $user = Zend_Auth::getInstance()->getIdentity();
            $user_internal = $user->internal_id;
        }

        $result = $model->save($table, $data, $user_internal);

        if (is_array($result)) {
            foreach ($result as $msg)
                $this->_helper->getHelper('FlashMessenger')->addMessage($msg);
            $this->_helper->getHelper('FlashMessenger')->addMessage(array('data' => $data));
            $this->_redirect('admin/common/item/' . $table . '/task/edit');
        }
        else
        {
            $this->_redirect('admin/common/item/' . $table . '/task/detail/id/' . $result);
        }
    }

    public function commonDelete($table) {
        $ids = $this->_request->getParam('id', false);

        if (!$ids) {
            throw new Zend_Controller_Action_Exception('Necessario id per l\'eliminazione', 404);
        }

        $model = new Model_Generics();

        if (is_array($ids))
        {
            foreach ($ids as $id)
            {
                $model->delete($table, $id);
            }
        }
        else
        {
            $result = $model->delete($table, $ids);
        }

        $this->_redirect('admin/common/item/' . $table . '/task/list');
    }

    public function templatesAction()
    {
        $model = new Model_Templates();

        $this->view->templates = $model->getTemplates();
    }

    public function templatedAction()
    {
        $model = new Model_Templates();

        $success = $model->downloadTemplate($this->_request->getParam('id'));

        if($success)
        {
            exit;
        }
    }

    public function templateuAction()
    {
        $model = new Model_Templates();

        $success = $model->uploadTemplate($this->_request->getParam('id'));

        if($success)
        {
            $this->_helper->getHelper('FlashMessenger')->addMessage('File di template aggiornato.');
            $this->_redirect('admin/templates');
        }
    }

    public function templaterAction()
    {
        $model = new Model_Templates();

        $success = $model->restoreTemplate($this->_request->getParam('id'));

        if($success)
        {
            $this->_helper->getHelper('FlashMessenger')->addMessage('File di template ripristinato.');
        }
        else
        {
            $this->_helper->getHelper('FlashMessenger')->addMessage('Impossibile ripristinare il file di template.');
        }
        $this->_redirect('admin/templates');
    }

}
