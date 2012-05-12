<?php

/*
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 * 
 * Copyright (c) 2012 Ing. Švantner Ján <janci@janci.net>
 * 
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

/**
 * Create any drivers by Nette Drivers
 *
 * @author Ing. Švantner Ján <janci@janci.net>
 */
class DriverFactory extends NObject {
    
    /**
     * return SqlDriver by type
     * @param ISupplementalDriver $nette_driver
     * @return ISqlDriver
     */
    public static function createSqlDriver(ISupplementalDriver $nette_driver){
        if($nette_driver instanceof NSqliteDriver) {
            return new SqliteDriver();
        }
    }
    
}
