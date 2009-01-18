<?php

/**
 * class AuthUser
 *
 * All informations of the logged in user, plug all method for login, login
 * and permissions
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @since  0.5
 */

class AuthUser
{
    const SESSION_KEY               = 'frog_auth_user';
    const COOKIE_KEY                = 'frog_auth_user';
    const ALLOW_LOGIN_WITH_EMAIL    = false;
    const COOKIE_LIFE               = 1209600; // 2 weeks
    const DELAY_ON_INVALID_LOGIN    = true;
    
    static protected $is_logged_in  = false;
    static protected $user_id       = false;
    static protected $is_admin      = false;
    static protected $record        = false;
    static protected $permissions   = array();
    
    static public function load()
    {
        if (isset($_SESSION[self::SESSION_KEY]) && isset($_SESSION[self::SESSION_KEY]['username']))
            $user = User::findBy('username', $_SESSION[self::SESSION_KEY]['username']);
        else if (isset($_COOKIE[self::COOKIE_KEY]))
            $user = self::challengeCookie($_COOKIE[self::COOKIE_KEY]);
        else
            return false;
        
        if ( ! $user)
            return self::logout();
        
        self::setInfos($user);
    }
    
    static public function setInfos(Record $user)
    {
        $_SESSION[self::SESSION_KEY] = array('username' => $user->username);
        
        self::$record = $user;
        self::$is_logged_in = true;
        self::$permissions = $user->getPermissions();
        self::$is_admin = self::hasPermission('administrator');
    }
    
    static public function isLoggedIn()
    {
        return self::$is_logged_in;
    }
    
    static public function getRecord()
    {
        return self::$record ? self::$record: false;
    }
    
    static public function getId()
    {
        return self::$record ? self::$record->id: false;
    }
    
    static public function getUserName()
    {
        return self::$record ? self::$record->username: false;  
    }
    
    static public function getPermissions()
    {
        return self::$permissions;
    }
    
    static public function hasPermission($permission)
    {
        return in_array(strtolower($permission), self::$permissions);
    }
    
    static public function login($username, $password, $set_cookie=false)
    {
        self::logout();
          
        $user = User::findBy('username', $username);
        
        if ( ! $user instanceof User && self::ALLOW_LOGIN_WITH_EMAIL)
            $user = User::findBy('email', $username);

        if ($user instanceof User && $user->password == sha1($password))
        {
            $user->last_login = date('Y-m-d H:i:s');
            $user->save();
            
            if ($set_cookie)
            {
                $time = $_SERVER['REQUEST_TIME'] + self::COOKIE_LIFE;
                setcookie(self::COOKIE_KEY, self::bakeUserCookie($time, $user), $time, '/', null, (isset($_ENV['SERVER_PROTOCOL']) && ((strpos($_ENV['SERVER_PROTOCOL'],'https') || strpos($_ENV['SERVER_PROTOCOL'],'HTTPS')))));
            }
            
            self::setInfos($user);
            return true;
        }
        else
        {
            if (self::DELAY_ON_INVALID_LOGIN)
            {
                if ( ! isset($_SESSION[self::SESSION_KEY.'_invalid_logins']))
                    $_SESSION[self::SESSION_KEY.'_invalid_logins'] = 1;
                else
                    ++$_SESSION[self::SESSION_KEY.'_invalid_logins'];
                
                sleep(max(0, min($_SESSION[self::SESSION_KEY.'_invalid_logins'], (ini_get('max_execution_time') - 1))));
            }
            return false;   
        }
    }
    
    static public function logout()
    {
        unset($_SESSION[self::SESSION_KEY]);
        
        self::eatCookie();
        self::$record = false;
        self::$user_id = false;
        self::$is_admin = false;
        self::$permissions = array();
    }
    
    static protected function challengeCookie($cookie)
    {
        $params = self::explodeCookie($cookie);
        if (isset($params['exp'], $params['id'], $params['digest']))
        {
            if ( ! $user = Record::findByIdFrom('User', $params['id']))
                return false;
            
            if (self::bakeUserCookie($params['exp'], $user) == $cookie && $params['exp'] > $_SERVER['REQUEST_TIME'])
                return $user;
            
        }
        return false;
    }
    
    static protected function explodeCookie($cookie)
    {
        $pieces = explode('&', $cookie);
        
        if (count($pieces) < 2)
            return array();
        
        foreach ($pieces as $piece)
        {
            list($key, $value) = explode('=', $piece);
            $params[$key] = $value;
        }
        return $params;
    }
    
    static protected function eatCookie()
    {
        setcookie(self::COOKIE_KEY, false, $_SERVER['REQUEST_TIME']-self::COOKIE_LIFE, '/', null, (isset($_ENV['SERVER_PROTOCOL']) && (strpos($_ENV['SERVER_PROTOCOL'],'https') || strpos($_ENV['SERVER_PROTOCOL'],'HTTPS'))));
    }
    
    static protected function bakeUserCookie($time, $user)
    {
        return 'exp='.$time.'&id='.$user->id.'&digest='.md5($user->username.$user->password);
    }
    
} // end AuthUser class
