<?php
/**
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 *
 * Copyright (c) 2012 Ján Švantner (http://www.janci.net)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

class Publisher extends NObject {
    
    private $public_directory = "."; 
    private $files;
    
    public function publicDirectory($namespace, $dirname){
        $this->files[$namespace][] = $dirname;
    }
    
    public function publicFile($namespace, $filename){
        $this->files[$namespace][] = $filename;
    }
    
    public function publish(){
        if(!isset($this->files)) return;
        
        $d = DIRECTORY_SEPARATOR;
        $pub_dir = $this->getPubDir();
        if(!file_exists($pub_dir)) mkdir($pub_dir);
        
        foreach($this->files as $namespace => $filepack){
            $path = str_replace('.',$d,$namespace);
            $checklist = explode('.',$namespace);
            
            $checkpath = $pub_dir;
            foreach($checklist as $directory){
                $checkpath = $checkpath.$d.$directory;
                if(!file_exists($checkpath)) mkdir($checkpath);
            }
            
            foreach($filepack as $dir_file){
                if(file_exists($pub_dir.$d.$path.$d.basename($dir_file))) continue;
                $success = symlink($dir_file, $pub_dir.$d.$path.$d.basename($dir_file));
                if($success == false) 
                    throw new InvalidHostingException("Can't create symlink {$dirname} to ".$pub_dir.$d.$path);
            }
        }
    }
    
    private function getPubDir(){
        return $this->public_directory.DIRECTORY_SEPARATOR.'pub';
    }
    
    public function filePath($namespace, $filename){
        $d = DIRECTORY_SEPARATOR;
        $path = str_replace('.', $d, $namespace);
        $file = $this->getPubDir().$d.$path.$d.$filename;
        
        if(!file_exists($file)) return null;
        return realpath($file);
    }
    
    public function wwwPath($namespace, $filename){
        if($this->filePath($namespace, $filename)==null) return "#";
        else return '/pub/'.str_replace ('.', '/', $namespace).'/'.$filename;
    }
    
    public function clean(){
        rmdir($this->public_directory.DIRECTORY_SEPARATOR.'pub');
    }
    
}
