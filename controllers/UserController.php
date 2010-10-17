<?php

class UserController extends Controller
{
    public function login() 
    {   
        if (isset($_POST['form_action']))
        {
            $user = load('User', array('email' => $_POST['email'], 'password' => $_POST['password']));
            
            if ($user->isValid())
            {
                $_SESSION['USER']['ID'] = (string)$user->id;
                $user->update(array('last_login' => new MongoDate()));
                header('Location: /'); exit;
            }
        }
        
        $vars['user'] = new User;
        
        $this->l('Template')->load('user/login', 'base', $vars)->output();
    }
    
    public function signup() 
    {   
        if (isset($_POST['form_action']))
        {
            $input = array(
                'email' => $_POST['email'], 
                'password' => $_POST['password']
            );
            
            print_r($input);
            
            add('User', $input);
        }
        
        $vars['user'] = new User;
             
        $this->l('Template')->load('user/signup', 'base', $vars)->output();
    }
    
    public function profile()
    {        
        $user = load('User', array('_id' => $_SESSION['USER']['ID']));
        
        if (isset($_POST['form_action']))
        {
            $input = array(
                'status'            => $_POST['status'],
                'privacy'           => $_POST['privacy'],
                'avatar'            => $_POST['avatar'],
                'torrents_per_page' => $_POST['torrents_per_page'],                
            );
            
            $user->update($input);
            
            $user->load(array('_id' => $_SESSION['USER']['ID']));
        }
        
        $vars['user'] = $user;
        
        $this->l('Template')->load('user/profile', 'base', $vars)->output();
    }
}

?>