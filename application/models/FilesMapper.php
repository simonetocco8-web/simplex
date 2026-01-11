<?php

class Model_FilesMapper
{
    protected $_root_path;
    
    /**
    * Constructor
    * 
    * Loads the repo path
    */
    public function __construct()
    {
        $config = Zend_Registry::get('config');
        $this->_root_path = $config->files_root;        
    }
    
    /**
    * Retrieve the files at the given path or by company or user id
    * 
    * @param mixed $path
    * @param mixed $what
    * @param mixed $id
    * @return array
    */
    public function getFiles($path, $what, $id, $options = array())
    {
        $dir = $this->_root_path;
        $subdir = '';
        if($path)
        {
            $subdir = $this->decodePath($path);
        }
        else
        {
            if($what)
            {
                if($what == 'c')
                {
                    $subdir .= '/aziende';
                }
                elseif($what == 'u')
                {
                    $subdir .= '/utenti';
                }
                
                if($id)
                {
                    $subdir .= '/' . $id;
                }
            }
        }
        
        $dir .= '/' . $subdir;
        
        $files = array();
        $dirs = array();

        $path_parts = explode('/', $subdir);
        $first_part = $path_parts[0] != '' ? 0 : 1;
        $skip = count($path_parts) == $first_part + 2;

        if (is_dir($dir)) 
        {
            if ($dh = opendir($dir)) 
            {
                while (($file = readdir($dh)) !== false) 
                {
                    if($file == '.' || (($dir == $this->_root_path . '/' || $skip) && $file == '..'))
                    {
                        
                    }
                    else
                    {
                        /*
                        echo $file .'<br />';
                        echo ($subdir . '/' . $file) .'<br />';
                        echo realpath($this->_root_path . '/' . $subdir . '/' . $file) .'<br />';
                        
                        exit;
                        */
                        
                        if($file == '..')
                        {
                            $pathToFile = $this->encodePath(dirname($subdir));
                        }
                        else
                        {
                            $pathToFile = $this->encodePath($subdir . '/' . $file);
                        }

                        $last_modified = $this->getLastModified($subdir . '/' . $file);
                        
                        if($this->isDir($subdir . '/' . $file))
                        {
                            $dirs[] = array('name' => $file, 'path' => $pathToFile, 'folder' => true, 'size' => '', 'last_modified' => $last_modified);
                        }
                        else
                        {
                            $size = $this->getSize($subdir . '/' . $file) . ' KB';
                            $files[] = array('name' => $file, 'path' => $pathToFile, 'folder' => false, 'size' => $size, 'last_modified' => $last_modified);
                        }
                        
                        //echo "filename: $file : filetype: " . filetype($dir . $file) . "\n";
                    }
                }
                closedir($dh);
            }
        }

        if(isset($options['order']) && isset($options['order']['sort']) && $options['order']['sort'] != '')
        {
            $this->sortFiles($dirs, $options['order']);
            $this->sortFiles($files, $options['order']);
        }
        else
        {
            $this->sortFiles($dirs, array('sort' => 'name', 'dir' => 'DESC'));
            $this->sortFiles($files, array('sort' => 'name', 'dir' => 'DESC'));
        }

        $array = array_merge($dirs, $files);

        return $array;
    }

    public function sortFiles(& $files, $order)
    {
        $function = 'sort_by_';
        switch($order['sort'])
        {
            case 'name':
                $function .= 'name';
                break;
            case 'size':
                $function .= 'size';
                break;
            case 'last_modified':
                $function .= 'last_modified';
                break;
        }

        $function .= '_' . $order['dir'];

        usort($files, array($this, $function));
    }

    public function sort_by_name_ASC($a, $b)
    {
        return $a['name'] < $b['name'];
    }

    public function sort_by_name_DESC($a, $b)
    {
        return $a['name'] > $b['name'];
    }

    public function sort_by_size_ASC($a, $b)
    {
        return $a['size'] < $b['size'];
    }

    public function sort_by_size_DESC($a, $b)
    {
        return $a['size'] > $b['size'];
    }

    public function sort_by_last_modified_ASC($a, $b)
    {
        return $a['last_modified'] < $b['last_modified'];
    }

    public function sort_by_last_modified_DESC($a, $b)
    {
        return $a['last_modified'] > $b['last_modified'];
    }

