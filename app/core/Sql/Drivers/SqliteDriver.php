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
        $macro = 'ALTER TABLE `{table}` {columns} ';
        
        $macro = str_replace('{table}', $table_name, $macro);
        $columns = array();
        
        $tbx_columns = $connection->getSupplementalDriver()->getColumns($table_name);
        $tb_columns = array();
        foreach($tbx_columns as $tb_column) $tb_columns[$tb_column['name']] = $tb_column;
        
        foreach($table_config as $column=>$config){
            if(isset($tb_columns[$column])) continue;
            if($config['foreign']!='') $config['isnull'] = 'NULL';
            $columns[] = "ADD `{$column}` {$config['type']} {$config['isnull']} {$config['default']} {$config['foreign']}";
            $macro_sql = str_replace('{columns}', implode(', ',$columns), $macro);
            $connection->exec($macro_sql);
        }
        
    }
    
    protected function create(NConnection $connection, $table_name, $table_config){
        $macro = 'CREATE TABLE `{table}` ( {columns} )';
        $macro = str_replace('{table}', $table_name, $macro);
        $columns = array();

        foreach($table_config as $column=>$config){
            $columns[] = "`{$column}` {$config['type']} {$config['unique']} {$config['isnull']} {$config['default']} {$config['foreign']}";
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
                
                
                $new_configuration[$table_name][$column_name] = array('default'=>'', 'unique'=>'', 'foreign'=>'', 'isnull'=>'NOT NULL');
                
                $new_configuration[$table_name][$column_name]['type'] = $new_type;
                
                if($array_represent && isset($column_type['isnull']) && $column_type['isnull']==true)
                    $new_configuration[$table_name][$column_name]['isnull'] = 'NULL';
                
                if($array_represent && isset($column_type['unique']) && $column_type['unique']==true)
                    $new_configuration[$table_name][$column_name]['unique'] = 'UNIQUE';
                
                if($array_represent && isset($column_type["default"]))
                    $new_configuration[$table_name][$column_name]['default'] = 'DEFAULT "'.$column_type['default'].'"';
                
                if($array_represent && isset($column_type["foreign"])) {
                    list($reftable, $refindex) = explode('.',$column_type['foreign']);
                    $new_configuration[$table_name][$column_name]['foreign'] = "REFERENCES {$reftable}({$refindex}) ON DELETE CASCADE ON UPDATE CASCADE";
                }
                
            }
        return $new_configuration;   
    }
    
    public function setConfig($config) {
        $this->config = self::reconfigure($config);
    }
}
