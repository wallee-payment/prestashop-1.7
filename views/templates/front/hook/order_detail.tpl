{*
 * wallee Prestashop
 *
 * This Prestashop module enables to process payments with wallee (https://www.wallee.com).
 *
 * @author customweb GmbH (http://www.customweb.com/)
 * @copyright 2017 - 2025 customweb GmbH
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 *}
<div id="wallee_documents" style="display:none">
{if !empty($walleeInvoice)}
	<a target="_blank" href="{$walleeInvoice|escape:'html':'UTF-8'}">{l s='Download your %name% invoice as a PDF file.' sprintf=['%name%' => 'wallee'] mod='wallee'}</a>
{/if}
{if !empty($walleePackingSlip)}
	<a target="_blank" href="{$walleePackingSlip|escape:'html':'UTF-8'}">{l s='Download your %name% packing slip as a PDF file.' sprintf=['%name%' => 'wallee'] mod='wallee'}</a>
{/if}
</div>
