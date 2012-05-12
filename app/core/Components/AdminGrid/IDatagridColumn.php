<?php

/*
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 * 
 * Copyright (c) 2012 Ing. Švantner Ján <janci@janci.net>
 * 
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

/**
 * Represent one column type in datagrid
 * @author Ing. Švantner Ján <janci@janci.net>
 */
interface IDatagridColumn {
    /**
     * return width for column in perzent or px
     * @return string
     */
    public function getWidth();
    
    /**
     * return column title
     * @return string 
     */
    public function getTitle();
    
    /**
     * apply select to selection
     */
    public function modifySelection(NTableSelection $selection);
    
    /**
     * return text alignment (center/left/right)
     * @return string 
     */
    public function getAlign();
    
    /**
     * return column name inserted to selection
     * @return string 
     */
    public function getColumnName();
    
    /**
     * filter on output (eg. datetime)
     * @return string
     */
    public function filter($text);
    
    /**
     * set values by configuration 
     */
    public function applyConfiguration($configuration);
}
