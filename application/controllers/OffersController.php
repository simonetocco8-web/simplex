<?php

class OffersController extends Zend_Controller_Action
{
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('list', 'html')
                    ->addActionContext('list', 'json')
                    ->addActionContext('cr', 'json')
                    ->addActionContext('cs', 'html')
                    ->addActionContext('css', 'json')
                    ->addActionContext('cco', 'html')
                    ->addActionContext('top', 'html')
                    ->addActionContext('companies', 'json')
                    ->addActionContext('ccos', 'json')
                    ->initContext();
    }

    public function indexAction()
    {
        $this->_forward('list');
    }
    
    public function ccoAction()
    {
    	$id = $this->_request->getParam('id', false);

		if(!$id)
		{
			throw new Exception('Id missing', 500);
		}
    	
		$model = new Model_UsersMapper();

        $users_repo = Maco_Model_Repository_Factory::getRepository('user');
        //$dtgs = $model->getDtgs();
		//$dtgs = $model->getAllUsers();
        $dtgs = $users_repo->getUsersOfType('DTG');
		
		$util = new Maco_Html_Utils();
        
        $dtgs = $util->parseDbRowsForSelectElement($dtgs , 'user_id', 'username', array('nome', 'cognome'));
		
   //     $commons = new Model_Common();

     //   $this->view->status = $commons->getArrayForSelectElementSimple('offer_status', 'id', 'name');
        
		$this->view->dtgs = $dtgs;
		
		$this->view->id = $id;
    }
    
    public function ccosAction()
    {
    	$order_id_dtg = $this->_request->getParam('dtg');
    	//$offer_status = $this->_request->getParam('new_state');
    	$offer_status = null;
        
    	$offer_id = $this->_request->getParam('id');

        $repo = Maco_Model_Repository_Factory::getRepository('order');

		$res = $repo->createFromOffer($offer_id, $order_id_dtg);
        
		if(is_array($res))
		{
			$this->view->result = false;
			$this->view->message = json_encode($res); //'Impossibile creare la commessa!';
		}
		else
		{
			$this->view->result = true;
			$this->view->message = 'Commessa creata correttamente!';
		}
    }
    
    public function listAction()
    {
        $sort = $this->_request->getParam('_s', false);
        $dir = $this->_request->getParam('_d', 'ASC');

        $search = $_GET;

        /** @var $repo Model_Offer_Repository */
        $repo = Maco_Model_Repository_Factory::getRepository('offer');

        $deleted = $this->_request->getParam('deleted', 0);

        $this->view->deleted = $deleted;

        $perpage = $this->_request->getParam('perpage', Zend_Registry::getInstance()->get('config')->entries_per_page);

        if(isset($_GET['_export']) && $_GET['_export'] == 1)
        {
            $repo->exportOffers($sort, $dir, $search, $deleted);
            exit;
        }

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

        // search elements
        $searchRepo = new Model_Offer_Search();
        
        $r = new Maco_DaGrid_Render_Html();

        $id_companies = $this->_request->getParam('id_company', '');
        $id_companies = explode(',', $id_companies);
        $id_company = false;
        if(count($id_companies) == 1)
        {
            $id_company = $id_companies[0];
        }
        $r->id_company = $id_company;

        $r->with_export = true;
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
        $g->setAdvancedSearch($searchRepo);
        //$g->setPaginator(true);
        $g->setRowsPerPage($perpage);
        $g->setId('offers');

        $this->view->dag = $g;
        
        if($this->_request->isXmlHttpRequest())
        {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy('offers.phtml');
        }        
    }
    
    /**
    * Ritorna le aziende che hanno offerte per questa internal
    * 
    */
    public function companiesAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('offer');
        
        $search = $this->_request->getParam('search');
        
        $companies = $repo->getCompaniesWithOffers($search);
        
        $response = array();
        
        foreach($companies as $v)
        {
            $response[] = array($v['company_id'], $v['ragione_sociale']);
        }
        
        echo json_encode($response);
        exit;
    }
    
    public function testpdfAction()
    {
         $content = "
            <page>
                <h1>Exemple d'utilisation</h1>
                <br>
                Ceci est un <b>exemple d'utilisation</b>
                de <a href='http://html2pdf.fr/'>HTML2PDF</a>.<br>
            </page>";

        require_once(LIBRARY_PATH . '/html2pdf/html2pdf.class.php');
        $html2pdf = new HTML2PDF('P','A4','it');
        //$html2pdf->setNewPage('A4', 'P', array('img' => 'http://v2.simplex.local/img/excellentia-logo.png', 'posY' => 0, 'width' => 80));
        $html2pdf->WriteHTML($content);
        $html2pdf->Output('exemple.pdf');
        
        exit;
    }
    
    public function testpdf2Action()
    {
         $content = "
            <page>
                <h1>Exemple d'utilisation</h1>
                <br>
                Ceci est un <b>exemple d'utilisation</b>
                de <a href='http://html2pdf.fr/'>HTML2PDF</a>.<br>
            </page>";

        require_once 'dompdf/dompdf_config.inc.php';
        $autoloader = Zend_Loader_Autoloader::getInstance(); // assuming we're in a controller
        $autoloader->pushAutoloader('DOMPDF_autoload');
        
        //require_once(LIBRARY_PATH . '/dompdf/dompdf.php');
            $dompdf = new DOMPDF();
            $dompdf->load_html($content);
            
            $dompdf->render();
            $dompdf->stream("sample.pdf");


        
        exit;
    }
    
    public function export3Action()
    {
        $offer_id = $this->_request->getParam('id', false);
        
        if(!$offer_id)
        {
            throw new Zend_Controller_Action_Exception(
                'Necessario id offerta per la esportazione', 404);                
        }
        
        $type = $this->_request->getParam('type', 'pdf');
        
        $mailMerge = new Zend_Service_LiveDocx_MailMerge();
 
        $mailMerge->setUsername('dop3')
                  ->setPassword('batigol');
         
        $filesMapper = new Model_FilesMapper();
        
        $repo = Maco_Model_Repository_Factory::getRepository('offer');
        $offer = $repo->findWithDependenciesById($offer_id);
        
        $template_name = 'template_' . $offer->id_service . '_' . $offer->id_subservice . '.docx';
        
        if(!$filesMapper->pathExists($filesMapper->getTemplatePath(false) . $template_name))
        {
            $this->_helper->getHelper('FlashMessenger')->addMessage('Il file di template per l\'esportazione dell\'offerta non esiste.');
            $this->_redirect('offers/detail/id/' . $offer_id);
            //throw new Zend_Controller_Action_Exception('file di template non presente', 404);
        }
        
        //$mailMerge->setLocalTemplate($filesMapper->getTemplatePath() . 'template-offerta.docx');
        
        $mailMerge->setLocalTemplate($filesMapper->getTemplatePath() . $template_name);
        
        
        $moments = array();
        $total = 0;
        foreach($offer->moments as $moment)
        {
            $moments[] = array(
                'moment_tipologia' => $moment->tipologia,
                'moment_importo'   => number_format($moment->getImportoScontato(), 2, ',', '.')
            );
            $total += $moment->getImportoScontato();
        }
        
        $mailMerge->assign('luogo', $offer->luogo)
                  ->assign('date_offer', Maco_Utils_DbDate::fromDb($offer->date_offer))
                  ->assign('code_offer', $offer->code_offer)
                  ->assign('validita', $offer->validita)
                  ->assign('company_name', $offer->company->ragione_sociale)
                  ->assign('company_address', $offer->company->addresses[0]->getCleanAddress())
                  ->assign('service_name', $offer->service_name)
                  ->assign('subservice_name', $offer->subservice_name)
                  ->assign('subject', $offer->subject)
                  ->assign('contact_name', $offer->company_contact_name)
                  ->assign('moment', $moments)
                  ->assign('total', number_format($total, 2, ',', '.'));
         
        $mailMerge->createDocument();
         
        $document = $mailMerge->retrieveDocument($type);
        
        switch($type)
        {
            case 'docx':
                header('Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
                break;
            default:
                header('Content-type: application/pdf');
                break;
        }
        
        // We'll be outputting a PDF
        
        // It will be called downloaded.pdf
        header('Content-Disposition: attachment; filename="offerta ' . $offer->code_offer . '.' . $type . '"');

        // The PDF source is in original.pdf
        //readfile('document.pdf');
        
        echo $document;
        
        exit;
        //file_put_contents('document2.pdf', $document);
    }
    
    public function exportAction()
    {
        $offer_id = $this->_request->getParam('id', false);
        
        if(!$offer_id)
        {
            throw new Zend_Controller_Action_Exception(
                'Necessario id offerta per la esportazione', 404);                
        }
        
        include(LIBRARY_PATH . '/Tbs/tbs_class.php');
        include(LIBRARY_PATH . '/Tbs/plugins/tbs_plugin_opentbs.php');
        
        $filesMapper = new Model_FilesMapper();                                   
        
        $repo = Maco_Model_Repository_Factory::getRepository('offer');
        $offer = $repo->findWithDependenciesById($offer_id);
        
        $template_name = 'template_' . $offer->id_service . '_' . $offer->id_subservice . '.docx';
        
        if(!$filesMapper->pathExists($filesMapper->getTemplatePath(false) . $template_name))
        {
            $this->_helper->getHelper('FlashMessenger')->addMessage('Il file di template per l\'esportazione dell\'offerta non esiste.');
            $this->_redirect('offers/detail/id/' . $offer_id);
            //throw new Zend_Controller_Action_Exception('file di template non presente', 404);
        }
        
        $tbs = new clsTinyButStrong; // new instance of TBS
        $tbs->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin
        
        $tbs->LoadTemplate($filesMapper->getTemplatePath() . $template_name);
        
        global $company_name;
        global $partita_iva;
        global $company_address;
        global $luogo;
        global $date_offer;
        global $code_offer;
        global $validita;
        global $service_name;
        global $subservice_name;
        global $work_time;
        global $subject;
        global $contact_name;
        global $total;
        
        $moments = array();
        $total = 0;
        foreach($offer->moments as $moment)
        {
            $moments[] = array(
                'moment_tipologia' => $moment->tipologia,
                'moment_importo'   => number_format($moment->getImportoScontato(), 2, ',', '.')
            );
            $total += $moment->getImportoScontato();
        }
        
        $company_name = $offer->company->ragione_sociale;
        $company_address = count($offer->company->addresses) > 0
                ? $offer->company->addresses[0]->getCleanAddress()
                : '';
        $partita_iva = $offer->company->partita_iva;
        $luogo = $offer->luogo;
        $date_offer = Maco_Utils_DbDate::fromDb($offer->date_offer);
        $code_offer = $offer->code_offer;
        $validita = $offer->validita;
        $service_name = utf8_decode($offer->service_name);
        $subservice_name = utf8_decode($offer->subservice_name);
        $work_time = 'xxx';
        $subject = $offer->subject;
        $contact_name = $offer->service_name;
        $total = number_format($total, 2, ',', '.');
        
        $tbs->MergeBlock('a', $moments);
        
        $tbs->Show(OPENTBS_DOWNLOAD, $offer->code_offer . '.docx');
        exit;
        
        
        -dd($tbs);
        
        $doc = new tinyDoc();
        $doc->setZipMethod('ziparchive');
        $doc->setProcessDir(APPLICATION_PATH . '/../temp');

        //$doc->createFrom(array('extension' => 'docx'));
        $doc->createFrom($filesMapper->getTemplatePath() . $template_name);
        
        $doc->loadXml('word/document.xml');
        
        $moments = array();
        $total = 0;
        foreach($offer->moments as $moment)
        {
            $moments[] = array(
                'tipologia' => $moment->tipologia,
                'importo'   => number_format($moment->getImportoScontato(), 2, ',', '.')
            );
            $total += $moment->getImportoScontato();
        }
            /*
        $doc->mergeXmlField('luogo', $offer->luogo);
        $doc->mergeXmlField('date_offer', Maco_Utils_DbDate::fromDb($offer->date_offer));
        $doc->mergeXmlField('code_offer', $offer->code_offer);
        $doc->mergeXmlField('validita', $offer->validita . ' giorni');
        $doc->mergeXmlField('company_name', $offer->company->ragione_sociale);
        $doc->mergeXmlField('company_address', $offer->company->addresses[0]->getCleanAddress());
        $doc->mergeXmlField('service', $offer->service_name);
        $doc->mergeXmlField('subservice_name', $offer->subservice_name);
        $doc->mergeXmlField('subject', $offer->subject);
        $doc->mergeXmlField('contact_name', $offer->company_contact_name);
        $doc->mergeXmlField('total', number_format($total, 2));
        */
        $doc->mergeXmlBlock('block1', $moments);
           /*
           $doc->mergeXmlBlock('block1',
      array(
        array('firstname' => 'John'   , 'lastname' => 'Doe'),
        array('firstname' => 'Douglas', 'lastname' => 'Adams'),
        array('firstname' => 'Roger'  , 'lastname' => 'Waters'),
        array('firstname' => 'Roger'  , 'lastname' => 'Waters'),
        array('firstname' => 'Roger'  , 'lastname' => 'Waters'),
        array('firstname' => 'Roger'  , 'lastname' => 'Waters'),
        array('firstname' => 'Roger'  , 'lastname' => 'Waters'),
      )
    );
             */
        
        $doc->saveXml();
        $doc->close();

        // send and remove the document
        $doc->sendResponse();
        $doc->remove();
        exit;
    }
    public function editAction()
    {
		$repo = Maco_Model_Repository_Factory::getRepository('offer');
        
        $offer = null;

        // if is post we should save the data
        if($this->_request->isPost())
        {			
			$result = $repo->saveFromData($_POST);

            if(is_array($result))
            {
				$layout = Zend_Layout::getMvcInstance();
                foreach($result as $msg)
                {
                    //$this->_helper->getHelper('FlashMessenger')->addMessage('Inserimento dati non riuscito');                
                    //$this->_helper->getHelper('FlashMessenger')->addMessage($msg);
                }
                $layout->flashMessages = $result;
                //$this->_redirect('offers/edit' . (($_POST['offer_id'] != '') ? ('/id/' . $_POST['offer_id']) : ''));
                $offer = $repo->getFromData($_POST);
            }                                                                
            else
            {
                $this->_redirect('offers/detail/id/' . $result);
            }
        }
        
        $id_company = (int) $this->_request->getParam('id_company', false);
        $id_offer = (int) $this->_request->getParam('id', false);

        $companyRepo = Maco_Model_Repository_Factory::getRepository('company');

        if(!$id_company && !$id_offer)
        {
            throw new Zend_Controller_Action_Exception('id_company or id offer needed', 500);
        }

        $commonModel = new Model_Common();
        
        if($id_offer)
        {
        	$new_revision = $this->_request->getParam('r', false);

        	//$this->view->data = $offersModel->getDetail($id_offer);
            $this->view->offer = $repo->findWithDependenciesById($id_offer);

            $subservices = $commonModel->getArrayForSelectElementSimple('subservices', 'subservice_id', 'name', 'id_service = ' . $this->view->offer->id_service);
        	$this->view->subservices = array(0 => '') + $subservices;

            $id_company = $this->view->offer->id_company;

            $this->view->returl_url = '/offers/edit/id/' . $id_offer;
            
        	if($new_revision)
        	{
        		$this->view->contentTitle = "Modifica Offerta - Nuova Revisione";
        		$this->view->new_revision = true;
        	}
        	else
        	{
            	$this->view->contentTitle = "Modifica Offerta";
            	$this->view->new_revision = false;
        	}
            
        }
        else
        {
            $company = $companyRepo->findById($id_company);
            
            if($company['is_cliente'] != 1 && $company['is_cliente'] != 2)
            {
                throw new Exception('impossibile creare un\'offerta per questa azienda');
            }
            
            // TODO potremmo recuperare i dati validi inseriti
            //$this->view->data = $offersModel->getEmptyDetail();
            if($offer)
            {
                $this->view->offer = $offer;
            }
            else
            {
                $this->view->offer = $repo->getNewOffer();
            }
            
            $this->view->returl_url = '/offers/edit/id_company/' . $id_company;

            $this->view->offer->id_company = $id_company;
            $this->view->offer->date_offer = date('d/m/Y');

            $this->view->contentTitle = "Nuova Offerta per " . $company->ragione_sociale;
        }

        $util = new Maco_Html_Utils();

        $promotori = $companyRepo->getCompanies(null, null, array('ispromotore' => 1));

        $promotori = $util->parseDbRowsForSelectElement($promotori, 'company_id', 'ragionesociale');
        $this->view->promotori = array(0 => '') + $promotori;

        $services = $commonModel->getArrayForSelectElementSimple('services', 'service_id', 'name');
        $this->view->services = array(0 => '') + $services;

        $levels = $commonModel->getArrayForSelectElementSimple('interests_levels', 'interests_level_id', 'name');
        $this->view->levels = array(0 => '') + $levels;

        $pagamenti = $commonModel->getArrayForSelectElementSimple('pagamenti', 'pagamento_id', 'name');
        $this->view->pagamenti = array(0 => '') + $pagamenti;

        $contacts = $commonModel->getArrayForSelectElementSimple('contacts', 'contact_id', array('nome', 'cognome'), 'id_company = ' . $id_company);
        $this->view->contacts = array(0 => '') + $contacts;

        $users_repo = Maco_Model_Repository_Factory::getRepository('user');
        $users = $users_repo->getUsersWithPermissions(array(
            array('offers', 'create')
        ));
        $users = $util->parseDbRowsForSelectElement($users, 'user_id', 'username', array('nome', 'cognome'));
        $this->view->users = array(0 => '') + $users;
    }
    
    public function saveAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('offer');

        $result = $repo->saveFromData($_POST);

        if(is_array($result))
        {
            foreach($result as $msg)
            {
                //$this->_helper->getHelper('FlashMessenger')->addMessage('Inserimento dati non riuscito');                
                $this->_helper->getHelper('FlashMessenger')->addMessage($msg);
            }
            $this->_redirect('offers/edit' . (($_POST['offer_id'] != '') ? ('/id/' . $_POST['offer_id']) : ''));
        }                                                                
        else
        {
            $this->_redirect('offers/detail/id/' . $result);
        }
    }
    
    public function detailAction()
    {
    	$id = $this->_request->getParam('id', false);

        $repo = Maco_Model_Repository_Factory::getRepository('offer');
    	
    	if(!$id)
        {
			$id_offer = $this->_request->getParam('id_offer', false);
			$rev = $this->_request->getParam('r', false);
			$year = $this->_request->getParam('y', false);

			if(!$id_offer || $rev === false || $year == false)
			{
				throw new Zend_Controller_Action_Exception(
					'Necessario id oppure id_offer-rev-year per il dettaglio offerta', 404);
			}
			
            $this->view->offer = $repo->findWithDependenciesByIdOfferAndRevision($id_offer, $year, $rev);
            $id = $this->view->offer->offer_id;
        }
        else 
        {
        	$this->view->offer = $repo->findWithDependenciesById($id);
        }
        
        if(!$this->view->offer)
        {
            $this->_helper->getHelper('FlashMessenger')->addMessage('Offerta non trovata');
            $this->_redirect('offers/list');
        }
        
        $id = $this->view->offer['id_offer'];
        
        $this->view->revisions = $repo->getRevisionsStory($this->view->offer->internal_code, $this->view->offer->id_offer, $this->view->offer->year);

        $filesMapper = new Model_FilesMapper();
        $this->view->offerPdf = $filesMapper->getOfferPdf($this->view->offer);

      //  $this->view->tasksList = $this->_getTasksForOffer($this->view->offer->offer_id);
        $this->view->tasks = $this->_getTasksForOfferSimple($this->view->offer->offer_id);
    }

    protected function _getTasksForOfferSimple($id)
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
        $options['where']['tasks.id_offer'] = $id;
        //$options['where']['done'] = 1;

        $tasks = $mapper->fetch($options);
        return $tasks;
    }

    protected function _getTasksForOffer($id)
    {
        $mapper = new Model_TasksMapper();

        $options = array();

        $options['where']['done'] = $this->_request->getParam('done', 0);
        $options['where']['id_offer'] = $id;
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
    
    public function makeorderAction()
    {
        $id = $this->_request->getParam('id', false);
        
        if(!$id)
        {            
            throw new Zend_Controller_Action_Exception(
                    'Necessario l\'id della offerta per creare la commessa', 404);                
        }
        
        $model = new Model_OrdersMapper();
        
        $result = $model->makeOrderFromOffer($id, null);

        if(is_array($result))
        {
            foreach($result as $msg)
                $this->_helper->getHelper('FlashMessenger')->addMessage('Inserimento dati non riuscito');
                
            $this->_redirect('offers/detail/id/' . $id);
        }
        else
        {
            $this->_redirect('orders/detail/id/' . $result);
        }
    }
    
    /**
     * Azione cambio revisione
     * 
     * @return void
     */
    public function crAction()
    {
    	$id_offer = $this->_request->getParam('id_offer', false);
		$rev = $this->_request->getParam('r', false);
		
		if(!$id_offer || !$rev)
		{
			throw new Zend_Controller_Action_Exception(
				'Necessario id oppure id_offer-rev per il dettaglio offerta', 404);				
		}

        $repo = Maco_Model_Repository_Factory::getRepository('offer');      
		$this->view->result = $repo->activateRevision($id_offer, $rev);
    }
    
    /**
     * Azione mostra form per cambio stato
     * 
     * @return void
     */
    public function csAction()
    {
    	$id = $this->_request->getParam('id', false);

		if(!$id)
		{
			throw new Exception('Id missing', 500);
		}

        $repo = Maco_Model_Repository_Factory::getRepository('offer');
        $offer = $repo->find($id);

        // TODO: aggiungere controllo su azienda provvisoria

        $model = new Model_OffersMapper();
		$this->view->status = $offer->id_status;
		$this->view->id_approver = $offer->id_approver;
        $this->view->with_approver = false;
        $this->view->provvisoria = false;

        // carico gli stati
        $commonModel = new Model_Common();
        $user = Zend_Auth::getInstance()->getIdentity();
        if($offer['id_status'] == 1
           && ($user->user_object->has_permission('offers', 'approve_any')
               || ($user->user_object->has_permission('offers', 'approve') && $offer['created_by'] == $user->user_id)))
        {
            $company_repo = Maco_Model_Repository_Factory::getRepository('company');
            $company = $company_repo->find($offer->id_company);

            // forzo lo stato a 3 - 4
            $where = 'offer_status_id = 3';

            if($company['partita_iva'] != 'Provvisoria')
            {
                $where .= ' or offer_status_id = 4';
            }

            $statuses = $commonModel->getArrayForSelectElementSimple('offer_status', 'offer_status_id', 'name', $where);
            $this->view->id_approver = $user->user_id;
        }
        else
        {
            if($offer->id_status == 1)
            {
                $this->view->with_approver = true;
                $util = new Maco_Html_Utils();
                $users_repo = Maco_Model_Repository_Factory::getRepository('user');
                $users = $users_repo->getUsersWithPermissions(array(
                    array('offers', 'approve')
                ));
                $users = $util->parseDbRowsForSelectElement($users, 'user_id', 'username', array('nome', 'cognome'));
                $this->view->users = array('' => '') + $users;
            }
            elseif($offer->id_status == 3)
            {
                $company_repo = Maco_Model_Repository_Factory::getRepository('company');
                $company = $company_repo->find($offer->id_company);
                if($company['partita_iva'] == 'Provvisoria')
                {
                    $this->view->provvisoria = true;
                }
            }


            $where = 'id_depends_on like \'%' . $this->view->status . '%\'';
            $statuses = $commonModel->getArrayForSelectElementSimple('offer_status', 'offer_status_id', 'name', $where);
        }
        
        $this->view->statuses = $statuses;

		$this->view->id = $id;
    }
    
	/**
     * Azione cambio stato
     * 
     * @return void
     */
    public function cssAction()
    {
        /*
        $this->view->status = 1;
        $this->view->result = true;
        $this->view->message = 'Operazione effettuata con successo';
        
        return;
        */
        
    	$id = $this->_request->getParam('id', false);

    	$status = $this->_request->getParam('status', false);
    	
		if(!$id || !$status)
		{
			throw new Exception('Id or status missing', 500);
		}

        $repo = Maco_Model_Repository_Factory::getRepository('offer');

        $id_approver = $this->_request->getParam('id_approver', false);
        $hidden = $this->_request->getParam('hidden', false);

        $this->view->result = $repo->setStatus($id, $status, $id_approver, $hidden);

		$commonModel = new Model_Common();
        // carico gli stati
        
        $this->view->id = $id;
        
        $this->view->status = $commonModel->getValueFromId('offer_status', 'name', $status, 'offer_status_id');
        $this->view->message = 'Operazione effettuata con successo';
    }
    
    public function topAction()
    {
        $id = $this->_request->getParam('id');
        
        if(!$id)
        {
            throw new Zend_Controller_Action_Exception(
                'Necessario id oppure id_offer-rev per il dettaglio offerta', 404);                
        }
        
        $repo = Maco_Model_Repository_Factory::getRepository('offer');
        $this->view->offer = $repo->findWithDependenciesById($id);

        $filesMapper = new Model_FilesMapper();
        $this->view->offerPdf = $filesMapper->getOfferPdf($this->view->offer);

        $this->render('detail/offertop');
    }
}
