<?php

/**
 * Class Layout.
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Mika Tuupola <tuupola@appelsiini.net>
 */

class Layout extends Record
{
    const TABLE_NAME = 'layout';
    
    public $name;
    public $content_type = 'text/html';
    public $content;
    
    public $created_on;
    public $updated_on;
    public $created_by_id;
    public $updated_by_id;
    
    public function beforeInsert()
    {
        $this->created_by_id = AuthUser::getId();
        $this->created_on = date('Y-m-d H:i:s');
        return true;
    }
    
    public function beforeUpdate()
    {
        $this->updated_by_id = AuthUser::getId();
        $this->updated_on = date('Y-m-d H:i:s');
        return true;
    }
    
    public function updater() {
        return User::findById($this->updatedById());
    }

    public function creator() {
        return User::findById($this->createdById());
    }
        
    public function isUsed()
    {
        /* TODO: This should use Page::count() */
        return Record::countFrom('Page', 'layout_id=?', array($this->id));
    }
        
    /* We need these before PHP 5.3. Older do not have late static binding. */
    static function find($params=null, $class=__CLASS__) {
        return parent::find($params, $class);
    }

    static function findById($id, $class=__CLASS__) {
        return parent::findById($id, $class);
    }
    
    static function count($params=null, $class=__CLASS__) {
        return parent::count($params, $class);
    }
    
}
