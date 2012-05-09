<?php

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

