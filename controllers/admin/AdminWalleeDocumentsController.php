<?php
if (! defined('_PS_VERSION_')) {
    exit();
}

class AdminWalleeDocumentsController extends ModuleAdminController
{

    public function postProcess()
    {
        parent::postProcess();
        // We want to be sure that displaying PDF is the last thing this controller will do
        exit();
    }

    public function initProcess()
    {
        parent::initProcess();
        $access = Profile::getProfileAccess($this->context->employee->id_profile,
            (int) Tab::getIdFromClassName('AdminOrders'));
        if ($access['view'] === '1' && ($action = Tools::getValue('action'))) {
            $this->action = $action;
        } else {
            die(Tools::displayError($this->module->l('You do not have permission to view this.')));
        }
    }

    public function processWalleeInvoice()
    {
        if (Tools::isSubmit('id_order')) {
            try {
                $order = new Order(Tools::getValue('id_order'));
                Wallee_DownloadHelper::downloadInvoice($order);
            } catch (Exception $e) {
                die(Tools::displayError($this->module->l('Could not fetch the document.')));
            }
        } else {
            die(Tools::displayError($this->module->l('The order Id is missing.')));
        }
    }

    public function processWalleePackingSlip()
    {
        if (Tools::isSubmit('id_order')) {
            try {
                $order = new Order(Tools::getValue('id_order'));
                Wallee_DownloadHelper::downloadPackingSlip($order);
            } catch (Exception $e) {
                die(Tools::displayError($this->module->l('Could not fetch the document.')));
            }
        } else {
            die(Tools::displayError($this->module->l('The order Id is missing.')));
        }
    }
}
