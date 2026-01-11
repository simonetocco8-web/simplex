<?php

class CompaniesController extends Zend_Controller_Action
{
	public function init()
	{
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('list', 'html')
            ->addActionContext('percentforpartner', 'json')
            ->addActionContext('tbl', 'json')
            ->addActionContext('detail', 'json')
            ->addActionContext('acaaddress', 'json')
            ->addActionContext('exclude', 'json')
            ->addActionContext('lo', 'json')
            ->addActionContext('lo', 'html')
		    ->initContext();
	}

	public function indexAction()
	{
		$this->_forward('list');
	}
    
    public function tblAction()
    {
        $search = array('ragionesociale' => $this->_request->getParam('search'));
        
        if($is_promotore = $this->_request->getParam('is_promotore', false))
        {
            $search['ispromotore'] = 1;
        }
        
        $repo = Maco_Model_Repository_Factory::getRepository('company');
        $values = $repo->getCompanies('ragionesociale', 'ASC', $search);
        
        $result = array();
        
        foreach($values as $v)
        {
            $result[] = array($v['company_id'], '<div><b>' . $v['ragionesociale'] . '</b><br />p.IVA: ' . $v['partita_iva'] . '</div>', $v['ragionesociale']);
        }
        
        echo json_encode($result);
        exit;
    }
    
    public function percentforpartnerAction()
    {
        $id = (int) $this->_request->getParam('id', false);
        if(!$id)
        {
            $this->view->result = '';
        }
        else
        {
            $repo = Maco_Model_Repository_Factory::getRepository('company');
            $company = $repo->find($id);
            $this->view->result = $company->promotore_percent;
        }
    }

    public function setintAction()
    {
        $company_id = (int) $this->_request->getParam('id');
        $internal_id = (int) $this->_request->getParam('int');

        $repo = Maco_Model_Repository_Factory::getRepository('company');
        $res = $repo->setInternal($company_id, $internal_id);

        if($res)
        {
             $this->_redirect('companies/detail/id/' . $company_id);
        }
        else
        {
            throw new Zend_Controller_Action_Exception('Errori nell\'operazione', 500);
        }
    }

	public function listAction()
	{
        $repo = Maco_Model_Repository_Factory::getRepository('company');

        $user = Zend_Auth::getInstance()->getIdentity();
        $excluded = !$user->user_object->has_permission('companies', 'view_excluded') ? 0
            : $this->_request->getParam('excluded', 0);

        $sort = $this->_request->getParam('_s', 'ragionesociale');
        $dir = $this->_request->getParam('_d', 'ASC');

        //$search = $_POST;
        $search = $_GET;

        $perpage = $this->_request->getParam('perpage', Zend_Registry::getInstance()->get('config')->entries_per_page);

        if(isset($_GET['_export']) && $_GET['_export'] == 1)
        {
            $repo->exportCompanies($sort, $dir, $search, $excluded);
            exit;
        }

        if($this->_request->isXmlHttpRequest())
        {
            $companies = $repo->getCompanies($sort, $dir, $search, $excluded, $perpage);
        }
        else
        {
            $companies = array();
        }

        $this->view->excluded = (bool) $excluded;

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

        // search elements
        $searchRepo = new Model_Company_Search();
        
        $r = new Maco_DaGrid_Render_Html();
        $r->with_export = true;
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
        $g->setAdvancedSearch($searchRepo);
        $g->setId('companies');

        $this->view->dag = $g;
        
        if($this->_request->isXmlHttpRequest())
        {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy();
        }
	}
    
    public function excludeAction()
    {
        $id = $this->_request->getParam('id', false);
        if(!$id)
        {
            $this->view->result = false;
            $this->view->message = 'Azienda non trovata!';
        }
        else
        {
            $repo = Maco_Model_Repository_Factory::getRepository('company');
            $company = $repo->find($id);
            
            // TOGGLE ACTION
            $company->deleted = (int) !$company->deleted;
            $company->setValidatorAndFilter(new Model_Company_Validator());
            
            if($company->isValid())
            {
                $repo->save($company);
                $this->view->result = true;
                
                if($company->deleted == 1)
                {
                    $this->view->message = 'Azienda esclusa!';
                    $this->view->newLinkText = 'ripristina';
                }
                else
                {
                    $this->view->message = 'Azienda ripristinata!';
                    $this->view->newLinkText = 'escludi';
                }
            }
            else
            {
                $this->view->result = false;
                $this->view->message = 'Impossibile modificare lo stato dell\'azienda!';
            }
        }
    }
	
