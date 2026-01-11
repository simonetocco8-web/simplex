<?php

class Maco_Chat_Manager
{
    /**
    * dnb adapter
    * 
    * @var Zend_Db_Adapter_Abstract
    */
    protected $_dbAdapter;
    
    /**
    * Constructor
    * 
    * @param Zend_Db_Adapter_Abstract $dbAdapter
    * @return Maco_Chat_Manager
    */
    public function __construct($dbAdapter)
    {
        $this->_dbAdapter = $dbAdapter;
    }
    
    /**
    * Returns an array of logged user with id, username, name and lastname
    * 
    * @param int $me id of the user which request the data
    * @param int $internal_id id of the user's connected internal
    * @return array
    */
    public function getLoggedUsers($me, $internal_id)
    {
        $select = $this->_dbAdapter->select();
        $select->from('logged_users')
            ->joinLeft('users', 'user_id = logged_user_id', 'username')
            ->joinLeft('contacts', 'id_contact = contact_id', array('nome', 'cognome'))
            ->where('user_id <> ?', $me)
            ->where('logged_user_internal_id = ?', $internal_id);

        return $this->_dbAdapter->fetchAll($select);
    }
    
    /**
    * Refresh the last_activity for this user and delete the inactive users
    * 
    * @param int $me id of the user which request the data
    * @param int $internal_id id of the user's connected internal
    * @return bool
    */
    public function ping($me, $internal_id)
    {
        $this->_dbAdapter->query('delete logged_users, chat_messages 
            from logged_users 
            left join chat_messages on logged_user_id = receiver 
            where last_activity < SUBTIME(now(), \'0:0:10\')');
        
        $this->_dbAdapter->query('INSERT INTO logged_users (logged_user_id, logged_user_internal_id, logged_time, last_activity)
            VALUES (?, ?, now(), now())
            ON DUPLICATE KEY UPDATE last_activity = NOW(), logged_user_internal_id = ?', array((int)$me, (int)$internal_id, (int)$internal_id));
    }
    
    /**
    * Saves a new message to the chat messages table and return the timestamp inserted
    * 
    * @param int $sender 
    * @param int $receiver
    * @param string $message
    * @return int the timestamp
    */
    public function addMessage($sender, $internal_id, $receiver, $message)
    {
        $this->_dbAdapter->insert('chat_messages', array(
            'sender'    => $sender,
            'internal_id'    => $internal_id,
            'receiver'      => $receiver,
            'message' => $message,
            'ts'      => new Zend_Db_Expr('now()')
        ));
        
        $id = $this->_dbAdapter->lastInsertId();
        return $this->_dbAdapter->fetchOne('select ts from chat_messages where chat_message_id =?', $id);
    }
    
    /**
    * Returns the messages needed for the given receiver
    * 
    * @param int $receiver
    * @param int $lastTimestamp last timestamp received
    * @return array
    */
    public function getMessagesFor($receiver, $internal_id, $lastTimestamp = 0)
    {
        $select = $this->_dbAdapter->select();
        $select->from('chat_messages', array('message', 'ts', 'sender_id' => 'sender', 'receiver_id' => 'receiver'))
            ->joinLeft(array('u1' => 'users'), 'u1.user_id = sender', array('sender_username' => 'u1.username'))
            ->joinLeft(array('u2' => 'users'), 'u2.user_id = receiver', array('receiver_username' => 'u2.username'))
            ->joinLeft(array('c1' => 'contacts'), 'u1.id_contact = c1.contact_id', array('sender_nome' => 'c1.nome', 'sender_cognome' => 'c1.cognome'))
            ->joinLeft(array('c2' => 'contacts'), 'u2.id_contact = c2.contact_id', array('receiver_nome' => 'c2.nome', 'receiver_cognome' => 'c2.cognome'))
            ->where('(receiver = ? or sender = ?)', $receiver)
            ->where('ts > ?', $lastTimestamp)
            ->where('chat_messages.internal_id = ?', $internal_id)
            ->order('ts ASC');
        return $this->_dbAdapter->fetchAll($select);
    }
}
