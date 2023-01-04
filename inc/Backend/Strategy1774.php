<?php
/**
 * wallee Prestashop
 *
 * This Prestashop module enables to process payments with wallee (https://www.wallee.com).
 *
 * @author customweb GmbH (http://www.customweb.com/)
 * @copyright 2017 - 2023 customweb GmbH
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */

/**
 * Webhook processor to handle transaction completion state transitions.
 */
class WalleeBackendStrategy1774 extends WalleeBackendDefaultstrategy {

    public function isVoucherOnlyWallee(Order $order, array $postData)
    {
        return isset($postData['cancel_product']['voucher']) && $postData['cancel_product']['voucher'] == 1;
    }

}
