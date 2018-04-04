<?php
if (! defined('_PS_VERSION_')) {
    exit();
}

class Wallee_OrderStatus
{

    private static $orderStatesConfig = array(
        'WLE_REDIRECTED' => array(
            'color' => '#4169e1',
            'name' => 'wallee Redirected',
            'invoice' => 0,
            'logable' => 0,
            'image' => 'redirected'
        ),
        'WLE_AUTHORIZED' => array(
            'color' => '#0000cd',
            'name' => 'wallee Authorized',
            'invoice' => 0,
            'logable' => 1,
            'image' => 'authorized'
        ),
        'WLE_WAITING' => array(
            'color' => '#000080',
            'name' => 'wallee Waiting',
            'invoice' => 0,
            'logable' => 1,
            'image' => 'waiting'
        ),
        'WLE_MANUAL' => array(
            'color' => '#191970',
            'name' => 'wallee Manual Decision',
            'invoice' => 0,
            'logable' => 1,
            'image' => 'manual'
        )
    );

    private static $orderStates = array();

    public static function getRedirectOrderStatus()
    {
        return self::getOrderStatus('WLE_REDIRECTED');
    }
    
    public static function getAuthorizedOrderStatus()
    {
        return self::getOrderStatus('WLE_AUTHORIZED');
    }

    public static function getWaitingOrderStatus()
    {
        return self::getOrderStatus('WLE_WAITING');
    }

    public static function getManualOrderStatus()
    {
        return self::getOrderStatus('WLE_MANUAL');
    }

    public static function registerOrderStatus(){
        foreach(self::$orderStatesConfig as $key => $ignore){
            self::getOrderStatusId($key);
        }
    }
    
    private static function getOrderStatusId($key)
    {
        $result = Configuration::getGlobalValue($key);
        if (! empty($result)) {
            return $result;
        }
        //Just in case order state is deleted after installation
        return self::createOrderState($key);
    }

    /**
     *
     * @return OrderState
     */
    private static function getOrderStatus($key)
    {
        if (! isset(self::$orderStates[$key]) || self::$orderStates[$key] === null) {
            self::$orderStates[$key] = new OrderState(self::getOrderStatusId($key));
        }
        return self::$orderStates[$key];
    }

    private static function createOrderState($key)
    {
        $config = self::$orderStatesConfig[$key];
        $state = new OrderState();
        $state->color = $config['color'];
        $state->deleted = 0;
        $state->hidden = 0;
        $state->logable = $config['logable']; ;
        foreach (Language::getLanguages() as $language) {
            $state->name[$language['id_lang']] = $config['name'];
        }
        $state->delivery = 0;
        $state->invoice = $config['invoice'];
        $state->paid = 0;
        $state->send_email = 0;
        $state->template = '';
        $state->unremovable = 1;
        $state->module_name = 'wallee';
        $state->add();
        $source = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logo'.DIRECTORY_SEPARATOR.$config['image'].'.gif';
        $destination = _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'os'.DIRECTORY_SEPARATOR.(int) $state->id.'.gif';
        copy($source, $destination);
        self::setOrderStatusId($key, $state->id);
        return $state->id;
    }

    private static function setOrderStatusId($key, $id)
    {
        return Configuration::updateGlobalValue($key, $id);
    }
}