    public function getSize($path, $encoded = false)
    {
        if($encoded)
        {
            $size = filesize($this->_root_path . $this->decodePath($path));
        }
        else
        {
            $size = filesize($this->_root_path . $path);
        }

        return round(($size / 1024), 2); // bytes to KB
    }

    public function getLastModified($path, $encoded = false)
    {
        $file_path = $encoded
                ? $this->_root_path . $this->decodePath($path)
                : $this->_root_path . $path;

        return date ("Y-m-d H:i:s", filemtime($file_path));
    }

    public function cleanPath($path)
    {
        $path = realpath($this->_root_path . $path);
        $root = realpath($this->_root_path);
        $path = str_replace($root, '', $path);
        return $path;
    }
    
    public function isDir($path, $encoded = false)
    {
        if($encoded)
        {
            return is_dir($this->_root_path . $this->decodePath($path));
        }
        else
        {
            return is_dir($this->_root_path . $path);
        }
        
    }
    
    public function pathExists($path)
    {
        return (file_exists($this->_root_path . $path));
    }
    
    public function getRepoPath()
    {
        return $this->_root_path;
    }

    public function getOfferPdfFolder($offer)
    {
        $user = Zend_Auth::getInstance()->getIdentity();

        $offer_folder = '/aziende/'
            . $user->internal_abbr . '_'
            . $offer->company->company_id . '/commerciale/'
            . $offer->service_code . '_'
            . $offer->service_name . '/' . $offer->year;

        return $offer_folder;
    }

    public function getOfferPdfFileName($offer)
    {
        $offer_filename = $offer->code_offer . '.pdf';
        return $offer_filename;
    }

    public function getOfferPdf($offer)
    {
        $folder = $this->getOfferPdfFolder($offer);
        $file_name = $this->getOfferPdfFileName($offer);
        $offer_file = $folder . '/' . $file_name;

        if($this->pathExists($offer_file))
        {
            return $this->encodePath($offer_file);
        }

        return false;
    }

    public function getOrderPdfFolder($order)
    {
        $user = Zend_Auth::getInstance()->getIdentity();

        $order_folder = '/aziende/'
            . $user->internal_abbr . '_'
            . $order->offer->company->company_id . '/produzione/'
            . $order->offer->service_code . '_'
            . $order->offer->service_name . '/' . $order->year;

        return $order_folder;
    }

    public function getOrderPdfFileName($order)
    {
        $order_filename = $order->code_order . '.pdf';
        return $order_filename;
    }

    public function getOrderPdf($order)
    {
        $folder = $this->getOrderPdfFolder($order);
        $file_name = $this->getOrderPdfFileName($order);
        $order_file = $folder . '/' . $file_name;

        if($this->pathExists($order_file))
        {
            return $this->encodePath($order_file);
        }

        return false;
    }
    
    public function getTemplatePath($full = true)
    {
        if($full)
        {
            return $this->_root_path . '/templates/';
        }
        return '/templates/';
    }

