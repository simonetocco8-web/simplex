<?php

class Maco_Input_Utils
{
    /**
    * Format the multiple values (name[]) data passed (via POST) in a more 
    * suitable way to manage for saving into db
    * 
    * @param mixed $fields array of fields to extract from the data. if a key is given then it will replace the data key
    * @param string the prefix of the input name
    * @param mixed $data
    */
    public function formatDataForMultipleFields($fields, $inputNamePrefix, $data)
    {
        $returnData = array();
        if($data instanceof Zend_Filter_Input)
        {
            for($i = 0;; $i++)
            {
                $rowExists = false;
                $rowGood = false;
                foreach($fields as $field)
                {
                    $realField = $inputNamePrefix . $field;
                    $row = $data->$realField;
                    if(isset($row[$i]))
                    {
                        $rowExists = true;
                        if(!empty($row[$i]))
                        {
                            $rowGood = true;
                            break;
                        }
                    }
                }

                if(!$rowExists || !$rowGood)
                {
                    return $returnData;
                }
                else
                {
                    $d = array();
                    foreach($fields as $key => $field)
                    {
                        $realField = $inputNamePrefix . $field;
                        $row = $data->$realField;
                        if(is_int($key))
                        {
                            $d[$field] = $row[$i];
                        }
                        else
                        {
                            $d[$key] = $row[$i];
                        }
                    }
                    $returnData[] = $d;
                }
            }
        }
        elseif(is_array($data))
        {
            for($i = 0;; $i++)
            {
                $rowExists = false;
                $rowGood = false;
                foreach($fields as $field)
                {
                    $realField = $inputNamePrefix . $field;
                    if(isset($data[$realField]))
                    $row = $data[$realField];
                    
                    if(isset($row[$i]))
                    {
                        $rowExists = true;                        
                        if(!empty($row[$i]))
                        {
                            $rowGood = true;
                            break;
                        }
                    }
                }

                if(!$rowExists || !$rowGood)
                {
                    return $returnData;
                }
                else
                {
                    $d = array();
                    foreach($fields as $key => $field)
                    {
                        $realField = $inputNamePrefix . $field;
                        if (isset($data[$realField]))
                        $row = $data[$realField];
                        
                        if(is_int($key))
                        {
                            $d[$field] = $row[$i];
                        }
                        else
                        {
                            $d[$key] = $row[$i];
                        }
                    }
                    $returnData[] = $d;
                }
            }
        }
        else
        {
            throw new Exception('I valori passati devono essere ho un array o un\'istanza di Zend_Filter_Input');
        }

        return $returnData;
    }
    
    public function parseInput($data, $prefixes = array())
    {
        $parsed = array();
        
        foreach($data as $key => $value)
        {
            foreach($prefixes as $prefix)
            {
                $value = strstr($key, $prefix);
            }
        }
    }
}
