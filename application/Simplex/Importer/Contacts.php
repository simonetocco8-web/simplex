<?php

class Simplex_Importer_Contacts extends Simplex_Importer_Abstract
{
    protected $_refs = array();
    protected $_skipped = 0;
    protected $_mail_skipped = 0;
    public function import()
    {
        $contact_repo = Maco_Model_Repository_Factory::getRepository('contact');
                
        $objWorksheet = $this->_reader->getSheet(0);
        
        foreach ($objWorksheet->getRowIterator() as $row)
        {
            $index = $row->getRowIndex();
            if ($index > 1)
            {
                $id = $this->_getValue($objWorksheet, 'A', $index);
                if ($id == '')
                {
                    // end
                    break;
                }

                $company_id = $this->_getValue($objWorksheet, 'B', $index);

                $this->_addReferente($objWorksheet, 'C', 'D', 'E', 'F', $index, $company_id);
            }
            
        }   
    }
    
    protected function _addReferente(&$objWorksheet, $colr, $colt, $colm, $colruolo, $row, $company_id)
    {
        $cognome = $this->_getValue($objWorksheet, $colr, $row);
        if($cognome)
        {
            if(!array_key_exists($company_id, $this->_refs))
            {
                $this->_refs[$company_id] = $this->_db->fetchCol('select cognome from contacts where id_company = ' . $company_id);
            }
            if(in_array($cognome, $this->_refs[$company_id]))
            {
                echo ++$this->_skipped . ' - ' . $cognome . ' esiste per azienda ' . $company_id . '<br />';
                return;
            }

            $description = $this->_getValue($objWorksheet, $colruolo, $row);
            $this->_db->insert('contacts', array(
                'created_by' => 1,
                'date_created' => new Zend_Db_Expr('now()'),
                'deleted' => 0,
                'cognome' => $cognome,
                'description' => $description,
                'id_company' => $company_id
            ));
            
            $contact_id = $this->_db->lastInsertId();
            
            $number = $this->_getValue($objWorksheet, $colt, $row);
            if($number)
            {
                $telephone_repo = Maco_Model_Repository_Factory::getRepository('telephone');
        
                $tel = $telephone_repo->getNewTelephone();
                $tel->setValidatorAndFilter(new Model_Telephone_Validator());
                $tel->number = $number;
                $tel->id_contact = $contact_id;
                if($tel->isValid())
                {
                    $telephone_repo->save($tel);
                }        
                else
                {
                    -dd($tel->getInvalidMessages());
                }
            }
            
            $mail_value = str_replace(' ', '', $this->_getValue($objWorksheet, $colm, $row));
            if($mail_value)
            {
                $mail_repo = Maco_Model_Repository_Factory::getRepository('mail');
                $mail = $mail_repo->getNewMail();
                $mail->setValidatorAndFilter(new Model_Mail_Validator());
                $mail->mail = $mail_value;
                $mail->id_contact = $contact_id;
                if($mail->isValid())
                {
                    $mail_repo->save($mail);
                }        
                else
                {
                    echo '----- ' .  ++$this->_mail_skipped . ' EMAIL ' . $mail_value .  ' SKIPPED per ' . $cognome . ' - company ' . $company_id;
                    //-dd($mail->getInvalidMessages());
                   //echo '<br />' . $index . '<br />';
                   //var_dump($mail->getInvalidMessages());
                }
            }
        }
    }
}
