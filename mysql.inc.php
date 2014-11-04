<?php
// Last Update:2014/05/24 22:30:42
class Mysql {
    
    private static $db = null;

    /*
    *   Refenenced by: http://tw1.php.net/manual/en/class.pdo.php#97682
    */
    public static function get_db(){
        if( self::$db ){
            return self::$db;
        }
        $setting = parse_ini_file("connect.ini");
        $user = $setting['username'];
        $passwd = $setting['password'];
        $database = $setting['database'];
        try{
            self::$db = new PDO( "mysql:host=localhost;dbname=" . $database, $user, $passwd );
        }
        catch ( PDOException $e ){
              print "Error!: " . $e->getMessage() . "<br/>";
              die();
        }

        return self::$db;
    }

    public static function __callStatic( $name, $args){
        
        $callback = array( self::get_db(), $name );
        return call_user_func_array( $callback, $args );
    }

    public function __destruct(){
    
        //Debug::func_end("link for $this->func_name");
    }
}
