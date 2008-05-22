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
        if ( ! empty($this->filter_id))
            $this->content_html = Filter::get($this->filter_id)->apply($this->content);
        else
            $this->content_html = $this->content;
        
        return true;
    }
    
    public static function findByPageId($id)
    {
        return self::findAllFrom('PagePart', 'page_id='.(int)$id.' ORDER BY id');
    }
    
    public static function deleteByPageId($id)
    {
        return self::$__CONN__->exec('DELETE FROM '.self::tableNameFromClassName('PagePart').' WHERE page_id='.(int)$id) === false ? false: true;
    }

} // end PagePart class
