<?php
abstract class CMSControl extends NControl
{
    protected $db;
    private $default_template;
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
    
    public function getConfig(){
        $dir = dirname(dirname(dirname($this->getReflection()->getFileName())));
        $config_file = $dir.DIRECTORY_SEPARATOR.'config.yaml';

        if(file_exists($config_file)){
            return spyc_load_file($config_file);
        } else {
            $name = $this->getReflection()->getName();
            return array('name'=>$name);
        }
    }
    
    public function configure($config){
        
    }
}
