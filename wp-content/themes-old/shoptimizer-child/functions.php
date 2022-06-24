<?php
require_once "sss/SocialLogin.php";

/*This file is part of shoptimizer-child, shoptimizer child theme.

All functions of this file will be loaded before of parent theme functions.
Learn more at https://codex.wordpress.org/Child_Themes.

Note: this function loads the parent stylesheet before, then child theme stylesheet
(leave it in place unless you know what you are doing.)
*/

if ( ! function_exists( 'suffice_child_enqueue_child_styles' ) ) {
    function shoptimizer_child_enqueue_child_styles() {
        // loading parent style
        wp_register_style(
          'parente2-style',
          get_template_directory_uri() . '/style.css'
        );

        wp_enqueue_style( 'parente2-style' );
        wp_enqueue_script( 'my-custom-script', get_stylesheet_directory_uri() . '/custom.js', array( 'jquery'), '', true);
        // loading child style
        // wp_register_style(
        //   'childe2-style',
        //   get_stylesheet_directory_uri() . '/style.css'
        // );
        // wp_enqueue_style( 'childe2-style');
     }
}
add_action( 'wp_enqueue_scripts', 'shoptimizer_child_enqueue_child_styles' );
require_once 'woo-product-filter/config.php';
require_once 'woo-product-filter/functions.php';
require_once 'woo-product-filter/woo-product-filter.php';

// **********************************************************************// 
// ! Product brand label
// **********************************************************************//

add_action( 'admin_enqueue_scripts', 'et_brand_admin_scripts' );
if(!function_exists('et_brand_admin_scripts')) {
    function et_brand_admin_scripts() {
        $screen = get_current_screen();
        if ( in_array( $screen->id, array('edit-brand') ) )
          wp_enqueue_media();
    }
}
if(!function_exists('et_product_brand_image')) {
    function et_product_brand_image() {
        global $post, $wpdb, $product;
        $terms = wp_get_post_terms( $post->ID, 'brand' );

        if(count($terms)>0) { ?>
            <div class="sidebar-widget product-brands">
                <h4 class="widget-title"><span><?php _e('Product brand', '') ?></span></h4>
                <?php
                    foreach($terms as $brand) {
                        $image          = '';
                        $thumbnail_id   = absint( get_woocommerce_term_meta( $brand->term_id, 'thumbnail_id', true ) ); ?>
                        <a href="<?php echo get_term_link($brand); ?>">
                            <?php if ($thumbnail_id) :
                            	$image = etheme_get_image( $thumbnail_id ); ?>
                                    <?php if($image != ''): ?>
                                        <img src="<?php echo $image; ?>" title="<?php echo $brand->name; ?>" alt="<?php echo $brand->name; ?>" class="brand-image" />
                                    <?php else: ?>
                                        <?php echo $brand->name; ?>
                                    <?php endif; ?>
                            <?php else :
                            	echo $brand->name;
                            endif; ?>
                        </a>
                <?php } ?>
            </div>
        <?php } 
    } 
}

