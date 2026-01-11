<?php
class LoginController extends Zend_Controller_Action
{
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('index', 'json')
                    ->addActionContext('cinternal', 'json')
                    ->initContext();
    }

	public function indexAction()
	{
		$db = $this->_getParam('db');
		$db = $this->getInvokeArg('bootstrap')->getResource('db');

		$loginForm = new Form_Login_Login();

		if ($this->_request->isPost() && $loginForm->isValid($_POST))
		{
			$adapter = new Zend_Auth_Adapter_DbTable(
			$db,
                'users',
                'username',
                'password',
                'MD5(CONCAT(?, password_salt)) AND active = 1'
                );

                $adapter->setIdentity($loginForm->getValue('uname'));

                $adapter->setCredential($loginForm->getValue('pword'));

                $auth = Zend_Auth::getInstance();

                $result = $auth->authenticate($adapter);

                if ($result->isValid())
                {
                	$data = $adapter->getResultRowObject(null, array('password', 'password_salt'));

                    // todo: bruttissimo
                	$data->userIsAdmin = ($data->id_role < 3);

                	$auth->getStorage()->write($data);

                    $repo = Maco_Model_Repository_Factory::getRepository('user');
                    $userDetail = $repo->find($data->user_id);
                    
                    /*
                    $model = new Model_UsersMapper();
                    $userDetail = $model->getDetail($data->user_id);
                    */
                    
                    if(count($userDetail->internals) > 1)
                    {
                        $data->internals = (new Maco_Html_Utils)->parseDbRowsForSelectElement($userDetail->internals, 'internal_id', 'abbr');
                        if($id_internal = $this->_request->getParam('id_internal', false))
                        {
                            $isIn = false;
                            foreach($userDetail->internals as $internal)
                            {
                                if($internal['internal_id'] == $id_internal)
                                {
                                    $isIn = true;
                                    $data->internal_id = $internal['internal_id'];
                                    $data->internal_abbr = $internal['abbr'];
                                    
                                    $data->office_id = false;
                                    $data->office_name = false;
                                    
                                    // todo: forse troppo?
                                    $data->user_object = $userDetail;
                                    
                                    //$data->internal_name = $internal['full_name'];
                                    $this->view->result = true;
                                    $this->view->message = "ottimo... accediamo!";
                                    $this->view->internal = true;
                                    break;
                                }
                            }
                            if(!$isIn){
                                $this->view->result = false;
                                $this->view->message = "Impossibile accedere al sistema!";
                            }
                        }
                        else
                        {
                            $this->view->result = true;
                            $this->view->message = "Login effettuata. Ora <b>scegli</b> l'azienda interna!<br /><br />";
                            $this->view->internals = $userDetail->internals;
                        }
                    }
                    else
                    {
                        // nell'array ci sar� sempre una azienda interna (pu� essere vuota)
                        $internal = array_shift(array_values($userDetail->internals));
                        if(is_array($internal) && $internal['internal_id'] != '')
                        {
                            $data->internals = Maco_Html_Utils::parseDbRowsForSelectElement($userDetail->internals, 'internal_id', 'abbr');
                            $data->internal_id = $internal['internal_id'];
                            $data->internal_abbr = $internal['abbr'];
                            //$data->internal_name = $internal['full_name'];
                            
                            if($internal['office_id'] != '')
                            {
                                $data->office_id = $internal['office_id'];
                                $data->office_name = $internal['office_name'];
                            }
                            else
                            {
                                $data->office_id = false;
                                $data->office_name = false;
                            }
                            
                            // todo: forse troppo?
                            $data->user_object = $userDetail;

                            $this->view->message = "ottimo... accediamo!";
                            $this->view->result = true;
                            $this->view->internal = true;
                        }
                        else
                        {
                            $this->view->internal = false;
                            $this->view->message = "L'utente non &egrave; associato a nessuna azienda interna!";
                        }
                    }
                    

                	//$this->_helper->FlashMessenger->addMessage('Successful Login');
                	//$this->_redirect('/');
                }
                else
                {
                    $this->view->result = false;
                	$this->view->message = "I dati inseriti non sono validi!";
                }
		}

        if(!$this->_request->isXmlHttpRequest())
        {
            $this->view->form = $loginForm;
            Zend_Layout::getMvcInstance()->assign('title', 'simpl.ex :: Accesso');
            $this->_helper->layout->setLayoutPath(dirname(dirname(__FILE__)) . '/layouts/scripts')
            ->setLayout('login-layout');
        }
	}

    public function cinternalAction()
    {
        $id_internal = $this->_request->getParam('id_internal', false);
        if(!$id_internal)
        {
            $this->view->result = false;
        }

        $auth = Zend_Auth::getInstance();
        $user = $auth->getIdentity();

        /*
        $model = new Model_UsersMapper();
        $userDetail = $model->getDetail($user->user_id);
        */
        
        $repo = Maco_Model_Repository_Factory::getRepository('user');
        $userDetail = $repo->find($user->user_id);

        foreach($userDetail['internals'] as $internal)
        {
            if($internal['internal_id'] == $id_internal)
            {
                $user->internal_id = $internal['internal_id'];
                $user->internal_abbr = $internal['abbr'];
                //$data->internal_abbr = $internal['abbr'];
                //$data->internal_name = $internal['full_name'];
                $this->view->result = true;
                return;
            }
        }
        $this->view->result = false;
    }

	public function outAction()
	{
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		Zend_Session::destroy();
		$this->_redirect('/');
	}
}
