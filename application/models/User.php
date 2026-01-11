<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 7-ott-2010
 * Time: 16.55.21
 * To change this template use File | Settings | File Templates.
 */

class Model_User extends Maco_Model_Abstract
{
    protected $user_id;

    protected $created_by;

    protected $date_created;

    protected $modified_by;

    protected $date_modified;

    protected $deleted;

    protected $username;

    protected $password;

    protected $password_salt;

    protected $id_role;

    protected $role_name;
    protected $role_description;

    protected $active;

    protected $id_contact;

    protected $contact;

    protected $internals = array();
    
    protected $permissions = array();
    
    
    public function has_permission($resource, $action = null)
    {
        if($action !== null)
        {
            return in_array(array(
                    'id_user' => $this->user_id,
                    'resource' => $resource,
                    'action' => $action
                ), $this->permissions);
        }
        
        foreach($this->permissions as $p)
        {
            if($p['resource'] == $resource)
            {
                return true;
            }
        }
        return false;
    }
}
