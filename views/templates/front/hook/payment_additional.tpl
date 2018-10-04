{*
 * wallee Prestashop
 *
 * This Prestashop module enables to process payments with wallee (https://www.wallee.com).
 *
 * @author customweb GmbH (http://www.customweb.com/)
 * @copyright 2017 - 2018 customweb GmbH
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 *}
<div sytle="display:none" class="wallee-method-data" data-method-id="{$methodId}" data-configuration-id="{$configurationId}"></div>
<section>
  {if !empty($description)}
    <p>{$description}</p>
  {/if}
  {if !empty($feeValues)}
	<span class="wallee-payment-fee"><span class="wallee-payment-fee-text">{l s='Additional Fee:' mod='wallee'}</span>
		<span class="wallee-payment-fee-value">
			{if ($priceDisplayTax)}
	          	{Tools::displayPrice($feeValues.fee_total)} {l s='(tax excl.)' mod='wallee'}
	        {else}
	          	{Tools::displayPrice($feeValues.fee_total_wt)} {l s='(tax incl.)' mod='wallee'}
	        {/if}
       </span>
   </span>
{/if}
  
</section>
