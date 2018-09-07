<?php
if (! defined('_PS_VERSION_')) {
    exit();
}

/**
 * wallee Prestashop
 *
 * This Prestashop module enables to process payments with wallee (https://www.wallee.com).
 *
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */

define('WALLEE_VERSION', '1.0.10');

require_once (__DIR__ . DIRECTORY_SEPARATOR . 'wallee_autoloader.php');
require_once (__DIR__ . DIRECTORY_SEPARATOR . 'wallee-sdk' . DIRECTORY_SEPARATOR .
    'autoload.php');

class Wallee extends Wallee_AbstractModule
{

    protected function installHooks()
    {
        return parent::installHooks() && $this->registerHook('paymentOptions') &&
            $this->registerHook('actionCronJob') &&
            $this->registerHook('actionFrontControllerSetMedia');
    }

    protected function getBackendControllers()
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
            )
        );
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->uninstallControllers() &&
            $this->uninstallConfigurationValues();
    }

    public function getContent()
    {
        $output = $this->getMailHookActiveWarning();
        $output .= $this->getCronModuleActiveWarning();
        $output .= $this->handleSaveAll();
        $output .= $this->handleSaveApplication();
        $output .= $this->handleSaveEmail();
        $output .= $this->handleSaveFeeItem();
        $output .= $this->handleSaveDownload();
        $output .= $this->handleSaveSpaceViewId();
        $output .= $this->handleSaveOrderStatus();
        $output .= $this->displayHelpButtons();
        return $output . $this->displayForm();
    }

    protected function getCronModuleActiveWarning()
    {
        $output = "";
        if (! Module::isInstalled('cronjobs') || ! Module::isEnabled('cronjobs')) {
            $error = "<b>" . $this->l('The module "Cron tasks manager" is not active.') . "</b>";
            $error .= "<br/>";
            $error .= $this->l('This module is required for updating pending transactions, completions, voids and refunds.');
            $error .= "<br/>";
            $output .= $this->displayError($error);
        }
        return $output;
    }

    protected function getConfigurationForms()
    {
        return array(
            $this->getEmailForm(),
            $this->getFeeForm(),
            $this->getDocumentForm(),
            $this->getSpaceViewIdForm(),
            $this->getOrderStatusForm()
        );
    }

    protected function getConfigurationValues()
    {
        return array_merge($this->getApplicationConfigValues(), $this->getEmailConfigValues(),
            $this->getFeeItemConfigValues(), $this->getDownloadConfigValues(), 
            $this->getSpaceViewIdConfigValues(),
            $this->getOrderStatusConfigValues());
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
            $possiblePaymentMethods = Wallee_Service_Transaction::instance()->getPossiblePaymentMethods(
                $cart);
        }
        catch (Exception $e) {
            return array();
        }
        $shopId = $cart->id_shop;
        $configurations = array();
        $language = Context::getContext()->language->language_code;
        $methods = array();
        foreach ($possiblePaymentMethods as $possible) {
            $methodConfiguration = Wallee_Model_MethodConfiguration::loadByConfigurationAndShop(
                $possible->getSpaceId(), $possible->getId(), $shopId);
            if (! $methodConfiguration->isActive()) {
                continue;
            }
            $methods[] = $methodConfiguration;
        }
        $result = array();
        foreach (Wallee_Helper::sortMethodConfiguration($methods) as $methodConfiguration) {
            $parameters = $this->getParametersFromMethodConfiguration($methodConfiguration, $cart,
                $shopId, $language);
            $parameters['priceDisplayTax'] = Group::getPriceDisplayMethod(Group::getCurrent()->id);
            $parameters['orderUrl'] = $this->context->link->getModuleLink('wallee',
                'order', array(), true);
            $this->context->smarty->assign($parameters);
            
            $paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
            $paymentOption->setCallToActionText($parameters['name']);
            $paymentOption->setLogo($parameters['image']);
            $paymentOption->setAction($parameters['link']);
            $paymentOption->setAdditionalInformation(
                $this->context->smarty->fetch(
                    'module:wallee/views/templates/front/hook/payment_additional.tpl'));
            $paymentOption->setForm(
                $this->context->smarty->fetch(
                    'module:wallee/views/templates/front/hook/payment_form.tpl'));
            $paymentOption->setModuleName($this->name);
            $result[] = $paymentOption;
        }
        return $result;
    }

    public function hookActionFrontControllerSetMedia($arr)
    {
        if ($this->context->controller->php_self == 'order' ||
            $this->context->controller->php_self == 'cart') {
            $uniqueId = $this->context->cookie->wle_device_id;
            if ($uniqueId == false) {
                $uniqueId = Wallee_Helper::generateUUID();
                $this->context->cookie->wle_device_id = $uniqueId;
            }
            $scriptUrl = Wallee_Helper::getBaseGatewayUrl() . '/s/' .
                Configuration::get(self::CK_SPACE_ID) . '/payment/device.js?sessionIdentifier=' .
                $uniqueId;
            $this->context->controller->registerJavascript(
                'wallee-device-identifier', $scriptUrl,
                array(
                    'server' => 'remote',
                    'attributes' => 'async="async"'
                ));
        }
        if ($this->context->controller->php_self == 'order') {
            $this->context->controller->registerStylesheet('wallee-checkut-css',
                'modules/' . $this->name . '/css/frontend/checkout.css');
            $this->context->controller->registerJavascript('wallee-checkout-js',
                'modules/' . $this->name . '/js/frontend/checkout.js');
            Media::addJsDef(
                array(
                    'walleeCheckoutUrl' => $this->context->link->getModuleLink(
                        'wallee', 'checkout', array(), true),
                    'walleeMsgJsonError' => $this->l('The server experienced an unexpected error, you may try again or try to use a different payment method.')
                ));
            if (isset($this->context->cart) && Validate::isLoadedObject($this->context->cart)) {
                try {
                    $jsUrl = Wallee_Service_Transaction::instance()->getJavascriptUrl(
                        $this->context->cart);
                    $this->context->controller->registerJavascript(
                        'wallee-iframe-handler', $jsUrl,
                        array(
                            'server' => 'remote',
                            'priority' => 45,
                            'attributes' => 'id="wallee-iframe-handler"'
                        ));
                        
                }
                catch (Exception $e) {
                }
            }
        }
        if ($this->context->controller->php_self == 'order-detail') {
            $this->context->controller->registerJavascript('wallee-checkout-js',
                'modules/' . $this->name . '/js/frontend/orderdetail.js');
        }
    }

    public function hookActionAdminControllerSetMedia($arr)
    {
        parent::hookActionAdminControllerSetMedia($arr);
        $this->context->controller->addCSS(
            __PS_BASE_URI__ . 'modules/' . $this->name . '/css/admin/general.css');
    }

    protected function hasBackendControllerDeleteAccess(AdminController $backendController)
    {
        return $backendController->access('delete');
    }

    protected function hasBackendControllerEditAccess(AdminController $backendController)
    {
        return $backendController->access('edit');
    }

    public function hookActionCronJob($param)
    {
        $voidService = Wallee_Service_TransactionVoid::instance();
        if ($voidService->hasPendingVoids()) {
            $voidService->updateVoids();
        }
        $completionService = Wallee_Service_TransactionCompletion::instance();
        if ($completionService->hasPendingCompletions()) {
            $completionService->updateCompletions();
        }
        $refundService = Wallee_Service_Refund::instance();
        if ($refundService->hasPendingRefunds()) {
            $refundService->updateRefunds();
        }
    }

    public function getCronFrequency()
    {
        return array(
            'hour' => - 1,
            'day' => - 1,
            'month' => - 1,
            'day_of_week' => - 1
        );
    }
}



