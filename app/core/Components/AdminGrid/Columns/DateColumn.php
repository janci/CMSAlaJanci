<?php

/*
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 * 
 * Copyright (c) 2012 Ing. Švantner Ján <janci@janci.net>
 * 
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

/**
 * Column for show format date
 *
 * @author Ing. Švantner Ján <janci@janci.net>
 */
class DateColumn extends TextColumn {
    protected $align = "center";
    
    public function filter($text) {
        $text = date('d.m.Y', $text);
        return parent::filter($text);
    }
}
