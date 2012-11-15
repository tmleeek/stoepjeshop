$mav(document).ready(function(){			
	$mav('#ma-accordion ul.level0').before('<span class="head"><a href="javascript:void(0)"></a></span>');			
	$mav('#ma-accordion li.level0.active').addClass('selected');
	// applying the settings			
	$mav("#ma-accordion  li  span").click(function(){
		if(false == $mav(this).next('ul').is(':visible')) {
			$mav('#ma-accordion ul').slideUp(300);
		}
		$mav(this).next('.level0').slideToggle(300);
		
		if($mav(this).parent().hasClass('selected')) {
			$mav(this).parent().addClass('unselected');
		}
		
		$mav('#ma-accordion li.selected').each(function() {
				$mav(this).removeClass('selected');
		});
		if(!$mav(this).parent().hasClass('unselected')) {
			$mav(this).parent().addClass('selected');
		}
		$mav('#ma-accordion li.unselected').each(function() {
				$mav(this).removeClass('unselected');
		});
	});
});