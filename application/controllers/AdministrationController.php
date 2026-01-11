<?php

class AdministrationController extends Zend_Controller_Action
{
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('tobe', 'html')
                ->addActionContext('tobe', 'json')
                ->addActionContext('production', 'json')
                ->addActionContext('production', 'html')
                ->addActionContext('open', 'json')
                ->addActionContext('open', 'html')
                ->addActionContext('closed', 'html')
                ->addActionContext('closed', 'json')
                ->addActionContext('tranches', 'html')
                ->addActionContext('tranches', 'json')
                ->addActionContext('invoices', 'html')
                ->addActionContext('invoices', 'json')
                ->addActionContext('tranche', 'html')
                ->addActionContext('np', 'html')
                ->addActionContext('nps', 'json')
                ->addActionContext('nc', 'html')
                ->addActionContext('ncs', 'json')
                ->addActionContext('pc', 'html')
                ->addActionContext('pcs', 'json')
                ->initContext();
    }
    public function indexAction()
    {
        $this->_forward('tobe');
    }

    public function OLD_productionAction()
    {
        $options = array();
        $this->doList($options, 'Produzione');
    }

    public function tobeAction()
    {
        //$options['search'] = array('moments.fatturato' => 0);

        //$options['without-invoice'] = true;
        $options = array('search' => array('fatturazione' => 1));
        $this->doList($options, 'Momenti di Fatturazione');
    }

    public function openAction_()
    {
        $options['search'] = array('moments.fatturato' => 1, 'moments.closed' => 0);

        $this->doList($options);
    }

    public function closedAction_()
    {
        $options['search'] = array('moments.closed' => 1);

        $this->doList($options);
    }


    public function draftAction()
    {
        $invoices_repo = Maco_Model_Repository_Factory::getRepository('invoice');

        // if is post save the data
        if($this->_request->isPost())
        {
            $result = $invoices_repo->saveFromData($_POST);

            if(!is_array($result))
            {
                $this->_redirect('administration/invoice/id/' . $result);
            }
            $layout = Zend_Layout::getMvcInstance();
            $layout->flashMessages = $result;
            $this->view->data = $_POST;
        }

        $id_invoice = false;
        $ids = $this->_request->getParam('id', false);

        if(!$ids)
        {
            // vediamo se abbiamo passato un id fattura
            $id_invoice = $this->_request->getParam('idv', false);

            if(!$id_invoice)
            {
                throw new Zend_Controller_Action_Exception('Necessario id', 404);
            }
        }

        $repo = Maco_Model_Repository_Factory::getRepository('moment');

        if($id_invoice)
        {
            $invoice = $invoices_repo->findWithDependenciesById($id_invoice);
            $ids = $repo->getMomentsIdForInvoice($id_invoice);
        }
        else
        {
            $invoice = $invoices_repo->getNewInvoice();
            if(!is_array($ids))
            {
                $ids = array($ids);
            }
        }

        $moments = array();

        foreach($ids as $id)
        {
            $moments[] = $repo->findWithDependencies($id);
        }

        $this->view->moments = $moments;

        $commonModel = new Model_Common();

        $pagamenti = $commonModel->getArrayForSelectElementSimple('pagamenti', 'pagamento_id', 'name');
        $this->view->pagamenti = $pagamenti;
        $this->view->ibans = $commonModel->getArrayForSelectElementSimple('ibans', 'iban_id', array('bank', 'iban'));

        $this->view->invoice = $invoice;

        if($id_invoice)
        {
            $this->view->id_tipo_pagamento = $invoice->id_tipo_pagamento;
        }
        else
        {
            $this->view->id_tipo_pagamento = $moments[0]->order->offer->id_pagamento;
        }

        return;

        $this->view->id = $id;

        $model = new Model_Administration();

        $data = $model->getDetail($id);

        $this->view->data = $data;
    }

    public function dofatturaAction()
    {
        $id = $this->_request->getParam('id', false);

        if(!$id)
        {
            throw new Zend_Controller_Action_Exception('Necessario id', 404);
        }

        $model = new Model_Administration();

        $res = $model->doFattura($id);

        //if($res)
        {
            $this->_redirect('administration/detail/id/' . $id);
        }
    }

    public function closeAction()
    {
        $id = $this->_request->getParam('id', false);

        if(!$id)
        {
            throw new Zend_Controller_Action_Exception('Necessario id', 404);
        }

        $model = new Model_Administration();

        $res = $model->close($id);

        //if($res)
        {
            $this->_redirect('administration/detail/id/' . $id);
        }
    }

    public function doList($options, $label = '')
    {
        $model = new Model_Administration();

        if($this->_request->isXmlHttpRequest())
        {
            $perpage = $this->_request->getParam('perpage', Zend_Registry::getInstance()->get('config')->entries_per_page);
            $options['perpage'] = $perpage;
            $moments = $model->getMoments($options);
        }
        else
        {
            $moments = array();
        }

        $this->view->total = $model->getTotal($options);

        /*
        $tot = 0;
        foreach($moments as $m)
        {
            $tot += $m['importo'];
        }
        $this->view->total = $tot;
        */

        $g = new Maco_DaGrid_Grid();
        $g->addColumns(array(
            array(
                'label' => '',
                'class' => 'checkbox',
                'field' => 'moment_id',
                'options' => array(
                    'cb_name' => 'mdraft[]',
                ),
                'search' => false
            ),
            array(
                'label' => 'Commessa',
                'field' => 'code_order',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'order_id'
                     ),
                    'base' => '/orders/detail',
                    'separator' => '-'
                )
            ),
            array(
                'label' => 'Offerta',
                'field' => 'code_offer',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'offer_id'
                     ),
                    'base' => '/offers/detail',
                    'separator' => '-'
                )
            ),
            array(
                'label' => 'Azienda',
                'field' => 'ragione_sociale',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'company_id'
                     ),
                    'base' => '/companies/detail'
                )
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
                'field' => 'tipologia',
                'label' => 'Momento di Fatturazione',
            ),
            array(
                'field' => 'expected_date',
                'label' => 'Data',
                'renderer' => 'datetime',
                'search' => 'datetime',
            ),
            array(
                'field' => 'importo',
            ),
            /*
            array(
                'field' => 'fatturato',
                'renderer' => 'bool',
                'search' => 'bool',
            )*/
        ));
        /*
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
                                'id' => 'moment_id',
							),
                            'base' => '/administration/draft',
                            'img' => '/img/edit.png',
                            'title' => 'Modifica',
						),
					)
				)
			)
		));
        */

        // search elements
        $searchRepo = new Model_Moment_Search();

        $r = new Maco_DaGrid_Render_Html();
        $r->setView($this->view);
        $r->withFastSearch = false;
        $r->addClasses(array('fluid', 'listview'));
        //        $r->setId('users');
        $r->setTrIdPrefix('man_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($moments);
        $g->setRenderer($r);
        $g->setSource($s);
        //$g->setPaginator(true);
        $g->setRowsPerPage($this->_request->getParam('perpage', 25));
        $g->setAdvancedSearch($searchRepo);
        $g->setId('moments');

        $this->view->dag = $g;

        $this->view->label = $label;

        //   if($this->_request->isXmlHttpRequest())
        {
            //$this->_helper->viewRenderer->setNoRender();
            //$g->deploy();
        }
        //else
        {
            $this->render('list');
        }
    }

    public function productionAction()
    {
        $options = array();
        $model = new Model_Administration();

        if($this->_request->isXmlHttpRequest())
        {
            $sort = $this->_request->getParam('_s', false);
            if($sort)
            {
                $dir = $this->_request->getParam('_d', 'ASC');
                $options['_order'] = $sort . ' ' . $dir;
            }

            $perpage = $this->_request->getParam('perpage',
                Zend_Registry::getInstance()->get('config')->entries_per_page);
            $options['perpage'] = $perpage;

            $moments = $model->getMoments($options);
            $this->view->total = $model->getTotal($options);
        }
        else
        {
            $moments = array();
            $this->view->total = 0;
        }

        /*
        $tot = 0;
        foreach($moments as $m)
        {
            $tot += $m['importo'];
        }
        $this->view->total = $tot;
        */

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
                    'separator' => '-'
                )
            ),
            array(
                'label' => 'Offerta',
                'field' => 'code_offer',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'offer_id'
                    ),
                    'base' => '/offers/detail',
                    'separator' => '-'
                )
            ),
            array(
                'label' => 'Azienda',
                'field' => 'ragione_sociale',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'company_id'
                    ),
                    'base' => '/companies/detail'
                )
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
                'field' => 'tipologia',
                'label' => 'Momento di Fatturazione',
            ),
            array(
                'field' => 'expected_date',
                'label' => 'Data',
                'renderer' => 'datetime',
                'search' => 'datetime',
            ),
            array(
                'field' => 'fatturato',
                'renderer' => 'bool',
                'search' => 'bool',
            ),
            array(
                'label' => 'Ore Studio',
                'field' => 'c_ore_studio',
            ),
            array(
                'label' => 'Ore Azienda',
                'field' => 'c_ore_azienda',
            ),
            array(
                'label' => 'Ore Certificazione',
                'field' => 'c_ore_certificazione',
            ),
            array(
                'label' => 'Totale Ore',
                'field' => 'c_ore_total',
            ),
            array(
                'label' => '# Incontri',
                'field' => 'c_n_incontri',
            ),
            array(
                'label' => 'Km A/R Totali',
                'field' => 'c_n_km',
            ),
            array(
                'label' => '# Ore Viaggio',
                'field' => 'c_ore_viaggio',
            ),
            array(
                'label' => 'Costo / Km',
                'field' => 'c_costo_km',
            ),
            array(
                'field' => 'importo',
            ),
            array(
                'label' => 'Iporto / Ore',
                'field' => 'importo_per_ora',
            ),
        ));

        // search elements
        $searchRepo = new Model_Moment_Search_Production();

        $r = new Maco_DaGrid_Render_Html();
        $r->setView($this->view);
        $r->withFastSearch = false;
        $r->addClasses(array('fluid', 'listview'));
        //        $r->setId('users');
        $r->setTrIdPrefix('man_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($moments);
        $g->setRenderer($r);
        $g->setSource($s);
        //$g->setPaginator(true);
        $g->setRowsPerPage($this->_request->getParam('perpage', 25));
        $g->setAdvancedSearch($searchRepo);
        $g->setId('moments');

        $this->view->dag = $g;

        $this->view->label = 'Produzione';

        $this->render('production');
    }

    public function invoicesAction()
    {
        $sort = $this->_request->getParam('_s', false);
        $dir = $this->_request->getParam('_d', 'ASC');

        $search = $_GET;

        /** @var $invoices_repo Model_Invoice_Repository */
        $invoices_repo = Maco_Model_Repository_Factory::getRepository('invoice');
        /*
                if(!isset($search['a_type']) || empty($search['a_type']))
                {
                    $search['a_type'] = array(0);
                }
                if((!isset($search['status']) || trim($search['status']) == '') && (!isset($search['a_status']) || empty($search['a_status'])))
                {
                    $search['a_status'] = array(0);
                    $_POST['status'] = '0';
                }
        */
        $perpage = $this->_request->getParam('perpage',
                        Zend_Registry::getInstance()->get('config')->entries_per_page);
        if($this->_request->isXmlHttpRequest())
        {
            $sort = $this->_request->getParam('_s', false);
            $dir = $this->_request->getParam('_d', 'ASC');
            if($sort)
            {
                $options['_order'] = $sort . ' ' . $dir;
            }


            $options['perpage'] = $perpage;

            $invoices = $invoices_repo->getInvoices($sort, $dir, $search, null, $perpage);
        }
        else
        {
            $invoices = array();
        }

        $tot = 0;
        foreach($invoices as $i)
        {
            $tot += $i['importo'];
        }
        $this->view->total = $tot;

        $g = new Maco_DaGrid_Grid();
        $g->addColumns(array(
            array(
                'label' => 'Codice',
                'field' => 'code_invoice',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'invoice_id'
                    ),
                    'base' => '/administration/invoice'
                )
            ),
            array(
                'label' => 'Data Emissione',
                'field' => 'date_invoice',
                'renderer' => 'datetime'
            ),
            array(
                'label' => 'Data Scadenza',
                'field' => 'date_end',
                'renderer' => 'datetime'
            ),
            array(
                'field' => 'ragione_sociale',
                'label' => 'Azienda'
            ),
            array(
                'field' => 'importo',
                'label' => 'importo',
            ),
            array(
                'field' => 'status',
                'label' => 'Chiusa',
                'renderer' => 'bool',
                'search' => 'bool'
            ),
        ));



        $searchRepo = new Model_Invoice_Search();

        $r = new Maco_DaGrid_Render_Html();
        $r->withFastSearch = false;
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        //        $r->setId('users');
        $r->setTrIdPrefix('inv_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($invoices);
        $g->setRenderer($r);
        $g->setSource($s);
        $g->setAdvancedSearch($searchRepo);
        //$g->setPaginator(true);
        $g->setRowsPerPage($perpage);
        $g->setId('invoices');

        $this->view->dag = $g;

        if($this->_request->isXmlHttpRequest())
        {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy();
        }
    }

    public function exportAction()
    {
        $invoice_id = $this->_request->getParam('id', false);

        if(!$invoice_id)
        {
            throw new Zend_Controller_Action_Exception(
                'Necessario id fattura per la esportazione', 404);
        }

        include(LIBRARY_PATH . '/Tbs/tbs_class.php');
        include(LIBRARY_PATH . '/Tbs/plugins/tbs_plugin_opentbs.php');

        $filesMapper = new Model_FilesMapper();

        $repo = Maco_Model_Repository_Factory::getRepository('invoice');
        $invoice = $repo->findWithDependenciesById($invoice_id);

        //$template_name = 'template_' . $offer->id_service . '_' . $offer->id_subservice . '.docx';

        $template_name = 'fattura-2.docx';

        if(!$filesMapper->pathExists($filesMapper->getTemplatePath(false) . $template_name))
        {
            $this->_helper->getHelper('FlashMessenger')->addMessage('Il file di template per l\'esportazione dell\'offerta non esiste.');
            $this->_redirect('administration/invoice/id/' . $invoice_id);
            //throw new Zend_Controller_Action_Exception('file di template non presente', 404);
        }

        $tbs = new clsTinyButStrong; // new instance of TBS
        $tbs->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin

        $tbs->LoadTemplate($filesMapper->getTemplatePath() . $template_name);

        global $ragione_sociale;
        global $indirizzo1;
        global $indirizzo2;
        global $data;
        global $numero;
        global $codice_cliente;
        global $partita_iva;
        global $codice_fiscale;
        global $pagamento;
        global $totale_importi;
        global $netto_importi;
        global $totale_imponibile;
        global $totale_imposta;
        global $totale_documento;
        global $spese_trasferta;
        global $spese_varie;
        global $varie_iva;
        global $scadenze;
        global $iban;
        global $banca;

        $db = Zend_Registry::get('dbAdapter');
        $services_codes = $db->fetchRow('select ss.cod as sscod, ss.name as ssname, s.cod as scod, s.name as sname
                    from subservices ss, services s
                    where s.service_id = ss.id_service and ss.subservice_id = ' . $invoice->order->offer->id_subservice);
        $codice = $services_codes['scod'] . '-' . $services_codes['sscod'];
        $servizio = $services_codes['sname'] . ' - ' . $services_codes['ssname'];
        $ivas = array();
        $moments = array();
        $total = array(0, 0);
        foreach($invoice->moments as $idx => $moment)
        {
         //   if($moment->fatturazione)
            {
                $scontato = $moment->i_prezzo - $moment->i_prezzo * $moment->i_sconto / 100;
                $total[0] += $scontato;
                $iva = $scontato * $moment->i_iva / 100;
                $total[1] += $iva;

                $descrizione = $moment->tipologia;
                //$descrizione = $moment->tipologia;
                //$descrizione .= ' ' . $servizio;

                $importo = $moment->getImportoScontato();
                $moments[] = array(
                    'descrizione' => utf8_decode($descrizione),
                    'servizio' => utf8_decode($servizio),
                    'codice' => $codice,
                    'sconto' => $moment->i_sconto,
                    'prezzo' => number_format($moment->i_prezzo, 2, ',', '.'),
                    'importo' => number_format($scontato, 2, ',', '.'),
                    'iva' => $moment->i_iva,
                );

                $iva_key = (string) $moment->i_iva;
                if(!array_key_exists($iva_key, $ivas))
                {
                    $ivas[$iva_key] = array(
                        'code' => str_pad((string)$moment->i_iva, 3, '0', STR_PAD_LEFT),
                        'imponibile' => 0,
                        'iva' => $moment->i_iva,
                        'descrizione' => 'iva ' . $moment->i_iva . ' %',
                        'imposta' => 0
                    );
                }
                $ivas[$iva_key]['imponibile'] += $scontato;
                $ivas[$iva_key]['imposta'] += $iva;
            }
        }

        foreach($ivas as $k => $iv)
        {
            $ivas[$k]['imponibile'] = number_format($iv['imponibile'], 2, ',', '.');
            $ivas[$k]['imposta'] = number_format($iv['imposta'], 2, ',', '.');
        }

        $spese_trasferta = ($invoice->trasferta && $invoice->trasferta != '0.00') ? number_format($invoice->trasferta, 2, ',', '.') : '';
        $spese_varie = ($invoice->varie && $invoice->varie != '0.00') ? number_format($invoice->varie, 2, ',', '.') : '';
        $varie_iva = ($invoice->varie_iva && $spese_varie) ? '(IVA ' . $invoice->varie_iva . ' %)' : '';

        $totale_importi = $netto_importi = $totale_imponibile = $total[0];
        $netto_importi = number_format($netto_importi, 2, ',', '.');
        $totale_imponibile += $invoice->trasferta + $invoice->varie;
        $totale_netto = $totale_imponibile;
        $totale_imponibile = number_format($totale_imponibile, 2, ',', '.');
        $totale_imposta = $total[1];
        if($invoice->varie_iva && $invoice->varie)
        {
            $totale_imposta += $invoice->varie * $invoice->varie_iva / 100;
        }
        $totale_documento = $totale_netto + $totale_imposta;

        $totale_importi = number_format($totale_importi, 2, ',', '.');
        $totale_imposta = number_format($totale_imposta, 2, ',', '.');
        $totale_documento = number_format($totale_documento, 2, ',', '.');

        $ragione_sociale = utf8_decode($invoice->order->offer->company->ragione_sociale);

        if(count($invoice->order->offer->company->addresses) > 0)
        {
            $indirizzo1 = utf8_decode($invoice->order->offer->company->addresses[0]->getFirstPartAddress());
            $indirizzo2 = utf8_decode($invoice->order->offer->company->addresses[0]->getLastPartAddress());
        }
        else
        {
            $indirizzo1 = $indirizzo2  = '';
        }

        $data = Maco_Utils_DbDate::fromDb($invoice->date_invoice);
        $numero = $invoice->code_invoice;
        $codice_cliente = str_pad($invoice->order->offer->company->company_id, 4, '0', STR_PAD_RIGHT);
        $partita_iva = $invoice->order->offer->company->partita_iva;
        $codice_fiscale = $invoice->order->offer->company->cf;
        $pagamento = utf8_decode($invoice->tipo_pagamento_name);
        $iban = utf8_decode($invoice->iban);
        $banca = utf8_decode($invoice->bank);

        $scadenze = '';
        foreach($invoice->tranches as $tranche)
        {
            $scadenze .= Maco_Utils_DbDate::fromDb($tranche['date_expected'])
                    . ': ' . number_format($tranche['importo'], 2, ',', '.') . ' - ';
        }
        if($scadenze != '')
        {
            $scadenze = substr($scadenze, 0, -3);
        }

        $tbs->MergeBlock('a', $moments);
        $tbs->MergeBlock('b', $ivas);

        $tbs->Show(OPENTBS_DOWNLOAD, $invoice->code_invoice . '.docx');
        exit;
    }

    public function invoiceAction()
    {
        $id = $this->_request->getParam('id', false);

        $repo = Maco_Model_Repository_Factory::getRepository('invoice');

        if(!$id)
        {
            throw new Zend_Controller_Action_Exception(
                'Necessario id oppure id_offer-rev per il dettaglio offerta', 404);
        }

        $this->view->invoice = $repo->findWithDependenciesById($id);

        if($this->view->invoice->type == 1)
        {
            $this->render('credito');
        }
    }

    public function tranchesAction()
    {
        /** @var $tranches_repo Model_Tranche_Repository */
        $tranches_repo = Maco_Model_Repository_Factory::getRepository('tranche');

        $options = array();

        $tranches = $tranches_repo->getTranches($options);

        $tot = 0;
        foreach($tranches as $i)
        {
            $tot += $i['importo'];
        }
        $this->view->total = $tot;

        $g = new Maco_DaGrid_Grid();
        $g->addColumns(array(
            array(
                'label' => 'Codice',
                'field' => 'tranche_id',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'tranche_id'
                    ),
                    'base' => '/administration/tranche'
                )
            ),
            array(
                'field' => 'code_invoice',
                'label' => 'Fattura'
            ),
            array(
                'field' => 'importo',
                'label' => 'Importo'
            ),
            array(
                'field' => 'pagato',
                'label' => 'pagato'
            ),
            array(
                'label' => 'Data',
                'field' => 'date_expected',
                'renderer' => 'datetime'
            ),
            array(
                'field' => 'status',
                'label' => 'stato',
                'path_to_template' => APPLICATION_PATH . '/Simplex/DaGrid/scripts/',
                'renderer' => 'status_tranche',
                'search' => 'status_tranche'
            ),
        ));


        $r = new Maco_DaGrid_Render_Html();
        $r->setView($this->view);
        $r->addClasses(array('fluid', 'listview'));
        //        $r->setId('users');
        $r->setTrIdPrefix('tra_');
        $s = new Maco_DaGrid_Source_Array();
        $s->pushData($tranches);
        $g->setRenderer($r);
        $g->setSource($s);
        //$g->setPaginator(true);
        $g->setRowsPerPage($this->_request->getParam('perpage', 20));
        $g->setId('tranches');

        $this->view->dag = $g;

        if($this->_request->isXmlHttpRequest())
        {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy();
        }
    }

    public function trancheAction()
    {
        $id = $this->_request->getParam('id', false);

        $repo = Maco_Model_Repository_Factory::getRepository('tranche');

        if(!$id)
        {
            throw new Zend_Controller_Action_Exception(
                'Necessario id per il dettaglio pagamento', 404);
        }

        $this->view->tranche = $repo->findWithDependenciesById($id);
    }

    /**
     * Azione mostra form per un nuovo pagamento
     *
     * @return void
     */
    public function npAction()
    {
        $tranche_id = $this->_request->getParam('tid', false);

        if(!$tranche_id)
        {
            throw new Exception('Id missing', 500);
        }

        $payment_id = $this->_request->getParam('pid', false);

        $payment_repo = Maco_Model_Repository_Factory::getRepository('payment');

        $this->view->payment = $payment_id ? $payment_repo->find($payment_id) : $payment_repo->getNewPayment();

        $tranche_repo = Maco_Model_Repository_Factory::getRepository('tranche');
        $tranche = $tranche_repo->findWithDependenciesById($tranche_id);

        $this->view->tranche = $tranche;
    }

    /**
     * Azione salvataggio pagamento
     *
     * @return void
     */
    public function npsAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('payment');

        $result = $repo->saveFromData($_POST);

        if(is_array($result))
        {
            $this->view->result = false;
            $this->view->message = reset($result);
        }
        else
        {
            $this->view->result = true;
            $this->view->message = 'Operazione effettuata con successo';
            $this->view->id = $this->_request->getParam('id_tranche', 0);
        }
    }

    /**
     * Azione mostra form per una nuova nota credito
     *
     * @return void
     */
    public function ncAction()
    {
        $invoice_id = $this->_request->getParam('iid', false);

        if(!$invoice_id)
        {
            throw new Exception('Id missing', 500);
        }

        $invoices_repo = Maco_Model_Repository_Factory::getRepository('invoice');

        $this->view->invoice = $invoices_repo->findWithDependenciesById($invoice_id);

        $commonModel = new Model_Common();

        // carico i pagamenti
        $this->view->pagamenti = $commonModel->getArrayForSelectElementSimple('pagamenti', 'pagamento_id', 'name');
    }

    /**
     * Azione salvataggio pagamento
     *
     * @return void
     */
    public function ncsAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('invoice');

        $result = $repo->saveNotaCredito($_POST);

        if(is_array($result))
        {
            $this->view->result = false;
            $this->view->message = reset($result);
        }
        else
        {
            $this->view->result = true;
            $this->view->message = 'Operazione effettuata con successo';
            $this->view->id = $result;
        }
    }

    /**
     * Azione mostra form per pagamento nota credito
     *
     * @return void
     */
    public function pcAction()
    {
        $invoice_id = $this->_request->getParam('iid', false);

        if(!$invoice_id)
        {
            throw new Exception('Id missing', 500);
        }

        $this->view->invoice_id = $invoice_id;

        $commonModel = new Model_Common();
        // carico i pagamenti
        $this->view->pagamenti = array('' => '') + $commonModel->getArrayForSelectElementSimple('pagamenti', 'pagamento_id', 'name');
    }

    /**
     * Azione salvataggio pagamento
     *
     * @return void
     */
    public function pcsAction()
    {
        $repo = Maco_Model_Repository_Factory::getRepository('invoice');

        $result = $repo->pagaNotaCredito($_POST);

        if(is_array($result))
        {
            $this->view->result = false;
            $this->view->message = reset($result);
        }
        else
        {
            $this->view->result = true;
            $this->view->message = 'Operazione effettuata con successo';
            $this->view->id = $result;
        }
    }
}
