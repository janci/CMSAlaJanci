<?php
/**
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 *
 * Copyright (c) 2012 Ján Švantner (http://www.janci.net)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

/**
 * Abstract class for plugins
 *
 * @author Švantner Ján <janci@janci.net>
 */
abstract class BasePlugin extends NObject {
    /** @var array */
    private $config;
    
    /** @var array */
    private $controls;
    
    /** @var BasePresenter */
    private $presenter;
    
    /**
     * initialize all controls for plugin from configuration 
     */
    public function initializeControls(){
        if(isset($this->controls)) return;
        
        $config = $this->getConfig();
        $this->controls = array();
        
        if(!isset($config['controls'])) return;
        foreach($config['controls'] as $control){
            $this->controls[] = new $control;
        }
    }
    
    /**
     * get plugin name
     * @return string 
     */
    public function getName(){
        $config = $this->getConfig();
        return $config['name'];
    }
    
    public function getControls(){
        $this->initializeControls();
        return $this->controls;
    }
    
    /**
     * get configuration of plugin
     * @return type 
     */
    public function getConfig(){
        if(isset($this->config)) return $this->config;
        
        $dir = dirname($this->getReflection()->getFileName());
        $config_file = $dir.DIRECTORY_SEPARATOR.'config.yaml';

        if(file_exists($config_file)){
            return $this->config = spyc_load_file($config_file);
        } else {
            $name = $this->getReflection()->getName();
            return $this->config = array('name'=>$name);
        }
    }
    
    public function installSql(NConnection $connection){
        $dir = dirname($this->getReflection()->getFileName());
        $config_file = $dir.DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'config.nsql';
        if(file_exists($config_file)) {
            $sql = Sql::fromNsql($config_file);
            $sql->run($connection);
        }
    }
}

