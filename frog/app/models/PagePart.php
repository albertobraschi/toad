<?php

/**
 * class PagePart
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @since  0.1
 */

class PagePart extends Record
{
    const TABLE_NAME = 'page_part';
    
    public $name = 'body';
    public $filter_id = '';
    public $page_id = 0;
    public $content = '';
    public $content_html = '';
    
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
    
    /* TODO: Get rid of PagePart::deleteByPageId() method */
    public static function deleteByPageId($id)
    {
        return self::$__CONN__->exec('DELETE FROM '.self::tableNameFromClassName('PagePart').' WHERE page_id='.(int)$id) === false ? false: true;
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

    public static function findByPageId($id, $class=__CLASS__) {
        $params['where'] = sprintf('page_id=%d', $id);
        return parent::find($params, $class);            
    }
    
    public static function findByNameAndPageId($name, $id, $class=__CLASS__) {
        $params['where'] = sprintf("name='%s' AND page_id=%d", $name, $id);
        $sql = Record::buildSql($params, $class);
        return self::connection()->query($sql, PDO::FETCH_CLASS, $class)->fetch();
    }
} 