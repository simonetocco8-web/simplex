<?php
/**
 * Created by Marcello Stani.
 * Date: 29/07/13
 * Time: 15.07
 */

class Model_Modulistica{

    public function exec($options = array())
    {
        $action = $options['action'];

        switch($action)
        {
            case 'companies':
                $this->exec_companies($options);
                break;

            case 'tasks':
                $this->exec_tasks($options);
                break;
        }
    }

    public function exec_companies($options)
    {
        $w = $options['w'];

        switch($w)
        {
            case 'ci':
                $this->export_companies_ci($options);
                break;
        }
    }

    public function exec_tasks($options)
    {
        $w = $options['w'];

        switch($w)
        {
            case 'ca':
                $this->export_tasks_ca($options);
                break;
        }
    }

    public function export_companies_ci($options)
    {
        include(LIBRARY_PATH . '/Tbs/tbs_class.php');
        include(LIBRARY_PATH . '/Tbs/plugins/tbs_plugin_opentbs.php');

        $filesMapper = new Model_FilesMapper();

        $company_id = $options['id'];

        $repo = Maco_Model_Repository_Factory::getRepository('company');
        $company = $repo->findWithDependencies(array('company_id' => $company_id));

        $template_name = 'ci.docx';

        if(!$filesMapper->pathExists($filesMapper->getTemplatePath(false) . $template_name))
        {
            $this->_helper->getHelper('FlashMessenger')->addMessage('Il file di template per l\'esportazione della carta intestata non esiste.');
            $this->_redirect('companies/detail/id/' . $company_id);
        }

        $tbs = new clsTinyButStrong; // new instance of TBS
        $tbs->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin

        $tbs->LoadTemplate($filesMapper->getTemplatePath() . $template_name);

        global $company_name;
        global $company_address;

        $company_name = $company->ragione_sociale;
        $company_address = count($company->addresses) > 0
            ? $company->addresses[0]->getCleanAddress()
            : '';
        /*
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

        */

        $file_name = 'carta-intestata.docx';
        $tbs->Show(OPENTBS_DOWNLOAD, $file_name);
        exit;
    }

    public function export_tasks_ca($options)
    {
        include(LIBRARY_PATH . '/Tbs/tbs_class.php');
        include(LIBRARY_PATH . '/Tbs/plugins/tbs_plugin_opentbs.php');

        $filesMapper = new Model_FilesMapper();

        $task_id = $options['id'];

        $repo = Maco_Model_Repository_Factory::getRepository('task');
        $task = $repo->find($task_id);

        $userRepo = Maco_Model_Repository_Factory::getRepository('user');
        $user = $userRepo->find($task->id_who);

        $template_name = 'ca.docx';

        if(!$filesMapper->pathExists($filesMapper->getTemplatePath(false) . $template_name))
        {
            $this->_helper->getHelper('FlashMessenger')->addMessage('Il file di template per l\'esportazione della conferma appuntamento non esiste.');
            $this->_redirect('tasks/detail/id/' . $task_id);
        }

        $tbs = new clsTinyButStrong; // new instance of TBS
        $tbs->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin

        $tbs->LoadTemplate($filesMapper->getTemplatePath() . $template_name);


        global $date;
        global $time;
        global $address;
        global $who;

        $date = Maco_Utils_DbDate::fromDb($task->when, Maco_Utils_DbDate::DBDATE_DATE);
        $time = Maco_Utils_DbDate::fromDb($task->when, Maco_Utils_DbDate::DBDATE_TIME);

        $who = '';
        if($user->contact)
        {
            if($user->contact->nome)
            {
                $who = $user->contact->nome;
            }
            if($user->contact->cognome)
            {
                $who .= ' ' . $user->contact->cognome;
            }
        }
        if($who == '')
        {
            $who = $user->username;
        }

        $address = $task->subject_data;

        $file_name = 'conferma-appuntamento.docx';
        $tbs->Show(OPENTBS_DOWNLOAD, $file_name);
        exit;
    }
}