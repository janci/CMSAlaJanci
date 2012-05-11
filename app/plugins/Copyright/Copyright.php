<?php
/**
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 *
 * Copyright (c) 2012 Ján Švantner (http://www.janci.net)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

/**
 * Components for rendering copyright
 *
 * @author Svantner Jan
 */
class Copyright extends CMSControl {
    
    /**
     * Set company url
     * @param int $year 
     */
    public function setCompanyUrl($url){
        $this->template->company_url = $url;
    }
    
    /**
     * Set created year
     * @param int $year 
     */
    public function setCreatedYear($year){
        $this->template->created_year = $year;
    }    
    
    /**
     * Set current year
     * @param int $year 
     */
    public function setCurrentYear($year){
        $this->template->year = $year;
    }
    
    /**
     * Set company name
     * @param string $company_name 
     */
    public function setCompanyName($company_name){
        $this->template->company_name = $company_name;
    }
    
    public function configure($config) {
        parent::configure($config);
        if(isset($config['year'])) $this->setCreatedYear($config['year']);
        if(isset($config['company_name'])) $this->setCompanyName($config['company_name']);
        if(isset($config['company_url'])) $this->setCompanyUrl($config['company_url']);
    }
    
    /**
     * Render year part 
     */
    public function renderYear(){
        $template = $this->getDefaultTemplate();
        $template->render();
    }
    
    /**
     * Render company name part 
     */
    public function renderCompany(){
        $template = $this->getDefaultTemplate();
        $template->render();
    }
    
    /**
     * render copyright component 
     */
    public function render(){
        $template = $this->getDefaultTemplate();
        $template->render();
    }
}

