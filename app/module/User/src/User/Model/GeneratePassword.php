<?php

namespace User\Model;

class GeneratePassword {

    /**
     * Security Salt For Admin
     * @var string
     /*/
    private static $_adminSecuritySalt = 'fa3dFg849dLjgiUgSvDSbkyc34z';
    
    /**
     * Security Salt For User
     * @var string
     /*/
    private static $_userSecuritySalt = 'FgDfw545efgdrgSaRyAxD53z45';
    
    
    
    /**
     * @param   string  $password
     * @param   string  $type
     * @return  string
     /*/
    public static function generate($password = '', $type = 'user') 
    {
        if ($type != 'user') {
            return  $password != '' ? md5($password.self::$_adminSecuritySalt): '';
        }
        return  $password != '' ? md5($password.self::$_userSecuritySalt): '';
    }
    
    
    public static function randomPassword($length = 8) 
    {
        $chars =  'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.
            '0123456789``-=~!@#$%^&*()_+,./<>?;:[]{}\|';
        return substr( str_shuffle( $chars ), 0, $length );
        /*
        $password = '';
        $max = strlen($chars) - 1;
        
        for ($i = 0; $i < $length; $i++)
            $password .= $chars[rand(0, $max)];
        
        return $password;
        */
    }
}