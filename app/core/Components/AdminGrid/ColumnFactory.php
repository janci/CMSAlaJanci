<?php

/*
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 * 
 * Copyright (c) 2012 Ing. Švantner Ján <janci@janci.net>
 * 
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

/**
 * factory for datagrid columns
 *
 * @author Ing. Švantner Ján <janci@janci.net>
 */
class ColumnFactory extends NObject {
    
    /**
     * create new datagrid column by name
     * @param string $type
     * @return \IDatagridColumn
     * @throws PluginException 
     */
    public static function createDatagridColumn($type){
        $class_name = $type."Column";
        if(!class_exists($class_name))
            throw new PluginException("Missing column type `{$type}`.");
        
        $datagrid_column = new $class_name();
        if(!($datagrid_column instanceof IDatagridColumn))
            throw new PluginException("Class {$class_name} must implement IDatagridColumn.");
            
        return $datagrid_column;
    }
}
