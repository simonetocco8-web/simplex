<?php
class ErrorController extends Zend_Controller_Action
{
	public function errorAction()
	{
		$errors = $this->_getParam('error_handler');
        $this->getResponse()->clearBody(); 
		if($errors->exception instanceof App_Exception_AccessDenied)
		{
			$this->_helper->redirector->gotoSimple('index', 'index', 'auth');
			return;
		}

		switch ($errors->type)
		{
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
				// 404 error -- controller or action not found
				$this->getResponse()->setHttpResponseCode(404);
				$this->view->frontMessage = 'Siamo spiacenti ma la pagina cercata non esiste.';
				break;
            
			default:
				// application error
				$this->getResponse()->setHttpResponseCode(500);
				$this->view->frontMessage = 'Errore interno nell\'applicazione';
				break;
		}

        Maco_Logger_File::info('------------------------------------------------------');
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity())
        {
            $user = $auth->getIdentity();
            Maco_Logger_File::info($user->user_id . ' - ' . $user->username);
        }
        Maco_Logger_File::info('--- Dati Richiesta');
        Maco_Logger_File::info('Module: ' .  $this->_request->getModuleName() 
            . ' - Controller: ' . $this->_request->getControllerName() 
            . ' - Action' . $this->_request->getActionName());
        Maco_Logger_File::info('--- Dati Eccezione');
		Maco_Logger_File::info($errors->exception . ': ' . $errors->exception->getMessage());

		// pass the actual exception object to the view
		$this->view->exception = $errors->exception;
		$this->view->message = $errors->exception->getMessage();
		$this->view->requestParams = $this->_request->getParams();

	}
    
    public function deniedAction()
    {
        
    }
}