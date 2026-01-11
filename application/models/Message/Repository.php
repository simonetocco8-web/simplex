<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 8-ott-2010
 * Time: 15.02.56
 * To change this template use File | Settings | File Templates.
 */

class Model_Message_Repository
{
    /**
     * Messages mysql mapper
     *
     * @var Model_Message_Mapper
     */
    protected $_messageMapper;

    public function __construct()
    {
        $this->_messageMapper = new Model_Message_Mapper();
    }

    public function getNewMessage()
    {
        $new = new Model_Message();
        return $new;
    }

    public function find($id)
    {
        $item = $this->_messageMapper->find($id);
        return $item;
    }

    public function save($item)
    {
        return $this->_messageMapper->save($item);
    }

    public function saveFromData($data, $prefix = '')
    {
		$message = new Model_Message();
        $message->setValidatorAndFilter(new Model_Message_Repository());
        $message->setData($data, $prefix);

        if($message->isValid())
        {
            Maco_Model_TransactionManager::beginTransaction();

            try
            {
                $message_id = (int) $this->_messageMapper->save($message);

                Maco_Model_TransactionManager::commit();
                return $message_id;
            }
            catch(Exception $e)
            {
                Maco_Model_TransactionManager::rollback();
                return array($e->getMessage());
            }
        }
        else
        {
            return $message->getInvalidMessages();
        }
    }

     public function getMessages($sort = 'date_created', $dir = 'DESC', $search = array(), $per_page = NULL, $deleted = false)
    {
        $db = $this->_messageMapper->getDbAdapter();

        $select = $db->select();

        $user = Zend_Auth::getInstance()->getIdentity();
        $id_internal = $user->internal_id;

        $select->from('messages', '*')
            ->where('title not like \'%nuovo impegno%\' and messages.id_internal = ' . $id_internal)
            ->joinLeft(array('u1' => 'users'), 'u1.user_id = messages.created_by', array('from_name' => 'u1.username'))
            ->joinLeft(array('u2' => 'users'), 'u2.user_id = messages.to', array('to_name' => 'u2.username'));

        if($deleted === false)
        {
            $select->where('messages.deleted = 0');
        }
        elseif($deleted === true)
        {
            $select->where('messages.deleted = 1');
        }

        $select->order($sort . ' ' . $dir);

        // where
        if(isset($search['to']) && !empty($search['to']) && $search['to'] != '')
        {
            $select->where($db->quoteIdentifier('to') . ' = ?', $search['to']);
        }

        if(isset($search['type']) && $search['type'] !== '')
        {
            $select->where($db->quoteIdentifier('type') . ' = ?', $search['type']);
        }

        if($per_page !== NULL)
        {
            $values = Zend_Paginator::factory($select);
            $values->setItemCountPerPage($per_page);
            $values->setCurrentPageNumber(isset($search['page']) ? $search['page'] : 1);
        }
        else
        {
            $values = $db->fetchAll($select);
        }

        return $values;
    }
    
    public function deleteMessageById($message_id)
    {
        return $this->_messageMapper->delete($message_id);
    }
    
    public function send($to, $title, $body, $type = 0, $from = null, $uid = null, $no_delete = 0)
    {
        if(!is_array($to))
        {
            $to = array($to);
        }

        $queuer = new Simplex_Email_Queuer();

        foreach($to as $id_receiver)
        {
            $message = $this->getNewMessage();

            $message->setValidatorAndFilter(new Model_Message_Validator());

            $message->title = $title;
            $message->body = $body;

            $message->to = $id_receiver;

            $message->type = $type;

            $message->no_delete = (int) $no_delete;

            if($uid !== null)
            {
                $message->uid = $uid;
            }

            $user = Zend_Auth::getInstance()->getIdentity();
            $message->id_internal = $user->internal_id;

            if($message->isValid())
            {
                $this->save($message);

                if($message->type == Model_Message_Types::TODO)
                {
                    /** @var $db Zend_Db_Adapter_Mysqli */
                    $db = Zend_Registry::get('dbAdapter');

                    $contact = $db->fetchAll('select m.mail, c.nome, c.cognome from users u
                        left join contacts c on u.id_contact = c.contact_id
                        left join mails m on m.id_contact = c.contact_id where u.user_id = '
                        . $db->quote($id_receiver));

                    $contact = $contact[0];

                    if($contact['mail'])
                    {
                        $toEmail = $contact['mail'];
                        if($contact['nome'] || $contact['cognome'])
                        {
                            $toEmail = array(
                                $toEmail,
                                trim($contact['nome'] . ' ' . $contact['cognome'])
                            );
                        }

                        // TODO: add http://simpl.excellentia.it

                        $queuer->addToQueue(array(
                            'from' => array('simplex@excellentia.it', 'Simpl.ex'),
                            'to' => $toEmail,
                            'subject' => $message->title,
                            'message' => $message->body,
                        ));
                    }
                }
            }
        }

        return true;
    }

    public function deleteByToAndUid($to, $uid)
    {
        return $this->_messageMapper->deleteByToAndUid($to, $uid);
    }

    public function deleteByUid($uid)
    {
        return $this->_messageMapper->deleteByUid($uid);
    }
}
