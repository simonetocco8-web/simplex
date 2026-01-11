<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 6-ott-2010
 * Time: 18.12.48
 * To change this template use File | Settings | File Templates.
 */
 
class Simplex_Acl_RulesManager
{
    private $_resources = array();
    private $_rules = array();
    private $_nestedRules = array();
    private $_roles = array();

    public function __construct($user)
    {
        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select();

        $select->from('rules', 'id_resource_action')
      //          ->where('id_user = ?', $user->user_id) // todo restore
                ->joinLeft('resource_actions', 'id_resource_action = resource_action_id',
                    array('action_name' => 'name', 'action_description' => 'description'))
                ->joinLeft('resources', 'resource_id = id_resource', '*')
                ->order('_index ASC');

        $this->_rules = $db->fetchAll($select);
        
        foreach($this->_rules as $rule)
        {
            $with_parent = (isset($rule['parent_resource']) && $rule['parent_resource'] != '');
            $resource_id = ($with_parent) ? $rule['parent_resource'] : $rule['resource_id'];
            if(!array_key_exists($resource_id, $this->_resources))
            {
                $this->_resources[$resource_id] = $rule['controller']; 
            }
            if($with_parent)
            {
                $this->_nestedRules[$resource_id]['pages'][] = $rule;
            }
            else
            {
                $this->_nestedRules[$resource_id] = $rule;
                $this->_nestedRules[$resource_id]['pages'] = array();
            }
        }

        unset($select);
        $select = $db->select();
        $this->_roles = $db->fetchPairs($select->from('roles', array('role_id', 'name')));
    }

    public function getResources()
    {
        return $this->_resources;
    }

    public function getRules()
    {
        return $this->_rules;
    }

    public function getRoles()
    {
        return $this->_roles;
    }

    public function getNestedRules()
    {
        return $this->_nestedRules;
    }
}