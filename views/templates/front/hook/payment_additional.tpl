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