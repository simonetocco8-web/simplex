<?php
/**
 * Created by Marcello Stani.
 * User: Marcello
 * Date: 18/12/11
 * Time: 19.10
 */

class Maco_Settings_Db
{
    /**
     * @var bool Are settings loaded?
     */
    protected static $_loaded = false;

    protected static $_cached = array();

    /**
     * Return a setting value from the db
     *
     * @static
     * @param string $name the setting name
     * @param null $default in case no setting found for the given name
     * @param null $id_user a user id (for user specified settings)
     * @return string
     */
    public static function get($name, $default = null, $id_user = 0)
    {
        if(!self::$_loaded)
        {
            $db = Zend_Registry::get('dbAdapter');
            $values = $db->fetchAll('select * from settings order by id_user');
            foreach($values as $value)
            {
                if(!array_key_exists($value['id_user'], self::$_cached))
                {
                    self::$_cached[$value['id_user']] = array();
                }
                self::$_cached[$value['id_user']][$value['name']] = $value['value'];
            }
        }

        $value = $default;
        $found = false;
        if(array_key_exists($id_user, self::$_cached))
        {
            if(array_key_exists($name,self::$_cached[$id_user]))
            {
                $value = self::$_cached[$id_user][$name];
                $found = true;
            }
        }

        if(!$found && $id_user != 0)
        {
            $id_user = 0;
            if(array_key_exists($id_user, self::$_cached))
            {
                if(array_key_exists($name,self::$_cached[0]))
                {
                    $value = self::$_cached[$id_user][$name];
                }
            }

        }

        return $value;
    }


    /**
     * Create or update a given setting value
     * @static
     * @param string $name the setting name
     * @param mixed $value the value to save
     * @param null $id_user a user id (for user specified settings)
     */
    public static function set($name, $value, $id_user = 0)
    {
        $old = self::get($name, null, $id_user);

        $db = Zend_Registry::get('dbAdapter');

        if($old === null)
        {
            $data = array(
                'name' => $name,
                'value' => $value,
                'id_user' => $id_user,
            );
            $db->insert('settings', $data);
        }
        elseif($old != $value)
        {
            $where = array(
                'name = ?' => $name,
                'id_user = ?' => $id_user,
            );
            $data = array('value' => $value);
            $db->update('settings', $data, $where);
        }
    }
}