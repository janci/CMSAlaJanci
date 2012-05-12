<?php

/*
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 * 
 * Copyright (c) 2012 Ing. Švantner Ján <janci@janci.net>
 * 
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

/**
 * Class Sql for parse config file and can create or update table structure
 *
 * @author Ing. Švantner Ján <janci@janci.net>
 */
class Sql extends NObject {
    
    /** @var array */
    private $configuration;
    
    /**
     * create sql from file nsql (yaml prototype)
     * @param type $filename 
     * @return Sql;
     */
    public static function fromNsql($filename){
        $sql = new Sql;
        $sql->configuration = spyc_load_file($filename);
        return $sql;
    }
    
    /**
     * run configuration
     * @param NConnection $connection 
     */
    public function run(NConnection $connection){
        $ndriver = $connection->getSupplementalDriver();
        $driver = DriverFactory::createSqlDriver($ndriver);
        $driver->setConfig($this->configuration);
        $driver->run($connection);
        
        unset($driver);
    }
}
