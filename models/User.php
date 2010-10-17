<?php

class User extends Base
{
    public $fields = array(
        'passhash',
        'email',
        'password',
        'secret',
        'status',
        'added',
        'last_login',
        'last_access',
        'privacy',
        'avatar',
        'uploaded',
        'downloaded',
        'title',
        'torrents_per_page'
    );
    
    public function isLoggedIn()
    {
        return isset($_SESSION['USER']['ID']) && $_SESSION['USER']['ID'] > 0 ? true : false;
    }
    
    public function add($input)
    {
        $input['passhash'] = md5(microtime() . rand(1000, 9999));
        $input['title'] = 'noob';
        $input['status'] = 'pending';
        $input['last_login'] = new MongoDate();
        $input['last_access'] = new MongoDate();
        
        return parent::add($input);
    }
}

?>