add_action( 'init', 'et_create_brand_taxonomies', 0 );
if(!function_exists('et_create_brand_taxonomies')) {
    function et_create_brand_taxonomies() {
        $labels = array(
            'name'              => _x( 'Brands', '' ),
            'singular_name'     => _x( 'Brand', '' ),
            'search_items'      => __( 'Search Brands', '' ),
            'all_items'         => __( 'All Brands', '' ),
            'parent_item'       => __( 'Parent Brand', '' ),
            'parent_item_colon' => __( 'Parent Brand:', '' ),
            'edit_item'         => __( 'Edit Brand', '' ),
            'update_item'       => __( 'Update Brand', '' ),
            'add_new_item'      => __( 'Add New Brand', '' ),
            'new_item_name'     => __( 'New Brand Name', '' ),
            'menu_name'         => __( 'Brands', '' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'capabilities'          => array(
                'manage_terms'      => 'manage_product_terms',
                'edit_terms'        => 'edit_product_terms',
                'delete_terms'      => 'delete_product_terms',
                'assign_terms'      => 'assign_product_terms',
            ),
            'rewrite'           => array( 'slug' => 'brand' ),
        );

        register_taxonomy( 'brand', array( 'product' ), $args );
    }
}

add_action( 'brand_add_form_fields', 'et_brand_fileds' );
if(!function_exists('et_brand_fileds')) {
    function et_brand_fileds() {
        global $woocommerce;
        ?>
        <div class="form-field">
            <label for="term_fields[short_description]"><?php _e('Short description'); ?></label>
            <textarea cols="40" rows="5" id="term_fields[short_description]" name="term_fields[short_description]"></textarea>
            <p class="description"><?php _e('Please enter short description'); ?></p>
        </div>
        <div class="form-field">
            <label><?php _e( 'Thumbnail', 'woocommerce' ); ?></label>
            <div id="brand_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo woocommerce_placeholder_img_src(); ?>" width="60px" height="60px" /></div>
            <div style="line-height:60px;">
                <input type="hidden" id="brand_thumbnail_id" name="brand_thumbnail_id" />
                <button type="submit" class="upload_image_button button"><?php _e( 'Upload/Add image', 'woocommerce' ); ?></button>
                <button type="submit" class="remove_image_button button"><?php _e( 'Remove image', 'woocommerce' ); ?></button>
            </div>
            <script type="text/javascript">
            	// Only show the "remove image" button when needed
            	if ( ! jQuery('#brand_thumbnail_id').val() )
            		jQuery('.remove_image_button').hide();
            	// Uploading files
                var file_frame;

                jQuery(document).on( 'click', '.upload_image_button', function( event ){
                	event.preventDefault();
                	// If the media frame already exists, reopen it.
                    if ( file_frame ) {
                        file_frame.open();
                        return;
                    }
                    // Create the media frame.
                    file_frame = wp.media.frames.downloadable_file = wp.media({
                        title: '<?php _e( 'Choose an image', 'woocommerce' ); ?>',
                        button: {
                            text: '<?php _e( 'Use image', 'woocommerce' ); ?>',
                        },
                        multiple: false
                    });
                    // When an image is selected, run a callback.
                    file_frame.on( 'select', function() {
                        attachment = file_frame.state().get('selection').first().toJSON();

                        jQuery('#brand_thumbnail_id').val( attachment.id );
                        jQuery('#brand_thumbnail img').attr('src', attachment.url );
                        jQuery('.remove_image_button').show();
                    });
                    // Finally, open the modal.
                    file_frame.open();
                });

                jQuery(document).on( 'click', '.remove_image_button', function( event ){
                    jQuery('#brand_thumbnail img').attr('src', '<?php echo woocommerce_placeholder_img_src(); ?>');
                    jQuery('#brand_thumbnail_id').val('');
                    jQuery('.remove_image_button').hide();
                    return false;
                });

            </script>
            <div class="clear"></div>
        </div>
        <?php
    }
}


add_action( 'brand_edit_form_fields', 'et_edit_brand_fields', 10,2 );
if(!function_exists('et_edit_brand_fields')) {
    function et_edit_brand_fields( $term, $taxonomy ) {
        global $woocommerce;
        $image          = '';
        $thumbnail_id   = absint( get_woocommerce_term_meta( $term->term_id, 'thumbnail_id', true ) );
        if ($thumbnail_id) :
            $image = wp_get_attachment_thumb_url( $thumbnail_id );
        else :
            $image = woocommerce_placeholder_img_src();
        endif;

        if (current_filter() == 'brand_edit_form_fields') {
        $short_description = get_term_meta($term->term_id, 'short_description', true);
        ?>
        <tr class="form-field">
            <th valign="top" scope="row"><label for="term_fields[short_description]"><?php _e('Short description'); ?></label></th>
            <td>
                <?php     
                $settings = array(
                    'wpautop' => true, 
                    'media_buttons' => true, 
                    'quicktags' => true, 
                    'textarea_rows' => '15', 
                    'textarea_name' => 'term_fields[short_description]' );
                wp_editor($short_description, 'short_description', $settings); 
                ?>
                <br />
       <!-- <textarea class="large-text" cols="50" rows="5" id="term_fields[short_description]" name="term_fields[short_description]"><?php //echo esc_textarea($short_description); ?></textarea><br/>  -->
                <span class="description"><?php _e('Please enter short description'); ?></span>
            </td>
        </tr>
    <?php }?>
        <tr class="form-field">
            <th scope="row" valign="top"><label><?php _e( 'Thumbnail', 'woocommerce' ); ?></label></th>
            <td>
                <div id="brand_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo $image; ?>" width="60px" height="60px" /></div>
                <div style="line-height:60px;">
                    <input type="hidden" id="brand_thumbnail_id" name="brand_thumbnail_id" value="<?php echo $thumbnail_id; ?>" />
                    <button type="submit" class="upload_image_button button"><?php _e( 'Upload/Add image', 'woocommerce' ); ?></button>
                    <button type="submit" class="remove_image_button button"><?php _e( 'Remove image', 'woocommerce' ); ?></button>
                </div>
                <script type="text/javascript">
    
                    // Uploading files
                    var file_frame;
    
                    jQuery(document).on( 'click', '.upload_image_button', function( event ){
    
                        event.preventDefault();
    
                        // If the media frame already exists, reopen it.
                        if ( file_frame ) {
                            file_frame.open();
                            return;
                        }
    
                        // Create the media frame.
                        file_frame = wp.media.frames.downloadable_file = wp.media({
                            title: '<?php _e( 'Choose an image', 'woocommerce' ); ?>',
                            button: {
                                text: '<?php _e( 'Use image', 'woocommerce' ); ?>',
                            },
                            multiple: false
                        });
    
                        // When an image is selected, run a callback.
                        file_frame.on( 'select', function() {
                            attachment = file_frame.state().get('selection').first().toJSON();
    
                            jQuery('#brand_thumbnail_id').val( attachment.id );
                            jQuery('#brand_thumbnail img').attr('src', attachment.url );
                            jQuery('.remove_image_button').show();
                        });
    
                        // Finally, open the modal.
                        file_frame.open();
                    });
    
                    jQuery(document).on( 'click', '.remove_image_button', function( event ){
                        jQuery('#brand_thumbnail img').attr('src', '<?php echo woocommerce_placeholder_img_src(); ?>');
                        jQuery('#brand_thumbnail_id').val('');
                        jQuery('.remove_image_button').hide();
                        return false;
                    });
    
                </script>
                <div class="clear"></div>
            </td>
        </tr>
        <?php
    }
}

if(!function_exists('et_brands_fields_save')) {
    function et_brands_fields_save( $term_id, $tt_id, $taxonomy ) {
        
        if ( isset( $_POST['brand_thumbnail_id'] ) )
            update_woocommerce_term_meta( $term_id, 'thumbnail_id', absint( $_POST['brand_thumbnail_id'] ) );
    
        delete_transient( 'wc_term_counts' );

         if (!isset($_POST['term_fields'])) {
                return;
            }

            foreach ($_POST['term_fields'] as $key => $value) {
                update_term_meta($term_id, $key, $value);
            }
    }
}

add_action( 'created_term', 'et_brands_fields_save', 10,3 );
add_action( 'edit_term', 'et_brands_fields_save', 10,3 );

// zoom remove
function remove_image_zoom_support() {
    remove_theme_support( 'wc-product-gallery-zoom' );
    remove_theme_support( 'wc-product-gallery-lightbox' );
}
add_action( 'wp', 'remove_image_zoom_support', 100 );
function custom_single_product_image_html( $html, $post_id ) {
    $post_thumbnail_id = get_post_thumbnail_id( $post_id );
    return get_the_post_thumbnail( $post_thumbnail_id, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ) );
}
add_filter('woocommerce_single_product_image_thumbnail_html', 'custom_single_product_image_html', 10, 2);

