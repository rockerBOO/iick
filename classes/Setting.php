<?php

class Setting
{
    public static function classesToTables()
    {
        global $_MODELS;
        
        $_MODELS = parse_ini_file(ROOT_PATH . 'config/models.ini');
        
        return $_MODELS;
    }
    
    public static function globalObj($type, $args=array())
    {
        switch ($type)
        {
            case 'db':
                return Setting::getGlobalDbObj($args);
            case 'cache':
                return Setting::getGlobalCacheObj($args);            
        }
    }
    
    public static function getGlobalDbObj(array $args=array())
    {
        global $_DB_OBJ;
        
        $prefix = $args['prefix'];
        
        if (isset($_DB_OBJ[$prefix]) == false)
        {
            // requirePackage('mysqli', 2);
                $timer = Timer::start('Mysqli Loading ... ' . $prefix . '_HOST');
            $databaseInterfaceClass = 'MysqliConnect';
            
            $params = array(
                'host'                    => constant($prefix . '_HOST'), 
                'username'                => constant($prefix . '_USER'), 
                'password'                => constant($prefix . '_PASS'), 
                'database'                => constant($prefix . '_DB')
            );
            
            $_DB_OBJ[$prefix] = new $databaseInterfaceClass($params);
            
                Timer::stop($timer);
            
            // echo 'Getting new db connection. ' . $prefix . ', ' . print_r($params, true) . "\n";
        }
        
        return $_DB_OBJ[$prefix];
    }
    
    public static function getGlobalCacheObj(array $args=array())
    {
        global $_CACHE_OBJ;
        
        if ($_CACHE_OBJ == false)
        {
            // requirePackage('memcache', 3);
            
            $_CACHE_OBJ = new Cache('babes');
            // $_CACHE_OBJ->addVirbServers();
            
            // echo 'Getting new cache connection'."\n";
        }
        
        return $_CACHE_OBJ;
    }

    public static function getGlobalInputFilterObj(array $args=array())
    {
        global $_INPUT_FILTER_OBJ;
        
        if ($_INPUT_FILTER_OBJ == false)
        {
            // require_once(ROOT_PATH . VIRB_PATH . 'classes/InputFilter.php');
            $_INPUT_FILTER_OBJ = new InputFilter();
        }
        
        return $_INPUT_FILTER_OBJ;
    }
 
    public static function getClassName($table)
    {
        return array_search($table, Setting::classesToTables());
    }
    
    public static function getTableName($class)
    {
        if (is_object($class))
        {
            $className = get_class($class);
        }
        else
        {
            $className = $class;
        }
        
        $classesToTables =& Setting::classesToTables();
                
        if (isset($classesToTables[$className]))
        {
            return $classesToTables[$className];
        }
        
        echo 'Class ' . $className . ' was not setup'."\n";
        return false;
    }
    
    public static function getTables()
    {
        return Setting::classesToTables();
    }
    
    public static function getInstalledModels()
    {
        return array_keys(Setting::classesToTables());
    }
}

?>