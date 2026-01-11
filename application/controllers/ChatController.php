<?php

class ChatController extends Zend_Controller_Action
{
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext')
            ->addActionContext('update', 'json')
            ->initContext();
    }
    
    public function preDispatch()
    {
        if(!$this->_request->isXmlHttpRequest())
        {
            throw new Exception('error code: 5001', 404);
        }
    }
    
    public function updateAction()
    {
        $auth = Zend_Auth::getInstance();
        $user = $auth->getIdentity();
        
        $user_id = $user->user_id;
        $internal_id = $user->internal_id;

        $output = array();
        
        $chat = new Maco_Chat_Manager(Zend_Registry::get('dbAdapter'));
        
        if($user_id)
        {
            // refresh activity status
            $chat->ping($user_id, $internal_id);
            
            // get users
            $output['users'] = $chat->getLoggedUsers($user_id, $internal_id);
            
            //if messages push to the db
            if(isset($_POST['messages']))
            {
                foreach($_POST['messages'] as $msg)
                {
                    $timestamp = $chat->addMessage($user_id, $internal_id, $msg['to'], $msg['message']);
                    $output['msg_sent'] = $timestamp;
                }
            }
            
            // load messages for this user
            $messages = $chat->getMessagesFor($user_id, $internal_id, $this->_request->getParam('lastTimestamp', 0));
            
            $output['messages'] = $messages;
        }
        else
        {
            
        }
        
        echo json_encode($output);
        exit;
    }
}
