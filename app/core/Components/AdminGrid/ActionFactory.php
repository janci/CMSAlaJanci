<?php

/*
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 * 
 * Copyright (c) 2012 Ing. Švantner Ján <janci@janci.net>
 * 
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

/**
 * factory for create datagrid action
 *
 * @author Ing. Švantner Ján <janci@janci.net>
 */
class ActionFactory extends NObject{
    
    /**
     * create new object datagrid action by type, if null return LinkAction
     * @param string $type
     * @return IDatagridAction
     * @throws PluginException 
     */
    public static function createDatagridAction($type){
        if(!isset($type)) $type = 'Link';
        $class_name = $type.'Action';
        if(!class_exists($class_name))
            throw new PluginException("Missing action type $type.");
        
        $action = new $class_name;
        return $action;
    }
}
