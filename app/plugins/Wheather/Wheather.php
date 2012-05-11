<?php
/**
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 *
 * Copyright (c) 2012 Ján Švantner (http://www.janci.net)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

class Wheather extends CMSControl {
    const SERVER_URL = "http://api.janci.net/wheather/sk/";
    
    public function render($param=null){
        if(!isset($param['city'])) throw new Exception('Missing city param.');
        $template = $this->getDefaultTemplate();
        $param['city'] = str_replace('-', '%20', NStrings::webalize($param['city']));
        $template->wheather = json_decode(file_get_contents(self::SERVER_URL.$param['city']));
        $template->temp_style = isset($param['temp_style'])?$param['temp_style']:"";
        $template->render();
    }
}

