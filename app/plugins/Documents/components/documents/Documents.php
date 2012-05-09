<?php

class Documents extends CMSControl implements IFilter {
    
    protected function attached($presenter) {
        parent::attached($presenter);
        $this->getTemplate()->documents = $this->db->table('document')->where('remove',0)->order('created DESC');
    }
    
    public function filter($content){
        $template = $this->getDefaultTemplate();
        return str_replace('{documents}', $template, $content);
    }
    
    public function admin(){
        AdminPresenter::extensionMethod('handleRemoveNew', array($this,'handleRemoveNew'));
    }
    
    public function render(){
        $template = $this->getDefaultTemplate();
        $template->render();
    }
}

