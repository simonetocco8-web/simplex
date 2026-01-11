<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 6-set-2010
 * Time: 9.38.48
 * To change this template use File | Settings | File Templates.
 */

class Model_Task extends Maco_Model_Abstract
{
    protected $task_id;

    protected $created_by;

    protected $date_created;

    protected $modified_by;

    protected $date_modified;

    protected $id_parent;

    protected $id_who;

    protected $when;

    protected $when_flexible;

    protected $time_expected;

    protected $finishs;

    protected $what;

    protected $id_company;

    protected $id_subject;

    protected $subject;

    protected $subject_data;

    protected $with_auto;

    protected $info;

    protected $sector;

    protected $done;

    protected $date_done;

    protected $note;

    protected $id_offer;

    protected $_sectors = array(
        '1' => 'Direzione',
        '2' => 'Commerciale',
        '3' => 'Produzione',
        '4' => 'Amministrazione',
        '5' => 'Varie',
    );

    protected $_whats = array(
        '1' => 'Telefonata',
        '2' => 'Email',
        '3' => 'Incontro',
        '4' => 'Parlato',
        '5' => 'Riunione',
        '6' => 'Videoconferenza',
        '7' => 'Analisi',
        '8' => 'PFM',
    );

    public function getSectors()
    {
        return $this->_sectors;
    }

    public function getSector()
    {
        if(array_key_exists($this->sector, $this->_sectors))
        {
            return $this->_sectors[$this->sector];
        }
        return '';
    }

    public static function getSectorForValue($val)
    {
        $dummy = new self();
        if(array_key_exists($val, $dummy->_sectors))
        {
            return $dummy->_sectors[$val];
        }
        return '';
    }

    public function getWhats()
    {
        return $this->_whats;
    }

    public function getWhat()
    {
        if(array_key_exists($this->what, $this->_whats))
        {
            return $this->_whats[$this->what];
        }
        return '';
    }

    public static function getWhatForValue($what)
    {
        $dummy = new self();
        if(array_key_exists($what, $dummy->_whats))
        {
            return $dummy->_whats[$what];
        }
        return '';
    }

    public static function getFormattedTimeExpectedForValue($val)
    {
        $out = '';
        $and = '';
        if(empty($val))
        {
            return $out;
        }

        $parsed = Maco_Utils_Time::fromValue($val);

        $h = $parsed['hours'];
        $m = $parsed['minutes'];

        if($h > 0)
        {
            $out = $h . (($h == 1) ? ' ora' : ' ore');
            $and = ' e ';
        }

        if($m > 0)
        {
            $out .= $and . $m . (($m > 1) ? ' minuti' : 'minuto');
        }
        return $out;
    }

    public function getFormattedTimeExpected()
    {
        $out = '';
        $and = '';
        if(empty($this->time_expected))
        {
            return $out;
        }

        $parsed = Maco_Utils_Time::fromValue($this->time_expected);

        $h = $parsed['hours'];
        $m = $parsed['minutes'];

        if($h > 0)
        {
            $out = $h . (($h == 1) ? ' ora' : ' ore');
            $and = ' e ';
        }

        if($m > 0)
        {
            $out .= $and . $m . (($m > 1) ? ' minuti' : 'minuto');
        }
        return $out;
    }

