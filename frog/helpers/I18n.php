<?php

defined('I18N_PATH') or define('I18N_PATH', APP_PATH.DIRECTORY_SEPARATOR.'i18n');
define('DEFAULT_LOCALE', 'en');

/**
 * I18n : Internationalisation function and class
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @copyright 2007 Philippe Archambault
 * @package Frog
 * @version 0.1
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 */


/**
 * this function is the must permisive as possible, you cand chose your own pattern for vars in 
 * the string, it could be ':var_name', '#var_name', '{varname}', '%varname', '%varname%', 'VARNAME' ...
 *
 *
 * return = array('hello world!' => 'bonjour le monde!',
 *                'user ":user" is logged in' => 'l\'utilisateur ":user" est connecté',
 *                'Posted by %user% on %month% %day% %year% at %time%' => 'Publié par %user% le %day% %month% %year% à %time%'
 *               );
 *
 * __('hello world!'); // bonjour le monde!
 * __('user ":user" is logged in', array(':user' => $user)); // l'utilisateur "demo" est connecté
 * __('Posted by %user% on %month% %day% %year% at %time%', array(
 *      '%user%' => $user, 
 *      '%month%' => __($month), 
 *      '%day%' => $day, 
 *      '%year%' => $year, 
 *      '%time%' => $time)); // Publié par demo le 3 janvier 2006 à 19:30
 */
function __($string, $args=null)
{
	if (I18n::getLocale() != DEFAULT_LOCALE)
		$string = I18n::getText($string);

	if ($args === null) return $string;
	
	return strtr($string, $args);
}

class I18n 
{
	private static $locale = DEFAULT_LOCALE;
	private static $array = array();
	
	public static function setLocale($locale)
	{
		self::$locale = $locale;
		if ($locale != DEFAULT_LOCALE)
			self::loadArray();
	}
	
	public static function getLocale()
	{
		return self::$locale;
	}
	
	public static function getText($string)
	{
		return isset(self::$array[$string]) ? self::$array[$string] : $string;
	}
	
	public static function loadArray()
	{
		$catalog_file = I18N_PATH.DIRECTORY_SEPARATOR.self::$locale.'-message.php';

		// assign returned value of catalog file
		// file return a array (source => traduction)
		if (file_exists($catalog_file))
		{
			$array = include $catalog_file;
			self::add($array);
		}
	}
	
	public static function add($array)
	{
		if (!empty($array))
			self::$array = array_merge(self::$array, $array);
	}

} // end I18n class
