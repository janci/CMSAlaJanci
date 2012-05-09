<?php

class Gallery extends CMSControl implements IFilter {
    
    protected function attached($presenter) {
        parent::attached($presenter);
        $this->getTemplate()->gallery = $this->db->table('album')->where('remove',0);
    }
    
    public function filter($content){
        $template = $this->getTemplate();
        $template->setFile(dirname(__FILE__). '/template.latte');
        return str_replace('{albums}', $template, $content);
    }
    
    public function render(){
        $template = $this->getTemplate();
        $template->setFile(dirname(__FILE__). '/template.latte');
        $template->render();
    }
}

