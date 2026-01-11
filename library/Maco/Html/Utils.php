<?php

class Maco_Html_Utils
{
    public function parseDbRowsForSelectElement($rows, $idField, $field, $extras = array(), $delimiter = ', ', $extraDelimiter = ' ')
    {
        $ret = array();
        foreach($rows as $row)
        {
            $ret[$row[$idField]] = $row[$field];
            
            if(!empty($extras))
            {
                $one = true;
                foreach($extras as $f)
                {
                    if($row[$f] != '')
                    {
                        $del = $extraDelimiter;
                        if($one)
                        {
                            $del = $delimiter;
                            $one = false;
                        }
                        
                        $ret[$row[$idField]] .= $del . $row[$f];
                    }
                }
            }
        }
        
        return $ret;
    }
}
