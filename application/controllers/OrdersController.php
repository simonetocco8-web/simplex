<?php

class OrdersController extends Zend_Controller_Action
{
    protected $_sal_array  = array(
        ''  => '',
        '0'  => '0',
        '10' => '10',
        '20' => '20',
        '30' => '30',
        '40' => '40',
        '50' => '50',
        '60' => '60',
        '70' => '70',
        '80' => '80',
        '90' => '90',
        '100' => '100',
    );

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('list', 'html')
                    ->addActionContext('list', 'json')
                    ->addActionContext('mp', 'html')
                    ->addActionContext('up', 'html')
                    ->addActionContext('closefase', 'html')
                    ->addActionContext('closefases', 'json')
                    ->addActionContext('mps', 'json')
                    ->addActionContext('mc', 'html')
                    ->addActionContext('mcomm', 'html')
                    ->addActionContext('mcm', 'html')
                    ->addActionContext('uc', 'html')
                    ->addActionContext('ucm', 'html')
                    ->addActionContext('ucg', 'html')
                    ->addActionContext('mcs', 'json')
                    ->addActionContext('mcomms', 'json')
                    ->addActionContext('mcms', 'json')
                    ->addActionContext('mpm', 'html')
                    ->addActionContext('mpms', 'json')
                    ->addActionContext('companies', 'json')
                    ->addActionContext('enti', 'json')
                    ->initContext();
    }

    public function indexAction()
    {
        $this->_forward('list');
    }
    
    public function listAction()
    {
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

        $search = $_GET;
        
        $perpage = $this->_request->getParam('perpage', Zend_Registry::getInstance()->get('config')->entries_per_page);

        if(isset($_GET['_export']) && $_GET['_export'] == 1)
        {
            $repo->exportOffers($sort, $dir, $search);
            exit;
        }

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

        // search elements
        $searchRepo = new Model_Order_Search();
        
        $r = new Maco_DaGrid_Render_Html();
        $r->with_export = true;
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
        $g->setAdvancedSearch($searchRepo);
        //$g->setPaginator(true);
        $g->setRowsPerPage($perpage);
        $g->setId('orders');

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
        $repo = Maco_Model_Repository_Factory::getRepository('order');
        
        $search = $this->_request->getParam('search');
        
        $companies = $repo->getCompaniesWithOrders($search);
        
        $response = array();
        
        foreach($companies as $v)
        {
            $response[] = array($v['company_id'], $v['ragione_sociale']);
        }
        
        echo json_encode($response);
        exit;
    }

    public function incaricoAction()
    {
        $id_order = $this->_request->getParam('id_order', false);
        $rco = $this->_request->getParam('rco', false);

        if(!$rco || !$id_order)
        {
            throw new Zend_Controller_Action_Exception(
                'Necessario id order o rco per la presa incarico', 404);
        }

        $rco = base64_decode($rco);

        $repo = Maco_Model_Repository_Factory::getRepository('order');
        $ret = $repo->setAssigned($id_order, $rco);

        $this->_redirect('orders/detail/id/' . $id_order);
    }

    public function mpmAction()
    {
        $id_moment = $this->_request->getParam('mid', false);
        $id_order = $this->_request->getParam('id', false);

        if(!$id_moment || !$id_order)
        {
            throw new Zend_Controller_Action_Exception(
                    'Necessario id per il dettaglio momento', 404);
        }

        $model = new Model_OrdersMapper();

        $data = $model->getMomentDetail($id_moment, $id_order);

        if($data['p_valore_g_uomo'] != 0)
        {
            $data['p_gg_hm'] = $data['order']->offer['total'] / $data['p_valore_g_uomo'];
            $data['p_ore_hm'] = $data['p_gg_hm'] * 8;
        }
        else
        {
            $data['p_gg_hm'] = 0;
            $data['p_ore_hm'] = 0;
        }
        
        $this->view->data = $data;
        $this->view->id_order = $id_order;
    }
    
    /**
    * Salva i nuovi dati di pianificazione momento
    *
    */
    public function mpmsAction()
    {
        $model = new Model_OrdersMapper();

        $this->view->result = $model->savePianificazionePerMoment($_POST);

        $this->view->message = ($this->view->result)
            ? 'dati consuntivi aggiornati'
            : 'impossibile aggiornare il consuntivo';
    }
    
    public function exportraliAction()
    {
        $order_id = $this->_request->getParam('id', false);
        
        if(!$order_id)
        {
            throw new Zend_Controller_Action_Exception(
                'Necessario id commessa per la esportazione', 404);                
        }
        
        include(LIBRARY_PATH . '/Tbs/tbs_class.php');
        include(LIBRARY_PATH . '/Tbs/plugins/tbs_plugin_opentbs.php');
         
        $filesMapper = new Model_FilesMapper();
        
        $repo = Maco_Model_Repository_Factory::getRepository('order');
        $order = $repo->findWithDependenciesById($order_id);
        
        $template_name = 'rali.docx';
        
        if(!$filesMapper->pathExists($filesMapper->getTemplatePath(false) . $template_name))
        {
            $this->_helper->getHelper('FlashMessenger')->addMessage('Il file di template per l\'esportazione dell\'offerta non esiste.');
            $this->_redirect('orders/detail/id/' . $offer_id);
        }
        
        $tbs = new clsTinyButStrong; // new instance of TBS
        $tbs->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin
        
        $tbs->LoadTemplate($filesMapper->getTemplatePath() . $template_name);
        
        global $codice_cliente;
        global $cliente;
        global $settore;
        global $cod_rali;
        global $servizio;
        global $sottoservizio;
        global $cod_offerta;
        global $specifiche;
        global $luogo;
        global $contatto;
        global $prodotti;
        global $note_offerta;
        global $organico_medio;
        global $data_rali;
        global $rco;
        global $data_chiusura;
        global $dtg;
        global $note_produzione;
        global $note_consuntivo;
        global $sal;

        $codice_cliente = str_pad($order->offer->company->company_id, 4, '0', STR_PAD_LEFT);
        $cliente = utf8_decode($order->offer->company->ragione_sociale);
        $settore = utf8_decode(($order->offer->company->categoria_name) ?: '');
        $cod_rali = $order->code_order;
        $servizio = utf8_decode($order->offer->service_name);
        $sottoservizio = utf8_decode($order->offer->subservice_name);
        $cod_offerta = $order->offer->code_offer;
        $specifiche = utf8_decode($order->offer->subject);
        $luogo = utf8_decode($order->offer->luogo);
        $contatto = utf8_decode($order->offer->company_contact_name);
        $contatto = utf8_decode($order->offer->company_contact_name);
        $prodotti = utf8_decode($order->offer->company->prodotti);
        $note_offerta = utf8_decode($order->offer->note);
        $organico_medio = utf8_decode($order->offer->company->organico_medio_name);
        $data_rali = Maco_Utils_DbDate::fromDb($order->rali_date);
        $rco = utf8_decode($order->offer->rco_name);
        $data_chiusura = Maco_Utils_DbDate::fromDb($order->data_chiusura_richiesta);
        $dtg = utf8_decode($order->dtg_name);
        $note_produzione = utf8_decode($order->note_pianificazione);
        $note_consuntivo = utf8_decode($order->note_consuntivo);
        $sal = ($order->sal == '0' || $order->sal == '') ? '0 %' : utf8_decode($order->sal) . ' %';

        $fasi = array();
        $fasic = array();
        foreach($order->offer->moments as $k => $m)
        {
            $hm = '';
            $gg = '';
            if($m['p_valore_g_uomo'] != '' && $m['p_valore_g_uomo'] != 0)
            {
                 $gg = number_format($order['offer']['total'] / $m['p_valore_g_uomo'], 2, ',', '.');
                 $hm = number_format($gg * 8, 2, ',', '.');
            }
            $fasi[] = array(
                'indice' => $k + 1,
                'nome' => $m->tipologia,
                'hhm' => $hm,
                'gg' => $gg,
                'ninc' => $m->p_n_incontri,
                'hstudio' => $m->p_ore_studio,
                'data' => Maco_Utils_DbDate::fromDb($m->expected_date),
            );
            
            $fasic[] = array(
                'indice' => $k + 1,
                'nome' => $m->tipologia,
                'ore_totali' => $m->c_ore_studio + $m->c_ore_azienda + $m->c_ore_certificazione + $m->c_ore_viaggio,
                'ore_studio' => $m->c_ore_studio,
                'ore_certificazione' => $m->c_ore_certificazione,
                'ore_azienda' => $m->c_ore_azienda,
                'ore_viaggio' => $m->c_ore_viaggio,
                'n_incontri' => $m->c_n_incontri,
                'km' => $m->c_n_km,
                'done' => $m->done,
                'data' => Maco_Utils_DbDate::fromDb($m->date_done),
            );
        }
        
        $rcs = array();
        $datas = array();
        foreach($order->rcos as $rc)
        {
            $rcs[] = array('rc' => $rc['rco']);
            $datas[] = array('data' => Maco_Utils_DbDate::fromDb($rc['date_assigned']));
        }
        
        $tbs->MergeBlock('b', $rcs);
        $tbs->MergeBlock('c', $datas);
        $tbs->MergeBlock('d', $fasic);
        $tbs->MergeBlock('f', $fasi);
        
         $file_name = 'rali ' . $order->code_order .  '.docx';
         $tbs->Show(OPENTBS_DOWNLOAD, $file_name);
         exit;
        
        /*
        $moments = array();
        $total = 0;
        foreach($offer->moments as $moment)
        {
            $moments[] = array(
                'moment_tipologia' => $moment->tipologia,
                'moment_importo'   => number_format($moment->getImportoScontato(), 2)
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
                  ->assign('total', number_format($total, 2));
        */
        
        $fasi = array();
        foreach($order->offer->moments as $i => $moment)
        {
            $fasi[] = array(
                'indice_fase' => $i,
                'nome_fase' => $moment->tipologia
            );
        }
        
        $mailMerge
                ->assign('id_cliente', str_pad($order->offer->company->company_id, 4, '0', STR_PAD_LEFT))
                  ->assign('company_name', $order->offer->company->ragione_sociale)
                  ->assign('company_settore', $order->offer->company->ea_name)
                  ->assign('rali_code', $order->code_order)
                  ->assign('service', $order->offer->service_name)
                  ->assign('subservice', $order->offer->subservice_name)
                  ->assign('offer_code', $order->offer->code_offer)
                  ->assign('specifiche', $order->offer->subject)
                  ->assign('luogo', $order->offer->luogo)
                  ->assign('contatto', $order->offer->company_contact_name)
                  ->assign('note_offerta', $order->offer->note)
                  ->assign('oraganico_medio', $order->offer->company->organico_medio_name)
                  ->assign('data_rali', Maco_Utils_DbDate::fromDb($order->rali_date))
                  ->assign('rco', $order->offer->rco_name)
                  ->assign('data_chiusura', Maco_Utils_DbDate::fromDb($order->order->data_chiusura_richiesta))
                  ->assign('h_hm', ($order['valore_g_uomo'] != '' && $order['valore_g_uomo'] != 0) ? number_format($order['offer']['total'] / $order['valore_g_uomo'] * 8, 2, ',', '.') : '')
                  ->assign('gg', ($order['valore_g_uomo'] != '' && $order['valore_g_uomo'] != 0) ? number_format($order['offer']['total'] / $order['valore_g_uomo'], 2, ',', '.') : '')
                  ->assign('n_incontri', $order->n_incontri)
                  ->assign('note_produzione', $order->note)
                  ->assign('fasi', $fasi)
                  ;
        
        $mailMerge->createDocument();
         
        $document = $mailMerge->retrieveDocument($type);
        //$type = 'docx';
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
        header('Content-Disposition: attachment; filename="rali_ ' . $order->code_order . '.' . $type . '"');

        // The PDF source is in original.pdf
        //readfile('document.pdf');
        
        echo $document;
        
        exit;
        //file_put_contents('document2.pdf', $document);
    }
    
    /**
    * Modifica la pianificazione di una commessa
    * 
    */
    public function mpAction()
    {
        $id = $this->_request->getParam('id', false);
        
        if(!$id)
        {
            throw new Zend_Controller_Action_Exception(
                    'Necessario id per il dettaglio commessa', 404);                
        }
        
        $repo = Maco_Model_Repository_Factory::getRepository('order');
        $data = $repo->findWithDependenciesById($id);
        
        $this->view->data_chiusura_da_offerta = '';
        if($data['date_chiusura_richiesta'] == '' || $data['date_chiusura_richiesta'] == '0000-00-00')
        {
            $date_expected = $data->offer->moments[count($data->offer->moments) - 1]->expected_date;
            if($date_expected != '' && $date_expected != '0000-00-00')
            {
                $data['date_chiusura_richiesta'] = $date_expected;
                $this->view->data_chiusura_da_offerta = '(da offerta)';
            }
        }
        
        $this->view->data = $data;
        $this->view->inputNamePrefix = '';
        
        $util = new Maco_Html_Utils();
        $userModel = new Model_UsersMapper();
        $rcos = $userModel->getRcos();
        $rcos = $util->parseDbRowsForSelectElement($rcos, 'user_id', 'username', array('nome', 'cognome'));
        
        if($data['valore_g_uomo'] != '' && $data['valore_g_uomo'] != 0)
        {
            $this->view->data['ore_hm'] = number_format($data->offer['total_raw'] / $data['valore_g_uomo'] * 8, 2);
            $this->view->data['gg_hm'] = number_format($data->offer['total_raw'] / $data['valore_g_uomo'], 2);
        }
        
        $this->view->rcos = array(0 => '') + $rcos;
    }
    
	/**
    * Modifica il consuntivo di una commessa
    * 
    */
    public function mcAction()
    {
        $id = $this->_request->getParam('id', false);
        
        if(!$id)
        {
            throw new Zend_Controller_Action_Exception(
                    'Necessario id per il dettaglio commessa', 404);                
        }

        $repo = Maco_Model_Repository_Factory::getRepository('order');
        $this->view->data = $repo->findWithDependenciesById($id);
        $this->view->sal_array = $this->_sal_array;
    }
    
    /**
    * Modifica dati generali commessa
    * 
    */
    public function mcommAction()
    {
        $id = $this->_request->getParam('id', false);
        
        if(!$id)
        {
            throw new Zend_Controller_Action_Exception(
                    'Necessario id per il dettaglio commessa', 404);                
        }
        
        $repo = Maco_Model_Repository_Factory::getRepository('order');
        
        $data = $repo->findWithDependenciesById($id);
        
        $data['note_da_offerta'] = '';
        if($data['note'] == '')
        {
            $data['note_da_offerta'] = '(da offerta)';
            $data['note'] = $data->offer['note'];    
        }
        
        $this->view->data = $data;
    }
    
    /**
    * Salva i nuovi dati commessa
    * 
    */
    public function mcommsAction()
    {
        $model = new Model_OrdersMapper();
        
        $this->view->result = $model->saveCommessa($_POST);
        
        $this->view->message = ($this->view->result)
            ? 'dati consuntivo aggiornati'    
            : 'impossibile aggiornare il consuntivo';
    }

    /**
    * Modifica il consuntivo di una commessa
    *
    */
    public function mcmAction()
    {
        $id_moment = $this->_request->getParam('id_moment', false);
        $id_order = $this->_request->getParam('id_order', false);

        if(!$id_moment || !$id_order)
        {
            throw new Zend_Controller_Action_Exception(
                    'Necessario id per il dettaglio momento', 404);
        }

        $model = new Model_OrdersMapper();

        $data = $model->getMomentDetail($id_moment, $id_order);

        // si può chiudere il momento?
        $can_close = true;
        foreach($data['order']->offer->moments as $moment)
        {
            if($moment->index >= $data['index'])
            {
                break;
            }
            if($moment->done != 1)
            {
                $can_close = false;
            }
        }

        $this->view->data = $data;
        $this->view->id_order = $id_order;
        $this->view->can_close = $can_close;
    }
    
	/**
    * Salva i nuovi dati di consuntivo
    * 
    */
    public function mcsAction()
    {
        $model = new Model_OrdersMapper();
        
        $this->view->result = $model->saveConsuntivo($_POST);
        
        $this->view->message = ($this->view->result)
            ? 'dati consuntivo aggiornati'    
            : 'impossibile aggiornare i dati commessa';
    }

    /**
    * Salva i nuovi dati di consuntivo momento
    *
    */
    public function mcmsAction()
    {
        $model = new Model_OrdersMapper();

        $result = $model->saveConsuntivoPerMoment($_POST);

        switch($result)
        {
            case 1:
                $this->view->result = true;
                $this->view->message = 'dati consuntivi aggiornati';
                break;
            case 2:
                $this->view->result = true;
                $this->view->message = 'dati consuntivi aggiornati e <b>fase di lavorazione chiusa</b>';
                break;
            case 3:
                $this->view->result = true;
                $this->view->message = 'dati consuntivi aggiornati, <b>fase di lavorazione chiusa</b> e <h3>commessa completata</h3>';
                break;
            case false:
            default:
                $this->view->result = false;
                $this->view->message = 'impossibile aggiornare il consuntivo';
                break;
        }
    }
    
    /**
     * Chiude una fase e imposta la data effettiva
     * 
     * @return void
     */
    public function closefaseAction()
    {
    	$id_moment = $this->_request->getParam('id');
    	
    	if(!$id_moment)
        {
            throw new Zend_Controller_Action_Exception(
                    'Necessario id per la chiusura fase', 404);                
        }
        
        $this->view->id_moment = $id_moment;
    }
    
	/**
    * Salva la chiusura di una fase lavorativa
    * 
    */
    public function closefasesAction()
    {
        $model = new Model_OrdersMapper();
        
        $this->view->result = $model->closeFase($_POST);

        $this->view->message = ($this->view->result)
            ? 'fase chiusa'    
            : 'impossibile chiudere la fase';
    }
    
    public function cancelAction()
    {
        $id = $this->_request->getParam('id', false);
        
        if(!$id)
        {
            throw new Zend_Controller_Action_Exception(
                    'Necessario id per il dettaglio commessa', 404);                
        }
        
        $repo = Maco_Model_Repository_Factory::getRepository('order');
        
        $repo->cancelOrder($id);
        
        $this->_redirect('orders/detail/id/' . $id);
    }
    
    public function suspendAction()
    {
        $id = $this->_request->getParam('id', false);
        
        if(!$id)
        {
            throw new Zend_Controller_Action_Exception(
                    'Necessario id per il dettaglio commessa', 404);                
        }
        
        $repo = Maco_Model_Repository_Factory::getRepository('order');
        
        // cancel status id = 4
        
        $repo->suspendOrder($id);
        
        $this->_redirect('orders/detail/id/' . $id);
    }
    
    public function resumeAction()
    {
        $id = $this->_request->getParam('id', false);
        
        if(!$id)
        {
            throw new Zend_Controller_Action_Exception(
                    'Necessario id per il dettaglio commessa', 404);                
        }
        
        $repo = Maco_Model_Repository_Factory::getRepository('order');
        
        // cancel status id = 4
        
        $repo->resumeOrder($id);
        
        $this->_redirect('orders/detail/id/' . $id);
    }
    
    /**
    * Aggiorna pianificazione commessa
    * 
    */
    public function upAction()
    {
        $id = $this->_request->getParam('id', false);
        
        if(!$id)
        {
            throw new Zend_Controller_Action_Exception(
                    'Necessario id per il dettaglio commessa', 404);                
        }
        
        $repo = Maco_Model_Repository_Factory::getRepository('order');
        $data = $repo->findWithDependenciesById($id);
        
        $this->view->order = $data;
        
        if($data['valore_g_uomo'] != '' && $data['valore_g_uomo'] != 0)
        {
            $this->view->order['ore_hm'] = number_format($data->offer['total'] / $data['valore_g_uomo'], 2);
            $this->view->order['gg_hm'] = number_format($data->offer['total'] / $data['valore_g_uomo'] / 8, 2);
        }
    }
    
	/**
    * Aggiorna consuntivo commessa
    * 
    */
    public function ucAction()
    {
        $id = $this->_request->getParam('id', false);
        
        if(!$id)
        {
            throw new Zend_Controller_Action_Exception(
                    'Necessario id per il dettaglio commessa', 404);                
        }
        
        $repo = Maco_Model_Repository_Factory::getRepository('order');
        $data = $repo->findWithDependenciesById($id);
        
        $this->view->order = $data;
    }

    /**
    * Aggiorna consuntivo commessa di un momento di lavorazione
    *
    */
    public function ucmAction()
    {
        $id_moment = $this->_request->getParam('id_moment', false);
        $id_order = $this->_request->getParam('id_order', false);

        if(!$id_moment || !$id_order)
        {
            throw new Zend_Controller_Action_Exception(
                    'Necessario id per il dettaglio commessa', 404);
        }

        $repo = Maco_Model_Repository_Factory::getRepository('moment');

        $model = new Model_OrdersMapper();

        $data = $model->getMomentDetail($id_moment, $id_order);
        
        $this->view->data = $data;
    }

    /**
    * Aggiorna consuntivo commessa di un momento di lavorazione
    *
    */
    public function ucgAction()
    {
        $id_order = $this->_request->getParam('id_order', false);

        if(!$id_order)
        {
            throw new Zend_Controller_Action_Exception(
                    'Necessario id per il dettaglio commessa', 404);
        }

        $repo = Maco_Model_Repository_Factory::getRepository('order');

        $order = $repo->findWithDependenciesById($id_order);

        $data = array(
            'c_ore_studio' => 0,
            'c_ore_azienda' => 0,
            'c_ore_certificazione' => 0,
            'c_n_incontri' => 0,
            'c_ore_viaggio' => 0,
            'c_n_km' => 0,
            'c_costo_km' => 0,
            'pl' => array(),
            'p_valore_g_uomo' => 0,
            'c_costo_km_tot' => 0,
            'valore_reale_tot' => 0,
            'c_pl_importo_tot' => 0
        );

        $count_costo_km = 0;
        $count_v_g_u = 0;
        foreach($order->offer->moments as $moment)
        {
            $data['c_ore_studio'] += $moment['c_ore_studio'];
            $data['c_ore_azienda'] += $moment['c_ore_azienda'];
            $data['c_ore_certificazione'] += $moment['c_ore_certificazione'];
            $data['c_n_incontri'] += $moment['c_n_incontri'];
            $data['c_ore_viaggio'] += $moment['c_ore_viaggio'];
            $data['c_n_km'] += $moment['c_n_km'];
            if($moment['c_costo_km'] && $moment['c_costo_km'] != '' && $moment['c_costo_km'] != 0 && $moment['c_costo_km'] != '0.00')
            {
                $data['c_costo_km'] += $moment['c_costo_km'];
                $data['c_costo_km_tot'] += $moment['c_costo_km'] * $moment['c_n_km'];
                ++$count_costo_km;
            }

            $data['c_pl_importo_tot'] += $moment['c_pl_importo'];

            $data['pl'][] = array(
                'c_pl_note' => $moment['c_pl_note'],
                'c_pl_importo' => $moment['c_pl_importo'],
            );
            if($moment['p_valore_g_uomo'] && $moment['p_valore_g_uomo'] != '' && $moment['p_valore_g_uomo'] != 0 && $moment['p_valore_g_uomo'] != '0.00')
            {
                $data['p_valore_g_uomo'] += $moment['p_valore_g_uomo'];
                $data['valore_reale_tot'] += ($moment['c_ore_studio'] + $moment['c_ore_azienda'] + $moment['c_ore_certificazione']) * $moment['p_valore_g_uomo'] / 8;
                ++$count_v_g_u;
            }
        }
        if($count_costo_km > 0)
        {
            $data['c_costo_km'] /= $count_costo_km;
        }
        else
        {
               $data['c_costo_km'] = 0;
        }
        if($count_v_g_u > 0)
        {
            $data['p_valore_g_uomo'] /= $count_v_g_u;
        }
        else
        {
            $data['p_valore_g_uomo'] = 0;
        }


        $this->view->data = $data;
    }

    /**
    * Salva la nuova pianificazione
    * 
    */
    public function mpsAction()
    {
        $model = new Model_OrdersMapper();
        
        $this->view->result = $model->savePianificazione($_POST);
        
        $this->view->message = ($this->view->result)
            ? 'pianificazione aggiornata'    
            : 'impossibile aggiornare la pianificazione';
    }
    
     /**
    * Ritorna le aziende che hanno offerte per questa internal
    * 
    */
    public function entiAction()
    {
        $db = Zend_Registry::get('dbAdapter');
        
        $enti = $db->fetchCol('select distinct ente from orders');
        
        $response = array();
        
        foreach($enti as $v)
        {
            if($v)
            {
                $response[] = array($v, $v);
            }
        }
        
        echo json_encode($response);
        exit;
    }
    
    public function detailAction()
    {
        $id = $this->_request->getParam('id', false);

        $repo = Maco_Model_Repository_Factory::getRepository('order');
        
        if(!$id)
        {
            throw new Zend_Controller_Action_Exception(
                    'Necessario id per il dettaglio commessa', 404);                
        }

        $order = $repo->findWithDependenciesById($id);
        
        if(!$order)
        {
            $this->_helper->getHelper('FlashMessenger')->addMessage('Commessa non trovata');
            $this->_redirect('orders/list');
        }

        $budget = $order->offer->offer_importo;
        if($order->offer->sconto != '')
        {
            $budget = $budget - ($budget * $order->offer->sconto / 100);
        }
        
        $this->view->budget = $budget;
        
        $this->view->pianificazioneMenu = true;

        $this->view->order = $order;

        $filesMapper = new Model_FilesMapper();
        $this->view->orderPdf = $filesMapper->getOrderPdf($order);

        //   $this->view->tasksList = $this->_getTasksForOrder($order->order_id);
        $this->view->tasks = $this->_getTasksForOfferSimple($order->offer->offer_id);
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
        $options['where']['tasks.id_order'] = $id;
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
}
