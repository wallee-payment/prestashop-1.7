<?php
/**
 * wallee Prestashop
 *
 * This Prestashop module enables to process payments with wallee (https://www.wallee.com).
 *
 * @author customweb GmbH (http://www.customweb.com/)
 * @copyright 2017 - 2021 customweb GmbH
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */

if (! defined('_PS_VERSION_')) {
    exit();
}

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'wallee_autoloader.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'wallee-sdk' . DIRECTORY_SEPARATOR .
    'autoload.php');
class Wallee extends PaymentModule
{
    
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->name = 'wallee';
        $this->tab = 'payments_gateways';
        $this->author = 'Customweb GmbH';
        $this->bootstrap = true;
        $this->need_instance = 0;
        $this->version = '1.2.4';
        $this->displayName = 'wallee';
        $this->description = $this->l('This PrestaShop module enables to process payments with %s.');
        $this->description = sprintf($this->description, 'wallee');
        $this->module_key = 'da87433bdf5237b4d9755ce977402e47';
        $this->ps_versions_compliancy = array(
            'min' => '1.7',
            'max' => _PS_VERSION_
        );
        parent::__construct();
        $this->confirmUninstall = sprintf(
            $this->l('Are you sure you want to uninstall the %s module?', 'abstractmodule'),
            'wallee'
        );
        
