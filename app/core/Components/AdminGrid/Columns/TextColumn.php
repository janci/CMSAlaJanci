<?php

/*
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 * 
 * Copyright (c) 2012 Ing. Švantner Ján <janci@janci.net>
 * 
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

/**
 * TextColumn for DataGrid
 *
 * @author Ing. Švantner Ján <janci@janci.net>
 */
class TextColumn extends NObject implements IDatagridColumn {
    
    protected $align="left";
    protected $columnname;
    protected $title;
    protected $width="auto";
    
    public function applyConfiguration($configuration){
        $this->columnname = $configuration['name'];
        $this->title = $configuration['title'];
        if(isset($configuration['align']))
            $this->align = $configuration['align'];
        if(isset($configuration['width']))
            $this->width = $configuration['width'];
    }
    
    public function filter($text) {
        return NTemplateHelpers::escapeHtml($text);
    }
    
    public function getAlign() {
        return $this->align;
    }
    
    public function getColumnName() {
        return $this->columnname;
    }
    
    public function getTitle() {
        return $this->title;
    }
    
    public function getWidth() {
        return $this->width;
    }
    
    public function modifySelection(NTableSelection $selection) {
        $selection->select($this->getColumnName());
    }
}
