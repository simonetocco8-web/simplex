<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 6-set-2010
 * Time: 13.43.59
 */

class Model_Mail_Mapper extends Maco_Model_Mapper_Abstract
{
    protected $_modelName = 'Model_Mail';

    protected $_dbTableName = 'Model_DbTables_Mails';

    public function findForCompany($company)
    {
        $db = $this->getDbAdapter();
        $select = $db->select();
        $select->from('mails_companies', array())
                ->joinLeft('mails', 'mails.id = id_mail', array('mail', 'description', 'id'))
                ->where('id_company = ?', $company->id);

        $mails = $db->fetchAll($select);

        $ret = array();
        foreach ($mails as $mail)
        {
            $mailObj = new Model_Mail();
            $mailObj->setData($mail);
            $ret[] = $mailObj;
        }

        return $ret;
    }

    public function fetch($where)
    {
        $db = $this->getDbAdapter();
        $select = $db->select();
        $select->from('mails', '*');

        if(is_array($where))
        {
            foreach($where as $field => $value)
            {
                $select->where($field . ' = ?', $db->quote($value));
            }
        }
        elseif(is_string($where))
        {
            
        }

        $items = $db->fetchAll($select);

        $ret = array();
        foreach ($items as $item)
        {
            $obj = new Model_Mail();
            $obj->setData($item);
            $ret[] = $obj;
        }

        return $ret;
    }
}
