jQuery(function($) {    
    $('#wallee_documents').find('a').each(function(key, element){
	
		$("#order-infos ul").append("<li>").append(element);
    });
});