        // Remove Fee Item
        if (isset($this->context->cart) && Validate::isLoadedObject($this->context->cart)) {
            WalleeFeehelper::removeFeeSurchargeProductsFromCart($this->context->cart);
        }
        if (! empty($this->context->cookie->wle_error)) {
            $errors = $this->context->cookie->wle_error;
            if (is_string($errors)) {
                $this->context->controller->errors[] = $errors;
            } elseif (is_array($errors)) {
                foreach ($errors as $error) {
                    $this->context->controller->errors[] = $error;
                }
            }
            unset($_SERVER['HTTP_REFERER']); // To disable the back button in the error message
            $this->context->cookie->wle_error = null;
        }
    }
    
    public function addError($error)
    {
        $this->_errors[] = $error;
    }
    
    public function getContext()
    {
        return $this->context;
    }
    
    public function getTable()
    {
        return $this->table;
    }
    
    public function getIdentifier()
    {
        return $this->identifier;
    }
    
    public function install()
    {
        if (! WalleeBasemodule::checkRequirements($this)) {
            return false;
        }
        if (! parent::install()) {
            return false;
        }
        return WalleeBasemodule::install($this);
    }
    
    public function uninstall()
    {
        return parent::uninstall() && WalleeBasemodule::uninstall($this);
    }

    public function installHooks()
    {
        return WalleeBasemodule::installHooks($this) && $this->registerHook('paymentOptions') &&
            $this->registerHook('actionFrontControllerSetMedia');
    }

    public function getBackendControllers()
    {
        return array(
            'AdminWalleeMethodSettings' => array(
                'parentId' => Tab::getIdFromClassName('AdminParentPayment'),
                'name' => 'wallee ' . $this->l('Payment Methods')
            ),
            'AdminWalleeDocuments' => array(
                'parentId' => - 1, // No Tab in navigation
                'name' => 'wallee ' . $this->l('Documents')
            ),
            'AdminWalleeOrder' => array(
                'parentId' => - 1, // No Tab in navigation
                'name' => 'wallee ' . $this->l('Order Management')
            ),
            'AdminWalleeCronJobs' => array(
                'parentId' => Tab::getIdFromClassName('AdminTools'),
                'name' => 'wallee ' . $this->l('CronJobs')
            )
        );
    }

    public function installConfigurationValues()
    {
        return WalleeBasemodule::installConfigurationValues();
    }
    
    public function uninstallConfigurationValues()
    {
        return WalleeBasemodule::uninstallConfigurationValues();
    }
    
    public function getContent()
    {
        $output = WalleeBasemodule::getMailHookActiveWarning($this);
        $output .= WalleeBasemodule::handleSaveAll($this);
        $output .= WalleeBasemodule::handleSaveApplication($this);
        $output .= WalleeBasemodule::handleSaveEmail($this);
        $output .= WalleeBasemodule::handleSaveFeeItem($this);
        $output .= WalleeBasemodule::handleSaveDownload($this);
        $output .= WalleeBasemodule::handleSaveSpaceViewId($this);
        $output .= WalleeBasemodule::handleSaveOrderStatus($this);
        $output .= WalleeBasemodule::displayHelpButtons($this);
        return $output . WalleeBasemodule::displayForm($this);
    }

    public function getConfigurationForms()
    {
        return array(
            WalleeBasemodule::getEmailForm($this),
            WalleeBasemodule::getFeeForm($this),
            WalleeBasemodule::getDocumentForm($this),
            WalleeBasemodule::getSpaceViewIdForm($this),
            WalleeBasemodule::getOrderStatusForm($this)
        );
    }

    public function getConfigurationValues()
    {
        return array_merge(
            WalleeBasemodule::getApplicationConfigValues($this),
            WalleeBasemodule::getEmailConfigValues($this),
            WalleeBasemodule::getFeeItemConfigValues($this),
            WalleeBasemodule::getDownloadConfigValues($this),
            WalleeBasemodule::getSpaceViewIdConfigValues($this),
            WalleeBasemodule::getOrderStatusConfigValues($this)
        );
    }
    
    public function getConfigurationKeys()
    {
        return WalleeBasemodule::getConfigurationKeys();
    }

    public function hookPaymentOptions($params)
    {
        if (! $this->active) {
            return;
        }
        if (! isset($params['cart']) || ! ($params['cart'] instanceof Cart)) {
            return;
        }
        $cart = $params['cart'];
        try {
            $possiblePaymentMethods = WalleeServiceTransaction::instance()->getPossiblePaymentMethods(
                $cart
            );
        } catch (WalleeExceptionInvalidtransactionamount $e) {
            PrestaShopLogger::addLog($e->getMessage() . " CartId: " . $cart->id, 2, null, 'Wallee');
            $paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
            $paymentOption->setCallToActionText(
                $this->l('There is an issue with your cart, some payment methods are not available.')
            );
            $paymentOption->setAdditionalInformation(
                $this->context->smarty->fetch(
                    'module:wallee/views/templates/front/hook/amount_error.tpl'
                )
            );
            $paymentOption->setForm(
                $this->context->smarty->fetch(
                    'module:wallee/views/templates/front/hook/amount_error_form.tpl'
                )
            );
            $paymentOption->setModuleName($this->name . "-error");
            return array(
                $paymentOption
            );
        } catch (Exception $e) {
            PrestaShopLogger::addLog($e->getMessage() . " CartId: " . $cart->id, 1, null, 'Wallee');
            return array();
        }
        $shopId = $cart->id_shop;
        $language = Context::getContext()->language->language_code;
        $methods = array();
        foreach ($possiblePaymentMethods as $possible) {
            $methodConfiguration = WalleeModelMethodconfiguration::loadByConfigurationAndShop(
                $possible->getSpaceId(),
                $possible->getId(),
                $shopId
            );
            if (! $methodConfiguration->isActive()) {
                continue;
            }
            $methods[] = $methodConfiguration;
        }
        $result = array();
        
        $this->context->smarty->registerPlugin(
            'function',
            'wallee_clean_html',
            array(
                'WalleeSmartyfunctions',
                'cleanHtml'
            )
        );
        
        foreach (WalleeHelper::sortMethodConfiguration($methods) as $methodConfiguration) {
            $parameters = WalleeBasemodule::getParametersFromMethodConfiguration($this, $methodConfiguration, $cart, $shopId, $language);
            $parameters['priceDisplayTax'] = Group::getPriceDisplayMethod(Group::getCurrent()->id);
            $parameters['orderUrl'] = $this->context->link->getModuleLink(
                'wallee',
                'order',
                array(),
                true
            );
            $this->context->smarty->assign($parameters);
            $paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
            $paymentOption->setCallToActionText($parameters['name']);
            $paymentOption->setLogo($parameters['image']);
            $paymentOption->setAction($parameters['link']);
            $paymentOption->setAdditionalInformation(
                $this->context->smarty->fetch(
                    'module:wallee/views/templates/front/hook/payment_additional.tpl'
                )
            );
            $paymentOption->setForm(
                $this->context->smarty->fetch(
                    'module:wallee/views/templates/front/hook/payment_form.tpl'
                )
            );
            $paymentOption->setModuleName($this->name);
            $result[] = $paymentOption;
        }
        return $result;
    }

    public function hookActionFrontControllerSetMedia($arr)
    {
        if ($this->context->controller->php_self == 'order' || $this->context->controller->php_self == 'cart') {
            $uniqueId = $this->context->cookie->wle_device_id;
            if ($uniqueId == false) {
                $uniqueId = WalleeHelper::generateUUID();
                $this->context->cookie->wle_device_id = $uniqueId;
            }
            $scriptUrl = WalleeHelper::getBaseGatewayUrl() . '/s/' . Configuration::get(
                WalleeBasemodule::CK_SPACE_ID
            ) . '/payment/device.js?sessionIdentifier=' . $uniqueId;
            $this->context->controller->registerJavascript(
                'wallee-device-identifier',
                $scriptUrl,
                array(
                    'server' => 'remote',
                    'attributes' => 'async="async"'
                )
            );
        }
        if ($this->context->controller->php_self == 'order') {
            $this->context->controller->registerStylesheet(
                'wallee-checkut-css',
                'modules/' . $this->name . '/views/css/frontend/checkout.css'
            );
            $this->context->controller->registerJavascript(
                'wallee-checkout-js',
                'modules/' . $this->name . '/views/js/frontend/checkout.js'
            );
            Media::addJsDef(
                array(
                    'walleeCheckoutUrl' => $this->context->link->getModuleLink(
                        'wallee',
                        'checkout',
                        array(),
                        true
                    ),
                    'walleeMsgJsonError' => $this->l(
                        'The server experienced an unexpected error, you may try again or try to use a different payment method.'
                    )
                )
            );
            if (isset($this->context->cart) && Validate::isLoadedObject($this->context->cart)) {
                try {
                    $jsUrl = WalleeServiceTransaction::instance()->getJavascriptUrl($this->context->cart);
                    $this->context->controller->registerJavascript(
                        'wallee-iframe-handler',
                        $jsUrl,
                        array(
                            'server' => 'remote',
                            'priority' => 45,
                            'attributes' => 'id="wallee-iframe-handler"'
                        )
                    );
                } catch (Exception $e) {
                }
            }
        }
        if ($this->context->controller->php_self == 'order-detail') {
            $this->context->controller->registerJavascript(
                'wallee-checkout-js',
                'modules/' . $this->name . '/views/js/frontend/orderdetail.js'
            );
        }
    }
    
    public function hookDisplayTop($params)
    {
        return  WalleeBasemodule::hookDisplayTop($this, $params);
    }

    public function hookActionAdminControllerSetMedia($arr)
    {
        WalleeBasemodule::hookActionAdminControllerSetMedia($this, $arr);
        $this->context->controller->addCSS(__PS_BASE_URI__ . 'modules/' . $this->name . '/views/css/admin/general.css');
    }

    public function hasBackendControllerDeleteAccess(AdminController $backendController)
    {
        return $backendController->access('delete');
    }

    public function hasBackendControllerEditAccess(AdminController $backendController)
    {
        return $backendController->access('edit');
    }
    
    public function hookWalleeCron($params)
    {
        return WalleeBasemodule::hookWalleeCron($params);
    }
    /**
     * Show the manual task in the admin bar.
     * The output is moved with javascript to the correct place as better hook is missing.
     *
     * @return string
     */
    public function hookDisplayAdminAfterHeader()
    {
        $result = WalleeBasemodule::hookDisplayAdminAfterHeader($this);
        $result .= WalleeBasemodule::getCronJobItem($this);
        return $result;
    }
    
    public function hookWalleeSettingsChanged($params)
    {
        return WalleeBasemodule::hookWalleeSettingsChanged($this, $params);
    }
    
    public function hookActionMailSend($data)
    {
        return WalleeBasemodule::hookActionMailSend($this, $data);
    }
    
    public function validateOrder(
        $id_cart,
        $id_order_state,
        $amount_paid,
        $payment_method = 'Unknown',
        $message = null,
        $extra_vars = array(),
        $currency_special = null,
        $dont_touch_amount = false,
        $secure_key = false,
        Shop $shop = null
    ) {
        WalleeBasemodule::validateOrder($this, $id_cart, $id_order_state, $amount_paid, $payment_method, $message, $extra_vars, $currency_special, $dont_touch_amount, $secure_key, $shop);
    }
    
    public function validateOrderParent(
        $id_cart,
        $id_order_state,
        $amount_paid,
        $payment_method = 'Unknown',
        $message = null,
        $extra_vars = array(),
        $currency_special = null,
        $dont_touch_amount = false,
        $secure_key = false,
        Shop $shop = null
    ) {
        parent::validateOrder($id_cart, $id_order_state, $amount_paid, $payment_method, $message, $extra_vars, $currency_special, $dont_touch_amount, $secure_key, $shop);
    }
    
    public function hookDisplayOrderDetail($params)
    {
        return WalleeBasemodule::hookDisplayOrderDetail($this, $params);
    }
    
    public function hookDisplayBackOfficeHeader($params)
    {
        WalleeBasemodule::hookDisplayBackOfficeHeader($this, $params);
    }

    public function hookDisplayAdminOrderLeft($params)
    {
        return WalleeBasemodule::hookDisplayAdminOrderLeft($this, $params);
    }

    public function hookDisplayAdminOrderTabOrder($params)
    {
        return WalleeBasemodule::hookDisplayAdminOrderTabOrder($this, $params);
    }
    
    public function hookDisplayAdminOrderMain($params)
    {
        return WalleeBasemodule::hookDisplayAdminOrderMain($this, $params);
    }
    
    public function hookDisplayAdminOrderTabLink($params)
    {
        return WalleeBasemodule::hookDisplayAdminOrderTabLink($this, $params);
    }
    
    public function hookDisplayAdminOrderContentOrder($params)
    {
        return WalleeBasemodule::hookDisplayAdminOrderContentOrder($this, $params);
    }
    
    public function hookDisplayAdminOrder($params)
    {
        return WalleeBasemodule::hookDisplayAdminOrder($this, $params);
    }
    
    public function hookActionAdminOrdersControllerBefore($params)
    {
        return WalleeBasemodule::hookActionAdminOrdersControllerBefore($this, $params);
    }
    
    public function hookActionObjectOrderPaymentAddBefore($params)
    {
        WalleeBasemodule::hookActionObjectOrderPaymentAddBefore($this, $params);
    }
    
    public function hookActionOrderEdited($params)
    {
        WalleeBasemodule::hookActionOrderEdited($this, $params);
    }
    
    public function hookActionProductCancel($params)
    {
        WalleeBasemodule::hookActionProductCancel($this, $params);
    }
}