// recapture

add_action('lostpassword_form','add_captcha_to_login'); // Login Form Hook
add_action('woocommerce_register_form','add_captcha_to_login'); // Login Form Hook
add_action('login_form','add_captcha_to_login'); // Login Form Hook
add_action('woocommerce_login_form','add_captcha_to_login'); // Login Form Hook
add_action('woocommerce_lostpassword_form','add_captcha_to_login'); // Login Form Hook
add_action('register_form','add_captcha_to_login'); // Login Form Hook
add_filter('comment_form_defaults', 'add_captcha_to_login');
//add_action('comment_form', 'add_captcha_to_login', 999); // Check Login Hook
add_action( 'comment_form_logged_in_after', 'add_captcha_to_login' );
//add_action( 'comment_form_after_fields', 'add_captcha_to_login' );
function add_captcha_to_login(){
   ?>
   <script src="https://www.google.com/recaptcha/api.js" async defer></script>
   <p>
 <div class="g-recaptcha" data-sitekey="6Le6rDAbAAAAAA3M1JfQns6nL3-uTxwKuXTSBEt7" style="clear:both" required /></div>
   </p>
   <?php
}

/// email verify ....

function wc_registration_redirect( $redirect_to ) {     // prevents the user from logging in automatically after registering their account
    wp_logout();
    wp_redirect( '/my-account/?n=');                        // redirects to a confirmation message
    exit;
}

function wp_authenticate_user( $userdata ) {            // when the user logs in, checks whether their email is verified
    $has_activation_status = get_user_meta($userdata->ID, 'is_activated', false);
    if ($has_activation_status) {                           // checks if this is an older account without activation status; skips the rest of the function if it is
        $isActivated = get_user_meta($userdata->ID, 'is_activated', true);
        if ( !$isActivated ) {
            my_user_register( $userdata->ID );              // resends the activation mail if the account is not activated
            $userdata = new WP_Error(
                'my_theme_confirmation_error',
                __( '<strong>Error:</strong> Your account has to be activated before you can login. Please click the link in the activation email that has been sent to you.<br /> If you do not receive the activation email within a few minutes, check your spam folder or <a href="/verify/?u='.$userdata->ID.'">click here to resend it</a>.' )
            );
        }
    }
    return $userdata;
}

function my_user_register($user_id) {               // when a user registers, sends them an email to verify their account
    $user_info = get_userdata($user_id);                                            // gets user data
    $code = md5(time());                                                            // creates md5 code to verify later
    $string = array('id'=>$user_id, 'code'=>$code);                                 // makes it into a code to send it to user via email
    update_user_meta($user_id, 'is_activated', 0);                                  // creates activation code and activation status in the database
    update_user_meta($user_id, 'activationcode', $code);
    $url = get_site_url(). '/my-account/?p=' .base64_encode( serialize($string));       // creates the activation url
    $html = ( 'Please click <a href="'.$url.'">here</a> to verify your email address and complete the registration process.' ); // This is the html template for your email message body
    wc_mail($user_info->user_email, __( 'Activate your Account' ), $html);          // sends the email to the user
}

