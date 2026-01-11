<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 8-ott-2010
 * Time: 14.56.50
 * To change this template use File | Settings | File Templates.
 */

class Model_Message extends Maco_Model_Abstract{

    protected $message_id;

    protected $created_by;
    protected $from_name;

    protected $date_created;

    protected $to;
    protected $to_name;
    
    protected $title;
    
    protected $body;
    
    protected $read;
    
    protected $dashboard;

    protected $type;

    protected $uid;

    protected $no_delete;

    protected $deleted;

    protected $id_internal;
}
