(function($) {
    $(function() {
		var maxItems = 10;

        /* Will insert a dropdown at specified list. */
        function generateDropdown(list) {
            var dropdown = '<select class="layered-navigation-dropdown">';
            dropdown += '<option value="">- Selecteer ' + list.prev().text().toLowerCase() + ' -</option>';
            list.find('li a').each(function() {
				var amount = $(this).parent().html().split(/(\(\d+\))\s+?$/g)[1];
                dropdown += '<option value="' + $(this).attr('href') + '">' + $(this).text() + ' ' + amount + '</option>';
            });
            dropdown += '</select>';
            list.hide().after(dropdown);
        }

        /* Generate a dropdown for every list bigger than maximum allowed. */
        $('.filter-box ul').each(function() {
            if($(this).find('li').size() > maxItems) {
                generateDropdown($(this));
            }
        });

        /* Now let's make the dropdowns work... */
        $('.layered-navigation-dropdown').live('change', function() {
            if($(this).val().length) {
                window.location.href = $(this).val();
            }
        });
	});
})(jQuery);
