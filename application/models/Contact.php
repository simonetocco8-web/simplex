<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 8-ott-2010
 * Time: 14.56.50
 * To change this template use File | Settings | File Templates.
 */

class Model_Contact extends Maco_Model_Abstract
{
    protected $contact_id;

    protected $created_by;

    protected $date_created;

    protected $modified_by;

    protected $date_modified;

    protected $deleted;

    protected $id_contact_title;
    protected $contact_title;
    
    protected $nome;

    protected $cognome;

    protected $description;

    protected $id_company;

    protected $addresses = array();

    protected $telephones = array();

    protected $mails = array();
}
