<?php

class Simplex_Acl_Acl extends Zend_Acl
{
    protected $_resources = array();

    public function __construct($role, $resources, $rules, $roles)
    {
        $this->_roles = $roles;
        
        // carico le risorse dal DB
        $this->_resources = $resources;

        //aggiungo una serie di risorse
        foreach($this->_resources as $res)
        {
            $this->add(new Zend_Acl_Resource($res));
        }

        $this->add(new Zend_Acl_Resource('login'));
        $this->allow(null, 'login');
        $this->add(new Zend_Acl_Resource('index'));
        $this->allow(null, 'index');
        $this->allow(null, 'admin', 'subservicesbyserviceforselect');
        $this->allow(null, 'companies', array('acaddresses', 'percentforpartner'));

        $this->addRole(new Zend_Acl_Role($this->_roles[$role]));
        
        // Tutti possono vedere la index (home)
        //$this->allow(null, 'index');

        // unrestricted access to ADMIN
        //$admin = $this->_roles[1];
        //$this->allow($admin);

        foreach($rules as $rule)
        {
            $this->allow($this->_roles[$role], $rule['controller'], $rule['action_name']);
        }
    }
    
    /**
    * Returns the number of the resources
    * 
    * @return array
    */
    public function getResourcesCount()
    {
        return count($this->_resources);
    }
    
    /**
    * Returns the resource at the given index.
    * 
    * @param int $index
    * @return Zend_Acl_Resource
    */
    public function getResource(int $index)
    {
        if($index < 0 || $index >= $this->getResourcesCount())
        {
            return null;
        }
        return $this->_resources[$index];
    }
    
    /**
    * Returns all the resources as array
    * 
    * @return array
    */
    public function getResources()
    {
        return $this->_resources;
    }

    public function getRoles()
    {
        return $this->_roles;
    }

    public function getRoleName($value)
    {
        if(array_key_exists($value, $this->_roles))
        {
            return $this->_roles[$value];
        }

        throw new Exception('no role for the given key "' . $value . '"!');
    }

    public function getRoleValue($name)
    {
        if($value = array_search($name, $this->_roles))
        {
            return $value;
        }

        throw new Exception('no role for the given key "' . $name . '"!');
    }
}


?>
