{*
 * wallee Prestashop
 *
 * This Prestashop module enables to process payments with wallee (https://www.wallee.com).
 *
 * @author customweb GmbH (http://www.customweb.com/)
 * @copyright 2017 - 2019 customweb GmbH
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 *}
<div sytle="display:none" class="wallee-method-data" data-method-id="{$methodId}" data-configuration-id="{$configurationId}"></div>
<section>
  {if !empty($description)}
    <p>{$description nofilter}</p>
  {/if}
  {if !empty($surchargeValues)}
	<span class="wallee-surcharge wallee-additional-amount"><span class="wallee-surcharge-text wallee-additional-amount-test">{l s='Minimum Sales Surcharge:' mod='wallee'}</span>
		<span class="wallee-surcharge-value wallee-additional-amount-value">
			{if $priceDisplayTax}
				{Tools::displayPrice($surchargeValues.surcharge_total)} {l s='(tax excl.)' mod='wallee'}
	        {else}
	        	{Tools::displayPrice($surchargeValues.surcharge_total_wt)} {l s='(tax excl.)' mod='wallee'}
	        {/if}
       </span>
   </span>
  {/if}
  {if !empty($feeValues)}
	<span class="wallee-payment-fee wallee-additional-amount"><span class="wallee-payment-fee-text wallee-additional-amount-test">{l s='Payment Fee:' mod='wallee'}</span>
		<span class="wallee-payment-fee-value wallee-additional-amount-value">
			{if ($priceDisplayTax)}
	          	{Tools::displayPrice($feeValues.fee_total)} {l s='(tax excl.)' mod='wallee'}
	        {else}
	          	{Tools::displayPrice($feeValues.fee_total_wt)} {l s='(tax incl.)' mod='wallee'}
	        {/if}
       </span>
   </span>
  {/if}
  
</section>
