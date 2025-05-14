{*
 * wallee Prestashop
 *
 * This Prestashop module enables to process payments with wallee (https://www.wallee.com).
 *
 * @author customweb GmbH (http://www.customweb.com/)
 * @copyright 2017 - 2025 customweb GmbH
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 *}
<div sytle="display:none" class="wallee-method-data" data-method-id="{$methodId|escape:'html':'UTF-8'}" data-configuration-id="{$configurationId|escape:'html':'UTF-8'}"></div>
<section>
  {if !empty($description)}
    {* The description has to be unfiltered to dispaly html correcty. We strip unallowed html tags before we assign the variable to smarty *}
    <p>{wallee_clean_html text=$description}</p>
  {/if}
  {if !empty($surchargeValues)}
	<span class="wallee-surcharge wallee-additional-amount"><span class="wallee-surcharge-text wallee-additional-amount-test">{l s='Minimum Sales Surcharge:' mod='wallee'}</span>
		<span class="wallee-surcharge-value wallee-additional-amount-value">
			{if $priceDisplayTax}
				{Tools::displayPrice($surchargeValues.surcharge_total)|escape:'html':'UTF-8'} {l s='(tax excl.)' mod='wallee'}
	        {else}
	        	{Tools::displayPrice($surchargeValues.surcharge_total_wt)|escape:'html':'UTF-8'} {l s='(tax incl.)' mod='wallee'}
	        {/if}
       </span>
   </span>
  {/if}
  {if !empty($feeValues)}
	<span class="wallee-payment-fee wallee-additional-amount"><span class="wallee-payment-fee-text wallee-additional-amount-test">{l s='Payment Fee:' mod='wallee'}</span>
		<span class="wallee-payment-fee-value wallee-additional-amount-value">
			{if ($priceDisplayTax)}
	          	{Tools::displayPrice($feeValues.fee_total)|escape:'html':'UTF-8'} {l s='(tax excl.)' mod='wallee'}
	        {else}
	          	{Tools::displayPrice($feeValues.fee_total_wt)|escape:'html':'UTF-8'} {l s='(tax incl.)' mod='wallee'}
	        {/if}
       </span>
   </span>
  {/if}
  
</section>
