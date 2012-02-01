<?php

class Base {
    public function get($key) {
        if ($this->fieldExists($key))
        {
            if (isset($this->data[$key]))
            {
                return $this->data[$key];
            }
            else
            {            
                return '';
            }
        }
        
        return '';
    }
    
    public function loadMany($query) {
        $this->clearErrors();
        
        if (isset($query['_limit']))
        {
            $cursorArgs['_limit'] = $query['_limit'];
            unset($query['_limit']);
        }
        
        $cursor = $this->getMongoCollection()->find($query);
        
        if (false == $cursor)
        {
            $this->err('Could not find any results [many]');
            return array();
        }
        
        if (isset($cursorArgs['_limit']))
        {            
            $cursor->limit((int)$cursorArgs['_limit']);
        }

        return $cursor;
    }
    
    public function load($query) {
        $this->clearErrors();
        
        if (isset($query['_id']))
        {
            $query['_id'] = new MongoId($query['_id']);
        }
        
        $data = $this->getMongoCollection()->findOne($query);
        
        if (false == $data)
        {
            $this->err('Could not find any results');
            return $this;
        }
        
        $this->data = $data;
        
        if (isset($this->data['_id']))
        {
            $this->id = $this->data['_id'];
        }
        
        return $this;
    }
    
    public function add($input) {
        return $this->getMongoCollection()->insert($input);
    }
    
    public function update($input){
        return $this->getMongoCollection()->update(array('_id' => $this->id), array('$set' => $input));
    }
    
    public function fieldExists($field)
    {
        if ($field == '_id' || in_array($field, $this->fields))
        {
            return true;
        }
        
        return false;
    }
    
    public function mongoConnect()
    {
        if (false == isset($this->mongo))
        {
            $this->mongo = new Mongo();
        }
    }
    
    public function getMongoCollection()
    {
        $this->mongoConnect();
        
        return $this->mongo->iick->{$this->getCollectionName()};
    }
    
    public function getCollectionName()
    {
        return get_class($this);
    }
    
    public function isValid()
    {
        return false == $this->hasError() ? true : false;
    }
    
    public function hasError()
    {
        return isset($this->error) ? true : false;
    }
    
    public function err($errorMessage)
    {
        $this->error[] = $errorMessage;
    }
    
    public function clearErrors()
    {
        unset($this->error);
    }
    
}

?>