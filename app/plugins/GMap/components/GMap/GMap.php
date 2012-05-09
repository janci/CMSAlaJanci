<?php

class GMap extends CMSControl implements IFilter {
    
    public function filter($content){
        $template = $this->getTemplate();
        $template->setFile(dirname(__FILE__). '/template.latte');
        return str_replace('{gmap}', $template, $content);
    }
    
    public function render(){
        $template = $this->getTemplate();
        $template->setFile(dirname(__FILE__). '/template.latte');
        $template->render();
    }
}

