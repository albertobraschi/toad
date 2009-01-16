<?php 

/**
 * class PagePart
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @since  0.8.7
 */

class Tag extends Record
{
    const TABLE_NAME = 'tag';
    
    public $name;
    
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

    static function findByPageId($id, $class=__CLASS__) {
        $params['sql'] = sprintf("SELECT tag.id AS id, tag.name AS name 
                                  FROM %s AS page_tag, %s AS tag
                                  WHERE page_tag.page_id=%d AND page_tag.tag_id = tag.id",
                                  Record::tableize('PageTag'), Record::tableize('Tag'),
                                  $id);
        
        return parent::find($params, $class);
    }
    
    public static function findByName($name, $class=__CLASS__) {
        $params['where'] = sprintf("name='%s'", $name);
        $sql = Record::buildSql($params, $class);
        return self::connection()->query($sql, PDO::FETCH_CLASS, $class)->fetch();
    }
    
}