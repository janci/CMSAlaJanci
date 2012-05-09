<?php
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
     * as $name => CMSControl
     * @var array
     */
    private $plugins;
    
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
    public function attachComponents(){        
        $robotloader = $this->presenter->getService('robotloader');
        $classes = $robotloader->getIndexedClasses();
        $diparams = $this->presenter->getContext()->getParameters();
        $config = isset($diparams['plugins'])? $diparams['plugins']:array();
        foreach($classes as $class=>$filename){
            if(!class_exists($class)) continue;
            
            if(is_subclass_of($class, 'CMSControl')){
                $plugin_name = NStrings::lower($class);
                $control = new $class;
                
                if(isset($config[$plugin_name])) $control->configure($config[$plugin_name]);
                $this->presenter[$plugin_name] = $control;
                $this->plugins[$plugin_name] = $control;
                //if($plugin_name=='news') $control->getConfig();
            }
        }
    }
    
    /**
     * get plugin by name
     * @param string $pluginname
     * @return CMSControl
     * @throws PluginException 
     */
    public function getPlugin($pluginname){
        if (!isset($this->plugins[$pluginname])) throw new PluginException("Missing plugin `".$pluginname."`");
        return $this->plugins[$pluginname];
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
        foreach($this->plugins as $plugin){
            if($plugin instanceof IFilter)
                $this->content = $plugin->filter($this->content);
        }
    }
}