function my_init(){                                 // handles all this verification stuff
    if(isset($_GET['p'])){                                                  // If accessed via an authentification link
        $data = unserialize(base64_decode($_GET['p']));
        $code = get_user_meta($data['id'], 'activationcode', true);
        $isActivated = get_user_meta($data['id'], 'is_activated', true);    // checks if the account has already been activated. We're doing this to prevent someone from logging in with an outdated confirmation link
        if( $isActivated ) {                                                // generates an error message if the account was already active
            wc_add_notice( __( 'This account has already been activated. Please log in with your username and password.' ), 'error' );
        }
        else {
            if($code == $data['code']){                                     // checks whether the decoded code given is the same as the one in the data base
                update_user_meta($data['id'], 'is_activated', 1);           // updates the database upon successful activation
                $user_id = $data['id'];                                     // logs the user in
                $user = get_user_by( 'id', $user_id ); 
                if( $user ) {
                    wp_set_current_user( $user_id, $user->user_login );
                    wp_set_auth_cookie( $user_id );
                    do_action( 'wp_login', $user->user_login, $user );
                }
                wc_add_notice( __( '<strong>Success:</strong> Your account has been activated! You have been logged in and can now use the site to its full extent.' ), 'notice' );
            } else {
                wc_add_notice( __( '<strong>Error:</strong> Account activation failed. Please try again in a few minutes or <a href="/verify/?u='.$userdata->ID.'">resend the activation email</a>.<br />Please note that any activation links previously sent lose their validity as soon as a new activation email gets sent.<br />If the verification fails repeatedly, please contact our administrator.' ), 'error' );
            }
        }
    }
    if(isset($_GET['u'])){                                          // If resending confirmation mail
        my_user_register($_GET['u']);
        wc_add_notice( __( 'Your activation email has been resent. Please check your email and your spam folder.' ), 'notice' );
    }
    if(isset($_GET['n'])){                                          // If account has been freshly created
        wc_add_notice( __( 'Thank you for creating your account. You will need to confirm your email address in order to activate your account. An email containing the activation link has been sent to your email address. If the email does not arrive within a few minutes, check your spam folder.' ), 'notice' );
    }
}

// the hooks to make it all work
add_action( 'init', 'my_init' );
add_filter('woocommerce_registration_redirect', 'wc_registration_redirect');
add_filter('wp_authenticate_user', 'wp_authenticate_user',10,2);
add_action('user_register', 'my_user_register',10,2);

function getGMT($timestamp)
{
    $last_day_of_march = gmmktime(1, 0, 0, 3, 31, gmdate('Y', $timestamp));
    $last_sunday_of_march = $last_day_of_march - (gmdate('w', $last_day_of_march) * 86400);
    $last_day_of_october = gmmktime(1, 0, 0, 10, 31, gmdate('Y', $timestamp));
    $last_sunday_of_october = $last_day_of_october - (gmdate('w', $last_day_of_october) * 86400);
    if ($timestamp > $last_sunday_of_march && $timestamp < $last_sunday_of_october) {
        // Its DST For GMT So Add 1 Hour
        $timestamp += 3600;
    }
    return $timestamp;
}

//add_filter( 'woocommerce_cart_ready_to_calc_shipping', 'disable_shipping_calc_on_cart', 99 );
function delivery_message()
{
    if ((gmdate('H') + 2) < 18) {
        echo (gmdate('H') + 2) . gmdate(' : i : s', getGMT(time()));
    }
    exit();
}

add_action('wp_ajax_cyprustime', 'cyprustime');
add_action('wp_ajax_nopriv_cyprustime', 'cyprustime');
function cyprustime()
{
    ?>
    <script>
        var countDownDate = new Date("<?php echo gmdate('M d, Y');?> 14:00:00").getTime();
        // Update the count down every 1 second
        var x = new Date("<?php echo gmdate('M d, Y H:i:s');?>");
        var x1 = x.toUTCString();// changing the display to UTC string
        var gmt = new Date("<?php echo gmdate('M d, Y H:i:s');?>");
        var offSet = x.getTimezoneOffset();
        gmt.setMinutes(x.getMinutes() + offSet);
        var now = new Date("<?php echo gmdate('M d, Y h:i:s', getGMT(time()));?>").getTime()
        // Find the distance between now an the count down date
        var distance = countDownDate - now;
        // Time calculations for days, hours, minutes and seconds
        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var currentHours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var currentMinutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var currentSeconds = Math.floor((distance % (1000 * 60)) / 1000);

        if (currentHours > 0) {
            var currentTimeString = currentHours - 2 + " hrs " + currentMinutes + " mins ";
        }
        jQuery("#myclock").html(currentTimeString);
    </script>
    <?php

}
function timecounter() {
    $timestamp = time();
    //echo gmdate('H :i: s', getGMT(time()))+2."<br>".gmdate('H:i:s');
    if ((gmdate('H', getGMT(time())) + 2) < 14) {
        ?>
        Want it By <?php echo gmdate('l, M d'); ?>? Please order within <span id="myclock" style="color:#33B56C"></span>.


        <?php
    } else {
        echo "<p style='color:red; font-weight:600'>Products can't be delivered after 2:00 pm local time. Please call the store or select the following date.</p>";
    }
    ?>
     <div id="myclockdiv">
        <script type="text/javascript">
            updateClock();
            setInterval(updateClock, 60000)
            function updateClock() {
                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {action: 'cyprustime'},
                    success: function (data) {
                        jQuery("#myclock").html(data);
                    },
                });
            }
        </script>
    </div>
    <?php
}

