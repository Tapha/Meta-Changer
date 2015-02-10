//hide metadesc, check to see if meta description already exists and echo that as value else check and get data if it exists, it it doesn't then show the empty meta desc. on current select, push it to the metadesc as value, show metadesc.

(function($) {
    $(document).ready(function(){
    
    	//Hide metadesc for the time being and get current page
        $( "#metadesc" ).hide();
        var selected = $("#select_dropdown :selected").val();
        
        //console.log(t);
        //make WP_Query to see if a metadesc exists already. 
        var data = {
		'action': 'check_for_meta_desc',
		'page_id': selected
		};

		// We can also pass the url value separately from ajaxurl for front end AJAX implementations
		jQuery.post(ajaxurl, data, function(response) {

			// console.log(response);


			//Add Meta Description and show
			if (response == '0')
			{
				$("#metadesc").val('').attr('placeholder','Nothing yet.');
				$( "#metadesc" ).show();
			}
			else
			{

				$("#metadesc").val(response);
				$( "#metadesc" ).show();
			}
			
		});
        
        $(document).on('change', '#select_dropdown', function(e) {
		    var b = this.options[e.target.selectedIndex].value;

		    //make WP_Query to see if a metadesc exists already. 
	        var data2 = {
			'action': 'check_for_meta_desc',
			'page_id': b
			};

			// We can also pass the url value separately from ajaxurl for front end AJAX implementations
			jQuery.post(ajaxurl, data2, function(response2) {
				//Add Meta Description and show
				if (response2 == '0')
				{
					$("#metadesc").val('').attr('placeholder','Nothing yet.');
					$( "#metadesc" ).show();
				}
				else
				{
					$("#metadesc").val(response2);
					$( "#metadesc" ).show();
				}
			});
		});
		
		//Submit data
		/* attach a submit handler to the form */
	    $("#mcp_form_submit").submit(function(e){
	    	e.preventDefault();

	    	$('#meta_description_success').hide();

		    var post_id = $("#select_dropdown").val();
		    var desc_text = $("#metadesc").val();
		    $.ajax({ 
		         data: {action: 'mcp_insert_custom_table', post_id:post_id, desc_text:desc_text},
		         type: 'post',
		         url: ajaxurl,
		         success: function(data) {
		              //console.log(data); //should print out the name since you sent it along
		              $('#meta_description_success').show();
		
		        }
		    });
		
		});
		
		//On page load (through filter/action on main plugin file) check if meta description exists on hardcode, if not see if it exists in db. then show from db, if not then show nothing.
		
		
    });
})(jQuery);