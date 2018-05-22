/**
 * wallee Prestashop
 *
 * This Prestashop module enables to process payments with wallee (https://www.wallee.com).
 *
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
jQuery(function($) {    
    $('#wallee_documents').find('a').each(function(key, element){
	
		$("#order-infos ul").append("<li>").append(element);
    });
});