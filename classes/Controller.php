<?php

require(ROOT_PATH . 'classes/Template.php');

class Controller 
{    
    public function l($class)
    {
        global $OBJ_CACHE;
        
        if (isset($OBJ_CACHE[$class]))
        {
            return $OBJ_CACHE[$class];
        }
        
        return $OBJ_CACHE[$class] = new $class;
    }
    
    public function setUrls($urls)
    {
        $this->urls = $urls;
        
        return $this;
    }
    
    public function setUrlMatches($urlMatches)
    {
        $this->url_matches = $urlMatches;
        
        return $this;
    }
}

?>