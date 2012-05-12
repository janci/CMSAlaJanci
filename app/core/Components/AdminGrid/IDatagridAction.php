<?php

/*
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 * 
 * Copyright (c) 2012 Ing. Švantner Ján <janci@janci.net>
 * 
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

/**
 * interface to description actions in datagrid
 * @author Ing. Švantner Ján <janci@janci.net>
 */
interface IDatagridAction {
    
    /**
     * return string to plink
     * @return string 
     */
    public function getLink(NControl $control, $record_id);
    
    /**
     * return path to icon
     * @return string 
     */
    public function getIcon();
    
    /**
     * configure action by array
     * @param array
     */
    public function configure($config);
    
    /**
     * get action tooltip
     * @return string 
     */
    public function getTooltip();
}

