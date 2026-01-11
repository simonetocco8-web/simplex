<?php

class ContactsController extends Zend_Controller_Action
{
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('list', 'html')
                    ->addActionContext('list', 'json')
                    ->addActionContext('numbersfor', 'json')
                    ->addActionContext('addressesfor', 'json')
                    ->addActionContext('mailsfor', 'json')
                    ->addActionContext('contactsbycompanyforselect', 'html')
                    ->initContext();
    }

    public function indexAction()
    {
        $this->_forward('list');
    }
    
    public function contactsbycompanyforselectAction()
    {
        $model = new Model_ContactsMapper();
        
        $id = (int)$this->_request->getParam('id', 0);

        $contacts = $model->getListByCompanyId($id);
        
        // TODO: controllare se metto testo nel campo bianco
        $this->view->contacts = array('0' => '') + $contacts;
    }
    
    public function listAction()
    {
        $model = new Model_ContactsMapper();

        $sort = $this->_request->getParam('_s', false);
        $dir = $this->_request->getParam('_d', 'ASC');

        $search = $_POST;

        $contacts = $model->getList($sort, $dir, $search);

        $g = new Maco_DaGrid_Grid();
        $g->addColumns(array(
            array(
                'label' => 'Cognome',
                'field' => 'cognome',
                'class' => 'link', // class -> link set renderer -> link
                'options' => array(
                    'linksData' => array(
                        'id' => 'id'
                    ),
                    'base' => '/contacts/detail'
                )
            ),/*
            array(
                'label' => 'Nome e Cognome',
                'field' => 'cognome',
                'class' => 'merge',
                'options' => array(
                    'fields' => array(
                        'nome',
                        'cognome',
                    ),
                )
            ),*/
            array(
                'field' => 'nome'
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
            ),
            array(
                'field' => 'internals',
                'renderer' => 'array',
                'sortable' => false
            ),
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

        $this->view->dag = $g;

        if($this->_request->isXmlHttpRequest())
        {
            $this->_helper->viewRenderer->setNoRender();
            $g->deploy();
        }
    }
    
    public function tblAction()
    {
        $search = array('nome' => $this->_request->getParam('search'), 'cognome' => $this->_request->getParam('search'));
        
        $id_company = (int)$this->_request->getParam('id_company', false);
        
        $model = new Model_ContactsMapper();
        
        if($id_company)
        {
            $values = $model->getListByCompanyId($id_company, 'cognome', 'ASC', $search);
        }
        else
        {
            $values = $model->getList('cognome', 'ASC', $search, 'OR');
        }
        
        $result = array();
        
        foreach($values as $v)
        {
            $result[] = array($v['contact_id'], $v['nome'] . ' ' . $v['cognome']);
        }
        
        echo json_encode($result);
        exit;
    }
    
    public function numbersforAction()
    {
        $search = array('number' => $this->_request->getParam('search'));
        
        $id_contact = (int)$this->_request->getParam('id_contact', false);
        $id_company = (int)$this->_request->getParam('id_company', false);

        $telephoneRepo = Maco_Model_Repository_Factory::getRepository('telephone');
        //$values = $telephoneRepo->findByCompanyAndContact($id_company, $id_contact);
        $values = $telephoneRepo->findByContact($id_contact);

        $result = array();
        
        foreach($values as $v)
        {
            $result[] = array($v['number'], $v['number']);
        }
        
        echo json_encode($result);
        exit;
    }
    
    public function mailsforAction()
    {
        $search = array('number' => $this->_request->getParam('search'));
        
        $id_contact = (int)$this->_request->getParam('id_contact', false);
        $id_company = (int)$this->_request->getParam('id_company', false);

        $mailRepo = Maco_Model_Repository_Factory::getRepository('mail');

        //$values = $mailRepo->findByCompanyAndContact($id_company, $id_contact);
        $values = $mailRepo->findByContact($id_contact);

        $result = array();
        
        foreach($values as $v)
        {
            $result[] = array($v['mail'], $v['mail']);
        }
        
        echo json_encode($result);
        exit;
    }
    
    public function addressesforAction()
    {
        $search = array('address' => $this->_request->getParam('search'));
        
        $id_contact = (int)$this->_request->getParam('id_contact', false);
        $id_company = (int)$this->_request->getParam('id_company', false);

        //$model = new Model_AddressesMapper();

        $result = array();
        $addressRepo = Maco_Model_Repository_Factory::getRepository('address');

        if($id_contact)
        {
            //$values = $model->fetchByContactId($id_contact, $search);
            $values = $addressRepo->findByContact($id_contact, $id_company);

            foreach($values as $v)
            {
                $result[] = array($v['via'] . ', ' . $v['numero'] . ' - ' . $v['localita'] . '(' . $v['cap'] . ')', '<span class="info">Personale:</span> ' . $v['via'] . ', ' . $v['numero'] . ' - ' . $v['localita'] . ' (' . $v['cap'] . ')');
            }
        }

        if(!$id_company && $id_contact)
        {
            $contactRepo = Maco_Model_Repository_Factory::getRepository('contact');
            $contact = $contactRepo->find($id_contact);
            if($contact->id_company)
            {
                $id_company = $contact->id_company;
            }
        }

        if($id_company)
        {
            $values = $addressRepo->findByCompany($id_company);
            foreach($values as $v)
            {
                $result[] = array($v['via'] . ', ' . $v['numero'] . ' - ' . $v['localita'] . '(' . $v['cap'] . ')', '<span class="info">Azienda:</span> ' . $v['via'] . ', ' . $v['numero'] . ' - ' . $v['localita'] . ' (' . $v['cap'] . ')');
            }
        }
        
        echo json_encode($result);
        exit;
    }
    
    
    public function editAction()
    {
        $commonModel = new Model_Common();
        
        // carico le province
        $province = $commonModel->getArrayForSelectElementSimple('province', 'id', 'nomeprovincia');
        $this->view->province = array(0 => '') + $province;

        // carico le aziende interne
        $internals = $commonModel->fetchAllSingleTableDefault('internals');
        $this->view->internals = $internals;
        unset($commonModel);

        $id = (int)$this->_request->getParam('id', false);

        $model = new Model_ContactsMapper();

        if($id)
        {
            $this->view->data = $model->getDetail($id);
            $this->view->contentTitle = "Modifica Contatto";
        }
        else
        {
            // TODO potremmo recuperare i dati validi inseriti
            //$session = new Zend_Session_Namespace('contacts_edit');
            
            //if(isset($session->data))
            {
                //$this->view->data = $session->data;
                //Zend_Debug::dump($session->data);
                
                //$model = new Model_ContactsMapper();
               // Zend_Debug::dump($model->getDetail(16));
                //unset($session->data);
            }
            //else
            {
                $this->view->data = $model->getEmptyDetail();
            }
            
            $this->view->contentTitle = "Nuovo Contatto";
        }

        $this->view->inputNamePrefix = '';
        
        $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();
    }
    
    public function saveAction()
    {
        $model = new Model_ContactsMapper();
     
        $result = $model->save($_POST);

        if(is_array($result))
        {
            foreach($result as $msg)
                $this->_helper->getHelper('FlashMessenger')->addMessage('Inserimento dati non riuscito');
                $this->_helper->getHelper('FlashMessenger')->addMessage($msg);
            
                //$session = new Zend_Session_Namespace('contacts_edit');
                //$session->data = $_POST;
                
                $this->_redirect('contacts/edit');
        }
        else
        {
            $this->_redirect('contacts/detail/id/' . $result);
            
            //$this->_helper->layout->disableLayout();
            //$this->_helper->viewRenderer->setNoRender();
            
        }
    }
    
    public function detailAction()
    {
        $id = (int)$this->_request->getParam('id', false);

        if(!$id)
        {
            throw new Zend_Controller_Action_Exception('Necessario id per il dettaglio contatto', 404);
        }

        $model = new Model_ContactsMapper();

        $this->view->data = $model->getDetail($id);
    }
    
    public function deleteAction()
    {
        $ids = $this->_request->getParam('id', false);

        if(!$ids)
        {
            throw new Zend_Controller_Action_Exception('Necessario id per l\'eliminazione utente', 404);
        }

        $model = new Model_ContactsMapper();

        $result = $model->delete($ids);

        $this->_redirect('contacts/list');
    }
}
