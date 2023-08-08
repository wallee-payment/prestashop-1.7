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
 * This provider allows to create a Wallee_ShopRefund_IStrategy.
 * The implementation of
 * the strategy depends on the actual prestashop version.
 */
class WalleeBackendStrategyprovider
{
    private static $supported_strategies = [
        '1.7.7.4' => WalleeBackendStrategy1774::class
    ];

    /**
     * Returns the refund strategy to use
     *
     * @return WalleeBackendIstrategy
     */
    public static function getStrategy()
    {
        if (isset(self::$supported_strategies[_PS_VERSION_])) {
            return new self::$supported_strategies[_PS_VERSION_];
        }
        return new WalleeBackendDefaultstrategy();
    }
}
