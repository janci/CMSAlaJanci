<?php
/**
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 *
 * Copyright (c) 2012 Ján Švantner (http://www.janci.net)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

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