add_shortcode( 'timecounternew', 'timecounter' );

/*************************** add custom fields on checkout page start **********************/

add_filter('woocommerce_checkout_fields', 'custom_override_checkout_fields', 100);

function custom_override_checkout_fields($fields) {
    
    $fields['shipping']['shipping_gift_title'] = array(
        'type' => 'select',
        'label' => __('Title', 'woocommerce'),
        'placeholder' => _x('', 'placeholder', 'woocommerce'),
        'required' => false,
        'class' => array('form-row', 'form-row-first5', 'full-width-select-wrap'),
        'clear' => true
    );
    $fields['shipping']['shipping_gift_title']['options'] = array(
        '' => 'Select one',
        'Mr.' => 'Mr.',
        'Mrs.' => 'Mrs.',
        'Ms.' => 'Ms.',
        'Dr.' => 'Dr.',
    );
    $fields['shipping']['shipping_phone'] = array(
        'label' => __('Recipient Telephone', 'woocommerce'),
        'placeholder' => _x('', 'placeholder', 'woocommerce'),
        'required' => true,
        'class' => array('form-row', 'form-row-first5',),
        'clear' => false
    );
    $fields['shipping']['shipping_address_1'] = array(
        'label' => __('Address Line 1', 'woocommerce'),
        'placeholder' => _x('House number and street name', 'placeholder', 'woocommerce'),
        'required' => true,
        'class' => array('form-row'),
        'clear' => true
    );
    $fields['shipping']['shipping_address_2'] = array(
        'label' => __('Address Line 2', 'woocommerce'),
        'placeholder' => _x('Apartment, suite, unit etc. (optional)', 'placeholder', 'woocommerce'),
        'required' => false,
        'class' => array('form-row'),
        'clear' => true,
        'id' => ('route')
    );
    $fields['shipping']['shipping_location_type'] = array(
        'type' => 'select',
        'label' => __('Location Type', 'woocommerce'),
        'placeholder' => _x('', 'placeholder', 'woocommerce'),
        'required' => false,
        'class' => array('form-row', 'form-row-first5'),
        'clear' => false
    );
    $fields['shipping']['shipping_location_type']['options'] = array(
        '' => 'Select location type',
        'Residence' => 'Residence',
        'Apartment' => 'Apartment',
        'Business' => 'Business',
        'Church' => 'Church',
        'School' => 'School',
        'Hospital' => 'Hospital',
    );

    $fields['billing']['billing_address_1'] = array(
        'label' => __('Address Line 1', 'woocommerce'),
        'placeholder' => _x('House number and street name', 'placeholder', 'woocommerce'),
        'required' => true,
        'class' => array('form-row'),
        'clear' => true,
        'priority'=>60,
    );
    $fields['billing']['billing_address_2'] = array(
        'label' => __('Address Line 2', 'woocommerce'),
        'placeholder' => _x('Apartment, suite, unit etc. (optional)', 'placeholder', 'woocommerce'),
        'required' => false,
        'class' => array('form-row'),
        'clear' => true,
        'id' => ('route')
    );

    $shipping_filds = $fields['shipping'];
    unset($fields['shipping']);
    $fields['shipping']['shipping_gift_title'] = $shipping_filds['shipping_gift_title'];
    $fields['shipping']['shipping_first_name'] = $shipping_filds['shipping_first_name'];
    $fields['shipping']['shipping_last_name'] = $shipping_filds['shipping_last_name'];
    $fields['shipping']['ship_location_type'] = $shipping_filds['shipping_location_type'];
    $fields['shipping']['shipping_address_1'] = $shipping_filds['shipping_address_1'];
    $fields['shipping']['shipping_address_2'] = $shipping_filds['shipping_address_2'];
    $fields['shipping']['shipping_city'] = $shipping_filds['shipping_city'];
    $fields['shipping']['shipping_state'] = $shipping_filds['shipping_state'];
    $fields['shipping']['shipping_postcode'] = $shipping_filds['shipping_postcode'];
    $fields['shipping']['shipping_country'] = $shipping_filds['shipping_country'];
    $fields['shipping']['ship_phone'] = $shipping_filds['shipping_phone'];
    return $fields;
}

/*************************** add custom fields on checkout page end **********************/


