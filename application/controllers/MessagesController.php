<?php


class MessagesController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('list', 'html')
            ->addActionContext('delete', 'json')
            ->addActionContext('detail', 'html')
            ->addActionContext('list', 'html')
            ->initContext();
    }

    public function listAction()
    {
        $messages_repo = Maco_Model_Repository_Factory::getRepository('message');

        $user = Zend_Auth::getInstance()->getIdentity();
        $user_id = $user->user_id;

        $search = array_merge($this->_request->getParams(), array('to' => $user_id));

        $messages = $messages_repo->getMessages('message_id', 'DESC', $search, 5);

        foreach($messages as $key => $message)
        {
            if(stripos($message['title'], 'nuovo impegno') !== FALSE)
            {
                // no tasks in this list
                unset($messages[$key]);
            }
        }

        $this->view->type = $this->_request->getParam('type', false);

        $this->view->messages = $messages;
    }

    public function deleteAction()
    {
        $id = $this->_request->getParam('id', false);
        
        if(!$id)
        {
            throw new Exception('no id found');
        }
        
        $repo = Maco_Model_Repository_Factory::getRepository('message');
        $result = $repo->deleteMessageById($id);
        
        if($this->_request->isXmlHttpRequest())
        {
            if($result)
            {
                $this->view->result = true;
                $this->view->message = 'messaggio eliminato';
            }
            else
            {
                $this->view->result = false;
                $this->view->message = 'impossibile elimiare il messaggio!';
            }
        }
        else
        {
            if($result)
            {
                $this->_helper->getHelper('FlashMessenger')->addMessage('messaggio eliminato');
            }
            else
            {
                $this->_helper->getHelper('FlashMessenger')->addMessage('impossibile elimiare il messaggio!');
            }
            
            $red = $this->_request->getParam('r', false);
            $redirect = ($red) ? $red : '/dashboard';
            $this->_redirect($redirect);
        }
    }
}