    public function getDecodedPublicPath($path)
    {
        $path = $this->decodePath($path);
        return implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, $path), 3));
    }

    public function decodePath($path)
    {
        return base64_decode($path);
    }
    
    public function encodePath($path)
    {   
        return base64_encode($path);
    }

    public function buildPath($path)
    {
        $parts = explode('/', $path);

        $check = '';

        foreach($parts as $path)
        {
            if($path)
            {
                if(!$this->pathExists($check . '/' . $path))
                {
                    $this->createFolder($check, $path, false);
                }

                $check .= '/' . $path;
            }
        }
    }

    public function upload($path = '', $rename = false, $pathIsFolder = false, $overwrite = false, $archive = false)
    {
        if (!empty($_FILES)) {
            
            if($pathIsFolder && !$rename)
            {
                return 0;
            }
            
            $tempFile = $_FILES['Filedata']['tmp_name'];

            $subdir = '/';
            if($path != '')
            {
                $subdir = $this->decodePath($path);
            }
            
            $targetPath = $dir = $this->_root_path . $subdir;
            $targetDir = str_replace('//','/',$targetPath) . '/';
            $targetFile = '';
            
            if($pathIsFolder)
            {
                $targetFile = $rename;
            }
            else
            {
                $targetFile = $_FILES['Filedata']['name'];
                if($rename)
                {
                    // TODO: gestire il rename
                }
            }
            
            // $fileTypes  = str_replace('*.','',$_REQUEST['fileext']);
            // $fileTypes  = str_replace(';','|',$fileTypes);
            // $typesArray = split('\|',$fileTypes);
            // $fileParts  = pathinfo($_FILES['Filedata']['name']);
            
            // if (in_array($fileParts['extension'],$typesArray)) {
                // Uncomment the following line if you want to make the directory if it doesn't exist
                // mkdir(str_replace('//','/',$targetPath), 0755, true);
                
                if(file_exists($targetDir . $targetFile))
                {
                    if(!$overwrite)
                    {
                        // rinomina - appende un numero
                        $targetFile = $this->renameFileIfExists($targetDir, $targetFile);
                    }
                    else
                    {
                        // sovrascrive
                        unlink($targetDir . $targetFile);
                    }
                }

                if($archive)
                {
                    $zip = new ZipArchive;
                    if ($zip->open($tempFile) === TRUE)
                    {
                        $zip->extractTo($targetDir);
                        $zip->close();
                        unlink($tempFile);
                        return 1;
                    }
                    else
                    {
                        unlink($tempFile);
                        return 0;
                    }
                }

                if(move_uploaded_file($tempFile, $targetDir . $targetFile))
                {
                    return 1;
                }
                else
                {
                    return 0;
                }
                //echo str_replace($_SERVER['DOCUMENT_ROOT'],'',$targetFile);
                
            // } else {
            //     echo 'Invalid file type.';
            // }
        }
        return 0;
    }
    
    protected function renameFileIfExists($dir, $filename)
    {
        $ext = strrchr($filename, '.');
        $prefix = substr($filename, 0, -strlen($ext));
        
        $i = 1;
        while(file_exists($dir . $filename)) 
        { // If file exists, add a number to it.
            $filename = $prefix . ' (' . ++$i . ')' . $ext;
        }

        return $filename;
    }
    
    public function createFolder($path, $new_folder, $encoded = true)
    {
        if($encoded)
        {
            $real = $this->decodePath($path);
        }
        else
        {
            $real = $path;
        }

        if($real[strlen($real) -1] == '/')
        {
            $new = $real . $new_folder;
        }
        else
        {
            $new = $real . '/' . $new_folder;
        }

        if($new[0] == '/')
        {
            $new_folder_path = $this->getRepoPath() . $new;
        }
        else
        {
            $new_folder_path = $this->getRepoPath() . '/' . $new;
        }

        return mkdir($new_folder_path);
    }

    protected function addItemToArchive($path, $folder, $subfolder, ZipArchive &$zip)
    {
        if($subfolder == '')
        {
            $subfolder = '/';
        }
        $realFilePath = $this->_root_path . $folder . $subfolder . $path;

        if(is_dir($realFilePath))
        {
            $zip->addEmptyDir($subfolder . $path);
            $nodes = glob($realFilePath . '/*');

            //$newSubFolder = $subfolder . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR;
            $newSubFolder = ($subfolder == '') ? DIRECTORY_SEPARATOR : $subfolder;
            $newSubFolder .= $path . DIRECTORY_SEPARATOR;

            foreach($nodes as $node)
            {
                $this->addItemToArchive(basename($node), $folder, $newSubFolder, $zip);
            }
        }
        else
        {
            $zip->addFile($realFilePath, $subfolder . $path);
        }

    }

    public function downloadArchive($realPath, $files)
    {
        $zip = new ZipArchive;
        $archiveName = tempnam($this->_root_path, 'zip');

        if ($zip->open($archiveName, ZipArchive::OVERWRITE) === TRUE)
        {
            foreach($files as $file)
            {
                $this->addItemToArchive($file, $realPath, '', $zip);
                /*
                $realFilePath = $this->_root_path . $realPath . DIRECTORY_SEPARATOR . $file;

                if(!is_file($realFilePath))
                {
                    echo $realFilePath . ' not file';
                }
                if(!$zip->addFile($realFilePath, $file))
                {
                    echo $realFilePath . ' NOT ADDED';
                }
                */
            }
            $zip->close();

            if(!is_file($archiveName))
            {
                echo $archiveName . ' non file';
            }

            if(!is_readable($archiveName))
            {
                echo $archiveName . ' non leggibile';
            }

            header('Content-Type: application/zip');
            header('Content-Length: ' . filesize($archiveName));
            header('Content-Disposition: attachment; filename="archivio.zip"');
            readfile($archiveName);
            unlink($archiveName);
            exit;
        } else {
            echo 'failed';
        }
    }

    /*
     This function takes a path to a file to output ($file), 
     the filename that the browser will see ($name) and 
     the MIME type of the file ($mime_type, optional).
     
     If you want to do something on download abort/finish,
     register_shutdown_function('function_name');
     */
    public function download($file, $deleteAfterDownload = false)
    {
        $file = $this->_root_path . $file;

        if(!is_readable($file))
        {
            return false;
            //die('File not found or inaccessible!');
        }

        $size = filesize($file);
        $name = rawurldecode(basename($file));

        /* Figure out the MIME type (if not specified) */
        $known_mime_types=array(
             "pdf" => "application/pdf",
             "txt" => "text/plain",
             "html" => "text/html",
             "htm" => "text/html",
            "exe" => "application/octet-stream",
            "zip" => "application/zip",
            "doc" => "application/msword",
            "xls" => "application/vnd.ms-excel",
            "ppt" => "application/vnd.ms-powerpoint",
            "gif" => "image/gif",
            "png" => "image/png",
            "jpeg"=> "image/jpg",
            "jpg" =>  "image/jpg",
            "php" => "text/plain"
         );
 
        $file_extension = strtolower(substr(strrchr($file,"."),1));
        if(array_key_exists($file_extension, $known_mime_types))
        {
            $mime_type=$known_mime_types[$file_extension];
        } 
        else 
        {
            $mime_type="application/force-download";
        }
 
        @ob_end_clean(); //turn off output buffering to decrease cpu usage
 
        // required for IE, otherwise Content-Disposition may be ignored
        if(ini_get('zlib.output_compression'))
        {
            ini_set('zlib.output_compression', 'Off');
        }
 
        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="'.$name.'"');
        header("Content-Transfer-Encoding: binary");
        header('Accept-Ranges: bytes');
 
        /* The three lines below basically make the 
        download non-cacheable */
        header("Cache-control: private");
        header('Pragma: private');
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

        // multipart-download and download resuming support
        if(isset($_SERVER['HTTP_RANGE']))
        {
            list($a, $range) = explode("=",$_SERVER['HTTP_RANGE'],2);
            list($range) = explode(",",$range,2);
            list($range, $range_end) = explode("-", $range);
            $range=intval($range);
            if(!$range_end) 
            {
                $range_end=$size-1;
            } 
            else 
            {
                $range_end=intval($range_end);
            }
 
            $new_length = $range_end-$range+1;
            header("HTTP/1.1 206 Partial Content");
            header("Content-Length: $new_length");
            header("Content-Range: bytes $range-$range_end/$size");
        } 
        else 
        {
            $new_length=$size;
            header("Content-Length: ".$size);
        }
 
        /* output the file itself */
        $chunksize = 1*(1024*1024); //you may want to change this
        $bytes_send = 0;
        if ($file = fopen($file, 'r'))
        {
            if(isset($_SERVER['HTTP_RANGE']))
            {
                fseek($file, $range);
            }
 
            while(!feof($file) && 
                (!connection_aborted()) && 
                ($bytes_send<$new_length))
            {
                $buffer = fread($file, $chunksize);
                print($buffer); //echo($buffer); // is also possible
                flush();
                $bytes_send += strlen($buffer);
            }
            fclose($file);
        } 
        else
        { 
            return false;
            //die('Error - can not open file.');
        }

        if($deleteAfterDownload)
        {
            unlink($file);
        }

        die();
    }
    
    public function rm($path)
    {
        $realPath = $this->_root_path . $path;
        if($this->isDir($path))
        {
            return $this->removeFolder($realPath);
        }
        else
        {
            return unlink($realPath);
        }
    }
    
    public function removeFolder($dir)
    {
        if (is_dir($dir)) 
        {
            $objects = scandir($dir);
            foreach ($objects as $object) 
            {
                if ($object != "." && $object != "..") 
                {
                    if (filetype($dir."/".$object) == "dir")
                    {
                        $this->removeFolder($dir."/".$object);
                    } 
                    else
                    {
                        unlink($dir."/".$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
        return true;
    }
    
    public function createFolderIfCompanyOrUser($path)
    {
        $sub = dirname($path) . '/';
        
        if($sub == '/aziende/' || $sub == '/utenti/')
        {
            $last = str_replace($sub, '', $path);
            return $this->createFolder($sub, $last, false);
        }
    }
}
