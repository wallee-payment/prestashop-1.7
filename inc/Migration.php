<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Wallee_Migration extends Wallee_AbstractMigration{
   
    protected static function getMigrations(){
        return array(
            '1.0.0' => 'initialize_1_0_0'
        );
    }

    public static function initialize_1_0_0()
    {
        static::installBase();
    }
}
