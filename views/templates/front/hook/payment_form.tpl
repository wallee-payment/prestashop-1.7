{*
 * wallee Prestashop
 *
 * This Prestashop module enables to process payments with wallee (https://www.wallee.com).
 *
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 *}
<form action="{$orderUrl|escape:'html'}" class="wallee-payment-form" data-method-id="{$methodId}">
	<div id="wallee-{$methodId}">
		<div id="wallee-loader-{$methodId}" class="wallee-loader"></div>
	</div>
</form>