	public function acaddressesAction()
	{
		$id = (int) $this->_request->getParam('id', false);
		
		if(!$id)
		{
			throw new Zend_Controller_Action_Exception('Necessario id per il dettaglio azienda', 404);
		}
		
		$model = new Model_CompaniesMapper();
		
		$addresses = $model->getAddressesByCompanyId($id);
		
		$response = array();
		
		foreach($addresses as $ad)
		{
			$response[] = array($ad['via'] . ' ' . $ad['numero'] . ', ' . $ad['localita'] . ' (' . $ad['provincia'] . ')', $ad['via'] . ' ' . $ad['numero'] . ', ' . $ad['localita'] . ' (' . $ad['provincia'] . ')');
		}
		
		echo json_encode($response);
		exit;
	} 

    public function editcontactAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('contact');

        $id_company = (int) $this->_request->getParam('id', false);
        $id_contact = (int) $this->_request->getParam('idc', false);

        $utils = new Model_Common();
        $vals = array('' => '') + $utils->getArrayForSelectElementSimple('contact_titles', 'contact_title_id', 'name');
        $this->view->contact_titles = $vals;
        
        if($id_contact)
		{
            $this->view->contact = $repo->find($id_contact);
			$this->view->contentTitle = "Modifica Contatto";
		}
		else
		{
			// TODO potremmo recuperare i dati validi inseriti
            $this->view->contact = $repo->getNewContact();
			$this->view->contentTitle = "Nuova Contatto";
		}

        $company_repo = Maco_Model_Repository_Factory::getRepository('company');
        $company = $company_repo->find($id_company);
        
        $this->view->contentTitle .= ' per ' . $company->ragione_sociale;
        
