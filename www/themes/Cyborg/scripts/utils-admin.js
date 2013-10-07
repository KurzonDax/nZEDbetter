

/**
 * ajax_binaryblacklist_delete()
 *
 * @param id        binary id
 */
function ajax_binaryblacklist_delete(id)
{
    // no caching of results
    var rand_no = Math.random();
	$.ajax({
	  url       : WWW_TOP + '/admin/ajax_binaryblacklist-list.php?action=2&rand=' + rand_no,
	  data      : { bin_id: id},
	  dataType  : "html",
	  success   : function(data)
	  {
		  $('div#message').html(data);
		  $('div#message').show('fast', function() {});
		  $('#row-'+id).fadeOut(2000);
		  $('#message').fadeOut(5000);
	  },
	  error: function(xhr,err,e) { alert( "Error in ajax_binaryblacklist_delete: " + err ); }
	});
}

jQuery(function($){

    $('#regexGroupSelect').change(function() {
        document.location="?group=" + $("#regexGroupSelect option:selected").attr('value');
    });

// misc
    $('.confirm_action').click(function(){ return confirm('Are you sure?'); });

    var pageTitle = window.location.pathname.replace(/^.*\/([^/]*)/, "$1");
    $(document).ready(function() {
        if(pageTitle == "index.php" || pageTitle == ''){
            $(".nav-icon").addClass('active')
        } else {
        $("#main-nav").find(".dropdown-menu").find("li").has('a[href="'+pageTitle+'"]').parent().parent().addClass("active");
        }
    });
});
