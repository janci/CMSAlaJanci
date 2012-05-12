<?php
/**
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 *
 * Copyright (c) 2012 Ján Švantner (http://www.janci.net)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

/**
 * Abstract class for plugins
 *
 * @author Švantner Ján <janci@janci.net>
 */
abstract class BasePlugin extends NObject {
    /** @var array */
    private $config;
    
    /** @var array */
    private $controls;
    
    
    /**
     * initialize all controls for plugin from configuration 
     */
    public function initializeControls(){
        if(isset($this->controls)) return;
        
        $config = $this->getConfig();
        $this->controls = array();
        
        if(!isset($config['controls'])) return;
        foreach($config['controls'] as $control){
            $this->controls[] = new $control;
        }
    }
    
    /**
     * initialize all admin controls for plugin from components.yaml
     */
    public function initializeAdminControls(NConnection $connection, NPresenter $presenter){
        $dir = $this->getDirectory();
        $config_file = $dir.DIRECTORY_SEPARATOR.'components.yaml';
        if(file_exists($config_file)) {
            $config = spyc_load_file($config_file);
            
            if(isset($config['datagrid'])){
                foreach($config['datagrid'] as $dgname=>$dgconfig){
                    $datagrid = new AdminGrid;
                    $table = $dgconfig['table'];
                    $datagrid->setTable($connection->table($table)->where('remove',0));
                    $datagrid->configure($dgconfig);
                    if(isset($dgconfig['actions'])){
                        foreach($dgconfig['actions'] as $action_name=>$action_config){
                                if(is_string($action_config)){
                                    $ac = $action_config;
                                    $action_config = array();
                                    switch ($action_name) {
                                        case 'delete':
                                            $action_config['icon'] = '/admin_v1/images/admin/delete.png';
                                            $action_config['tooltip'] = "Vymazať záznam";
                                            $action_config['link'] = $ac;
                                            break;
                                        case 'edit':
                                            $action_config['icon'] = '/admin_v1/images/admin/edit.png';
                                            $action_config['tooltip'] = "Upraviť záznam";
                                            $action_config['link'] = $ac;
                                            break;
                                        default:
                                            throw new PluginException("Unknown predefined type `$action_config`.");
                                            break;
                                    }
                                }
                                
                                $type = isset($action_config['type'])? $action_config['type']:null;
                                $action = ActionFactory::createDatagridAction($type);
                                $action->configure($action_config);
                                $datagrid->addAction($action);
                                
                                
                            }
                    }
                    
                    foreach($dgconfig['columns'] as $column_name=>$column_config){
                        $type = isset($column_config['type'])? $column_config['type']:"text";
                        $column_config['name'] = $column_name;
                        
                        $column = ColumnFactory::createDatagridColumn($type);
                        $column->applyConfiguration($column_config);
                        
                        $datagrid->addColumn($column);
                    }
                    
                    $presenter[$dgname] = $datagrid;
                }
            }
        }
    }
    
    /**
     * get plugin name
     * @return string 
     */
    public function getName(){
        $config = $this->getConfig();
        return $config['name'];
    }
    
    public function getControls(){
        $this->initializeControls();
        return $this->controls;
    }
    
    protected function getDirectory(){
        return dirname($this->getReflection()->getFileName());
    }
    
    /**
     * get configuration of plugin
     * @return type 
     */
    public function getConfig(){
        if(isset($this->config)) return $this->config;
        
        $dir = $this->getDirectory();
        $config_file = $dir.DIRECTORY_SEPARATOR.'config.yaml';

        if(file_exists($config_file)){
            return $this->config = spyc_load_file($config_file);
        } else {
            $name = $this->getReflection()->getName();
            return $this->config = array('name'=>$name);
        }
    }
    
    public function installSql(NConnection $connection){
        $dir = $this->getDirectory();
        $config_file = $dir.DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'config.nsql';
        if(file_exists($config_file)) {
            $sql = Sql::fromNsql($config_file);
            $sql->run($connection);
        }
    }
}