/*************************** change the label on checkout page of address fields start **********************/
function myscript() { ?>
	<script type="text/javascript">
	 jQuery(document).ready(function(){
	 	setTimeout(function(){
	 		jQuery('#billing_address_1_field label').html('Address Line 1&nbsp;<abbr class="required" title="required">*</abbr></label>');
		 	jQuery('#shipping_address_1_field label').html('Address Line 1&nbsp;<abbr class="required" title="required">*</abbr></label>');
            jQuery('#shipping_city_field label').html('City/Town&nbsp;<abbr class="required" title="required">*</abbr> <span class="instruction">(We don’t deliver in North Cyprus.)</span></label>');
            jQuery('#shipping_postcode_field label').html('Postcode  / ZIP&nbsp;<abbr class="required" title="required">*</abbr> <span class="instruction">(Enter 0000 if unknown.)</span></label>');
            jQuery('#ship_phone_field label ').html('Recipient Telephone&nbsp;<abbr class="required" title="required">*</abbr> <button type="button"  id= "tooltip" class="btn btn-secondary btn-circle tooltip" data-toggle="tooltip" data-placement="top" title="Tooltip on top">?<span class="tooltiptext"><div class="tooltip-title">Info</div><p>If this is not a local Cyprus number please include the international country code. Every order is verified with the recipient prior to delivery. If we encounter any issues getting your gift to your recipient (for example, if they are not home at the time of delivery), we will call them to let them know we are doing everything we can to deliver them your gift.</p></span></button>');
	 	},1000);
	 })
    jQuery(".archive-content-more").click(function(){
		jQuery(".archive-content").toggleClass('expand');
	});
	</script>
<?php }

add_action('wp_footer', 'myscript');

/*************************** change the label on checkout page of address fields end **********************/

/*************************** show custom field data on thankyou page start **********************/

add_action( 'woocommerce_order_details_after_order_table', 'custom_field_display_cust_order_meta', 10, 1 );

function custom_field_display_cust_order_meta($order){
	echo '<p>' . $order->get_meta('_shipping_location_type' ). '</p>';
	echo '<p>' . $order->get_meta('_shipping_phone' ). '</p>';
}

/*************************** show custom field data on thankyou page end **********************/

/*************************** show custom field data on email start **********************/

function kia_display_email_order_meta( $order, $sent_to_admin, $plain_text ) { 
	echo '<p><strong>' . __( 'Recipient Telephone' ) . ': </strong>' . $order->get_meta('_shipping_phone' ) . '</p>';
    echo '<p><strong>' . __( 'Location Type' ) . ': </strong>' . $order->get_meta('_shipping_location_type' ). '</p>';
	 
} 
add_action('woocommerce_email_customer_details', 'kia_display_email_order_meta', 30, 3 );

/*************************** show custom field data on email end **********************/

/*************************** show custom field data on admin side start **********************/

function kia_display_order_data_in_admin( $order ){  ?>
    <div class="">
    	<?php 
    		echo '<p><strong>' . __( 'Title' ) . ': </strong>' . $order->get_meta( '_shipping_gift_title' ) . '</p>';
    		echo '<p><strong>' . __( 'Address Line 1' ) . ': </strong>' . $order->get_shipping_address_1() . '</p>';
    		echo '<p><strong>' . __( 'Address Line 2' ) . ': </strong>' . $order->get_shipping_address_2() . '</p>';
    		echo '<p><strong>' . __( 'Recipient Telephone' ) . ': </strong>' . $order->get_meta('_shipping_phone' ) . '</p>';
            echo '<p><strong>' . __( 'Location Type' ) . ': </strong>' . $order->get_meta('_shipping_location_type' ). '</p>';
		?>
    </div>
<?php }
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'kia_display_order_data_in_admin' );

/*************************** show custom field data on admin side end **********************/



add_filter( 'woocommerce_shipping_fields', 'misha_remove_fields' );

function misha_remove_fields( $fields ) {
	$fields['shipping_shipping_phone'] = array(
        'label' => __('Recipient Telephone', 'woocommerce'),
        'placeholder' => _x('', 'placeholder', 'woocommerce'),
        'required' => true,
        'class' => array('form-row', 'form-row-first5',),
        'clear' => false
    );
    $fields['shipping_shipping_location_type'] = array(
        'type' => 'select',
        'label' => __('Location Type', 'woocommerce'),
        'placeholder' => _x('', 'placeholder', 'woocommerce'),
        'required' => false,
        'class' => array('form-row', 'form-row-first5'),
        'clear' => false
    );
    $fields['shipping_shipping_location_type']['options'] = array(
        '' => 'Select location type',
        'Residence' => 'Residence',
        'Apartment' => 'Apartment',
        'Business' => 'Business',
        'Church' => 'Church',
        'School' => 'School',
        'Hospital' => 'Hospital',
    );
    return $fields;

}
add_action( 'init', 'custom_remove_hooks', 11 );
function custom_remove_hooks() {
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
	//remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
    remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
    add_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description_custom', 10 );
    function woocommerce_taxonomy_archive_description_custom() {
        ?>
        <div class="term-description">
        <h1 class="woocommerce-products-header__title"><?php echo woocommerce_page_title(); ?> </h1>
        <?php
        if ( is_product_taxonomy() && 0 === absint( get_query_var( 'paged' ) ) ) {
			$term = get_queried_object();
			if ( $term && ! empty( $term->description ) ) {
				echo '<div class="archive-content">' . wc_format_content( $term->description ) . '</div>' . '<a class="archive-content-more description-learn-more">learn more</a>'; 
			}
		}
        ?>
        </div>
    <?php }
}

