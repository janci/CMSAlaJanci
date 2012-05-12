<?php
/**
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 *
 * Copyright (c) 2012 Ján Švantner (http://www.janci.net)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

class ComponentBuilder extends NObject {    
    /**
     * current load presenter
     * @var BasePresenter
     */
    private $presenter;
    
    /**
     * page content
     * @var type 
     */
    private $content;
    
    /**
     * as $name => BasePlugin
     * @var array
     */
    private $plugins;
    
    /**
     * as $name=>CMSControl
     * @var array
     */
    private $controls;
    
    public function __construct($presenter){
        $this->presenter = $presenter;
    }
    
    /**
     * set page content for modification
     * @param string $content 
     */
    public function setContent($content){
        $this->content = $content;
    }
    
    /**
     * get modification content
     * @return string 
     */
    public function getContent(){
        return $this->content;
    }
    
    /**
     * attached components to presenter 
     */
    public function loadPlugins(){        
        $robotloader = $this->presenter->getService('robotloader');
        $classes = $robotloader->getIndexedClasses();
        $diparams = $this->presenter->getContext()->getParameters();
        $config = isset($diparams['plugins'])? $diparams['plugins']:array();
        foreach($classes as $class=>$filename){
            if(!class_exists($class)) continue;
            
            if(is_subclass_of($class, 'BasePlugin')){
                $plugin_name = NStrings::lower($class);
                $plugin = new $class;
                $plugin->installSql($this->presenter->getService('database'));
                
                $controls = $plugin->getControls();
                foreach($controls as $control){
                    $control->setPlugin($plugin);
                    if(isset($config[$plugin_name])) $control->configure($config[$plugin_name]);
                    $this->presenter[$plugin_name] = $control;
                    $this->controls[NStrings::lower($control->getName())] = $control;
                    $this->plugins[$plugin_name] = $plugin;
                }
            }
        }
    }
    
    /**
     * get plugin by name
     * @param string $pluginname
     * @return BasePlugin
     * @throws PluginException 
     */
    public function getPlugin($pluginname){
        if (!isset($this->plugins[$pluginname])) throw new PluginException("Missing plugin `".$pluginname."`");
        return $this->plugins[$pluginname];
    }
    
    /**
     * get all loaded controls from plugins 
     */
    public function getControls(){
        return $this->controls;
    }
    
    /**
     * get control by name
     * @param string
     * @return CMSControl
     */
    public function getControl($name){
        if (!isset($this->controls[$name])) throw new PluginException("Missing control with name `".$name."`");
        return $this->controls[$name];
    }
    
    /**
     * get all loaded plugins 
     */
    public function getPlugins(){
        return $this->plugins;
    }
    
    /**
     * run filter for content 
     */
    public function filter(){
        foreach($this->controls as $control){
            if($control instanceof IFilter)
                $this->content = $control->filter($this->content);
        }
    }
}

