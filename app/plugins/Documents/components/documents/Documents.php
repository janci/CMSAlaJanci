<?php
/**
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 *
 * Copyright (c) 2012 Ján Švantner (http://www.janci.net)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

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

