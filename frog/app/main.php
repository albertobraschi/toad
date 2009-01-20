<?php

if (!defined('HELPER_PATH')) define('HELPER_PATH', CORE_ROOT . '/helpers');
if (!defined('URL_SUFFIX'))  define('URL_SUFFIX',  '');

ini_set('date.timezone', DEFAULT_TIMEZONE);
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set(DEFAULT_TIMEZONE);    
} else {
    putenv('TZ='.DEFAULT_TIMEZONE);    
}

use_helper('I18n');

/* TODO: Do the need to be initialized? Use Lazy Loading instead. */
/* Intialize Setting and Plugin. */
Setting::init();
Plugin::init();

/**
 * Explode an URI and make a array of params
 * TODO: Shouldn't these be in helpers?
 */
function explode_uri($uri) 
{
    return preg_split('/\//', $uri, -1, PREG_SPLIT_NO_EMPTY);
}

function get_parts($page_id)
{
    global $__FROG_CONN__;
    
    $objPart = new stdClass;
    
    $sql = 'SELECT name, content_html FROM '.TABLE_PREFIX.'page_part WHERE page_id=?';
    
    if ($stmt = $__FROG_CONN__->prepare($sql)) {
        $stmt->execute(array($page_id));
        
        while ($part = $stmt->fetchObject()) {
            $objPart->{$part->name} = $part;            
        }
    }
    
    return $objPart;
}

function url_match($url)
{
    $url = trim($url, '/');
    
    if (CURRENT_URI == $url) {
        return true;        
    }
    
    return false;
}
  
function url_start_with($url)
{
    $url = trim($url, '/');
    
    if (CURRENT_URI == $url) {
        return true;        
    }
    
    if (strpos(CURRENT_URI, $url) === 0) {
        return true;        
    }
    
    return false;
}


function page_not_found()
{
    Observer::notify('page_not_found');
    
    include FROG_ROOT . '/404.php';
    exit;
}

function main()
{
    /* Get the uri string from the query. */
    $uri = $_SERVER['QUERY_STRING'];
    
    /* Real integration of GET. */
    if (strpos($uri, '?') !== false) {
        list($uri, $get_var) = explode('?', $uri);
        $exploded_get = explode('&', $get_var);
        
        if (count($exploded_get)) {
            foreach ($exploded_get as $get) {
                list($key, $value) = explode('=', $get);
                $_GET[$key] = $value;
            }
        }
    }
    
    /* Remove suffix page if found. */
    if (URL_SUFFIX !== '' and URL_SUFFIX !== '/') {
        $uri = preg_replace('#^(.*)('.URL_SUFFIX.')$#i', "$1", $uri);
    }
    define('CURRENT_URI', trim($uri, '/'));
    
    /* This is where 80% of the things are done. */
    $page = Page::findByUri($uri);
    
    /* If we found it, display it! */
    if (is_object($page)) {
        Observer::notify('page_found', $page);
        $page->show();
    } else {
        page_not_found();   
    }
} 

/* Ok come on! let's go! (movie: Hacker's) */
//ob_start();
main();
//ob_end_flush();