        $this->view->id_company = $id_company;
    }

    public function deletecontactAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('contact');

        $id_company = (int) $this->_request->getParam('id', false);
        $id_contact = (int) $this->_request->getParam('idc', false);

        if(! $id_contact)
        {
            throw new Zend_Controller_Action_Exception('Necessario id per il dettaglio contatti', 404);
        }

        $repo->delete($id_contact);

        $this->_redirect('companies/detail/id/' . $id_company);
    }
    
    public function exportAction()
    {
        $id = $this->_request->getParam('id');
        $type = $this->_request->getParam('type', 'pdf');
        $with_tasks = $this->_request->getParam('tasks', false);
        $type = 'docx';
        
        $repo = Maco_Model_Repository_Factory::getRepository('company');
        $company = $repo->findById($id);
        
        include(LIBRARY_PATH . '/Tbs/tbs_class.php');
        include(LIBRARY_PATH . '/Tbs/plugins/tbs_plugin_opentbs.php');
         
        $filesMapper = new Model_FilesMapper();
        
        $template_name = ($with_tasks) ? 'company-tasks.docx' : 'company-notasks.docx';
        
        if(!$filesMapper->pathExists($filesMapper->getTemplatePath(false) . $template_name))
        {
            $this->_helper->getHelper('FlashMessenger')->addMessage('Il file di template per l\'esportazione dell\'offerta non esiste.');
            $this->_redirect('orders/detail/id/' . $offer_id);
        }
        
        $tbs = new clsTinyButStrong; // new instance of TBS
        $tbs->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin
        
        $tbs->LoadTemplate($filesMapper->getTemplatePath() . $template_name);
        
        global $cliente_id;
        global $ragione_sociale;
        global $indirizzo;
        global $cap;
        global $citta;
        global $provincia;
        global $partita_iva;
        global $cf;
        global $organico_medio;
        global $categoria;
        global $fatturato;

        $cliente_id = $company->company_id;
        $ragione_sociale = utf8_decode($company->ragione_sociale);
        $partita_iva = $company->partita_iva;
        $cf = $company->cf;
        $organico_medio = utf8_decode($company->organico_medio_name);
        $categoria = utf8_decode($company->categoria_name);
        $fatturato = utf8_decode($company->fatturato_name);

        $sedi = array();
        foreach($company->addresses as $address)
        {
            $sedi[] = array(
                'descrizione' => utf8_decode($address->description),
                'indirizzo' => utf8_decode($address->getCleanAddress())
            );
        }
        $tbs->MergeBlock('i', $sedi);
        
        $tels = array();
        foreach($company->telephones as $tel)
        {
            $tels[] = array(
                'descrizione' => utf8_decode($tel->description),
                'numero' => utf8_decode($tel->number)
            );
        }
        $tbs->MergeBlock('t', $tels);
        
        $emails = array();
        foreach($company->mails as $mail)
        {
            $emails[] = array(
                'descrizione' => utf8_decode($mail->description),
                'email' => $mail->mail
            );
        }
        $tbs->MergeBlock('e', $emails);
        
        $webs = array();
        foreach($company->websites as $web)
        {
            $webs[] = array(
                'descrizione' => utf8_decode($web->description),
                'url' => $web->url
            );
        }
        $tbs->MergeBlock('w', $webs);
        
        
        $contacts_repo = Maco_Model_Repository_Factory::getRepository('contact');
        $sort = $this->_request->getParam('_s', 'cognome');
        $dir = $this->_request->getParam('_d', 'ASC');
        $search = $_POST;
        $search['id_company'] = $id;

        $contacts = $contacts_repo->getContacts($sort, $dir, $search);
        $items = array();
        foreach($contacts as $contact)
        {
            $items[] = array(
                'titolo' => $contact['contact_title'],
                'nome' => $contact['nome'] . ' ' . $contact['cognome'],
                'descrizione' => $contact['description'],
                'telefono' => implode(PHP_EOL, $contact['telephones']),
                'email' => implode(PHP_EOL, $contact['mails']),
            );
        }
        $tbs->MergeBlock('r', $items);

        if($with_tasks)
        {
            $tasks_repo = Maco_Model_Repository_Factory::getRepository('task');
            $tasks = $tasks_repo->getTasks('when', 'ASC', array(
                'id_company' => $id
            ));

            $items = array();
            foreach($tasks as $task)
            {
                $items[] = array(
                    'descrizione' => strip_tags(Model_Task::getFormattedTask($task))
                );
            }
            $tbs->MergeBlock('c', $items);
        }
        
        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select();
        $select->from('offers', array('date_accepted', 'code_offer', 'date_end', 'segnalato_da', 'sconto', 'importo' => 'offer_importo'))
            ->joinLeft(array('u1' => 'users'), 'u1.user_id = offers.id_rco', array('rco' => 'u1.username'))
            ->joinLeft(array('os' => 'offer_status'), 'os.offer_status_id = offers.id_status', array('status' => 'os.name'))
            //->joinLeft('moments', 'moments.id_offer = offers.offer_id', array())
            ->where('offers.active = 1')
            ->where('offers.id_company = ' . $db->quote($id))
            ->group('offers.offer_id')
            ->order('date_sent ASC');

        $offers = $db->fetchAll($select);
        $items = array();
        foreach($offers as $offer)
        {
            $items[] = array(
                'codice' => $offer['code_offer'],
                'data_aggiudicazione' => Maco_Utils_DbDate::fromDb($offer['date_accepted']),
                'rco' => utf8_decode($offer['rco']),
                'segnalato_da' => utf8_decode($offer['segnalato_da']),
                'stato' => utf8_decode($offer['status']),
                'importo' => number_format($offer['importo'], 2, ',', '.'),
                'data_chiusura' => Maco_Utils_DbDate::fromDb($offer['date_end']),
            );
        }
        $tbs->MergeBlock('o', $items);

        $file_name = 'scheda_azienda.docx';
        $tbs->Show(OPENTBS_DOWNLOAD, $file_name);
        exit;
    }
    
    public function savecontactAction()
    {   
        $repo = Maco_Model_Repository_Factory::getRepository('contact');

        $contact_id = $repo->saveFromData($_POST);

        if(is_array($contact_id))
        {
            foreach($contact_id as $msg)
            {
                $this->_helper->getHelper('FlashMessenger')->addMessage($msg);
            }
             
                //$session = new Zend_Session_Namespace('contacts_edit');
                //$session->data = $_POST;
             $this->_redirect('companies/editcontact' 
                . (($_POST['contact_id'] != '') 
                    ? ('/id/' . $_POST['id_company'] . '/idc/' . $_POST['contact_id'])  
                    : ''));   
        }
        else
        {
            $this->_redirect('companies/detailcontact/id/' . $this->_request->getParam('id_company') . '/idc/' . $contact_id);
        }
    }
    
	public function editAction()
	{
        $repo = Maco_Model_Repository_Factory::getRepository('company');

        $company = null;
        
        // if is post we should save the data
        if($this->_request->isPost())
        {
            $result = $repo->saveFromData($_POST);
              
            if (is_array($result)) {
                $layout = Zend_Layout::getMvcInstance();
                foreach ($result as $msg)
                {
                    $this->_helper->getHelper('FlashMessenger')->addMessage($msg);
                }
                $layout->flashMessages = $result;
                
                //$this->_redirect('companies/edit' . (($_POST['company_id'] != '') ? ('/id/' . $_POST['company_id']) : ''));
                $company = $repo->getFromData($_POST);
            }
            else
            {
                $this->_redirect('companies/detail/id/' . $result);
            }
        }
        
		$model = new Model_CompaniesMapper();
		
		$id = (int) $this->_request->getParam('id', false);

		// TODO: 12 Ã¨ l'id dello stato CLIENTE -> HARDCODED
		$statuswhere = 'status_id <> 1';
		
        $this->view->newCompany = 0;
        
		if($id)
		{
            $this->view->data = $repo->find($id);
			//$this->view->data = $model->getDetail($id);
			$this->view->contentTitle = "Modifica Azienda";

            // todo: effettuare questo controllo
			//if($model->hasOrders($id))
			{
			//	$statuswhere = null;
			}
		}
		else
		{
            if(!$company)
            {
			    // TODO potremmo recuperare i dati validi inseriti
                $company = $repo->getNewCompany();
		    //	$this->view->data = $model->getEmptyDetail();
                
                $this->view->newCompany = 1;
            }
            $this->view->data = $company;
            $this->view->contentTitle = "Nuova Azienda";
		}

		$commonModel = new Model_Common();
		$internals = $commonModel->fetchAllSingleTableDefault('internals');
		$this->view->internals = $internals;
		
		$statusList = $commonModel->getArrayForSelectElementSimple('status', 'status_id', 'name', $statuswhere);
		$this->view->statusList = /*array(0 => '') +*/ $statusList;
        
        $categorie = $commonModel->getArrayForSelectElementSimple('categories', 'category_id', 'name');
        $this->view->categorie = array(0 => '') + $categorie;
        
        $eas = $commonModel->getArrayForSelectElementSimple('ea', 'ea_id', 'name');
        $this->view->eas = array(0 => '') + $eas;
        
        $fatturati = $commonModel->getArrayForSelectElementSimple('fatturati', 'fatturato_id', 'name');
        $this->view->fatturati = array('' => '') + $fatturati;
        
        $organici = $commonModel->getArrayForSelectElementSimple('organici_medi', 'organico_medio_id', 'name');
        $this->view->organici_medi = array('' => '') + $organici;
        
        $come = $commonModel->getArrayForSelectElementSimple('conosciuto_come', 'conosciuto_come_id', 'name');
        $this->view->come = array(0 => '') + $come;
        
        $util = new Maco_Html_Utils();
        
        $userModel = new Model_UsersMapper();
        $users = $userModel->getAllActiveUsers();
        $users = $util->parseDbRowsForSelectElement($users, 'user_id', 'username', array('nome', 'cognome'));
        $this->view->users = array(0 => '') + $users;
        
        /*
        $contactsModel = new Model_ContactsMapper();
        $contacts = $contactsModel->getList();
        $contacts = $util->parseDbRowsForSelectElement($contacts, 'contact_id', 'nome', array('cognome'), ' ');
        $this->view->contacts = array(0 => '') + $contacts;
        */
        
		unset($commonModel);
	}
	
    /*
	public function saveAction()
	{
        $repo = Maco_Model_Repository_Factory::getRepository('company');

        $result = $repo->saveFromData($_POST);

        if (is_array($result)) {
            foreach ($result as $msg)
            {
                $this->_helper->getHelper('FlashMessenger')->addMessage($msg);
            }
            //$this->_helper->getHelper('FlashMessenger')->addMessage(array('data' => $data));
            $this->_redirect('companies/edit' . (($_POST['company_id'] != '') ? ('/id/' . $_POST['company_id']) : ''));
        }
        else
        {
            $this->_redirect('companies/detail/id/' . $result);
        }
	}
	*/
    
	public function detailAction()
	{
        if($pi = $this->_request->getParam('pi', false))
        {
            // json from edit company
            $repo = Maco_Model_Repository_Factory::getRepository('company');
            $company = $repo->findByPartitaIva($pi);
            $this->view->company = $company->toArray();
        }
        else
        {
            $id = $this->_request->getParam('id', false);

            if(!$id)
            {
                throw new Zend_Controller_Action_Exception('Necessario id per il dettaglio azienda', 404);
            }

            $repo = Maco_Model_Repository_Factory::getRepository('company');
            $company = $repo->findById($id);

            if(!$company->company_id)
            {
                throw new Zend_Controller_Action_Exception('Azienda non trovata.', 404);
            }

            $this->view->company = $company;

            $this->view->contactsList = $this->_getContactsForCompany($id);

            //$this->view->tasksList = $this->_getTasksForCompany($id);
            
            $this->view->tasks = $this->_getTasksForCompanySimple($id);
        }
	}

    public function loAction()
    {
        $id = $this->_request->getParam('id', false);
        if(!$id)
        {
            throw new Zend_Controller_Action_Exception('Id not found', 404);
        }

        $company_repo = Maco_Model_Repository_Factory::getRepository('company');

        if($this->_request->isPost() && $_POST['save'] == 1)
        {
            $result = $company_repo->setOffice($id, $_POST['id_office']);
            if($result)
            {
                $this->view->result = true;
			    $this->view->office = $result;
                $this->view->message = 'Sede modificata correttamente';
            }
            else
            {
                $this->view->result = false;
			    $this->view->message = 'impossibile effettuare l\'operazione!';
            }
        }
        else
        {
            $user = Zend_Auth::getInstance()->getIdentity();
            $utils = new Model_Common();
            $this->view->offices = $utils->getArrayForSelectElementSimple('offices', 'office_id', 'name', 'id_internal = ' . $user->internal_id);

            $this->view->company = $company_repo->findById($id);
        }
    }

    public function detailcontactAction()
    {
        $id_company = (int) $this->_request->getParam('id', false);
        $id_contact = (int) $this->_request->getParam('idc', false);

        if(!$id_company || !$id_contact)
        {
            throw new Zend_Controller_Action_Exception('Id or idc not found', 404);
        }

        $repo = Maco_Model_Repository_Factory::getRepository('contact');
        $contact = $repo->find($id_contact);

        if($contact->id_company != $id_company)
        {
            throw new Zend_Controller_Action_Exception('company Id not matched', 404);
        }

        $this->view->contact = $contact;
    }
    
    protected function _getContactsForCompany($id)
    {
        $repo = Maco_Model_Repository_Factory::getRepository('contact');

        $sort = $this->_request->getParam('_s', 'cognome');
        $dir = $this->_request->getParam('_d', 'ASC');

        $search = $_POST;
        $search['id_company'] = $id;

        $contacts = $repo->getContacts($sort, $dir, $search);

        $g = new Maco_DaGrid_Grid();
        $g->setSearchable(false);
        $g->addColumns(array(
            array(
                'field' => 'contact_title',
                'label' => 'titolo',
                'sortable' => false
            ),
            array(
                'label' => 'Cognome',
                'field' => 'cognome',
                'class' => 'link', // class -> link set renderer -> link
                'sortable' => false,
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
                'sortable' => false
            ),
            array(
                'field' => 'description',
                'label' => 'descrizione',
                'sortable' => false
            ),
            array(
                'field' => 'telephones',
                'label' => 'telefono',
                'renderer' => 'array-br',
                'sortable' => false
            ),
            array(
                'field' => 'mails',
                'label' => 'e-mail',
                'renderer' => 'array-br',
                'sortable' => false
            )
        ));

        $r = new Maco_DaGrid_Render_Html();
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        //        $r->setId('users');
        $r->setTrIdPrefix('man_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($contacts);
        $g->setRenderer($r);
        $g->setSource($s);
        //$g->setPaginator(true);
        $g->setRowsPerPage($this->_request->getParam('perpage', 20));
        $g->setId('contacts');

        return $g;        
    }

    protected function _getTasksForCompanySimple($id)
    {
        $mapper = new Model_TasksMapper();

        $options = array();

        if(!isset($_POST['done']))
        {
            $_POST['done'] = '0';
        }

        if($done = $this->_request->getParam('done', false))
        {
            $options['where']['done'] = $done;
        }
        $options['where']['tasks.id_company'] = $id;
        //$options['where']['done'] = 1;

        $tasks = $mapper->fetch($options);
        return $tasks;
    }
    
    protected function _getTasksForCompany($id)
    {
        $mapper = new Model_TasksMapper();

        $options = array();

        if(!isset($_POST['done']))
        {
            $_POST['done'] = '0';
        }

        $options['where']['done'] = $this->_request->getParam('done', 0);
        $options['where']['tasks.id_company'] = $id;
        $options['where']['done'] = 1;

        //$tasks = $mapper->fetch($options);
        //todo: real tasks
        $tasks = array();


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
}
