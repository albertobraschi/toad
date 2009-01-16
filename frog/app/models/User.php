<?php 

/**
 * class User
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Mika Tuupola <tuupola@appelsiini.net>
 */

class User extends Record
{
    const TABLE_NAME = 'user';
    
    public $name = '';
    public $email = '';
    public $username = '';
    
    public $created_on;
    public $updated_on;
    public $created_by_id;
    public $updated_by_id;
    
    public function getPermissions()
    {
        if (! isset($this->id)) {
            return array();
        }
        
        $perms = array();
        $sql = 'SELECT name FROM '.self::tableNameFromClassName('Permission').' AS permission, '. self::tableNameFromClassName('UserPermission')
             . ' WHERE permission_id = permission.id AND user_id='.$this->id;
        
        $stmt = self::$__CONN__->prepare($sql);
        $stmt->execute();
         
        while ($perm = $stmt->fetchObject()) {
            $perms[] = $perm->name;            
        }
        
        return $perms;
    }
    
    public static function findBy($column, $value)
    {
        return Record::findOneFrom('User', $column.' = ?', array($value));
    }
    
    public function beforeInsert()
    {
        $this->created_by_id = AuthUser::getId();
        $this->created_on = date('Y-m-d H:i:s');
        return true;
    }
    
    public function beforeUpdated()
    {
        $this->updated_by_id = AuthUser::getId();
        $this->updated_on = date('Y-m-d H:i:s');
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

} // end User class