/*** Add Continue Shopping Button on Cart Page*/

add_action( 'woocommerce_after_cart_totals', 'woo_add_continue_shopping_button_to_cart' );

function woo_add_continue_shopping_button_to_cart() {
 $shop_page_url = get_permalink( woocommerce_get_page_id( 'shop' ) );
 echo '<div class="">';
 echo ' <a href="'.$shop_page_url.'" class="continueshop-btn button alt wc-forward">Continue Shopping</a>';
 echo '</div>';
}


// /**
//  * Add a privacy policy checkbox on the checkout form
//  *
//  * @author Wil Brown zeropointdevelopment.com
//  */
// function zpd_add_checkout_privacy_policy() {

//     woocommerce_form_field( 'privacy_policy', array(
//         'type'          => 'checkbox',
//         'class'         => array('form-row privacy'),
//         'label_class'   => array('woocommerce-form__label woocommerce-form__label-for-checkbox checkbox'),
//         'input_class'   => array('woocommerce-form__input woocommerce-form__input-checkbox input-checkbox'),
//         'required'      => true,
//         'label'         => 'I have read and agree with the <a href="' . get_privacy_policy_url() . '">Privacy Policy</a>',
//     ));

// }
// add_action( 'woocommerce_review_order_before_submit',  'zpd_add_checkout_privacy_policy', 9 );

// // Show notice if customer doesn't check the box (required)

// /**
//  * Show notice if customer doesn't check the new privacy policy checkbox
//  *
//  * @author Wil Brown zeropointdevelopment.com
//  */
// function zpd_privacy_not_agreed() {
//     if ( ! (int) isset( $_POST['privacy_policy'] ) ) {
//         wc_add_notice( __( 'Please read and accept the <strong>privacy policy</strong> to proceed with your order.' ), 'error' );
//     }
// }
// add_action( 'woocommerce_checkout_process', 'zpd_privacy_not_agreed' );

add_action( 'woocommerce_before_add_to_cart_button', 'woocommerce_product_custom_fields', 20);

function woocommerce_product_custom_fields () {
global $woocommerce, $post;
    echo '<div class="product_custom_field" style="display:inline-block;background:#ccc;padding:10px;"><div class="first_sec"><label>Select Delivery Information</label><div class="delivery_input"><input type="text" class="zip_code_lookup">&nbsp;&nbsp;<button type="button" class="btn_delivery" value="Choose A delivery Date">Choose A delivery Date</button></div><br><a href="javascript:void(0);">Don\'t know the Zip Code ?</a></div><div class="second_sec" style="display:none;"><label>Select Delivery Information</label><input type="text" name="select_zip" readonly><br><a href="javascript:void(0);">Wrong delivery zip code? Type a new one</a><br><input type="text" name="selected_date" readonly><a href="javascript:void(0);">Choose a different delivery date</a><input type="hidden" name="zip_price"><input type="hidden" name="zip_date"><input type="hidden" name="zip_price1"></div></div>';
}

add_action( 'wp_ajax_nopriv_read_xml', 'read_xml' );
add_action( 'wp_ajax_read_xml', 'read_xml' );

function read_xml(){
    $xml = simplexml_load_file(ABSPATH.'zip_code_data.xml') or die("Error: Cannot create object");
    $zip_ar = array();
    $objJsonDocument = json_encode($xml);
    $arrOutput = json_decode($objJsonDocument, TRUE);
    foreach($arrOutput['entry'] as $key=>$value){
        $name = str_split($value['district'],1);
        $zip_ar[$value['country']][$value['state']][strtoupper($name[0])][]=array('zip_code'=>$value['zip'],'district'=>$value['district'],'price'=>$value['price']);
        array_multisort(array_column($zip_ar[$value['country']], 'district'), SORT_ASC, $zip_ar[$value['country']]);
    }
    echo json_encode($zip_ar);
    die;
}

add_action( 'wp_ajax_nopriv_check_zip', 'check_zip' );
add_action( 'wp_ajax_check_zip', 'check_zip' );

