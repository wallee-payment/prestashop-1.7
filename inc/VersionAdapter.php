<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\ServiceLocator;

class Wallee_VersionAdapter
{   
    
    public static function getConfigurationInterface(){
        return ServiceLocator::get('\\PrestaShop\\PrestaShop\\Core\\ConfigurationInterface');
        
    }
    
    public static function getAddressFactory(){
        return ServiceLocator::get('\\PrestaShop\\PrestaShop\\Adapter\\AddressFactory');        
    }
    
    public static function clearCartRuleStaticCache(){
        CartRule::resetStaticCache();
    }
}