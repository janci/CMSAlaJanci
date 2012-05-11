<?php
/**
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 *
 * Copyright (c) 2012 Ján Švantner (http://www.janci.net)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

class News extends CMSControl implements IFilter {
    
    protected function attached($presenter) {
        parent::attached($presenter);
        $this->getTemplate()->news = $this->db->table('new')->where('remove',0)->order('created DESC')->limit(10);
    }
    
    public function filter($content){
        $template = $this->getTemplate();
        $template->setFile(dirname(__FILE__). '/template.latte');
        return str_replace('{news}', $template, $content);
    }
    
    public function render(){
        $template = $this->getTemplate();
        $template->setFile(dirname(__FILE__). '/template.latte');
        $template->render();
    }
}

