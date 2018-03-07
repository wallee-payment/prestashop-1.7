jQuery(function($) {
    
    function moveWalleeManualTasks(){
    	$("#wallee_notifications").find("li").each(function(key, element){
    		$("#header_infos #notification").closest("ul").append(element);
    		var html = '<div class="component pull-md-right wallee-component"><ul>'+$(element).prop('outerHTML')+'</ul></div>';
    		$('.notification-center').closest('.component').after(html);
    	});
    }
    moveWalleeManualTasks();
    
});