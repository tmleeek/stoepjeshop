	// override shop by
$mav(document).ready(function(){
	$mav('#narrow-by-list dt').append('<span class="head"><a href="javascript:void(0)"></a></span>');
	$mav('.block-layered-nav span.head').click( function(){
		
		$mav(this).parent().next().slideToggle(300);
		
		if($mav(this).parent().hasClass('selected')) {
			$mav(this).parent().addClass('unselected');
		}
		
		$mav('.block-layered-nav dt').each(function() {
			$mav(this).removeClass('selected');
			$mav(this).next().css('display','none');
		});
		
		if(!$mav(this).parent().hasClass('unselected')) {
			$mav(this).parent().addClass('selected');
		}
		$mav('.block-layered-nav dt').each(function() {
				$mav(this).removeClass('unselected');
		});
		
	});
});