    /**
     * Un po grezzo!
     *
     * @param array $data
     */
    public static function getFormattedTask($data)
    {
        $db = Zend_Registry::get('dbAdapter');
        $when = Maco_Utils_DbDate::fromDb($data['when']);
        $time = Model_Task::getFormattedTimeExpectedForValue($data['time_expected']);
        $base_url = Zend_Controller_Front::getInstance()->getBaseUrl();

        $who = $db->fetchOne('select username from users where user_id = ' . $data['id_who']);
        $company = $db->fetchOne('select ragione_sociale from companies where company_id = ' . $data['id_company']);
        $out = $who . ', alle <a href="' . $base_url . '/tasks/detail/id/' . $data['task_id'] . '"><b>' . $when . '</b></a><span class="info"> (' . $time . '</span>) ';

        if($data['id_subject'] != '')
        {
            $receiver = '<a href="' . $base_url . '/companies/detailcontact/id/'
                    . $data['id_company'] . '/idc/' . $data['id_subject'] . '"><b>'
                    . $data['subject'] . '</b></a>';
        }
        else
        {
            $receiver = $data['subject'];
        }
        $company = '<a href="' . $base_url . '/companies/detail/id/'. $data['id_company']
                . '"><b>'. $company . '</b></a>';
        if($receiver == '')
        {
            $company = 'l\'azienda ' . $company;
        }
        else
        {
            $company = '(' . $company . ')';
        }

        switch($data['what'])
        {
            case 1:
                $out .= ' devi contattare telefonicamente ' . $receiver . ' ' . $company . ' al numero: ' . $data['subject_data'];
                break;
            case 2:
                $out .= ' devi contattare via email ' . $receiver . ' ' . $company . ' all\'indirizzo mail: ' . $data['subject_data'];
                break;
            case 3:
                $out .= ' devi incontrarsi con ' . $receiver . ' ' . $company . ' all\'indirizzo: ' . $data['subject_data'];
                break;
            case 5:
                $out .= ' devi fare riuniove con ' . $receiver . ' ' . $company . ' all\'indirizzo: ' . $data['subject_data'];
                break;
            case 6:
                $out .= ' devi fare videoconferenza con ' . $receiver . ' ' . $company . ' - info: ' . $data['subject_data'];
                break;
            case 7:
                $out .= ' dopo una analisi';
                break;
            case 8:
            $out .= ' in PFM';
            break;
            default:
                $out .= ' devi parlare con' . $receiver;
                break;
        }

        return $out;
    }

    public function getFormatted()
    {
        $db = Zend_Registry::get('dbAdapter');
        $when = Maco_Utils_DbDate::fromDb($this->when);
        $time = Model_Task::getFormattedTimeExpectedForValue($this->time_expected);
        $base_url = Zend_Controller_Front::getInstance()->getBaseUrl();

        $who = $db->fetchOne('select username from users where user_id = ' . $this->id_who);
        $company = $db->fetchOne('select ragione_sociale from companies where company_id = ' . $this->id_company);
        $out = $who . ', alle <a href="' . $base_url . '/tasks/detail/id/' . $this->task_id . '"><b>' . $when . '</b></a><span class="info">(' . $time . '</span>) ';

        if($this->id_subject != '')
        {
            $receiver = '<a href="' . $base_url . '/companies/detailcontact/id/'
                    . $this->id_company . '/idc/' . $this->id_subject . '"><b>'
                    . $this->subject . '</b></a>';
        }
        else
        {
            $receiver = $this->subject;
        }
        $company = '<a href="' . $base_url . '/companies/detail/id/'. $this->id_company
                . '"><b>'. $company . '</b></a>';
        if($receiver == '')
        {
            $company = 'l\'azienda ' . $company;
        }
        else
        {
            $company = '(' . $company . ')';
        }

        switch($this->what)
        {
            case 1:
                $out .= ' devi contattare telefonicamente ' . $receiver . ' ' . $company . ' al numero: ' . $this->subject_data;
                break;
            case 2:
                $out .= ' devi contattare via email ' . $receiver . ' ' . $company . ' all\'indirizzo mail: ' . $this->subject_data;
                break;
            case 3:
                $out .= ' devi incontrarsi con ' . $receiver . ' ' . $company . ' all\'indirizzo: ' . $this->subject_data;
                break;
            case 5:
                $out .= ' devi fare riuniove con ' . $receiver . ' ' . $company . ' all\'indirizzo: ' . $this->subject_data;
                break;
            case 6:
                $out .= ' devi fare videoconferenza con ' . $receiver . ' ' . $company . ' - info: ' . $this->subject_data;
                break;
            case 7:
                $out .= ' dopo una analisi';
                break;
            case 8:
            $out .= ' in PFM';
            break;
            default:
                $out .= ' devi parlare con' . $receiver;
                break;
        }

        return $out;
    }
}
