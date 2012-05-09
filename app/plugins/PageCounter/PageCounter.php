<?php

class PageCounter extends CMSControl {
    public function render($param=null){
        if(!isset($param['token'])) throw new Exception('Missing token param.');
        
        /**  Statistiky **/
        $token = $param['token'];//'61ba4e7267419050eaf9b581bc092bdf';
        $piwik_url = 'piwik.janci.net';
        $piwik_protokol = 'http';
        $piwik_method = 'VisitsSummary.getVisits';

        $statistics_url  = $piwik_protokol.'://'.$piwik_url.'/?module=API&method='.$piwik_method;
        $statistics_url .='&idSite=10&period=year&date=today&format=PHP&prettyDisplay=true&token_auth='.$token;

        $data = file_get_contents($statistics_url);

        $getdata = unserialize($data);
                
        
        $template = $this->getDefaultTemplate();
        $template->counter = $getdata;
        if(isset($param['style'])) $template->style = $param['style'];
        $template->render();
    }
}

