<?php

/*
 * This file is part of the CMSAlaJanci (https://github.com/janci/CMSAlaJanci)
 * 
 * Copyright (c) 2012 Ing. Švantner Ján <janci@janci.net>
 * 
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

/**
 * Driver for database sqlite
 *
 * @author Ing. Švantner Ján <janci@janci.net>
 */
class SqliteDriver extends NObject implements ISqlDriver {

    /** @var array */
    private $config;
    
    
    /**
     * execute query to database by configuration
     * synchronize tables
     * @param NConnection $connection 
     */
    public function run(NConnection $connection) {
        $tables_array = $connection->getSupplementalDriver()->getTables();
        $tables = array();
        
        foreach($tables_array as $table) 
            $tables[$table->name] = $table;

        foreach($this->config as $table_name => $table_configuration){
            if(isset($tables[$table_name]))
                $this->alter($connection, $table_name, $table_configuration);
            else
                $this->create($connection, $table_name, $table_configuration);
        }
    }
    
    protected function alter(NConnection $connection, $table_name, $table_config){
        
    }
    
    protected function create(NConnection $connection, $table_name, $table_config){
        $macro = 'CREATE TABLE `{table}` ( {columns} )';
        $macro = str_replace('{table}', $table_name, $macro);
        $columns = array();
        $indexes = '';

        foreach($table_config as $column=>$config){
            $columns[] = "`{$column}` {$config['type']} {$config['isnull']} {$config['default']}";
            if(isset($config['foreign'])) $foreigns[$column] = $config['foreign'];
        }
        
        
        if(isset($foreigns)){
            foreach($foreigns as $column=>$def){
                list($reftable, $refindex) = explode('.',$def);
                $columns[] = "FOREIGN KEY({$column}) REFERENCES {$reftable}({$refindex})";
            }
        }
        
        $macro = str_replace('{columns}', implode(', ',$columns), $macro);
        $connection->exec($macro);
        
    }
    
    /**
     * reconfiguration config to allow types in sqlite
     * @param array $configuration table_name=>column_name=>column_setting
     */
    public static function reconfigure($configuration){
        $new_configuration = array();
        
        foreach($configuration as $table_name => $table_settings)
            foreach($table_settings as $column_name=>$column_type){
                $array_represent  = is_array($column_type);
                if($array_represent) 
                    $column_type2 = $column_type['type'];
                else 
                    $column_type2 = $column_type;

                $column_type2 = NStrings::lower($column_type2);
                switch ($column_type2) {
                    case 'primary':
                        $new_type = 'INTEGER PRIMARY KEY AUTOINCREMENT';
                        break;
                    case 'boolean':
                        $array_represent = true;
                        $column_type = array();
                        $column_type['default']=0;
                        //continue as int
                    case 'datetime':
                    case 'timestamp':
                    case 'bigint':
                    case 'tinyint':
                    case 'integer':
                    case 'int':
                        $new_type = 'INTEGER';
                        break;
                    case 'double':
                    case 'float':
                        $new_type = 'REAL';
                        break;
                    case 'varchar':
                    case 'text':
                        $new_type = 'TEXT';
                        break;
                    default:
                        break;
                }
                
                
                $new_configuration[$table_name][$column_name] = array('default'=>'', 'isnull'=>'NOT NULL');
                
                $new_configuration[$table_name][$column_name]['type'] = $new_type;
                
                if($array_represent && isset($column_type['isnull']) && $column_type['isnull']==true)
                    $new_configuration[$table_name][$column_name]['isnull'] = 'NULL';
                
                if($array_represent && isset($column_type["default"]))
                    $new_configuration[$table_name][$column_name]['default'] = 'DEFAULT "'.$column_type['default'].'"';
                
                if($array_represent && isset($column_type["foreign"]))
                    $new_configuration[$table_name][$column_name]['foreign'] = $column_type["foreign"];
                
            }
        return $new_configuration;   
    }
    
    public function setConfig($config) {
        $this->config = self::reconfigure($config);
    }
}
