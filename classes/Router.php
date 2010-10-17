<?php

class Router
{    
    public function route() 
    {         
        foreach ($this->routes as $regex => $route) 
        {             
            if (preg_match('#' . $regex . '#', $this->url, $url_matches)) 
            {
                if (isset($route[1])) 
                {
                    
                } 
                elseif (strstr($route[0], '->')) 
                {
                    list($cname, $handler) = explode('->', $route[0]);
                    
                    $cname = $cname . 'Controller';
                    
                    require_once(ROOT_PATH . 'controllers/' . $cname . '.php');
                    
                    $controller = new $cname;
                    $controller->setUrlMatches($url_matches);
                    
                    $controller->$handler(); exit;
                } 
                else 
                {
                    $cname = $route[0] . 'Controller';
                    
                    require_once(ROOT_PATH . 'controllers/' . $cname . '.php');
                    
                    $controller = new $cname;
                    $controller->setUrlMatches($url_matches);
                    $controller->handle(); exit;
                }
            }
        }
        
        echo '404 dude';
    }
    
    public function load($url)
    {
        return $this->set_url($url);
    }
    
    public function set_url($url) 
    {        
        $queryStringPos = strpos($url, '?');
        
        if ($queryStringPos > 0)
        {
            $this->url = substr($url, 1, $queryStringPos-1);
        }
        else
        {
            $this->url = substr($url, 1);
        }
        
        return $this;
    }
    
    public function set_routes(array $routes)
    {       
        $this->routes = $routes;
        
        return $this;
    }
}

?>