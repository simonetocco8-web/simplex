<?php

class Simplex_Importer_Orders extends Simplex_Importer_Abstract
{        
    protected $_statuses = false;
        
    public function import()
    {
        $orders_repo = Maco_Model_Repository_Factory::getRepository('order');
        $momentsRepo = Maco_Model_Repository_Factory::getRepository('moment');
        
        $user_importer = new Simplex_Importer_Users();
        $user_importer->setConfig($this->_config);
        $user_importer->setDb($this->_db);
        
        $objWorksheet = $this->_reader->getSheet(0);
        
        foreach ($objWorksheet->getRowIterator() as $row)
        {
            $index = $row->getRowIndex();
            if ($index > 1)
            {
                $id1 = $this->_getValue($objWorksheet, 'A', $index);
                if ($id1 == '')
                {
                    // end
                    break;
                }
                
                $order = $orders_repo->getNewOrder();
                $order->setValidatorAndFilter(new Model_Order_Validator());

                $internal_code = $this->_getValue($objWorksheet, 'C', $index);
                if($internal_code == 'hq')
                {
                    $order->internal_code = 'ex';
                }
                else
                {
                    $order->internal_code = $internal_code;
                }

                $order->id_order = $this->_getValue($objWorksheet, 'D', $index);
                $order->year = $this->_getValue($objWorksheet, 'E', $index);

                $code_order = $this->_getValue($objWorksheet, 'B', $index);
                $code_order_parts = explode('/', $code_order);
                $code_order_internal = $code_order_parts[0];

                $order->code_order = implode('-', array($code_order_internal, $order->year, $order->id_order));
                
                // ritroviamo l'id offerta
                $code_offer = $this->_getValue($objWorksheet, 'F', $index);
                $code_offer_parts = explode('/', $code_offer);
                if(count($code_offer_parts) < 4)
                {
                    echo 'commessa saltata: ' . $code_order . ' - indice: ' . $index . ' - codice offerta non valido (meno di quattro parti)<br />';
                    continue;
                }
                $offer_internal = $code_offer_parts[0];
                $offer_year = $code_offer_parts[1];
                $offer_progr = $code_offer_parts[2];
                $offer_rev = $code_offer_parts[3];
                if($offer_internal == '' || $offer_year == '' || $offer_progr == '' || $offer_rev == '')
                {
                    echo 'commessa saltata: ' . $code_order . ' - indice: ' . $index . ' - internal offerta o anno offerta o progressivo offerta o revisione offerta vuoit<br />';
                    continue;
                }
                /*
                $offer_id = $this->_db->fetchOne(
                    'select offer_id from offers where ' .
                    'offers.internal_code = ' . $this->_db->quote($offer_internal) .
                    ' AND offers.year = ' . $this->_db->quote($offer_year) .
                    ' AND offers.id_offer = ' . $this->_db->quote($offer_progr) .
                    ' AND offers.revision = ' . $this->_db->quote($offer_rev)
                );
                */
                $offers_id = $this->_db->fetchCol(
                    'select offer_id from offers where offers.code_offer like ' .
                    $this->_db->quote(
                        $offer_internal . '-' . $offer_year . '-' . $offer_progr . '-%'
                    ) .
                    ' AND offers.revision = ' . $this->_db->quote($offer_rev));
                if(count($offers_id) > 1)
                {
                    echo 'commessa saltata: ' . $code_order . ' - indice: ' . $index . ' - cod.comm.: ' . $code_order . ' - cod.off.: ' . $code_offer . ' - trovate piu offerte per i seguenti dati: internal= '
                         . $offer_internal . ' - anno = ' . $offer_year . ' - progressivo = ' . $offer_progr . ' - revisione = ' . $offer_rev . ' <br />';
                    continue;
                }
                if(count($offers_id) == 0)
                {
                    echo 'commessa saltata: ' . $code_order . ' - indice: ' . $index . ' - cod.comm.: ' . $code_order . ' - cod.off.: ' . $code_offer . ' - impossibile trovare id offerta per i seguenti dati: internal= '
                         . $offer_internal . ' - anno = ' . $offer_year . ' - progressivo = ' . $offer_progr . ' - revisione = ' . $offer_rev . ' <br />';
                    continue;
                }

                $order->id_offer = $offers_id[0];
                
                //$data_rali = $this->_getValue($objWorksheet, 'BX', $index);
                $data_rali = $this->_getValue($objWorksheet, 'U', $index);
                if($data_rali != '')
                {
                    $formatted_data = PHPExcel_Style_NumberFormat::toFormattedString($data_rali, PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
                    $data_parts = explode('-', $formatted_data);
                    if($data_parts[0] == 1900)
                    {
                        $data_parts[0] = 2003;
                        $formatted_data = implode('-', $data_parts);
                    }
                    $order->rali_date = $formatted_data;
                }

                $order->valore_g_uomo = $this->_getValue($objWorksheet, 'AX', $index);

                // su queste lavoriamo a consuntivo
                //$order->n_incontri = $this->_getValue($objWorksheet, 'AJ', $index); // AJ AO AT(CONSUNTIVO)
                $order->n_incontri = $this->_getValue($objWorksheet, 'AT', $index); // AJ AO AT(CONSUNTIVO)
                $order->n_ore_studio = $this->_getValue($objWorksheet, 'AQ', $index);

                $order->sal = $this->_getValue($objWorksheet, 'AV', $index);
                
                // forzato
                $order->id_dtg = 1;
                
                $data_chiusura = $this->_getValue($objWorksheet, 'BE', $index);
                if($data_chiusura != '')
                {
                    $formatted_data = PHPExcel_Style_NumberFormat::toFormattedString($data_chiusura, PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
                    $data_parts = explode('-', $formatted_data);
                    if($data_parts[0] == 1900)
                    {
                        //$data_parts[0] = 2003;
                        //$formatted_data = implode('-', $data_parts);
                        $formatted_data = '';
                    }
                    $order->date_chiusura_richiesta = $formatted_data;
                }
                
                    
                $stato = strtolower($this->_getValue($objWorksheet, 'AG', $index));
                $commessa_chiusa = false;
                switch($stato)
                {
                    case 'annullata':
                    case 'annulata':
                        $order->id_status = 5;
                        break;
                    case 'assegnata':
                    case 'in lavorazione':
                        $order->id_status = 2;
                        break;
                    case 'completata':
                        $order->id_status = 3;
                        $commessa_chiusa = true;
                        break;
                    case 'da assegnare':
                        $order->id_status = 1;
                        break;
                    case 'sospesa':
                        $order->id_status = 4;
                        break;
                    default:
                        $order->id_status = 1;
                        break;
                }

                // lavoriamo sui possibili momenti
                $moments = $momentsRepo->findByOffer($order->id_offer);
                $quanti = count($moments);
                $ultimo_index = 1;

                // 1 momento
                $data_done = $this->_getValue($objWorksheet, 'BD', $index);
                $done = $this->_getValue($objWorksheet, 'BI', $index);
                if($commessa_chiusa || $done)
                {
                    $moments[0]->done = 1;
                }
                if($data_done != '')
                {
                    $formatted_data = PHPExcel_Style_NumberFormat::toFormattedString($data_done, PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
                    $data_parts = explode('-', $formatted_data);
                    if($data_parts[0] != 1900)
                    {
                        $moments[0]->date_done = $formatted_data;
                    }
                }

                // 2 momento
                if($quanti == 2)
                {
                    $data_done = $this->_getValue($objWorksheet, 'AK', $index);
                    $done = $this->_getValue($objWorksheet, 'BJ', $index);
                }
                else
                {
                    $data_done = $this->_getValue($objWorksheet, 'BY', $index);
                    $done = $this->_getValue($objWorksheet, 'BT', $index);
                }
                if($commessa_chiusa || $done)
                {
                    $moments[1]->done = 1;
                }
                if($data_done != '')
                {
                    $formatted_data = PHPExcel_Style_NumberFormat::toFormattedString($data_done, PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
                    $data_parts = explode('-', $formatted_data);
                    if($data_parts[0] != 1900)
                    {
                        $moments[1]->date_done = $formatted_data;
                    }
                }

                if($quanti > 2)
                {
                    $ultimo_index = 2;
                    // 3 momento
                    if($quanti == 3)
                    {
                        $data_done = $this->_getValue($objWorksheet, 'AK', $index);
                        $done = $this->_getValue($objWorksheet, 'BJ', $index);
                    }
                    else
                    {
                        $data_done = $this->_getValue($objWorksheet, 'BZ', $index);
                        $done = $this->_getValue($objWorksheet, 'BU', $index);
                    }
                    if($commessa_chiusa || $done)
                    {
                        $moments[2]->done = 1;
                    }
                    if($data_done != '')
                    {
                        $formatted_data = PHPExcel_Style_NumberFormat::toFormattedString($data_done, PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
                        $data_parts = explode('-', $formatted_data);
                        if($data_parts[0] != 1900)
                        {
                            $moments[2]->date_done = $formatted_data;
                        }
                    }

                    if($quanti > 3)
                    {
                        $ultimo_index = 3;
                        // 4 momento
                        if($quanti == 4)
                        {
                            $data_done = $this->_getValue($objWorksheet, 'AK', $index);
                            $done = $this->_getValue($objWorksheet, 'BJ', $index);
                        }
                        else
                        {
                            $data_done = $this->_getValue($objWorksheet, 'CA', $index);
                            $done = $this->_getValue($objWorksheet, 'BV', $index);
                        }
                        if($commessa_chiusa || $done)
                        {
                            $moments[2]->done = 1;
                        }
                        if($data_done != '')
                        {
                            $formatted_data = PHPExcel_Style_NumberFormat::toFormattedString($data_done, PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
                            $data_parts = explode('-', $formatted_data);
                            if($data_parts[0] != 1900)
                            {
                                $moments[3]->date_done = $formatted_data;
                            }
                        }

                        if($quanti > 4)
                        {
                            $ultimo_index = 4;
                            // 4 momento
                            $data_done = $this->_getValue($objWorksheet, 'AK', $index);
                            $done = $this->_getValue($objWorksheet, 'BJ', $index);
                            if($commessa_chiusa || $done)
                            {
                                $moments[2]->done = 1;
                            }
                            if($data_done != '')
                            {
                                $formatted_data = PHPExcel_Style_NumberFormat::toFormattedString($data_done, PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
                                $data_parts = explode('-', $formatted_data);
                                if($data_parts[0] != 1900)
                                {
                                    $moments[4]->date_done = $formatted_data;
                                }
                            }
                        }
                    }
                }

                // ribaltiamo tutti i dati sull'ultimo momento
                $ultimo_index = count($moments) - 1;

                // sovrascrivo il momento dell'importo o no?
                //$moments[$ultimo_index]->importo = $this->_getValue($objWorksheet, 'AF', $index);

                $moments[$ultimo_index]->importo_real = ''; // da calcolare
                $moments[$ultimo_index]->p_valore_g_uomo = $this->_getValue($objWorksheet, 'AX', $index);
                $moments[$ultimo_index]->p_n_incontri = $this->_getValue($objWorksheet, 'AJ', $index);
                $moments[$ultimo_index]->c_ore_studio = $this->_getValue($objWorksheet, 'AQ', $index);
                $moments[$ultimo_index]->c_ore_azienda = $this->_getValue($objWorksheet, 'BL', $index);
                $moments[$ultimo_index]->c_ore_certificazione = $this->_getValue($objWorksheet, 'AR', $index);
                $moments[$ultimo_index]->c_n_incontri = $this->_getValue($objWorksheet, 'AT', $index);
                $moments[$ultimo_index]->c_ore_viaggio = $this->_getValue($objWorksheet, 'AS', $index);
                $moments[$ultimo_index]->c_n_km = $this->_getValue($objWorksheet, 'BN', $index);
                $moments[$ultimo_index]->c_costo_km = $this->_getValue($objWorksheet, 'BP', $index);

                $valore_g_uomo = ($moments[$ultimo_index]->p_valore_g_uomo) ?: 0;
                $val1 = ($moments[$ultimo_index]->c_ore_studio + $moments[$ultimo_index]->c_ore_azienda) * $valore_g_uomo / 8;
                $val2 = $moments[$ultimo_index]->c_ore_certificazione * $valore_g_uomo / 8;
                $val3 = $moments[$ultimo_index]->c_n_km * $moments[$ultimo_index]->c_costo_km;
                $moments[$ultimo_index]->importo_real = $val1 + $val2 + $val3;

                // salviamo tutti i momenti
                foreach($moments as $moment)
                {
                    $moment->setValidatorAndFilter(new Model_Moment_Validator());
                    if($moment->isValid())
                    {
                        $momentsRepo->save($moment);
                    }
                    else
                    {
                        -dd($moment->getInvalidMessages());
                    }

                }
                
                $order->note = $this->_getValue($objWorksheet, 'M', $index);
                if(($n2 = $this->_getValue($objWorksheet, 'AD', $index) != ''))
                {
                   $order->note .= "\n" . $n2;
                }
                if(($n2 = $this->_getValue($objWorksheet, 'BQ', $index) != ''))
                {
                   $order->note .= "\n" . $n2;
                }

                $order->note_pianificazione = $this->_getValue($objWorksheet, 'BR', $index);
                $order->note_consuntivo = $this->_getValue($objWorksheet, 'AY', $index);

                $order->ente = $this->_getValue($objWorksheet, 'AW', $index);
               
                if($order->isValid())
                {
                    $order_id = $orders_repo->save($order);
                   
                    // RC
                    // prima elimino i possibili precedenti
                    //$this->_db->delete('orders_rcos', array('id_order' => $order_id));
                   
                    $rco = $this->_getValue($objWorksheet, 'AE', $index);
                   
                    if($rco != '')
                    {
                        $data_assigned = $this->_getValue($objWorksheet, 'BB', $index);
                        if($data_assigned != '')
                        {
                            $formatted_data = PHPExcel_Style_NumberFormat::toFormattedString($data_assigned, PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
                            $data_parts = explode('-', $formatted_data);
                            if($data_parts[0] == 1900)
                            {
                                $data_parts[0] = 2003;
                                $formatted_data = implode('-', $data_parts);
                            }
                            $data_assigned = $formatted_data;
                        }
                        $this->_db->insert('orders_rcos', array(
                            'id_order' => $order_id,
                            'rco' => $rco,
                            'date_assigned' => $data_assigned,
                            'index' => 1
                        ));
                    }
                }
                else
                {
                    -dd($order->getInvalidMessages());
                }
            }
        }   
    }
}
