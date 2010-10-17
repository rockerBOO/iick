<?php

class Timer
{
    public static function start($key)
    {
        global $_TIMER_START;
        
        if (isset($_TIMER_START) == false)
        {
            $_TIMER_START = array();
        }
        
        $_TIMER_START[$key] = microtime(true);
        
        return $key;
    }
    
    public static function stop($key)
    {
        global $_TIMER_START, $_TIMER_STOP;
        
        if (isset($_TIMER_STOP) == false)
        {
            $_TIMER_STOP = array();
        }
        
        $_TIMER_STOP[$key] = microtime(true);
        
        return true;
    }
    
    public static function reportAll()
    {
        global $_TIMER_START, $_TIMER_STOP;
        
        $result = array();
        
        foreach ($_TIMER_START as $key => $value)
        {
            if (isset($_TIMER_STOP[$key]) == false)
            {
                continue;
            }
            
            $result[$key] = $_TIMER_STOP[$key] - $value;
        }
        
        return $result;
    }
    
    public static function report($prefix='', $inclusive=true)
    {
        global $_TIMER_START, $_TIMER_STOP;
        
        $result = array();
        
        // Prefix filtering
        if ($prefix != '')
        {
            $prefixes = explode(',', $prefix);
            
            foreach ($_TIMER_START as $key => $value)
            {
                if (isset($_TIMER_STOP[$key]) == false)
                {
                    continue;
                }
                
                foreach ($prefixes as $prefix)
                {                    
                    if (substr($key, 0, strlen($prefix)) == $prefix && $inclusive == false)
                    {
                        continue 2;
                    }
                    elseif (substr($key, 0, strlen($prefix)) != $prefix && $inclusive)
                    {
                        continue 2;
                    }
                }
                
                $result[$key] = $_TIMER_STOP[$key] - $value;
            }

            return $result;
        }
        
        return self::reportAll();
    }
}

?>