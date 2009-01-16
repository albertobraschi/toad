<?php

/**
 * class Snippet
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Mika Tuupola <tuupola@appelsiini.net>
 */

class Snippet extends Record
{
    const TABLE_NAME = 'snippet';
    
    public $name;
    public $filter_id;
    public $content;
    public $content_html;
    
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
    
    public function beforeSave()
    {
        // apply filter to save is generated result in the database
        if (! empty($this->filter_id)) {
            $this->content_html = Filter::get($this->filter_id)->apply($this->content);
        } else {
            $this->content_html = $this->content;
        }
        return true;
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
    
    /* Helper finders. */
    public static function findByName($name, $class=__CLASS__) {
        $params['where'] = sprintf("name='%s'", $name);
        $sql = Record::buildSql($params, $class);
        return self::connection()->query($sql, PDO::FETCH_CLASS, $class)->fetch();
    }

} 

