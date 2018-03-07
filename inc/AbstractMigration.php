<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class Wallee_AbstractMigration
{   
    const CK_DB_VERSION = 'WLE_DB_VERSION';

    abstract protected static function getMigrations();
    
    public static function installDb(){
        try{
            static::migrateDb();
        }
        catch(Exception $e){
            PrestaShopLogger::addLog(
                $e->getMessage(), 2, null,
                'Wallee');
            return false;
        }
        return true;
    }

    protected static function migrateDb()
    {
        $currentVersion = Configuration::getGlobalValue(self::CK_DB_VERSION);
        if($currentVersion === false){
            $currentVersion = '0.0.0';
        }
        foreach (static::getMigrations() as $version => $functionName) {
            if (version_compare($currentVersion, $version, '<')) {
                Wallee_Helper::startDBTransaction();
                try{
                    call_user_func(array(
                        __CLASS__,
                        $functionName
                    ));
                    Configuration::updateGlobalValue(self::CK_DB_VERSION, $version);
                    Wallee_Helper::commitDBTransaction();
                }
                catch(Exception $e){
                    Wallee_Helper::rollbackDBTransaction();
                    throw $e;
                }
                $currentVersion = $version;
            }
        }
    }

    protected static function installBase()
    {
        $result = Db::getInstance()->execute("CREATE TABLE IF NOT EXISTS " . _DB_PREFIX_ . "wle_method_configuration(
				`id_method_configuration` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_shop` int(10) unsigned NOT NULL,
				`state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				`space_id` bigint(20) unsigned NOT NULL,
				`configuration_id` bigint(20) unsigned NOT NULL,
				`configuration_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
				`title` longtext COLLATE utf8_unicode_ci,
				`description` longtext COLLATE utf8_unicode_ci,
				`image` varchar(2047) COLLATE utf8_unicode_ci DEFAULT NULL,
                `sort_order` bigint(20) NOT NULL,
                `active` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `show_description` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `show_image` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `tax_rule_group_id` int(10) unsigned DEFAULT 0,
                `fee_base` int(10) unsigned DEFAULT 3,
                `fee_rate` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `fee_fixed` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `fee_add_tax` tinyint(1) unsigned NOT NULL DEFAULT 0,
				`date_add` datetime NOT NULL,
				`date_upd` datetime NOT NULL,
				PRIMARY KEY (`id_method_configuration`),
				UNIQUE KEY `unq_space_configuration_shop` (`space_id`,`configuration_id`, `id_shop`),
				INDEX `idx_space_id` (`space_id`),
				INDEX `idx_configuration_id` (`configuration_id`),
                INDEX `idx_id_shop` (`id_shop`),
                INDEX `idx_state` (`state`),
                INDEX `idx_active` (`active`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
        
        if ($result === false) {
            throw new Exception(DB::getMsgError());
        }
        
       
        $result = Db::getInstance()->execute("CREATE TABLE IF NOT EXISTS " . _DB_PREFIX_ . "wle_transaction_info(
				`id_transaction_info` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`transaction_id` bigint(20) unsigned NOT NULL,
				`state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				`space_id` bigint(20) unsigned NOT NULL,
				`space_view_id` bigint(20) unsigned DEFAULT NULL,
				`language` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				`currency` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				`authorization_amount` decimal(19,8) NOT NULL,
				`image` varchar(2047) COLLATE utf8_unicode_ci DEFAULT NULL,
				`labels` longtext COLLATE utf8_unicode_ci,
				`payment_method_id` bigint(20) unsigned DEFAULT NULL,
				`connector_id` bigint(20) unsigned DEFAULT NULL,
				`order_id` int(10) unsigned NOT NULL,
				`failure_reason` longtext COLLATE utf8_unicode_ci,
				`locked_at` datetime,
				`date_add` datetime NOT NULL,
				`date_upd` datetime NOT NULL,
				PRIMARY KEY (`id_transaction_info`),
				UNIQUE KEY `unq_transaction_id_space_id` (`transaction_id`,`space_id`),
				UNIQUE KEY `unq_order_id` (`order_id`),
                INDEX `state` (`state`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
        
        if ($result === false) {
            throw new Exception(DB::getMsgError());
        }
        
        $result = Db::getInstance()->execute("CREATE TABLE IF NOT EXISTS " . _DB_PREFIX_ . "wle_token_info(
				`id_token_info` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`token_id` bigint(20) unsigned NOT NULL,
				`state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				`space_id` bigint(20) unsigned NOT NULL,
				`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				`customer_id` int(10) unsigned NOT NULL,
				`payment_method_id` int(10) unsigned NOT NULL,
				`connector_id` bigint(20) unsigned DEFAULT NULL,
				`date_add` datetime NOT NULL,
				`date_upd` datetime NOT NULL,
				PRIMARY KEY (`id_token_info`),
				UNIQUE KEY `unq_token_id_space_id` (`token_id`,`space_id`),
				INDEX `idx_customer_id` (`customer_id`),
				INDEX `idx_payment_method_id` (`payment_method_id`),
                INDEX `idx_state` (`state`),
				INDEX `idx_connector_id` (`connector_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
        
        if ($result === false) {
            throw new Exception(DB::getMsgError());
        }        
        
        $result = Db::getInstance()->execute("CREATE TABLE IF NOT EXISTS " . _DB_PREFIX_ . "wle_cart_meta(
				`cart_id` int(10) unsigned NOT NULL,
                `meta_key` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
                `meta_value` longtext COLLATE utf8_unicode_ci NULL,
				UNIQUE KEY `unq_cart_id_key` (`cart_id`,`meta_key`),
                INDEX `idx_cart_id` (`cart_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
        
        if ($result === false) {
            throw new Exception(DB::getMsgError());
        }
        
        $result = Db::getInstance()->execute("CREATE TABLE IF NOT EXISTS " . _DB_PREFIX_ . "wle_order_meta(
				`order_id` int(10) unsigned NOT NULL,
                `meta_key` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
                `meta_value` longtext COLLATE utf8_unicode_ci NULL,
				UNIQUE KEY `unq_order_id_key` (`order_id`,`meta_key`),
                INDEX `idx_order_id` (`order_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
        
        if ($result === false) {
            throw new Exception(DB::getMsgError());
        }
                
        $result = Db::getInstance()->execute( "CREATE TABLE IF NOT EXISTS " . _DB_PREFIX_ . "wle_void_job(
				`id_void_job` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `void_id` bigint(20) unsigned,
				`state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				`transaction_id` bigint(20) unsigned NOT NULL,
				`space_id` bigint(20) unsigned NOT NULL,
				`order_id` bigint(20) unsigned NOT NULL,
				`failure_reason` longtext COLLATE utf8_unicode_ci,
                `date_add` datetime NOT NULL,
				`date_upd` datetime NOT NULL,
				PRIMARY KEY (`id_void_job`),
				INDEX `idx_transaction_id_space_id` (`transaction_id`,`space_id`),
				INDEX `idx_void_id_space_id` (`void_id`,`space_id`),
                INDEX `idx_state` (`state`),
                INDEX `idx_date_upd` (`date_upd`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
        
        if ($result === false) {
            throw new Exception(DB::getMsgError());
        }
        
        $result = Db::getInstance()->execute( "CREATE TABLE IF NOT EXISTS " . _DB_PREFIX_ . "wle_completion_job(
				`id_completion_job` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `completion_id` bigint(20) unsigned,
				`state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				`transaction_id` bigint(20) unsigned NOT NULL,
				`space_id` bigint(20) unsigned NOT NULL,
				`order_id` bigint(20) unsigned NOT NULL,
				`failure_reason` longtext COLLATE utf8_unicode_ci,
                `date_add` datetime NOT NULL,
				`date_upd` datetime NOT NULL,
				PRIMARY KEY (`id_completion_job`),
				INDEX `idx_transaction_id_space_id` (`transaction_id`,`space_id`),
				INDEX `idx_completion_id_space_id` (`completion_id`,`space_id`),
                INDEX `idx_state` (`state`),
                INDEX `idx_date_upd` (`date_upd`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
        
        if ($result === false) {
            throw new Exception(DB::getMsgError());
        }
        $result = Db::getInstance()->execute("CREATE TABLE IF NOT EXISTS " . _DB_PREFIX_ . "wle_refund_job(
                `id_refund_job` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `refund_id` bigint(20) unsigned,
                `external_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				`state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				`transaction_id` bigint(20) unsigned NOT NULL,
				`space_id` bigint(20) unsigned NOT NULL,
				`order_id` bigint(20) unsigned NOT NULL,
                `amount` decimal(19,8) NOT NULL,
                `refund_parameters` longtext COLLATE utf8_unicode_ci,
				`failure_reason` longtext COLLATE utf8_unicode_ci,
                `apply_tries` bigint(10) NOT NULL DEFAULT '0',
                `date_add` datetime NOT NULL,
				`date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_refund_job`),
                INDEX `idx_transaction_id_space_id` (`transaction_id`,`space_id`),
                INDEX `idx_refund_id_space_id` (`refund_id`,`space_id`),
                UNIQUE KEY `unq_external_id_space_id` (`external_id`,`space_id`),
                INDEX `idx_state` (`state`),
                INDEX `idx_date_upd` (`date_upd`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
         
          if ($result === false) {
            throw new Exception(DB::getMsgError());
          }
    }
}

