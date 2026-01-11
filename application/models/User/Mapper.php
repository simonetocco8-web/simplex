<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 8-ott-2010
 * Time: 12.01.30
 * To change this template use File | Settings | File Templates.
 */

class Model_User_Mapper extends Maco_Model_Mapper_Abstract
{
    protected $_modelName = 'Model_User';

    protected $_dbTableName = 'Model_DbTables_Users';

    public function findWithRole($id)
    {
        $db = $this->getDbAdapter();
        $select = $db->select();

        $select->from('users', '*')
            ->joinLeft('roles', 'id_role = role_id', array('role_name' => 'roles.name', 'role_description' => 'roles.description'))
            ->where('user_id = ?', $id);

        $row = $db->fetchRow($select);
        $item = new $this->_modelName();
        if(!empty($item))
        {
            $item->setData($row);
        }

        return $item;
    }

    public function findInternalsByUserId($id)
    {
        $db = $this->getDbAdapter();
        $select = $db->select();

        $select->from('users_internals', array())
            ->joinLeft('internals', 'id_internal = internal_id', array('abbr', 'full_name', 'internal_id'))
            ->joinLeft('offices', 'id_office = office_id', array('office_name' => 'offices.name', 'office_id'))
            ->where('id_user = ?', $id);

        return $db->fetchAll($select);
    }
    
    public function findPermissionsByUserId($id)
    {
        $db = $this->getDbAdapter();
        $select = $db->select();

        $select->from('users_permissions', '*')
            ->where('id_user = ?', $id);

        return $db->fetchAll($select);
    }
}
