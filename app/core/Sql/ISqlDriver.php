<?php

/*
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 * 
 * Copyright (c) 2012 Ing. Švantner Ján <janci@janci.net>
 * 
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

/**
 * interface for description driver fo Sql
 * @author Ing. Švantner Ján <janci@janci.net>
 */
interface ISqlDriver {
    /**
     * set configure 
     */
    public function setConfig($config);
    
    /** 
     * run sql by config 
     */
    public function run(NConnection $connection);
    
    /**
     * reconfigure configuration for support database type 
     */
    public static function reconfigure($configuration);
}

