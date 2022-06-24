<?php

/**

 * The template for displaying the footer.

 *

 * Contains the closing of the #content div and all content after

 *

 * @package Shoptimizer

 */



?>



		</div><!-- .col-full -->

	</div><!-- #content -->



</div>



	<?php do_action( 'shoptimizer_before_footer' ); ?>



	<?php

	/**

	 * Functions hooked in to shoptimizer_footer action

	 */

	do_action( 'shoptimizer_footer' );

	?>



	<?php do_action( 'shoptimizer_after_footer' ); ?>





</div><!-- #page -->

<?php wp_footer(); ?>

<script>
jQuery(document).ready(function(){
	jQuery(document).on('click','.product_custom_field .first_sec a, .click_go_back',function(){
		jQuery.ajax({
			url:'/wp-admin/admin-ajax.php',
			type:'POST',
			data:{
				action:'read_xml',
			},
			success:function(response){
				response = JSON.parse(response);
				var html='';
				jQuery.each(response,function(index,item){
					html='<ul>';
					jQuery.each(item,function(index1,item1){
						html+='<li><a href="javascript:void(0)" data-zip-state="'+index1+'" class="zip_state">'+index1+'</a></li>';
					});
					html+='</ul>';
				});
				jQuery('.zip_code_content .modal-body p').html(html);
				jQuery('.zip_code_content .modal-header h2').html("Please choose the recipient’s city to get started");
				jQuery('#myModal').show();
			}
		});
	})
	jQuery(document).on('click','.modal1 .close',function(){
		jQuery('#myModal').hide();
	})
	jQuery(document).on('click','.zip_state',function(){
		var state = jQuery(this).attr('data-zip-state');
		jQuery.ajax({
			url:'/wp-admin/admin-ajax.php',
			type:'POST',
			data:{
				action:'read_xml',
			},
			success:function(response){
				response = JSON.parse(response);
				var html='';
				var html1='<ul class="second_ul">';
				jQuery.each(response,function(index,item){
					jQuery.each(item,function(index1,item1){
						if(index1==state){
							jQuery.each(item1,function(index2,item2){
								html1+="<li><ul><div class='first_heading'>"+index2+"</div>";
								jQuery.each(item2,function(index3,item3){								
									html1+='<li><a href="javascript:void(0)" data-zip-district="'+item3.district+'" class="zip_dist" data-zip-zip="'+item3.zip_code+'" data-zip-price="'+item3.price+'">'+item3.district+'</a></li>';
								});
								html1+="</ul></li>";
							});
						}
					});
				});
				html1+='</ul>';
				jQuery('.zip_code_content .modal-header h2').html('Cites and Towns in '+state+"<br><a href='javascript:void(0);' class='click_go_back'>Go Back</a><br><input type='' placeholder='Know the city? Start typing it here...' class='search_city'>");
				jQuery('.zip_code_content .modal-body p').html(html+html1);
				jQuery('#myModal').show();
			}
		});
	});

	jQuery(document).on('keyup','.search_city',function(){
		var val_sel = jQuery(this).val();
		var count = 0;
		jQuery('.zip_dist').each(function(index,val){
			if (jQuery(this).text().search(new RegExp(val_sel, "i")) < 0) {
        jQuery(this).parent().hide();  // MY CHANGE
        if(jQuery(this).parent().parent().find('li:visible').length==0){
        	jQuery(this).parent().parent().parent().hide();
        }
      } else {
        jQuery(this).parent().show(); // MY CHANGE
       	jQuery(this).parent().parent().parent().show();
        count++;
      }
		})
	});

	jQuery(document).on('click','.zip_dist',function(){
		var zip = jQuery(this).attr('data-zip-zip');
		var price = jQuery(this).attr('data-zip-price');
		jQuery('.zip_code_lookup').val(zip);
		jQuery('.zip_code_lookup').attr('data-zip-price',price);
		jQuery('.product_custom_field .second_sec input:nth-child(10)').val(price);
		jQuery('#myModal').hide();
	});

	jQuery(document).on('click','.btn_delivery',function(){
		var zip_val = jQuery('.zip_code_lookup').val();
		var zip_price = '';
		jQuery('.error_val').remove();
		if(zip_val==''){
			jQuery('.product_custom_field label').after("<p class='error_val'>Please enter recipient's zip code for delivery information.</p>");
			return false;
		}
		if(isNaN(Math.floor(zip_val)) && jQuery.isNumeric(zip_val)==false){
			jQuery('.product_custom_field label').after("<p class='error_val'>Please enter a valid zip code for delivery information.</p>");
			return false;
		}

		jQuery.ajax({
			url:'/wp-admin/admin-ajax.php',
			type:'POST',
			data:{
				action:'check_zip',
				zip_code:zip_val,
			},
			success:function(response){
				var response = JSON.parse(response);
				if(response.message=='error'){
					jQuery('.product_custom_field label').after("<p class='error_val'>Please enter a valid CYPRUS zip code or this product is not available for delivery to "+zip_val+"</p>");
				}else{
					zip_price = response.data[zip_val];
				}
				jQuery('.zip_code_lookup').attr('data-zip-price',zip_price);
				var today = new Date();
				var year = today.getFullYear();
				var month = today.getMonth();
				var date = today.getDate();
				var date_html='<ul>';
				const allmonth = new Array('JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
				const weekday = new Array('SUN','MON','TUE','WED','THU','FRI','SAT');
				const allmonth_full = new Array('January','February','March','April','MAY','June','July','August','September','October','November','December');
				const weekday_full = new Array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
				for(var i=0; i<30; i++){
					var day = new Date(year, month, date + i);
					var add_price ='';
					var disabled='';
					var price_at = '';
					for(var key in response.data['price']){
						if(key==(weekday[day.getDay()]+" "+allmonth[day.getMonth()]+" "+day.getDate())){
							if(response.data['price'][key].price!=0){
								add_price='<div class="price_addition">+'+response.data['price'][key].price+'</div>';
								price_at = response.data['price'][key].price;
							}

							if(response.data['price'][key].disabled=='Y'){
								disabled = 'disabled';
							}
						}
					}
					date_html+="<li><button value='"+day.getFullYear()+"-"+(day.getMonth()+1<10?'0'+(day.getMonth()+1):day.getMonth()+1)+"-"+(day.getDate()<10?'0'+day.getDate():day.getDate())+"' data-label='"+weekday_full[day.getDay()]+","+allmonth_full[day.getMonth()]+" "+day.getDate()+"' "+disabled+" data-price='"+price_at+"'><div class='label'><span>"+weekday[day.getDay()]+" "+allmonth[day.getMonth()]+" "+day.getDate()+"</span></div>"+add_price+"</button></li>";
				}
				date_html+='</ul>';
				jQuery('.zip_code_content .modal-header h2').html("Choose a Delivery Date");
				if(!jQuery('.zip_code_content .modal-header').hasClass('added')){
					jQuery('.zip_code_content .modal-header h2').after("<br>Recipient's Zip Code: "+zip_val+"<br>Wrong zip code? <a href='javascript:void(0);' class='click_go_back'>Click here to type a new one.</a>");
					jQuery('.zip_code_content .modal-header').addClass('added');
				}
				var date_html_new='';
				for(var i=0; i<45; i++){
					var day = new Date(year, month+1, date + i);
					var add_price ='';
					var hide = false;
					var price_at='';
					for(var key in response.data['price']){
						if(key==(weekday[day.getDay()]+" "+allmonth[day.getMonth()]+" "+day.getDate())){
							add_price='+'+response.data['price'][key];
							price_at=response.data['price'][key];
						}
						if(response.data['price'][key].disabled=='Y'){
							hide = true;
						}
					}
					if(hide==false){
						date_html_new+="<option value='"+day.getFullYear()+"-"+(day.getMonth()+1<10?'0'+(day.getMonth()+1):day.getMonth()+1)+"-"+(day.getDate()<10?'0'+day.getDate():day.getDate())+"' data-label='"+weekday_full[day.getDay()]+","+allmonth_full[day.getMonth()]+" "+day.getDate()+"' data-price='"+price_at+"'>"+weekday[day.getDay()]+" "+allmonth[day.getMonth()]+" "+day.getDate()+" "+add_price+"</option>";
					}
				}
				var future_date = "<select class='date_sel'><option value=''></option>"+date_html_new+"</select>";
				jQuery('.zip_code_content .modal-body p').html(date_html+'<br>Please select an available delivery date.<br>Looking for a future delivery date?'+future_date);
				jQuery('#myModal').show();
			}
		});
	});

	jQuery(document).on('change','.date_sel',function(){
		var val = jQuery(this).val();
		var zip = jQuery('.zip_code_lookup').val();
		var val_label = jQuery('.date_sel').find(':selected').attr('data-label');
		var val_price = jQuery('.date_sel').find(':selected').attr('data-price');
		selected_date(zip,val,val_label,val_price);
	})

	jQuery(document).on('click','.zip_code_content button',function(){
		var val = jQuery(this).val();
		var zip = jQuery('.zip_code_lookup').val();
		var val_label = jQuery(this).attr('data-label');
		var val_price = jQuery(this).attr('data-price');
		selected_date(zip,val,val_label,val_price);
	})

	jQuery(document).on('click','.second_sec a:nth-child(4)',function(){
		jQuery('.product_custom_field .second_sec').hide();
		jQuery('.product_custom_field .first_sec').show();
	})

	jQuery(document).on('click','.second_sec a:nth-child(7)',function(){
		jQuery('.btn_delivery').trigger('click');
	})

});

function selected_date(zip,date,label,price){
	jQuery('.product_custom_field .first_sec').hide();
	jQuery('.product_custom_field .second_sec input:nth-child(2)').val(zip);
	jQuery('.product_custom_field .second_sec input:nth-child(6)').val(label);
	jQuery('.product_custom_field .second_sec input:nth-child(6)').attr('data-val',date);
	jQuery('.product_custom_field .second_sec input:nth-child(8)').val(price);
	jQuery('.product_custom_field .second_sec input:nth-child(9)').val(date);
	jQuery('.product_custom_field .second_sec').show();
	jQuery('#myModal').hide();
}
(function($){

$(document).ready(function(e) {
	alert('h');
    alert($('.woocommerce-product-gallery').offset().top);

});

})(jQuery);

/*jQuery(window).on("load", function(){

	if(jQuery(".single-product .product-details-wrapper .wcpa_form_outer .wcpa_row:nth-child(4) input[type='checkbox']").prop("checked") == false) {

		jQuery(".single-product .product-details-wrapper .wcpa_form_outer .wcpa_row:nth-child(7)").addClass('active');

	}

	else if(jQuery(".single-product .product-details-wrapper .wcpa_form_outer .wcpa_row:nth-child(4) input[type='checkbox']").prop("checked") == true) {

		jQuery(".single-product .product-details-wrapper .wcpa_form_outer .wcpa_row:nth-child(7)").removeClass('active');

	}

});*/


</script>

</body>

</html>