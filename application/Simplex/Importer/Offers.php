<?php

class Simplex_Importer_Offers extends Simplex_Importer_Abstract
{        
    protected $_pagamenti_def = array(
        'table' => 'pagamenti',
        'id' => 'pagamento_id',
        'field' => 'name'
    );
    
     protected $_interests_def = array(
        'table' => 'interests_levels',
        'id' => 'interests_level_id',
        'field' => 'name'
    );
    
    
    protected $_linked_to_one_internal = false;
    
    protected $_cached_companies = array();
    
    protected $_cap_cached = false;
    
    protected $_statuses = false;
    
    protected $_cached_services = array();
    
    protected $_cached_subservices = array();
    
    public function import()
    {
        $offers_repo = Maco_Model_Repository_Factory::getRepository('offer');
        
        $user_importer = new Simplex_Importer_Users();
        $user_importer->setConfig($this->_config);
        $user_importer->setDb($this->_db);
        
        $objWorksheet = $this->_reader->getSheet(0);
        
        $partners_importer = new Simplex_Importer_Partners();
        $partners_offset = $partners_importer->get_offset();
        unset($partners_importer);
        
        $this->_loadCachedServices();
        
        foreach ($objWorksheet->getRowIterator() as $row)
        {
            $index = $row->getRowIndex();
            if ($index > 1)
            {
                $contatore = $this->_getValue($objWorksheet, 'A', $index);
                if ($contatore == '')
                {
                    // end
                    break;
                }
                
                $offer = $offers_repo->getNewOffer();
                $offer->setValidatorAndFilter(new Model_Offer_Validator());

                $offer->deleted = 0;
                
                $year = $this->_getValue($objWorksheet, 'X', $index);
                
                if($year == 0)
                {
                    // non pu� essere 0
                    $year = 2002;
                    //continue;
                }
                
                // cod internal -> en diventa EX
                $internal_abbr = strtolower($this->_getValue($objWorksheet, 'C', $index));
                if($internal_abbr == 'en')
                {
                    $internal_abbr = 'ex';
                }
                $progressivo = $this->_getValue($objWorksheet, 'Y', $index);
                
                // Servizio
                $service_full = $this->_getValue($objWorksheet, 'AE', $index);
                $service_parts = explode('-', $service_full);
                if(!isset($service_parts[1]))
                {
                    echo 'skipped index: ' . $index . ' - service malformed. no service_parts[1]<br />';
                    continue;
                }
                $service_code = $service_parts[0];
                $service_name = $service_parts[1];
                if($service_name == '' || $service_code == '')
                {
                    echo 'skipped index: ' . $index . ' - service name or service code null]<br />';
                    continue;
                }
                $service_id = $this->_pushService($service_code, $service_name);
                $offer->id_service = $service_id;
                
                // Sotto-Servizio
                $subservice_full = $this->_getValue($objWorksheet, 'AF', $index);
                $subservice_parts = explode('-', $subservice_full);
                if(!isset($subservice_parts[1]))
                {
                    echo 'skipped index: ' . $index . ' - subservice malformed. no subservice_parts[1]<br />';
                    continue;
                }
                $subservice_code = $subservice_parts[0];
                $subservice_name = $subservice_parts[1];
                if($subservice_name == '' || $subservice_code == '')
                {
                    echo 'skipped index: ' . $index . ' - subservice name or subservice code null]<br />';
                    continue;
                }
                $subservice_id = $this->_pushSubservice($subservice_code, $subservice_name, $service_id);
                $offer->id_subservice = $subservice_id;
                
                // # revisione
                $revision = $this->_getValue($objWorksheet, 'Z', $index);
                if($revision == 28)
                {
                    $revision = 0;
                }

                // il codice dell'azienda lo prendo dalla cella excel prima parte dell'explode /
                $old_code_offer = $this->_getValue($objWorksheet, 'AB', $index);
                $old_code_offer_parts = explode('/', $old_code_offer);
                if(! isset($old_code_offer_parts[0]) || strlen($old_code_offer_parts[0]) != 2)
                {
                    echo 'skipped index: ' . $index . ' - offer code not good: ' . $old_code_offer . '<br />';
                    continue;
                }

                $offer->code_offer = strtolower($old_code_offer_parts[0]) . '-' . $year . '-' . $progressivo . '-' . $service_code . '-' . $subservice_code . '-' . $revision;
                //$offer->code_offer = $internal_abbr . '-' . $year . '-' . $progressivo . '-' . $service_code . '-' . $subservice_code . '-' . $revision;
                if($internal_abbr == 'hq')
                {
                    $internal_abbr = 'ex';
                }
                
                $offer->internal_code = $internal_abbr;
                $offer->year = $year;
                $offer->id_offer = $progressivo;
                $offer->revision = $revision;
                
                if($revision > 0)
                {
                    $precedenti = $this->_db->update(
                        'offers',
                        array('active' => 0),
                        array(
                            'internal_code = ' . $this->_db->quote($internal_abbr),
                            'year = ' .$year,
                            'id_offer = '  . $progressivo,
                            'revision < ' . $revision
                        )
                    );
                }
                
                $successive = $this->_db->fetchCol('select offer_id from offers where ' .
                    ' internal_code = ' . $this->_db->quote($internal_abbr) .
                    ' and year = ' .$year .
                    ' and id_offer = '  . $progressivo .
                    ' and revision > ' . $revision
                );
                
                $offer->active = ! (count($successive) > 0);
                
                
                
                $stato = strtolower($this->_getValue($objWorksheet, 'AA', $index));
                switch($stato)
                {
                    case 'aggiudicata':
                        $offer->id_status = 4;
                        break;
                    case 'Non approvata da cliente':
                    case 'non accettata':
                        $offer->id_status = 5;
                        break;
                    case 'approvata da direzione':
                        $offer->id_status = 2;
                        break;
                    case 'emessa verbalmente':
                    case 'inviata':
                        $offer->id_status = 3;
                        break;
                    case 'in compilazione':
                    case 'in attesa di approv.':
                    default:
                        $offer->id_status = 1;
                        break;
                }
                
                $offer->id_company = $this->_getValue($objWorksheet, 'E', $index);
                
                $offer->luogo = $this->_getValue($objWorksheet, 'F', $index);
                $l2 = $this->_getValue($objWorksheet, 'G', $index);
                if($l2 != '')
                {
                    $offer->luogo .= ' ' . $l2;
                }
                
                $promotore = $this->_getValue($objWorksheet, 'U', $index);
                if($promotore != 0)
                {
                    $real_promotore = $promotore + $partners_offset;
                    $offer->id_promotore = $real_promotore;
                    $offer->promotore_percent = (int) $this->_getValue($objWorksheet, 'AR', $index);
                }
                
                $data_offerta = $this->_getValue($objWorksheet, 'K', $index);
                $formatted_data = PHPExcel_Style_NumberFormat::toFormattedString($data_offerta, PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
                $data_parts = explode('-', $formatted_data);
                if($data_parts[0] == 1900)
                {
                    $data_offerta_2 = $this->_getValue($objWorksheet, 'N', $index);
                    $formatted_data_2 = PHPExcel_Style_NumberFormat::toFormattedString($data_offerta_2, PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
                    $data_parts_2 = explode('-', $formatted_data_2);

                    //$data_parts[0] = 2003;
                    $formatted_data = implode('-', $data_parts_2);
                }
                $offer->date_offer = $formatted_data;
                
                $validita = (int) $this->_getValue($objWorksheet, 'L', $index);
                if(!$validita)
                {
                    $validita = 60;
                }
                $offer->validita = $validita;
                
                $date_end = new DateTime($formatted_data);
                $date_end->add(new DateInterval('P' . $validita . 'D'));
                $offer->date_end = $date_end->format('Y-m-d');
                
                $data_invio = $this->_getValue($objWorksheet, 'N', $index);
                if($data_invio == '')
                {
                    // dove vuota -> vuota
                    // $offer->date_sent = $offer->date_offer;
                }
                else
                {
                    // dove 1900 -> vuota
                    /*
                    $formatted_data = PHPExcel_Style_NumberFormat::toFormattedString($data_invio, PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
                    $data_parts = explode('-', $formatted_data);
                    if($data_parts[0] == 1900)
                    {
                        $data_parts[0] = 2003;
                        $formatted_data = implode('-', $data_parts);
                    }
                    $offer->date_sent = $formatted_data;
                    */
                }
                
                $data_accettazione = $this->_getValue($objWorksheet, 'AC', $index);
                if($data_accettazione != '')
                {
                    $formatted_data = PHPExcel_Style_NumberFormat::toFormattedString($data_accettazione, PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
                    $data_parts = explode('-', $formatted_data);
                    if($data_parts[0] == 1900)
                    {
                        // vuota
                        /*
                        $data_parts[0] = 2003;
                        $formatted_data = implode('-', $data_parts);
                        */
                        $formatted_data = '';
                    }
                    $offer->date_accepted = $formatted_data;
                }
                
                $offer->subject = $this->_getValue($objWorksheet, 'AU', $index);
                
                $offer->note = $this->_getValue($objWorksheet, 'O', $index);
                $note_commessa = $this->_getValue($objWorksheet, 'AW', $index);
                if($note_commessa != '')
                {
                    $offer->note = '\n - ' . $note_commessa;
                }
                
                $offer->scadenze = $this->_getValue($objWorksheet, 'AV', $index);
                
                $offer->sconto = $this->_getValue($objWorksheet, 'BU', $index);
                
                $pagamento = $this->_getValue($objWorksheet, 'BM', $index);
                if(!$pagamento)
                {
                    $pagamento = 'rimessa diretta';
                }
                $offer->id_pagamento = $this->_getIdByValue($this->_pagamenti_def, $pagamento);
                
                $rco = strtolower($this->_getValue($objWorksheet, 'I', $index));
                if(!$rco)
                {
                    $rco = 'cm';
                }
                $offer->id_rco = $user_importer->getUserId($rco);
                
                $offer->id_segnalato_da = $user_importer->getUserId(strtolower($this->_getValue($objWorksheet, 'J', $index)));
                
                $interesse = $this->_getValue($objWorksheet, 'BX', $index);
                if(!$interesse)
                {
                    $interesse = 'medio';
                }
                $offer->id_interest = $this->_getIdByValue($this->_interests_def, ucfirst($interesse));
                
                if($offer->isValid())
                {
                    $offer_id = $offers_repo->save($offer);

                    // forziamo il creatore
                    $creatore = strtolower($this->_getValue($objWorksheet, 'AG', $index));
                    if(!$creatore)
                    {
                        $creatore = 'cm';
                    }
                    $created_by = $user_importer->getUserId($creatore);
                    $this->_db->update(
                        'offers',
                        array('created_by' => $created_by),
                        array('offer_id = ' . $offer_id)
                    );

                    $attiva_date = $this->_getValue($objWorksheet, 'BW', $index);

                    $moment_index = 0;
                    // i momenti
                    // 1. Acconto
                    $this->_addMoment($objWorksheet, $index, $offer_id, ++$moment_index, 'Inizio Lavori', 'Q', 'AY', 'AL', 'BY');
                    // 2.
                    $acc = $this->_getValue($objWorksheet, 'AI', $index);
                    $dat = ($attiva_date) ? $this->_getValue($objWorksheet, 'AM', $index) : '';
                    if($dat != '')
                    {
                        $formatted_data = PHPExcel_Style_NumberFormat::toFormattedString($dat, PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
                        $data_parts = explode('-', $formatted_data);
                        if($data_parts[0] == 1900)
                        {
                            $dat = '';
                        }
                    }
                    $cau = $this->_getValue($objWorksheet, 'AZ', $index);
                    $che = $this->_getValue($objWorksheet, 'BO', $index);
                    if(($acc != '' && $acc != 0)
                        || $dat != ''
                        || $cau != ''
                        || $che != '')
                    {
                        $this->_addMoment($objWorksheet, $index, $offer_id, ++$moment_index, 'Fase 2', 'AI', 'AZ', 'AM', 'BZ');
                    }
                    
                    // 3.
                    $acc = $this->_getValue($objWorksheet, 'AJ', $index);
                    $dat = ($attiva_date) ? $this->_getValue($objWorksheet, 'AN', $index) : '';
                    if($dat != '')
                    {
                        $formatted_data = PHPExcel_Style_NumberFormat::toFormattedString($dat, PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
                        $data_parts = explode('-', $formatted_data);
                        if($data_parts[0] == 1900)
                        {
                            $dat = '';
                        }
                    }
                    $cau = $this->_getValue($objWorksheet, 'BA', $index);
                    $che = $this->_getValue($objWorksheet, 'BP', $index);
                    if(($acc != '' && $acc != 0)
                        || $dat != ''
                        || $cau != ''
                        || $che != '')
                    {
                        $this->_addMoment($objWorksheet, $index, $offer_id, ++$moment_index, 'Fase 3', 'AJ', 'BA', 'AN', 'CA');
                    }
                    
                    // 4.
                    $acc = $this->_getValue($objWorksheet, 'AK', $index);
                    $dat = ($attiva_date) ? $this->_getValue($objWorksheet, 'BK', $index) : '';
                    if($dat != '')
                    {
                        $formatted_data = PHPExcel_Style_NumberFormat::toFormattedString($dat, PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
                        $data_parts = explode('-', $formatted_data);
                        if($data_parts[0] == 1900)
                        {
                            $dat = '';
                        }
                    }
                    $cau = $this->_getValue($objWorksheet, 'BB', $index);
                    $che = $this->_getValue($objWorksheet, 'BQ', $index);
                    if(($acc != '' && $acc != 0)
                        || $dat != ''
                        || $cau != ''
                        || $che != '')
                    {
                        $this->_addMoment($objWorksheet, $index, $offer_id, ++$moment_index, 'Fase 4', 'AK', 'BB', 'BK', 'CB');
                    }
                    
                    // . Saldo
                    $this->_addMoment($objWorksheet, $index, $offer_id, ++$moment_index, 'Saldo', 'BF', 'BH', 'BL', 'CC');
                }
                else
                {
                    -dd($offer->getInvalidMessages());
                }
            }
            
        }   
    }
    
    protected function _addMoment(&$objWorksheet, $index, $offer_id, $moment_index, $name, $importo_col, $tipologia_col, $date_col, $fatt_col)
    {
        $moments_repo = Maco_Model_Repository_Factory::getRepository('moment');
        $moment = $moments_repo->getNewMoment();
        $moment->setValidatorAndFilter(new Model_Moment_Validator());
        
        $moment->id_offer = $offer_id;
        $moment->done = 0;
        $moment->index = $moment_index;
        $moment->importo = $this->_getValue($objWorksheet, $importo_col, $index);
        $moment->tipologia = $name;
        $moment->fatturazione = 0;
        if($this->_getValue($objWorksheet, $fatt_col, $index))
        {
            $moment->fatturazione = 1;
        }
        $tipologia = $this->_getValue($objWorksheet, $tipologia_col, $index);
        if($tipologia != '')
        {
            $moment->tipologia .= ' - ' . $tipologia;
        }
        
        $date = $this->_getValue($objWorksheet, $date_col, $index);
        if($date != '')
        {
            $formatted_data = PHPExcel_Style_NumberFormat::toFormattedString($date, PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
            $data_parts = explode('-', $formatted_data);
            if($data_parts[0] == 1900)
            {
             // vuote
                $formatted_data = '';
             //   $data_parts[0] = 2003;
             //   $formatted_data = implode('-', $data_parts);

            }
            $moment->expected_date = $formatted_data;
        }

        if($moment->isValid())
        {
            return $moments_repo->save($moment);
        }
        else
        {
            -dd($offer->getInvalidMessages());
        }
    }
    
    protected function _loadCachedServices()
    {
        $this->_cached_services = $this->_db->fetchPairs('select service_id, name from services');
        
        $subservices = $this->_db->fetchAll('select subservice_id, name, id_service, cod from subservices');
        
        foreach($subservices as $subservice)
        {
            $this->_cached_subservices[$subservice['subservice_id']] = array(
                'name' => $subservice['name'],
                'cod' => $subservice['cod'],
                'id_service' => $subservice['id_service'],
            );
        }
    }
    
    protected function _pushService($service_code, $service_name)
    {
        $key = array_search($service_name, $this->_cached_services);
            
        if(!$key)
        {
            $this->_db->insert('services', array(
                'created_by' => 1,
                'date_created' => new Zend_Db_Expr('now()'),
                'name' => $service_name,
                'cod' => $service_code
            ));
            
            $key = $this->_db->lastInsertId();
            $this->_cached_services[$key] = $service_name;
        }
        return $key;
    }
    
    protected function _pushSubservice($subservice_code, $subservice_name, $service_id)
    {
        $key = false;
        foreach($this->_cached_subservices as $subservice_id => $subservice)
        {
            if(strtolower($subservice_code) == strtolower($subservice['cod']) &&
                strtolower($subservice_name) == strtolower($subservice['name']) && 
                $service_id == $subservice['id_service'])
            {
                $key = $subservice_id;
                break;
            }
        }
        if(!$key)
        {
            $this->_db->insert('subservices', array(
                'created_by' => 1,
                'date_created' => new Zend_Db_Expr('now()'),
                'id_service' => $service_id,
                'name' => $subservice_name,
                'cod' => $subservice_code
            ));
            $key = $this->_db->lastInsertId();
            $this->_cached_subservices[$key] = array(
                'cod' => $subservice_code,
                'name' => $subservice_name,
                'id_service' => $service_id
            );
        }
        return $key;
    }
    
    protected function _addReferenti(&$objWorksheet, $row, $comany_id)
    {
        $this->_addReferente($objWorksheet, 'AW', 'AZ', 'BC', $row, $comany_id);
        $this->_addReferente($objWorksheet, 'AX', 'BA', 'BD', $row, $comany_id);
        $this->_addReferente($objWorksheet, 'Ay', 'BB', 'BE', $row, $comany_id);
    }
    
    protected function _addReferente(&$objWorksheet, $colr, $colt, $colm, $row, $company_id)
    {
        $cognome = $this->_getValue($objWorksheet, $colr, $row);
        if($cognome)
        {
            $this->_db->insert('contacts', array(
                'created_by' => 1,
                'date_created' => new Zend_Db_Expr('now()'),
                'deleted' => 0,
                'cognome' => $cognome,
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
                   //echo '<br />' . $index . '<br />';
                   //var_dump($mail->getInvalidMessages());
                }
            }
        }
    }
    
    protected function _loadTelephone($objWorksheet, $col, $row, $description, $id_company)
    {
        $number = $this->_getValue($objWorksheet, $col, $row);
        if($number == '') return;
        
        $telephone_repo = Maco_Model_Repository_Factory::getRepository('telephone');
        
        $tel = $telephone_repo->getNewTelephone();
        $tel->setValidatorAndFilter(new Model_Telephone_Validator());
        $tel->number = $number;
        $tel->description = $description;
        $tel->id_company = $id_company;
        if($tel->isValid())
        {
            $telephone_repo->save($tel);
        }        
        else
        {
            -dd($tel->getInvalidMessages());
        }
    }
    
    protected function _findProvinciaRegione($cap, $localita)
    {
        if(!$this->_cap_cached)
        {
            $select = $this->_db->select();
            $select->from('comuni', array('localita' => 'comuni.nome', 'cap'))
                ->joinLeft('province', 'provincia_id = comuni.id_provincia', array('provincia' => 'province.nome'))
                ->joinLeft('regioni', 'regione_id = province.id_regione', array('regione' => 'regioni.nome'));
            
            $vals = $this->_db->fetchAll($select);
            
            $this->_cap_cached = array();
            foreach($vals as $v)
            {
                $this->_cap_cached[$v['cap']] = array(
                    'localita' => $v['localita'],
                    'provincia' => $v['provincia'],
                    'regione' => $v['regione'],
                    'cap' => $v['cap'],
                );
            }
        }
        
        if($cap != '' && isset($this->_cap_cached[$cap]))
        {
            return $this->_cap_cached[$cap];
        }
        
        if($localita != '')
        {
            foreach($this->_cap_cached as $l)
            {
                if($l['localita'] == $localita)
                {
                    return $l;
                }
            }
        }
        
        return false;
    }
    
    protected function _linkInternals($objWorksheet, $index, $company_id)
    {
        // prima eliminiamo ogni possibile collegamento
        $this->_db->delete('companies_internals', 'id_company = ' . $company_id);
        
        // ora carichiamo i collegamenti
        $this->_linked_to_one_internal = false;
        $this->_linkInternal($objWorksheet, 'D', $index, $company_id);
        $this->_linkInternal($objWorksheet, 'E', $index, $company_id);
        $this->_linkInternal($objWorksheet, 'F', $index, $company_id);
        $this->_linkInternal($objWorksheet, 'G', $index, $company_id);
        $this->_linkInternal($objWorksheet, 'H', $index, $company_id);
        $this->_linkInternal($objWorksheet, 'I', $index, $company_id);
        
        $this->_linkInternal($objWorksheet, 'C', $index, $company_id, !$this->_linked_to_one_internal);
    }
    
    protected function _linkInternal($objWorksheet, $col, $row, $company_id, $force = false)
    {
        $has_abbr = $this->_getValue($objWorksheet, $col, $row);
            
        if($has_abbr == '1' || $force)
        {
            $internals_cached = $this->_loadCached($this->_internals_def);
            $abbr = strtoupper($this->_getValue($objWorksheet, $col, 1));
            
            $key = array_search($abbr, $internals_cached);
            
            if(!$key)
            {
                $key = $this->_pushValue($this->_internals_def, $abbr);
            }
            
            //offices
            $id_office = NULL;
            $office = $this->_getValue($objWorksheet, 'BL', $row);
            if($office != '')
            {
                $office_cached = $this->_loadCached($this->_offices_def);
                $key_office = array_search($office, $office_cached);
                if(!$key_office)
                {
                    $key_office = $this->_pushOffice($office);
                }
                $id_office = $key_office;
            }
            
            $this->_db->insert('companies_internals', array(
                'id_company' => $company_id,
                'id_internal' => $key,
                'id_office' => $id_office
            ));
            
            $this->_linked_to_one_internal = true;
        }
    }
    
    protected function _pushOffice($value)
    {
        $this->_db->insert('offices', array(
            'created_by' => 1,
            'date_created' => new Zend_Db_Expr('now()'),
            'name' => $value,
            'id_internal' => 1
        ));
        
        $id = $this->_db->lastInsertId();
        
        unset($this->offices);
        return $id;
    }
    
    protected function _getStatusIdByValue($value)
    {                                                          
        $statuses = $this->_loadStatuses();
        $value = ucfirst($value);
        
        $key = array_search($value, $statuses);
        
        return ($key) ? $key : 2;
    }
    
    protected function _loadStatuses()
    {
        if(!$this->_statuses)
        {
            $this->_statuses = $this->_db->fetchPairs('select status_id, name from status');
        }
        return $this->_statuses;
    }
    
    protected function _loadCompanies()
    {
        $companies = $this->_db->fetchAll('select company_id, ragione_sociale, partita_iva from companies');
        foreach($companies as $company)
        {
            $this->_cached_companies[$company['company_id']] = array(
                'ragione_sociale' => $company['ragione_sociale'],
                'partita_iva' => $company['partita_iva'],
            );
        }
    }
}
