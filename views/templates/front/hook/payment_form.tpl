{*
 * wallee Prestashop
 *
 * This Prestashop module enables to process payments with wallee (https://www.wallee.com).
 *
 * @author customweb GmbH (http://www.customweb.com/)
 * @copyright 2017 - 2023 customweb GmbH
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 *}
<form action="{$orderUrl|escape:'html'}" class="wallee-payment-form" data-method-id="{$methodId|escape:'html':'UTF-8'}">
	<div id="wallee-{$methodId|escape:'html':'UTF-8'}">
		<input type="hidden" id="wallee-iframe-possible-{$methodId|escape:'html':'UTF-8'}" name="wallee-iframe-possible-{$methodId|escape:'html':'UTF-8'}" value="false" />
		<div id="wallee-loader-{$methodId|escape:'html':'UTF-8'}" class="wallee-loader"></div>
	</div>
</form>