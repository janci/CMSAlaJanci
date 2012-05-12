<?php
/**
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 *
 * Copyright (c) 2012 Ján Švantner (http://www.janci.net)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

abstract class CMSControl extends NControl
{
    protected $db;
    private $default_template;
    private $plugin;
    
    protected function attached($presenter) {
        parent::attached($presenter);
        $this->db = $presenter->getService('database');
    }
    
    public function getDefaultTemplate(){
        if(isset($this->default_template)) return $this->default_template;
        $template = $this->getTemplate();
        $template->setFile(dirname($this->getReflection()->getFileName()).DIRECTORY_SEPARATOR. 'template.latte');
        return $this->default_template=$template;
    }
    
    public function setPlugin(BasePlugin $plugin){
        $this->plugin = $plugin;
    }
    
    public function getPlugin(){
        if(!isset($this->plugin))
            throw new PluginException("Not any initialize plugin for component.");
        return $this->plugin;
    }
    
    public function getConfig(){
        $this->getPlugin()->getConfig();
    }
    
    public function configure($config){
        
    }
}
