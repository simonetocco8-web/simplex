<?php


class SdmController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('list', 'html')
            ->addActionContext('mt', 'html')
            ->addActionContext('mt', 'json')
            ->addActionContext('mpa', 'html')
            ->addActionContext('mpa', 'json')
            ->addActionContext('mpv', 'html')
            ->addActionContext('mpv', 'json')
            ->addActionContext('mpnv', 'html')
            ->addActionContext('mpnv', 'json')
            ->addActionContext('mr', 'json')
            ->addActionContext('mr', 'html')
            ->addActionContext('msr', 'json')
            ->addActionContext('msr', 'html')
            ->addActionContext('mssb', 'html')
            ->addActionContext('mssb', 'json')
            ->addActionContext('mrv', 'html')
            ->addActionContext('mrv', 'json')
            ->addActionContext('ms', 'json')
            ->addActionContext('ms', 'html')
            ->addActionContext('mnv', 'json')
            ->addActionContext('mnv', 'html')
            ->addActionContext('mv', 'json')
            ->addActionContext('mv', 'html')
            ->addActionContext('detail', 'html')
            ->initContext();
    }

    public function indexAction()
    {
        $this->_forward('list');
    }

    public function listAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('sdm2');

        if(isset($_GET['_export']) && $_GET['_export'] == 1)
        {
            $repo->exportSdms($_GET);
            exit;
        }

        if($this->_request->isXmlHttpRequest())
        {
            $segnalazioni = $repo->fetch($_GET);
        }
        else
        {
            $segnalazioni = array();
        }

        $g = new Maco_DaGrid_Grid();
        $g->addColumns(array(
            array(
                'label' => '#',
                'field' => 'sdm_id',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'sdm_id'
                    ),
                    'base' => '/sdm/detail'
                )
            ),
            array(
                'label' => 'Progressivo',
                'field' => 'code',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'sdm_id'
                    ),
                    'base' => '/sdm/detail'
                )
            ),
            array(
                'label' => 'Descrizione',
                'field' => 'problem',
            ),
            array(
                'field' => 'date_problem',
                'label' => 'Data Emissione',
                'renderer' => 'datetime'
            ),
            array(
                'field' => 'creator',
                'label' => 'Emittente'
            ),
            array(
                'field' => 'responsible',
                'label' => 'Responsabile'
            ),
            array(
                'field' => 'solver',
                'label' => 'Risolutore'
            ),
            array(
                'field' => 'id_status',
                'label' => 'stato',
                'path_to_template' => APPLICATION_PATH . '/Simplex/DaGrid/scripts/',
                'renderer' => 'stato_sdm',
                'search' => 'stato_sdm'
            ),
            array(
                'field' => 'with_prevention',
                'label' => 'Azione Preventiva',
                'path_to_template' => APPLICATION_PATH . '/Simplex/DaGrid/scripts/',
                'renderer' => 'stato_sdm',
                'search' => 'stato_sdm'
            ),
        ));

        // search elements
        $searchRepo = new Model_Sdm2_Search();

        $r = new Maco_DaGrid_Render_Html();
        $r->with_export = true;
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        $r->withFastSearch = false;
        //        $r->setId('users');
        $r->setTrIdPrefix('sdm_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($segnalazioni);
        $g->setRenderer($r);
        $g->setSource($s);
        $g->setAdvancedSearch($searchRepo);
        //$g->setPaginator(true);
        $g->setRowsPerPage($this->_request->getParam('perpage', 20));
        $g->setId('sdms');

        $this->view->dag = $g;

        if($this->_request->isXmlHttpRequest())
        {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy();
        }
    }


    public function editAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('sdm2');

        $id = (int) $this->_request->getParam('id', false);

        if($id)
        {
            $sdm = $repo->find($id);

            if($sdm->sdm_id === NULL)
            {
                // segnalazione non trovata
                throw new Zend_Controller_Action_Exception('Segnalazione non trovata!', 404);
            }

            $aut = Zend_Auth::getInstance()->getIdentity();
            if($sdm->id_status > 1 || $sdm->created_by != $aut->user_id)
            {
                // se la segnalazione non è bozza non è possibile modificarla
                // oppure non sei l'utente che la ha creata
                throw new Exception('Impossibile modificare questa segnalazione!');
            }

            $this->view->contentTitle = "Modifica Segnalazione";
        }
        else
        {
            // TODO potremmo recuperare i dati validi inseriti
            $sdm = $repo->getNewSdm();
            //    $this->view->data = $model->getEmptyDetail();
            $this->view->contentTitle = "Nuova Segnalazione";
        }

        $this->view->data = $sdm;

        $util = new Maco_Html_Utils();

        $user_repo = Maco_Model_Repository_Factory::getRepository('user');
        $users = $user_repo->getUsersWithPermissions(array(
            array('sdm', 'responsabile')
        ));
        $users = $util->parseDbRowsForSelectElement($users, 'user_id', 'username', array('nome', 'cognome'));
        $this->view->users = array('' => '') + $users;
    }

    public function saveAction()
    {
        // id_status = 2  <- NEW
        $id_status = 2;

        $repo = Maco_Model_Repository_Factory::getRepository('sdm2');
        if(isset($_POST['sdm_id']) && $_POST['sdm_id'] != '')
        {
            $sdm = $repo->findWithDependencies($_POST['sdm_id']);
        }
        else
        {
            $sdm = new Model_Sdm2();
        }
        $sdm->setValidatorAndFilter(new Model_Sdm2_Validator());

        $aut = Zend_Auth::getInstance()->getIdentity();
        $internal_code = strtolower($aut->internal_abbr);

        //code generation
        $progr_year = $repo->getNextYearAndProgr($internal_code);

        $data = array(
            'year' => $progr_year[0],
            'code' => $internal_code . '-' . $progr_year[0] . '-' . $progr_year[1],
            'id_status' => $id_status,
            'internal_code' => $internal_code
        );

        $sdm->setData($data);

        /** @var $db Zend_Db_Adapter_Mysqli */
        $db = Zend_Registry::get('dbAdapter');

        $db->beginTransaction();

        try
        {

            if($sdm->isValid())
            {
                $sdm_id = $repo->save($sdm);

                // TODO: add story 2

                $db->update('sdm_story', array(
                    'active' => 0
                ), 'id_sdm = ' . $db->quote($sdm_id) . ' and id_status = ' . $id_status);

                $sdm_story = new Model_Sdm2Story();
                $sdm_story->setValidatorAndFilter(new Model_Sdm2Story_Validator());

                $data = array(
                    'id_sdm' => $sdm_id,
                    'active' => 1,
                    'id_status' => $id_status,
                    'text1' => $_POST['problem'],
                    'date1' => date('Y-m-d'),
                    'id_user' => $_POST['id_responsible']
                );

                $sdm_story->setData($data);

                if($sdm_story->isValid())
                {
                    $sdm_story_repo = Maco_Model_Repository_Factory::getRepository('sdm2Story');

                    $sdm_story_repo->save($sdm_story);
                }
                else
                {
                    $db->rollBack();

                    $this->_helper->getHelper('FlashMessenger')->addMessage('Errore nel salvataggio storia sul db');

                    $this->_redirect('sdm/edit');
                }

                // messaggio al responsabile
                $message_repo = Maco_Model_Repository_Factory::getRepository('message');
                $message_title = 'SDM da trattare: ' . $sdm->code;

                $cf = Zend_Controller_Front::getInstance();
                $base_url = /* $cf->getRequest()->getScheme()
                    . '://'
                    . $cf->getRequest()->getHttpHost()
                    . */ $cf->getBaseUrl();
                $message_body = 'Sei stato assegnato come responsabile per la SDM  <a href="' . $base_url . '/sdm/detail/id/' . $sdm_id .'"><b>' . $sdm->code . '</b></a>.<br />';
                $message_repo->send($sdm_story->id_user, $message_title, $message_body, Model_Message_Types::TODO, null, $sdm_id . '-RSQ-MANAGE', true);

                $db->commit();

                $this->_redirect('sdm/detail/id/' . $sdm_id);
            }
            else
            {
                foreach($sdm->getInvalidMessages() as $cont)
                {
                    foreach($cont as $msg)
                    {
                        $this->_helper->getHelper('FlashMessenger')->addMessage($msg);
                    }
                }

                $db->rollBack();

                $this->_redirect('sdm/edit');
            }
        }
        catch(Exception $e)
        {
            var_dump($e->getMessage());exit;
            $db->rollBack();

            $this->_helper->getHelper('FlashMessenger')->addMessage('Errore nel salvataggio dati sul db');

            $this->_redirect('sdm/edit');
        }
    }

    /**
     * Valida una SDM (da bozza a nuova)
     * @return void
     */
    public function vAction()
    {
        $id = $this->_request->getParam('id', false);

        if(!$id)
        {
            throw new Exception('Id missing', 500);
        }

        $repo = Maco_Model_Repository_Factory::getRepository('sdm');
        $sdm = $repo->find($id);

        if($sdm->id_status > 1)
        {
            throw new Exception('Impossibile validare una segnalazione gi&agrave; attiva!', 500);
        }

        $sdm->setValidatorAndFilter(new Model_Sdm_Validator());

        $sdm->id_status = 2; // nuova
        $sdm->date_problem = date('Y-m-d');
        //code generation
        $progr_year = $repo->getNextYearAndProgr();
        $sdm->year = $progr_year[0];
        $sdm->code = $progr_year[0] . '-' . $progr_year[1];

        if($sdm->isValid())
        {
            $repo->save($sdm);

            // messaggio al RSQ
            $message_repo = Maco_Model_Repository_Factory::getRepository('message');
            $message_title = 'Nuova SDM';
            $cf = Zend_Controller_Front::getInstance();
            $base_url = /* $cf->getRequest()->getScheme()
                    . '://'
                    . $cf->getRequest()->getHttpHost()
                    . */ $cf->getBaseUrl();
            $message_body = 'Una nuova SDM <a href=" ' . $base_url . '/sdm/detail/id/' . $sdm->sdm_id .'"><b>' . $sdm->code . '</b></a> &egrave; stata creata.<br />';
            // TODO: utente che puo associare responsabile
            // 1. repository user
            $users_repo = Maco_Model_Repository_Factory::getRepository('user');
            // SDM that can SET RESPONSIBLE
            $usrs = $users_repo->getUsersOfType('SDMSR');
            $receivers = array();
            foreach($usrs as $user_id => $usr)
            {
                $receivers[] = $user_id;
            }
            $message_repo->send($receivers, $message_title, $message_body, Model_Message_Types::INFO);
        }
        else
        {
            foreach($sdm->getInvalidMessages() as $cont)
            {
                foreach($cont as $msg)
                {
                    $this->_helper->getHelper('FlashMessenger')->addMessage($msg);
                }
            }
        }
        $this->_redirect('sdm/detail/id/' . $id);
    }

    public function detailAction()
    {
        $id = $this->_request->getParam('id', false);

        if(!$id)
        {
            throw new Exception('Id missing', 500);
        }

        $repo = Maco_Model_Repository_Factory::getRepository('sdm2');

        $sdm = $repo->findWithDependencies($id);

        $this->view->sdm = $sdm;
    }

    public function exportAction()
    {
        $id = $this->_request->getParam('id', false);

        if(!$id)
        {
            throw new Exception('Id missing', 500);
        }

        $repo = Maco_Model_Repository_Factory::getRepository('sdm2');

        $sdm = $repo->findWithDependencies($id);

        $this->view->sdm = $sdm;

        $html = $this->view->render('sdm/detail.pdf.phtml');
        $pdfFile = dirname(APPLICATION_PATH). '/data/sdm' . uniqid() . '.pdf';

        include(LIBRARY_PATH . '/TCPDF/tcpdf.php');
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, 0, true, 0);
        $pdf->lastPage();
        $pdf->Output('sdm-' . $sdm['code'] . '.pdf', 'D');
        exit;
    }

    public function mtOLDAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('sdm');

        $id = $this->_request->getParam('id', false);

        if($this->_request->getParam('save') == 1)
        {
            $sdm = $repo->find($id);
            $sdm->setValidatorAndFilter(new Model_Sdm_Validator());
            $data = $_POST;

            //$data['date_resolution'] = date('Y-m-d');
            $data['id_status'] = 4;

            $sdm->setData($data);

            if($sdm->isValid())
            {
                $this->view->result = $repo->save($sdm);

                $this->view->message = 'risoluzione aggiornata';

                // messaggio al responsabile
                $message_repo = Maco_Model_Repository_Factory::getRepository('message');
                $message_title = 'SDM risolta';
                $cf = Zend_Controller_Front::getInstance();
                $base_url = /* $cf->getRequest()->getScheme()
                    . '://'
                    . $cf->getRequest()->getHttpHost()
                    . */ $cf->getBaseUrl();
                $message_body = 'La SDM <a href=" ' . $base_url . '/sdm/detail/id/' . $sdm->sdm_id .'"><b>' . $sdm->code . '</b></a> &egrave; stata risolta.<br />';

                $message_repo->send($sdm->id_responsible, $message_title, $message_body, Model_Message_Types::INFO);
            }
            else
            {
                $msg = '';
                foreach($sdm->getInvalidMessages() as $cont)
                {
                    foreach($cont as $msg)
                    {
                        $msg .= $msg;
                    }
                }

                $this->view->result = false;
                $this->view->message = $msg; //'impossibile aggiornare la pianificazione';
            }
        }
        else
        {

            if($id)
            {
                $sdm = $repo->find($id);
            }
            else
            {
                $sdm = $repo->getNewSdm();
            }
            if($sdm->date_resolution == '' || $sdm->date_resolution == '0000-00-00')
            {
                $sdm->date_resolution = date('d/m/Y');
            }
            $this->view->data = $sdm;
            $this->render('edit/trattamento');
        }
    }

    public function mtAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('sdm2');

        $id = $this->_request->getParam('id', false);

        if($this->_request->getParam('save') == 1)
        {
            $sdm = $repo->find($id);

            $sdm->setValidatorAndFilter(new Model_Sdm2_Validator());

            $data = array(
                'id_status' => Model_Sdm2::STATUS_SOLVED
            );

            $sdm->setData($data);

            if($sdm->isValid())
            {
                /** @var $db Zend_Db_Adapter_Mysqli */
                $db = Zend_Registry::get('dbAdapter');

                $db->beginTransaction();

                try
                {
                    $this->view->result = $repo->save($sdm);

                    // update old new story to inactive
                    $db->update('sdm_story', array(
                        'active' => 0
                    ), 'id_sdm = ' . $db->quote($id) . ' and id_status = ' . Model_Sdm2::STATUS_SOLVED);

                    // add story
                    $sdm_story = new Model_Sdm2Story();
                    $sdm_story->setValidatorAndFilter(new Model_Sdm2Story_Validator());

                    $resolution = $_POST['resolution'];
                    $date_resolution = Maco_Utils_DbDate::toDb($_POST['date_resolution']);

                    $data = array(
                        'id_sdm' => $id,
                        'active' => 1,
                        'id_status' => Model_Sdm2::STATUS_SOLVED,
                        'text1' => $resolution,
                        'date1' => date('Y-m-d'),
                        'date2' => $date_resolution,
                    );

                    $sdm_story->setData($data);

                    if($sdm_story->isValid())
                    {
                        $sdm_story_repo = Maco_Model_Repository_Factory::getRepository('sdm2Story');

                        $sdm_story_repo->save($sdm_story);

                        $message_repo = Maco_Model_Repository_Factory::getRepository('message');

                        // remove "to do"
                        $uid = $id . '-SOLVER-WORK';
                        $sdmWithDeps = $repo->findWithDependencies($id);
                        $toRemoveID = $sdmWithDeps->stories[Model_Sdm2::STATUS_WORKING]['active']['id_user']; // id Solver
                        $message_repo->deleteByToAndUid($toRemoveID, $uid);

                        // info to emitter
                        $cf = Zend_Controller_Front::getInstance();
                        $base_url = /* $cf->getRequest()->getScheme()
                    . '://'
                    . $cf->getRequest()->getHttpHost()
                    . */ $cf->getBaseUrl();
                        $message_title = 'SDM risolta: ' . $sdm->code;
                        $message_body = 'La SDM <a href=" ' . $base_url . '/sdm/detail/id/' . $id .'"><b>' . $sdm->code . '</b></a> &egrave; stata risolta.';
                        $message_repo->send($sdm['created_by'], $message_title, $message_body, Model_Message_Types::INFO);

                        // "to do" to responsible
                        $message_title = 'Risoluzione SDM da verificare : ' . $sdm->code;
                        $message_body = 'Devi verificare la risoluzione della SDM <a href=" ' . $base_url . '/sdm/detail/id/' . $id .'"><b>' . $sdm->code . '</b></a> .<br />';
                        $id_rsq = $sdmWithDeps->stories[Model_Sdm2::STATUS_NEW]['active']['id_user'];
                        $message_repo->send($id_rsq, $message_title, $message_body, Model_Message_Types::TODO, null, $id . '-RSQ-VERIFICATION', true);

                        $db->commit();

                        $this->view->message = 'segnalazione risolta';
                    }
                    else
                    {
                        $db->rollBack();

                        $this->view->result = false;
                        $this->view->message = 'errori nell\'operazione';
                    }
                }
                catch(Exception $e)
                {
                    $db->rollBack();

                    $this->view->result = false;
                    $this->view->message = 'errori nell\'operazione';
                }
            }
            else
            {
                $msg = '';
                foreach($sdm->getInvalidMessages() as $cont)
                {
                    foreach($cont as $msg)
                    {
                        $msg .= $msg;
                    }
                }

                $this->view->result = false;
                $this->view->message = 'errori nell\'operazione';
            }
        }
        else
        {
            if(!$id)
            {
                throw new Exception('Id missing', 500);
            }

            $sdm = $repo->find($id);

            if($sdm->sdm_id === null)
            {
                throw new Exception('Segnalazione non trovata', 404);
            }

            $this->view->data = $sdm;

            $this->render('edit/trattamento');
        }
    }

    public function mpaAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('sdm2');

        $id = $this->_request->getParam('id', false);

        if($this->_request->getParam('save') == 1)
        {
            $sdm = $repo->find($id);

            $sdm->setValidatorAndFilter(new Model_Sdm2_Validator());

            $data = array(
                'with_prevention' => Model_Sdm2::PREVENTION_DONE
            );

            $sdm->setData($data);

            if($sdm->isValid())
            {
                /** @var $db Zend_Db_Adapter_Mysqli */
                $db = Zend_Registry::get('dbAdapter');

                $db->beginTransaction();

                try
                {
                    $this->view->result = $repo->save($sdm);

                    // update old new story to inactive
                    $db->update('sdm_story', array(
                        'active' => 0
                    ), 'id_sdm = ' . $db->quote($id) . ' and id_status = ' . Model_Sdm2::PREVENTION_DONE);

                    // add story
                    $sdm_story = new Model_Sdm2Story();
                    $sdm_story->setValidatorAndFilter(new Model_Sdm2Story_Validator());

                    $prevention = $_POST['prevention'];
                    $date_prevention = Maco_Utils_DbDate::toDb($_POST['date_prevention']);

                    $data = array(
                        'id_sdm' => $id,
                        'active' => 1,
                        'id_status' => Model_Sdm2::PREVENTION_DONE,
                        'text1' => $prevention,
                        'date1' => date('Y-m-d'),
                        'date2' => $date_prevention,
                    );

                    $sdm_story->setData($data);

                    if($sdm_story->isValid())
                    {
                        $sdm_story_repo = Maco_Model_Repository_Factory::getRepository('sdm2Story');

                        $sdm_story_repo->save($sdm_story);

                        $message_repo = Maco_Model_Repository_Factory::getRepository('message');
                        $cf = Zend_Controller_Front::getInstance();
                        $base_url = /* $cf->getRequest()->getScheme()
                    . '://'
                    . $cf->getRequest()->getHttpHost()
                    . */ $cf->getBaseUrl();

                        // remove "to do"
                        $uid = $id . '-PREVENTION-WORK';
                        $sdmWithDeps = $repo->findWithDependencies($id);
                        $toRemoveID = $sdmWithDeps->stories[Model_Sdm2::PREVENTION_NEW]['active']['id_user']; // id Solver
                        $message_repo->deleteByToAndUid($toRemoveID, $uid);

                        // "to do" to responsible
                        $message_title = 'Azione Preventiva SDM da verificare : ' . $sdm->code;
                        $message_body = 'Devi verificare la azione preventiva attivata per la SDM <a href=" ' . $base_url . '/sdm/detail/id/' . $id .'"><b>' . $sdm->code . '</b></a> .<br />';
                        $id_rsq = $sdmWithDeps->stories[Model_Sdm2::STATUS_NEW]['active']['id_user'];
                        $message_repo->send($id_rsq, $message_title, $message_body, Model_Message_Types::TODO, null, $id . '-PREVENTION-VERIFICATION', true);

                        $db->commit();

                        $this->view->message = 'azione preventiva avviata';
                    }
                    else
                    {
                        $db->rollBack();

                        $this->view->result = false;
                        $this->view->message = 'errori nell\'operazione';
                    }
                }
                catch(Exception $e)
                {
                    $db->rollBack();

                    $this->view->result = false;
                    $this->view->message = 'errori nell\'operazione';
                }
            }
            else
            {
                $msg = '';
                foreach($sdm->getInvalidMessages() as $cont)
                {
                    foreach($cont as $msg)
                    {
                        $msg .= $msg;
                    }
                }

                $this->view->result = false;
                $this->view->message = 'errori nell\'operazione';
            }
        }
        else
        {
            if(!$id)
            {
                throw new Exception('Id missing', 500);
            }

            $sdm = $repo->find($id);

            if($sdm->sdm_id === null)
            {
                throw new Exception('Segnalazione non trovata', 404);
            }

            $this->view->data = $sdm;

            $this->render('edit/prevenzione');
        }
    }

    public function mrAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('sdm');

        $id = $this->_request->getParam('id', false);

        if($this->_request->getParam('save') == 1)
        {
            $sdm = $repo->find($id);

            $sdm->setValidatorAndFilter(new Model_Sdm_Validator());
            $data = $_POST;

            $data['date_set_responsible'] = date('Y-m-d');
            $data['id_status'] = 2;

            $sdm->setData($data);

            if($sdm->isValid())
            {
                $this->view->result = $repo->save($sdm);

                $this->view->message = 'responsabile aggiornato';

                // messaggio al responsabile
                $message_repo = Maco_Model_Repository_Factory::getRepository('message');
                $message_title = 'SDM da trattare';
                $cf = Zend_Controller_Front::getInstance();
                $base_url = /* $cf->getRequest()->getScheme()
                    . '://'
                    . $cf->getRequest()->getHttpHost()
                    . */ $cf->getBaseUrl();
                $message_body = 'Sei stato assegnato come responsabile per la SDM  <a href=" ' . $base_url . '/sdm/detail/id/' . $sdm->sdm_id .'"><b>' . $sdm->code . '</b></a>.<br />';

                $message_repo->send($sdm->id_responsible, $message_title, $message_body, Model_Message_Types::TODO);
            }
            else
            {
                $msg = '';
                foreach($sdm->getInvalidMessages() as $cont)
                {
                    foreach($cont as $msg)
                    {
                        $msg .= $msg;
                    }
                }

                $this->view->result = false;
                $this->view->message = 'impossibile aggiornare il responsabile';
            }
        }
        else
        {
            if(!$id)
            {
                throw new Exception('Id missing', 500);
            }

            $sdm = $repo->find($id);

            if($sdm->sdm_id === null)
            {
                throw new Exception('Segnalazione non trovata', 404);
            }

            $this->view->data = $sdm;

            $util = new Maco_Html_Utils();

            $userModel = new Model_UsersMapper();
            $users = $userModel->getAllActiveUsers();
            $users = $util->parseDbRowsForSelectElement($users, 'user_id', 'username', array('nome', 'cognome'));
            $this->view->users = array('' => '') + $users;


            $this->render('edit/responsabile');
        }
    }

    public function msAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('sdm2');

        $id = $this->_request->getParam('id', false);

        if($this->_request->getParam('save') == 1)
        {
            $sdm = $repo->find($id);

            $sdm->setValidatorAndFilter(new Model_Sdm2_Validator());
            $data = $_POST;

            $data = array(
                'id_status' => Model_Sdm2::STATUS_WORKING
            );

            $sdm->setData($data);

            if($sdm->isValid())
            {
                /** @var $db Zend_Db_Adapter_Mysqli */
                $db = Zend_Registry::get('dbAdapter');

                $db->beginTransaction();

                try
                {
                    $this->view->result = $repo->save($sdm);

                    // add story
                    $sdm_story = new Model_Sdm2Story();
                    $sdm_story->setValidatorAndFilter(new Model_Sdm2Story_Validator());

                    $cause = $_POST['cause'];
                    $note = $_POST['treatment'];
                    $date_expected = Maco_Utils_DbDate::toDb($_POST['date_expected_resolution']);
                    $id_user = $_POST['id_solver'];

                    $data = array(
                        'id_sdm' => $id,
                        'active' => 1,
                        'id_status' => Model_Sdm2::STATUS_WORKING,
                        'text1' => $cause,
                        'text2' => $note,
                        'date1' => date('Y-m-d'),
                        'date2' => $date_expected,
                        'id_user' => $id_user
                    );

                    $sdm_story->setData($data);

                    if($sdm_story->isValid())
                    {
                        $sdm_story_repo = Maco_Model_Repository_Factory::getRepository('sdm2Story');

                        $sdm_story_repo->save($sdm_story);

                        $message_repo = Maco_Model_Repository_Factory::getRepository('message');

                        // remove "to do"
                        $uid = $id . '-RSQ-MANAGE';
                        $sdmWithDeps = $repo->findWithDependencies($id);
                        $toRemoveID = $sdmWithDeps->stories[Model_Sdm2::STATUS_NEW]['active']['id_user']; // id RSQ
                        $message_repo->deleteByToAndUid($toRemoveID, $uid);

                        // info to emitter
                        $cf = Zend_Controller_Front::getInstance();
                        $base_url = /* $cf->getRequest()->getScheme()
                    . '://'
                    . $cf->getRequest()->getHttpHost()
                    . */ $cf->getBaseUrl();
                        $message_title = 'SDM Accettata: ' . $sdm['code'];
                        $message_body = 'La SDM <a href=" ' . $base_url . '/sdm/detail/id/' . $sdm->sdm_id .'"><b>' . $sdm->code . '</b></a> è stata accettata e messa in trattamento.';
                        $message_repo->send($sdm['created_by'], $message_title, $message_body, Model_Message_Types::INFO);

                        // "to do" to solver
                        $message_title = 'SDM da trattare: ' . $sdm['code'];
                        $message_body = 'Sei stato assegnato come responsabile del trattamento della SDM <a href=" ' . $base_url . '/sdm/detail/id/' . $sdm->sdm_id .'"><b>' . $sdm->code . '</b></a>.';
                        $message_repo->send($id_user, $message_title, $message_body, Model_Message_Types::TODO, null, $id . '-SOLVER-WORK', true);

                        $db->commit();

                        $this->view->message = 'segnalazione accettata ed in lavorazione';
                    }
                    else
                    {
                        $db->rollBack();

                        $this->view->result = false;
                        $this->view->message = 'errori nell\'operazione';
                    }
                }
                catch(Exception $e)
                {
                    $db->rollBack();

                    $this->view->result = false;
                    $this->view->message = 'errori nell\'operazione';
                }
            }
            else
            {
                $msg = '';
                foreach($sdm->getInvalidMessages() as $cont)
                {
                    foreach($cont as $msg)
                    {
                        $msg .= $msg;
                    }
                }

                $this->view->result = false;
                $this->view->message = 'impossibile aggiornare il risolutore';
            }
        }
        else
        {
            if(!$id)
            {
                throw new Exception('Id missing', 500);
            }

            $sdm = $repo->find($id);

            if($sdm->sdm_id === null)
            {
                throw new Exception('Segnalazione non trovata', 404);
            }

            $this->view->data = $sdm;

            $util = new Maco_Html_Utils();

            $user_repo = Maco_Model_Repository_Factory::getRepository('user');
            $users = $user_repo->getUsersWithPermissions(array(
                array('sdm', 'risolutore')
            ));
            $users = $util->parseDbRowsForSelectElement($users, 'user_id', 'username', array('nome', 'cognome'));
            $this->view->users = array('' => '') + $users;

            $this->view->action = 'ms';

            $this->render('edit/solver');
        }
    }

    public function msrAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('sdm2');

        $id = $this->_request->getParam('id', false);

        if($this->_request->getParam('save') == 1)
        {
            $sdm = $repo->find($id);

            $sdm->setValidatorAndFilter(new Model_Sdm2_Validator());
            $cause = $_POST['cause'];

            $data = array(
                'id_status' => Model_Sdm2::STATUS_REJECTED
            );

            $sdm->setData($data);

            if($sdm->isValid())
            {
                /** @var $db Zend_Db_Adapter_Mysqli */
                $db = Zend_Registry::get('dbAdapter');

                $db->beginTransaction();

                try
                {
                    $this->view->result = $repo->save($sdm);

                    // add story
                    $sdm_story = new Model_Sdm2Story();
                    $sdm_story->setValidatorAndFilter(new Model_Sdm2Story_Validator());

                    $data = array(
                        'id_sdm' => $id,
                        'active' => 1,
                        'id_status' => Model_Sdm2::STATUS_REJECTED,
                        'text1' => $cause,
                        'date1' => date('Y-m-d'),
                    );

                    $sdm_story->setData($data);

                    if($sdm_story->isValid())
                    {
                        $sdm_story_repo = Maco_Model_Repository_Factory::getRepository('sdm2Story');

                        $sdm_story_repo->save($sdm_story);

                        $message_repo = Maco_Model_Repository_Factory::getRepository('message');

                        // remove "to do"
                        $uid = $id . '-RSQ-MANAGE';
                        $sdmWithDeps = $repo->findWithDependencies($id);
                        $toRemoveID = $sdmWithDeps->stories[Model_Sdm2::STATUS_NEW]['active']['id_user']; // id RSQ
                        $message_repo->deleteByToAndUid($toRemoveID, $uid);

                        // info to emitter
                        $cf = Zend_Controller_Front::getInstance();
                        $base_url = /* $cf->getRequest()->getScheme()
                    . '://'
                    . $cf->getRequest()->getHttpHost()
                    . */ $cf->getBaseUrl();
                        $message_title = 'SDM Rifiutata: ' . $sdm['code'];
                        $message_body = 'La SDM <a href=" ' . $base_url . '/sdm/detail/id/' . $sdm->sdm_id .'"><b>' . $sdm->code . '</b></a> è stata rifiutata.';
                        $message_repo->send($sdm['created_by'], $message_title, $message_body, Model_Message_Types::INFO);

                        $db->commit();

                        $this->view->message = 'segnalazione rifiutata';
                    }
                    else
                    {
                        $db->rollBack();

                        $this->view->result = false;
                        $this->view->message = 'errori nell\'operazione';
                    }
                }
                catch(Exception $e)
                {
                    $db->rollBack();

                    $this->view->result = false;
                    $this->view->message = 'errori nell\'operazione';
                }
            }
            else
            {
                $msg = '';
                foreach($sdm->getInvalidMessages() as $cont)
                {
                    foreach($cont as $msg)
                    {
                        $msg .= $msg;
                    }
                }

                $this->view->result = false;
                $this->view->message = 'errori nell\'operazione';
            }
        }
        else
        {
            if(!$id)
            {
                throw new Exception('Id missing', 500);
            }

            $sdm = $repo->find($id);

            if($sdm->sdm_id === null)
            {
                throw new Exception('Segnalazione non trovata', 404);
            }

            $this->view->data = $sdm;

            $this->render('edit/reject');
        }
    }

    public function mssbAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('sdm2');

        $id = $this->_request->getParam('id', false);

        if($this->_request->getParam('save') == 1)
        {
            $sdm = $repo->find($id);

            $sdm->setValidatorAndFilter(new Model_Sdm2_Validator());
            $cause = $_POST['cause'];

            $data = array(
                'id_status' => Model_Sdm2::STATUS_SENDBACK
            );

            $sdm->setData($data);

            if($sdm->isValid())
            {
                /** @var $db Zend_Db_Adapter_Mysqli */
                $db = Zend_Registry::get('dbAdapter');

                $db->beginTransaction();

                try
                {
                    $this->view->result = $repo->save($sdm);

                    // add story
                    $sdm_story = new Model_Sdm2Story();
                    $sdm_story->setValidatorAndFilter(new Model_Sdm2Story_Validator());

                    $data = array(
                        'id_sdm' => $id,
                        'active' => 1,
                        'id_status' => Model_Sdm2::STATUS_SENDBACK,
                        'text1' => $cause,
                        'date1' => date('Y-m-d'),
                    );

                    $sdm_story->setData($data);

                    if($sdm_story->isValid())
                    {
                        $sdm_story_repo = Maco_Model_Repository_Factory::getRepository('sdm2Story');

                        $sdm_story_repo->save($sdm_story);

                        $message_repo = Maco_Model_Repository_Factory::getRepository('message');

                        // remove "to do"
                        $uid = $id . '-RSQ-MANAGE';
                        $sdmWithDeps = $repo->findWithDependencies($id);
                        $toRemoveID = $sdmWithDeps->stories[Model_Sdm2::STATUS_NEW]['active']['id_user']; // id RSQ
                        $message_repo->deleteByToAndUid($toRemoveID, $uid);

                        // info to emitter
                        // messaggio al responsabile
                        $cf = Zend_Controller_Front::getInstance();
                        $base_url = /* $cf->getRequest()->getScheme()
                    . '://'
                    . $cf->getRequest()->getHttpHost()
                    . */ $cf->getBaseUrl();
                        $message_title = 'SDM da revisionare: ' . $sdm->code;
                        $message_body = 'La SDM <a href=" ' . $base_url . '/sdm/detail/id/' . $id .'"><b>' . $sdm->code . '</b></a> da te creata necessita una revisione.';
                        $message_repo->send($sdm['created_by'], $message_title, $message_body, Model_Message_Types::TODO, null, $id . '-EMITTER-REVIEW', true);

                        $db->commit();

                        $this->view->message = 'segnalazione rimandata';
                    }
                    else
                    {
                        $db->rollBack();

                        $this->view->result = false;
                        $this->view->message = 'errori nell\'operazione';
                    }
                }
                catch(Exception $e)
                {
                    $db->rollBack();

                    $this->view->result = false;
                    $this->view->message = 'errori nell\'operazione';
                }
            }
            else
            {
                $msg = '';
                foreach($sdm->getInvalidMessages() as $cont)
                {
                    foreach($cont as $msg)
                    {
                        $msg .= $msg;
                    }
                }

                $this->view->result = false;
                $this->view->message = 'errori nell\'operazione';
            }
        }
        else
        {
            if(!$id)
            {
                throw new Exception('Id missing', 500);
            }

            $sdm = $repo->find($id);

            if($sdm->sdm_id === null)
            {
                throw new Exception('Segnalazione non trovata', 404);
            }

            $this->view->data = $sdm;

            $this->render('edit/sendback');
        }
    }

    public function mrvAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('sdm2');

        $id = $this->_request->getParam('id', false);

        if($this->_request->getParam('save') == 1)
        {
            $sdm = $repo->find($id);

            $sdm->setValidatorAndFilter(new Model_Sdm2_Validator());
            $cause = $_POST['cause'];

            $data = array(
                'id_status' => Model_Sdm2::STATUS_NEW
            );

            $sdm->setData($data);

            if($sdm->isValid())
            {
                /** @var $db Zend_Db_Adapter_Mysqli */
                $db = Zend_Registry::get('dbAdapter');

                $db->beginTransaction();

                try
                {
                    $this->view->result = $repo->save($sdm);

                    // recuperiamo l'id RSQ
                    $id_rsq = $db->fetchOne('select id_user from sdm_story where id_sdm = ' . $db->quote($id) . ' and id_status = ' . Model_Sdm2::STATUS_NEW);

                    // update old new story to inactive
                    $db->update('sdm_story', array(
                        'active' => 0
                    ), 'id_sdm = ' . $db->quote($id) . ' and id_status = ' . Model_Sdm2::STATUS_NEW);

                    // add story
                    $sdm_story = new Model_Sdm2Story();
                    $sdm_story->setValidatorAndFilter(new Model_Sdm2Story_Validator());

                    $data = array(
                        'id_sdm' => $id,
                        'active' => 1,
                        'id_status' => Model_Sdm2::STATUS_NEW,
                        'text1' => $cause,
                        'date1' => date('Y-m-d'),
                        'id_user' => $id_rsq
                    );

                    $sdm_story->setData($data);

                    if($sdm_story->isValid())
                    {
                        $sdm_story_repo = Maco_Model_Repository_Factory::getRepository('sdm2Story');

                        $sdm_story_repo->save($sdm_story);

                        $message_repo = Maco_Model_Repository_Factory::getRepository('message');

                        // remove "to do"
                        $uid = $id . '-EMITTER-REVIEW';
                        $toRemoveID = $sdm['created_by'];
                        $message_repo->deleteByToAndUid($toRemoveID, $uid);

                        // todo to responsible
                        // messaggio al responsabile
                        $message_title = 'SDM revisionata: ' . $sdm->code;
                        $cf = Zend_Controller_Front::getInstance();
                        $base_url = /* $cf->getRequest()->getScheme()
                    . '://'
                    . $cf->getRequest()->getHttpHost()
                    . */ $cf->getBaseUrl();
                        $message_body = 'Devi trattare la SDM <a href="' . $base_url . '/sdm/detail/id/' . $id .'"><b>' . $sdm->code . '</b></a> che avevi rimandato e che ora &egrave; stata revisionata.<br />';
                        $message_repo->send($sdm_story->id_user, $message_title, $message_body, Model_Message_Types::TODO, null, $id . '-RSQ-MANAGE', true);

                        $db->commit();

                        $this->view->message = 'segnalazione rimandata';
                    }
                    else
                    {
                        $db->rollBack();

                        $this->view->result = false;
                        $this->view->message = 'errori nell\'operazione';
                    }
                }
                catch(Exception $e)
                {
                    $db->rollBack();

                    $this->view->result = false;
                    $this->view->message = 'errori nell\'operazione';
                }
            }
            else
            {
                $msg = '';
                foreach($sdm->getInvalidMessages() as $cont)
                {
                    foreach($cont as $msg)
                    {
                        $msg .= $msg;
                    }
                }

                $this->view->result = false;
                $this->view->message = 'errori nell\'operazione';
            }
        }
        else
        {
            if(!$id)
            {
                throw new Exception('Id missing', 500);
            }

            $sdm = $repo->find($id);

            if($sdm->sdm_id === null)
            {
                throw new Exception('Segnalazione non trovata', 404);
            }

            $this->view->data = $sdm;

            $this->render('edit/review');
        }
    }

    public function mnvAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('sdm2');

        $id = $this->_request->getParam('id', false);

        if($this->_request->getParam('save') == 1)
        {
            $sdm = $repo->find($id);

            $sdm->setValidatorAndFilter(new Model_Sdm2_Validator());


            $data = array(
                'id_status' => Model_Sdm2::STATUS_WORKING
            );

            $sdm->setData($data);

            if($sdm->isValid())
            {
                /** @var $db Zend_Db_Adapter_Mysqli */
                $db = Zend_Registry::get('dbAdapter');

                $db->beginTransaction();

                try
                {
                    $this->view->result = $repo->save($sdm);

                    // update old new story to inactive
                    $db->update('sdm_story', array(
                        'active' => 0
                    ), 'id_sdm = ' . $db->quote($id) . ' and id_status = ' . Model_Sdm2::STATUS_WORKING);

                    // add story
                    $sdm_story = new Model_Sdm2Story();
                    $sdm_story->setValidatorAndFilter(new Model_Sdm2Story_Validator());

                    $cause = $_POST['cause'];
                    $note = $_POST['treatment'];
                    $date_expected = Maco_Utils_DbDate::toDb($_POST['date_expected_resolution']);
                    $id_user = $_POST['id_solver'];

                    $data = array(
                        'id_sdm' => $id,
                        'active' => 1,
                        'id_status' => Model_Sdm2::STATUS_WORKING,
                        'text1' => $cause,
                        'text2' => $note,
                        'date1' => date('Y-m-d'),
                        'date2' => $date_expected,
                        'id_user' => $id_user
                    );

                    $sdm_story->setData($data);

                    if($sdm_story->isValid())
                    {
                        $sdm_story_repo = Maco_Model_Repository_Factory::getRepository('sdm2Story');

                        $sdm_story_repo->save($sdm_story);

                        $message_repo = Maco_Model_Repository_Factory::getRepository('message');

                        // remove "to do"
                        $uid = $id . '-RSQ-VERIFICATION';
                        $sdmWithDeps = $repo->findWithDependencies($id);
                        $toRemoveID = $sdmWithDeps->stories[Model_Sdm2::STATUS_NEW]['active']['id_user']; // id RSQ
                        $message_repo->deleteByToAndUid($toRemoveID, $uid);

                        // info to emitter
                        $cf = Zend_Controller_Front::getInstance();
                        $base_url = /* $cf->getRequest()->getScheme()
                    . '://'
                    . $cf->getRequest()->getHttpHost()
                    . */ $cf->getBaseUrl();
                        $message_title = 'Trattamento SDM non accettato: ' . $sdm['code'];
                        $message_body = 'Il trattamento per la SDM <a href=" ' . $base_url . '/sdm/detail/id/' . $sdm->sdm_id .'"><b>' . $sdm->code . '</b></a> non &egrave; stata accettato.';
                        $message_repo->send($sdm['created_by'], $message_title, $message_body, Model_Message_Types::INFO);

                        // "to do" to solver
                        $message_title = 'SDM da trattare: ' . $sdm['code'];
                        $message_body = 'La risoluzione della SDM <a href="' . $base_url . '/sdm/detail/id/' . $sdm->sdm_id .'"><b>' . $sdm->code . '</b></a> non &egrave; stato accettato e sei stato assegnato come responsabile del trattamento della SDM.';
                        $message_repo->send($id_user, $message_title, $message_body, Model_Message_Types::TODO, null, $id . '-SOLVER-WORK', true);

                        $db->commit();

                        $this->view->message = 'segnalazione rimandata';
                    }
                    else
                    {
                        $db->rollBack();

                        $this->view->result = false;
                        $this->view->message = 'errori nell\'operazione';
                    }
                }
                catch(Exception $e)
                {
                    $db->rollBack();

                    $this->view->result = false;
                    $this->view->message = 'errori nell\'operazione';
                }
            }
            else
            {
                $msg = '';
                foreach($sdm->getInvalidMessages() as $cont)
                {
                    foreach($cont as $msg)
                    {
                        $msg .= $msg;
                    }
                }

                $this->view->result = false;
                $this->view->message = 'errori nell\'operazione';
            }
        }
        else
        {
            if(!$id)
            {
                throw new Exception('Id missing', 500);
            }

            $sdm = $repo->find($id);

            if($sdm->sdm_id === null)
            {
                throw new Exception('Segnalazione non trovata', 404);
            }

            $this->view->data = $sdm;

            $util = new Maco_Html_Utils();

            $user_repo = Maco_Model_Repository_Factory::getRepository('user');
            $users = $user_repo->getUsersWithPermissions(array(
                array('sdm', 'risolutore')
            ));
            $users = $util->parseDbRowsForSelectElement($users, 'user_id', 'username', array('nome', 'cognome'));
            $this->view->users = array('' => '') + $users;

            /** @var $db Zend_Db_Adapter_Mysqli */
            $db = Zend_Registry::get('dbAdapter');

            // recuperiamo l'id solver
            $this->view->id_solver = $db->fetchOne('select id_user from sdm_story where id_sdm = ' . $db->quote($id) . ' and id_status = ' . Model_Sdm2::STATUS_WORKING);
            $this->view->date_expected_resolution = $db->fetchOne('select date2 from sdm_story where id_sdm = ' . $db->quote($id) . ' and id_status = ' . Model_Sdm2::STATUS_WORKING);

            $this->view->action = 'mnv';

            $this->render('edit/solver');
        }
    }

    public function mpnvAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('sdm2');

        $id = $this->_request->getParam('id', false);

        if($this->_request->getParam('save') == 1)
        {
            $sdm = $repo->find($id);

            $sdm->setValidatorAndFilter(new Model_Sdm2_Validator());

            $data = array(
                'with_prevention' => Model_Sdm2::PREVENTION_NEW
            );

            $sdm->setData($data);

            if($sdm->isValid())
            {
                /** @var $db Zend_Db_Adapter_Mysqli */
                $db = Zend_Registry::get('dbAdapter');

                $db->beginTransaction();

                try
                {
                    $this->view->result = $repo->save($sdm);

                    // update old new story to inactive
                    $db->update('sdm_story', array(
                        'active' => 0
                    ), 'id_sdm = ' . $db->quote($id) . ' and id_status = ' . Model_Sdm2::PREVENTION_NEW);

                    // add story
                    $sdm_story = new Model_Sdm2Story();
                    $sdm_story->setValidatorAndFilter(new Model_Sdm2Story_Validator());

                    $cause = $_POST['cause'];
                    $date_expected = Maco_Utils_DbDate::toDb($_POST['date_expected_prevention']);
                    $id_user = $_POST['id_preventer'];

                    $data = array(
                        'id_sdm' => $id,
                        'active' => 1,
                        'id_status' => Model_Sdm2::PREVENTION_NEW,
                        'text1' => $cause,
                        'date1' => date('Y-m-d'),
                        'date2' => $date_expected,
                        'id_user' => $id_user
                    );

                    $sdm_story->setData($data);

                    if($sdm_story->isValid())
                    {
                        $sdm_story_repo = Maco_Model_Repository_Factory::getRepository('sdm2Story');

                        $sdm_story_repo->save($sdm_story);

                        $message_repo = Maco_Model_Repository_Factory::getRepository('message');

                        // remove "to do"
                        $uid = $id . '-PREVENTION-VERIFICATION';
                        $sdmWithDeps = $repo->findWithDependencies($id);
                        $toRemoveID = $sdmWithDeps->stories[Model_Sdm2::STATUS_NEW]['active']['id_user']; // id RSQ
                        $message_repo->deleteByToAndUid($toRemoveID, $uid);

                        // "to do" to solver
                        $cf = Zend_Controller_Front::getInstance();
                        $base_url = /* $cf->getRequest()->getScheme()
                    . '://'
                    . $cf->getRequest()->getHttpHost()
                    . */ $cf->getBaseUrl();
                        $message_title = 'Attivare azione preventiva per la SDM: ' . $sdm['code'];
                        $message_body = 'L\'azione preventiva eseguita per la SDM <a href="' . $base_url . '/sdm/detail/id/' . $sdm->sdm_id .'"><b>' . $sdm->code . '</b></a> non &egrave; stata verificata. Sei stato assegnato come responsabile per una nuova azione preventiva per la SDM.';
                        $message_repo->send($id_user, $message_title, $message_body, Model_Message_Types::TODO, null, $id . '-PREVENTION-WORK', true);

                        $db->commit();

                        $this->view->message = 'segnalazione rimandata';
                    }
                    else
                    {
                        $db->rollBack();

                        $this->view->result = false;
                        $this->view->message = 'errori nell\'operazione';
                    }
                }
                catch(Exception $e)
                {
                    $db->rollBack();

                    $this->view->result = false;
                    $this->view->message = 'errori nell\'operazione';
                }
            }
            else
            {
                $msg = '';
                foreach($sdm->getInvalidMessages() as $cont)
                {
                    foreach($cont as $msg)
                    {
                        $msg .= $msg;
                    }
                }

                $this->view->result = false;
                $this->view->message = 'errori nell\'operazione';
            }
        }
        else
        {
            if(!$id)
            {
                throw new Exception('Id missing', 500);
            }

            $sdm = $repo->find($id);

            if($sdm->sdm_id === null)
            {
                throw new Exception('Segnalazione non trovata', 404);
            }

            $this->view->data = $sdm;

            $util = new Maco_Html_Utils();

            $user_repo = Maco_Model_Repository_Factory::getRepository('user');
            $users = $user_repo->getUsersWithPermissions(array(
                array('sdm', 'preventer')
            ));
            $users = $util->parseDbRowsForSelectElement($users, 'user_id', 'username', array('nome', 'cognome'));
            $this->view->preventers = $users;

            /** @var $db Zend_Db_Adapter_Mysqli */
            $db = Zend_Registry::get('dbAdapter');

            // recuperiamo l'id solver
            $this->view->id_preventer = $db->fetchOne('select id_user from sdm_story where active = 1 and id_sdm = ' . $db->quote($id) . ' and id_status = ' . Model_Sdm2::PREVENTION_NEW);

            $this->render('edit/prevention');
        }
    }


    public function mvOLDAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('sdm');

        $id = $this->_request->getParam('id', false);

        if($this->_request->getParam('save') == 1)
        {
            $sdm = $repo->find($id);

            $sdm->setValidatorAndFilter(new Model_Sdm_Validator());
            $data = $_POST;

            $data['id_status'] = 5;
            $data['date_verification'] = date('Y-m-d');

            $sdm->setData($data);

            if($sdm->isValid())
            {
                $sdm_id = $repo->save($sdm);
                $this->view->result = $sdm_id;
                if(isset($data['id_user']))
                {
                    $repo->add_responsibles($sdm_id, $data['id_user']);
                }

                $this->view->message = 'dati verifica aggiornati';

                $message_repo = Maco_Model_Repository_Factory::getRepository('message');
                // messaggio al RSQ
                $message_title = 'Risoluzione SDM verificata';
                $cf = Zend_Controller_Front::getInstance();
                $base_url = /* $cf->getRequest()->getScheme()
                    . '://'
                    . $cf->getRequest()->getHttpHost()
                    . */ $cf->getBaseUrl();
                $message_body = 'La risoluzione della SDM <a href=" ' . $base_url . '/sdm/detail/id/' . $sdm->sdm_id .'"><b>' . $sdm->code . '</b></a> &egrave; stata verificata.<br />';
                // TODO: utente che puo associare responsabile
                // 1. repository user
                $users_repo = Maco_Model_Repository_Factory::getRepository('user');
                // SDM that can SET RESPONSIBLE
                $usrs = $users_repo->getUsersOfType('SDMSR');
                $receivers = array();
                foreach($usrs as $user_id => $usr)
                {
                    $receivers[] = $user_id;
                }
                $message_repo->send($receivers, $message_title, $message_body, Model_Message_Types::INFO);
                // messaggio all'emittente
                $message_title = 'SDM risolta e verificata';
                $message_body = 'La SDM <a href="' . $base_url . '/sdm/detail/id/' . $sdm->sdm_id .'"><b>' . $sdm->code . '</b></a> da te segnalata &egrave; stata correttamente risolta e verifcata.<br/>Grazie per il contributo!<br />';
                $message_repo->send($sdm['created_by'], $message_title, $message_body, Model_Message_Types::INFO);
            }
            else
            {
                $msg = '';
                foreach($sdm->getInvalidMessages() as $cont)
                {
                    foreach($cont as $msg)
                    {
                        $msg .= $msg;
                    }
                }

                $this->view->result = false;
                $this->view->message = 'impossibile aggiornare i dati di verifica';
            }
        }
        else
        {
            if($id)
            {
                $sdm = $repo->find($id);
            }
            else
            {
                $sdm = $repo->getNewSdm();
            }

            if($sdm->date_verifica == '' || $sdm->date_verifica == '0000-00-00')
            {
                $sdm->date_verifica = date('Y-m-d');
            }

            $util = new Maco_Html_Utils();

            $userModel = new Model_UsersMapper();
            $users = $userModel->getAllActiveUsers();
            $users = $util->parseDbRowsForSelectElement($users, 'user_id', 'username', array('nome', 'cognome'));
            $this->view->users = $users;

            $this->view->data = $sdm;
            $this->render('edit/verifica');
        }
    }

    public function mvAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('sdm2');

        $id = $this->_request->getParam('id', false);

        if($this->_request->getParam('save') == 1)
        {
            $sdm = $repo->find($id);

            $sdm->setValidatorAndFilter(new Model_Sdm2_Validator());

            $prevenzione = (isset($_POST['prevenzione']) && $_POST['prevenzione'])
                ? Model_Sdm2::PREVENTION_NEW
                : 0;

            $data = array(
                'id_status' => Model_Sdm2::STATUS_VERIFIED,
                'with_prevention' => $prevenzione
            );

            $sdm->setData($data);

            if($sdm->isValid())
            {
                /** @var $db Zend_Db_Adapter_Mysqli */
                $db = Zend_Registry::get('dbAdapter');

                $db->beginTransaction();

                try
                {
                    $this->view->result = $repo->save($sdm);

                    // update old new story to inactive
                    $db->update('sdm_story', array(
                        'active' => 0
                    ), 'id_sdm = ' . $db->quote($id) . ' and id_status = ' . Model_Sdm2::STATUS_VERIFIED);


                    // add story
                    $sdm_story = new Model_Sdm2Story();
                    $sdm_story->setValidatorAndFilter(new Model_Sdm2Story_Validator());

                    $verification = $_POST['verification'];

                    $data = array(
                        'id_sdm' => $id,
                        'active' => 1,
                        'id_status' => Model_Sdm2::STATUS_VERIFIED,
                        'text1' => $verification,
                        'date1' => date('Y-m-d'),
                    );

                    $sdm_story->setData($data);

                    if($sdm_story->isValid())
                    {
                        $sdm_story_repo = Maco_Model_Repository_Factory::getRepository('sdm2Story');

                        $sdm_story_id = $sdm_story_repo->save($sdm_story);

                        // responsibles
                        $id_users = isset($_POST['id_user']) ? $_POST['id_user'] : array();

                        foreach($id_users as $id_user)
                        {
                            if($id_user)
                            {
                                $db->insert('sdm2_responsibles', array(
                                    'id_sdm_story' => $sdm_story_id,
                                    'id_user' => $id_user
                                ));
                            }
                        }

                        $message_repo = Maco_Model_Repository_Factory::getRepository('message');

                        // remove "to do"
                        $uid = $id . '-RSQ-VERIFICATION';
                        $sdmWithDeps = $repo->findWithDependencies($id);
                        $toRemoveID = $sdmWithDeps->stories[Model_Sdm2::STATUS_NEW]['active']['id_user']; // id RSQ
                        $message_repo->deleteByToAndUid($toRemoveID, $uid);

                        // info to emitter
                        $cf = Zend_Controller_Front::getInstance();
                        $base_url = /* $cf->getRequest()->getScheme()
                    . '://'
                    . $cf->getRequest()->getHttpHost()
                    . */ $cf->getBaseUrl();
                        $message_title = 'Trattamento SDM verifcato: ' . $sdm['code'];
                        $message_body = 'Il trattamento per la SDM <a href=" ' . $base_url . '/sdm/detail/id/' . $sdm->sdm_id .'"><b>' . $sdm->code . '</b></a> &egrave; stata verificato.';
                        $message_repo->send($sdm['created_by'], $message_title, $message_body, Model_Message_Types::INFO);

                        if($prevenzione != 0)
                        {
                            if(isset($_POST['id_preventer']) && $_POST['id_preventer'])
                            {
                                // add story
                                $sdm_story = new Model_Sdm2Story();
                                $sdm_story->setValidatorAndFilter(new Model_Sdm2Story_Validator());

                                $data = array(
                                    'id_sdm' => $id,
                                    'active' => 1,
                                    'id_status' => Model_Sdm2::PREVENTION_NEW,
                                    'date1' => date('Y-m-d'),
                                    'id_user' => $_POST['id_preventer']
                                );

                                $sdm_story->setData($data);

                                if($sdm_story->isValid())
                                {
                                    $sdm_story_repo = Maco_Model_Repository_Factory::getRepository('sdm2Story');

                                    $sdm_story_repo->save($sdm_story);

                                    // "to do" to preventer
                                    $message_title = 'Attivare azione preventiva per SDM: ' . $sdm['code'];
                                    $message_body = 'Sei stato assegnato come responsabile dell\'azione preventiva per la SDM <a href="' . $base_url . '/sdm/detail/id/' . $sdm->sdm_id .'"><b>' . $sdm->code . '</b></a>.';
                                    $message_repo->send($_POST['id_preventer'], $message_title, $message_body, Model_Message_Types::TODO, null, $id . '-PREVENTION-WORK', true);

                                    $db->commit();
                                    $this->view->message = 'segnalazione verificata e azione preventiva avviata';
                                }
                                else
                                {
                                    $db->rollBack();
                                    $this->view->message = 'errori nell\'operazione';
                                }
                            }
                            else
                            {
                                $db->rollBack();

                                $this->view->result = false;
                                $this->view->message = 'errori nell\'operazione';
                            }
                        }
                        else
                        {
                            $db->commit();
                            $this->view->message = 'segnalazione verificata';
                        }
                    }
                    else
                    {
                        $db->rollBack();

                        $this->view->result = false;
                        $this->view->message = 'errori nell\'operazione';
                    }
                }
                catch(Exception $e)
                {
                    $db->rollBack();

                    $this->view->result = false;
                    $this->view->message = 'errori nell\'operazione';
                }
            }
            else
            {
                $msg = '';
                foreach($sdm->getInvalidMessages() as $cont)
                {
                    foreach($cont as $msg)
                    {
                        $msg .= $msg;
                    }
                }

                $this->view->result = false;
                $this->view->message = 'errori nell\'operazione';
            }
        }
        else
        {
            if(!$id)
            {
                throw new Exception('Id missing', 500);
            }

            $sdm = $repo->find($id);

            if($sdm->sdm_id === null)
            {
                throw new Exception('Segnalazione non trovata', 404);
            }

            if($sdm->date_verifica == '' || $sdm->date_verifica == '0000-00-00')
            {
                $sdm->date_verifica = date('Y-m-d');
            }

            $this->view->data = $sdm;

            $util = new Maco_Html_Utils();

            $userModel = new Model_UsersMapper();
            $users = $userModel->getAllActiveUsers();
            $users = $util->parseDbRowsForSelectElement($users, 'user_id', 'username', array('nome', 'cognome'));
            $this->view->users = $users;

            $user_repo = Maco_Model_Repository_Factory::getRepository('user');
            $preventers = $user_repo->getUsersWithPermissions(array(
                array('sdm', 'preventer')
            ));
            $preventers = $util->parseDbRowsForSelectElement($preventers, 'user_id', 'username', array('nome', 'cognome'));
            $this->view->preventers = $preventers;

            $this->render('edit/verifica');
        }
    }

    public function mpvAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('sdm2');

        $id = $this->_request->getParam('id', false);

        if($this->_request->getParam('save') == 1)
        {
            $sdm = $repo->find($id);

            $sdm->setValidatorAndFilter(new Model_Sdm2_Validator());

            $data = array(
                'with_prevention' => Model_Sdm2::PREVENTION_VERIFIED
            );

            $sdm->setData($data);

            if($sdm->isValid())
            {
                /** @var $db Zend_Db_Adapter_Mysqli */
                $db = Zend_Registry::get('dbAdapter');

                $db->beginTransaction();

                try
                {
                    $this->view->result = $repo->save($sdm);

                    // add story
                    $sdm_story = new Model_Sdm2Story();
                    $sdm_story->setValidatorAndFilter(new Model_Sdm2Story_Validator());

                    $verification = $_POST['verification'];

                    $data = array(
                        'id_sdm' => $id,
                        'active' => 1,
                        'id_status' => Model_Sdm2::PREVENTION_VERIFIED,
                        'text1' => $verification,
                        'date1' => date('Y-m-d'),
                    );

                    $sdm_story->setData($data);

                    if($sdm_story->isValid())
                    {
                        $sdm_story_repo = Maco_Model_Repository_Factory::getRepository('sdm2Story');

                        $sdm_story_repo->save($sdm_story);

                        $message_repo = Maco_Model_Repository_Factory::getRepository('message');

                        // remove "to do"
                        $uid = $id . '-PREVENTION-VERIFICATION';
                        $sdmWithDeps = $repo->findWithDependencies($id);
                        $toRemoveID = $sdmWithDeps->stories[Model_Sdm2::STATUS_NEW]['active']['id_user']; // id RSQ
                        $message_repo->deleteByToAndUid($toRemoveID, $uid);

                        $db->commit();

                        $this->view->message = 'azione preventiva verificata';
                    }
                    else
                    {
                        $db->rollBack();

                        $this->view->result = false;
                        $this->view->message = 'errori nell\'operazione';
                    }
                }
                catch(Exception $e)
                {
                    $db->rollBack();

                    $this->view->result = false;
                    $this->view->message = 'errori nell\'operazione';
                }
            }
            else
            {
                $msg = '';
                foreach($sdm->getInvalidMessages() as $cont)
                {
                    foreach($cont as $msg)
                    {
                        $msg .= $msg;
                    }
                }

                $this->view->result = false;
                $this->view->message = 'errori nell\'operazione';
            }
        }
        else
        {
            if(!$id)
            {
                throw new Exception('Id missing', 500);
            }

            $sdm = $repo->find($id);

            if($sdm->sdm_id === null)
            {
                throw new Exception('Segnalazione non trovata', 404);
            }

            if($sdm->date_verifica == '' || $sdm->date_verifica == '0000-00-00')
            {
                $sdm->date_verifica = date('Y-m-d');
            }

            $this->view->data = $sdm;

            $this->render('edit/preventionverifica');
        }
    }
}
