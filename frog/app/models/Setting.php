<?php

/**
 * class Setting 
 *
 * Provide a administration interface of some configuration
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @since  0.8.7
 */

class Setting extends Record
{
    const TABLE_NAME = 'setting';
    
    public $name;
    public $value;
    
    public static $settings = array();
    public static $is_loaded = false;
    
    public static function init()
    {
        if (!self::$is_loaded) {
            $settings = Record::findAllFrom('Setting');
            foreach($settings as $setting) {
                self::$settings[$setting->name] = $setting->value;                
            }
            
            self::$is_loaded = true;
        }
    }
    
    public static function get($name)
    {
        return isset(self::$settings[$name]) ? self::$settings[$name]: false;
    }
    
    public static function saveFromData($data)
    {
        $tablename = self::tableNameFromClassName('Setting');
        
        foreach ($data as $name => $value) {
            $sql = 'UPDATE '.$tablename.' SET value='.self::$__CONN__->quote($value)
                 . ' WHERE name='.self::$__CONN__->quote($name);
            self::$__CONN__->exec($sql);
        }
    }
    
    public static function getLanguages()
    {
        global $iso_639_1;
        
        $languages = array('en' => 'English');
        
        if ($handle = opendir(APP_PATH.'/i18n')) {
            while (false !== ($file = readdir($handle))) {
                if (strpos($file, '.') !== 0) {
                    $code = substr($file, 0, 2);
                    $languages[$code] = isset($iso_639_1[$code]) ? $iso_639_1[$code]: __('unknown');
                }
            }
            closedir($handle);
        }
        asort($languages);
        
        return $languages;
    }
    
    public static function getThemes()
    {
        $themes = array();
        $dir = FROG_ROOT.'/'.ADMIN_DIR.'/themes/';
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if (strpos($file, '.') !== 0 && is_dir($dir.$file)) {
                    $themes[$file] = Inflector::humanize($file);
                }
            }
            closedir($handle);
        }
        asort($themes);
        
        return $themes;
    }

} // end Setting class
