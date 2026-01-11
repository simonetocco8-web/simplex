<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 7-set-2010
 * Time: 11.16.13
 * To change this template use File | Settings | File Templates.
 */

class Model_Mail_Repository
{
    /**
     * Users mysql mapper
     *
     * @var Model_User_Mapper
     */
    protected $_mailMapper;

    public function __construct()
    {
        $this->_mailMapper = new Model_Mail_Mapper();
    }

    public function getNewMail()
    {
        $new = new Model_Mail();
        return $new;
    }

    public function find($id)
    {
        $item = $this->_mailMapper->find($id);
        return $item;
    }

    public function save($item)
    {
        return $this->_mailMapper->save($item);
    }

    public function delete($id)
    {
        return $this->_mailMapper->delete($id);
    }

    public function findByContact($id_contact)
    {
        return $this->_mailMapper->fetch(array('id_contact' => $id_contact));
    }

    public function findByCompany($id_company)
    {
        return $this->_mailMapper->fetch(array('id_company' => $id_company));
    }

      public function getFromData($data, $prefix = '')
    {
        $mail = new Model_Mail();
        $mail->setValidatorAndFilter(new Model_Mail_Validator());
        $mail->setData($data, $prefix);
        $mail->isValid();
        return $mail;
    }

    public function findByCompanyAndContact($id_company, $id_contact = null)
    {
        $where = array(
            'id_company' => $id_company
        );
        if($id_contact)
        {
            $where['id_contact'] = $id_contact;
        }

        return $this->_mailMapper->fetch($where);
    }
    
    public function saveFromData($data, $prefix = '')
    {
        $mail = new Model_Mail();
        $mail->setValidatorAndFilter(new Model_Mail_Validator());
        $mail->setData($data, $prefix);
        if($mail->isValid())
        {
            Maco_Model_TransactionManager::beginTransaction();

            try
            {
                $mail_id = $this->_mailMapper->save($mail);

                Maco_Model_TransactionManager::commit();
                return $mail_id;
            }
            catch(Exception $e)
            {
                Maco_Model_TransactionManager::rollback();
                return array($e->getMessage());
            }
        }
        else
        {
            return $mail->getInvalidMessages();
        }
    }
}
