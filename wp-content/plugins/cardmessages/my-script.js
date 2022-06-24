jQuery(document).ready(function(){
    jQuery('.woocommerce-shipping-destination').hide();
    jQuery('.woocommerce-shipping-calculator').hide();

});

jQuery(document).on('click','.zip_state',function(){
    var selectedState = jQuery(this).attr('data-zip-state');
    jQuery('.selectedState').val(selectedState);
});
jQuery(document).on('click','.zip_dist',function(){
    var selectedCity = jQuery(this).attr('data-zip-district');
    jQuery('.selectedCity').val(selectedCity);
});

jQuery(document).on('change','.select_occasion_message',function(){
    var element = jQuery(this).find('option:selected'); 
    //console.log(element.val());
    if(element.val()){
        var id = element.attr("message");
        //if(val == 1){
            jQuery('.select_more_option').show();
            jQuery.ajax({
                type : "POST",
                dataType : "json",
                url : my_ajax_object.ajax_url,
                data : {'action': "showMessages",nonce: my_ajax_object.nonce,data: {
                    id: id,
                }},
                success: function(response) {
                    jQuery('.select_message option').remove();
                    jQuery('.message_card_text').val('');
                    jQuery('.select_message').append(response.data);
                }
            });
            
        // }else{
            
        //     jQuery('.more-options').find('.select_more_option').hide();
        // }
    }else{
        //jQuery('.more-options').find('.select_more_option').hide();
    }
    
});

jQuery(document).on('change','.option_for_y_n',function(){
    var val = jQuery('.option_for_y_n:checked').val();
    var element = jQuery('.select_occasion_message').find('option:selected'); 
	var id = element.attr("message");
    //console.log(val);
    if(val == 1){
        jQuery('.message_card_text').prop('required',true);
        jQuery('.select_occasion_message').prop('required',true);
        jQuery('.select_more_option').show();
        jQuery('.showNow').show();
        jQuery.ajax({
            type : "POST",
            dataType : "json",
            url : my_ajax_object.ajax_url,
            data : {'action': "showMessages",nonce: my_ajax_object.nonce,data: {
                id: id,
            }},
            success: function(response) {
                jQuery('.select_message option').remove();
                jQuery('.message_card_text').val('');
                jQuery('.select_message').append(response.data);
            }
        });
        
    }else{
        jQuery('.showNow').hide();
        jQuery('.select_message option').remove();
        jQuery('.message_card_text').val('');
        jQuery('.message_card_text').prop('required',false);
        jQuery('.select_occasion_message').prop('required',false);
        jQuery('.more-options').find('.select_more_option').hide();
    }
})

jQuery(document).on('change','.select_message',function(){
    var element = jQuery('.select_message').find('option:selected').val();
    jQuery('.message_card_text').val(''); 
    jQuery('.message_card_text').val(element); 
});

jQuery(document).on("input keyup keypress blur change",'.message_card_text', function() {
    var maxlength = jQuery(this).attr("maxlength");
    var currentLength = jQuery(this).val().length;
    var texts = jQuery('.message_card_text').val();
    var number = 150;
    
    if(/^[a-zA-Z0-9- ]*$/.test(texts) == true) {
        jQuery('.error').text("");
        if (currentLength >= maxlength) {
            jQuery('.counter').text("You have reached the maximum number of characters.");
        }
        var number = parseInt(maxlength) - parseInt(currentLength);
        jQuery('.counter').text( parseInt(number) + " Characters left");
    }else{
        //jQuery(this).val(jQuery(this).val().replace(/[^a-z0-9]/gi, ''));
        jQuery('.error').text(" Invalid Character!");
    }
  });
jQuery('.sym_message_card_text').hide();

jQuery(document).on('change','.select_occasion_message',function(){
   // debugger;
    var element = jQuery('.select_occasion_message').find('option:selected').val();
    jQuery('.message_card_text').val(''); 
   jQuery('.message_card_text').val(element); 
        if(element=="Sympathy"){
          jQuery('.sym_message_card_text').show();
         jQuery('.sym_message_card_text').prop('required',true);

        } else{
          jQuery('.sym_message_card_text').hide();
          jQuery('.sym_message_card_text').prop('required',false);

        }
});