function check_zip(){
    $xml = simplexml_load_file(ABSPATH.'zip_code_data.xml') or die("Error: Cannot create object");
    $xml_price = simplexml_load_file(ABSPATH.'price_xml.xml') or die("Error: Cannot create object");
    $zip_ar = array();
    $objJsonDocument = json_encode($xml);
    $arrOutput = json_decode($objJsonDocument, TRUE);
    $objJsonDocument_price = json_encode($xml_price);
    $arrOutput_price = json_decode($objJsonDocument_price, TRUE);
    $message = 'error';
    $price_array = array();
    foreach($arrOutput_price['entry'] as $key=>$value){
        $price_array[$value['date']] = array('price'=>$value['price'],'disabled'=>$value['disabled']); 
    }
    foreach($arrOutput['entry'] as $key=>$value){
        if($value['zip']==$_POST['zip_code']){
            $zip_ar[$value['zip']]=$value['price'];
            $message='success';
        }
    }
    $zip_ar['price'] = $price_array;
    echo json_encode(array('message'=>$message,'data'=>$zip_ar));
    die;
}


add_filter( 'woocommerce_add_cart_item_data', 'add_cart_item_data', 25, 2 );
function add_cart_item_data( $cart_item_data, $product_id ) {

    //echo "<pre>";print_r($cart_item_data);
    //echo "<pre>";print_r($_POST);die;
    // Set the data for the cart item in cart object
    $zip_price = trim($_POST['zip_price']);
    $zip_price1 = trim($_POST['zip_price1']);
    $date = $_POST['selected_date'];
    $date_for = $_POST['zip_date'];
    $data = array() ;
    $product = wc_get_product($product_id); // The WC_Product Object
    $base_price = (float) $product->get_regular_price(); // Product reg price

    if($zip_price1!=0){
        $cart_item_data['custom_data']['heading_zip'] = 'Zip Price';
        $cart_item_data['custom_data']['price_zip'] = $zip_price1;
    }
    if($zip_price!=0){
        $cart_item_data['custom_data']['heading_date'] = 'Date Price';
        $cart_item_data['custom_data']['price_date'] = $zip_price;
    }
    $new_price = (float)$base_price + (float)$zip_price + (float)$zip_price1;

    $cart_item_data['custom_data']['selected_date'] = $date_for;
    $cart_item_data['custom_data']['show_date'] = $date;
    $cart_item_data['custom_data']['total_price'] = $new_price;
    $cart_item_data['custom_data']['unique_key'] = md5( microtime() . rand() ); // Make each item unique


    /*foreach( custom_checkboxes() as $key => $value ){
        if( isset( $_POST[$key] ) ){
            $cart_item_data['custom_data'][$key] = $data[$key] = $value;
        }
    }*/
    // Add the data to session and generate a unique ID
    /*if( count($data > 0 ) ){
        $cart_item_data['custom_data']['unique_key'] = md5( microtime().rand() );
        WC()->session->set( 'custom_data', $data );
    }*/
    //echo "<pre>";print_r($cart_item_data);die;
    return $cart_item_data;
}


add_action( 'woocommerce_before_calculate_totals', 'extra_price_add_custom_price', 20, 1 );
function extra_price_add_custom_price( $cart ) {
    //echo "<pre>";print_r($cart);echo "</pre>";die;
    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;

    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
        return;
    //echo "<pre>";print_r($cart->get_cart);die;
    foreach ( $cart->get_cart() as $cart_item ) {
        if( isset($cart_item['custom_data']['price_zip']) ){
            $cart_item['data']->set_price( (float) $cart_item['custom_data']['price_zip'] );
        }
        if( isset($cart_item['custom_data']['price_date']) ){
            $cart_item['data']->set_price( (float) $cart_item['custom_data']['price_date'] );
        }
    }
}

add_filter('woocommerce_cart_item_price', 'display_cart_items_custom_price_details', 20, 3 );
function display_cart_items_custom_price_details( $product_price, $cart_item, $cart_item_key ){
    if( isset($cart_item['custom_data']) ) {
        $product = $cart_item['data'];
        $product_price  = wc_price( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ) );
        $product_price .= '<br>' . wc_price( $cart_item['custom_data']['price_date'] ).'&nbsp;';
        $product_price .= '<br>' . wc_price( $cart_item['custom_data']['price_zip'] ).'&nbsp;';
    }
    return $product_price;
}

/*Start Change the product category tag h2 to h3*/
function woocommerce_template_loop_category_title_override( $category ) { ?>
    <h2 class="woocommerce-loop-category__title">
        <?php
        echo esc_html( $category->name ); //Update your title which you want to update here
        if ( $category->count > 0 ) {
            echo apply_filters( 'woocommerce_subcategory_count_html', ' <mark class="count">(' . esc_html( $category->count ) . ')</mark>', $category );
        } ?>
        </h2><?php
}
add_action( 'woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title_override', 10 );

remove_action( 'woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title', 10 );
/*End Change the product category tag h2 to h3*/


// widgets to add description widget on shop page
function widgets() {
    register_sidebar( array(
        'name'          => 'Shop Page Description',
        'id'            => 'shop-desc',
        'before_widget' => '<div class="shopper">',
        'after_widget'  => '</div>',
        'before_title'  => '',
        'after_title'   => '',
        
    ) );    
}

add_action('init', 'widgets');


