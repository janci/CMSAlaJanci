<?php

/*
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 * 
 * Copyright (c) 2012 Ing. Švantner Ján <janci@janci.net>
 * 
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

/**
 * LinkAction create standard action to open other page
 *
 * @author Ing. Švantner Ján <janci@janci.net>
 */
class LinkAction implements IDatagridAction {
    
    private $icon;
    private $link;
    private $tooltip;
    
    public function getIcon() {
        return $this->icon;
    }
    
    public function getLink(NControl $control, $record_id) {
        return $control->getPresenter()->link($this->link, array('id'=>$record_id));
    }
    

    public function configure($config) {
        $this->icon = $config['icon'];
        $this->link = $config['link'];
        $this->tooltip = $config['tooltip'];
    }

    public function getTooltip() {
        return $this->tooltip;
    }
}
