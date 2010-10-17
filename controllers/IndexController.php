<?php

class IndexController extends Controller
{
    public function index() 
    {   
        $vars['user'] = new User;
         
        $this->l('Template')->load('index', 'splash', $vars)->output();
    }
}

?>