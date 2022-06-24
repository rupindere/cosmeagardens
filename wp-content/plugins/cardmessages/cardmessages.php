<?php /* 
Plugin name: Card Messages 
Description: Card messages 
Author: Sanish 
Version: 1.0 */  

/* Occasion grid and menu and form */
function create_occasion() {
    register_post_type( 'Occasions',
        array(
            'labels' => array(
                'name' => 'Card Messages',
                'singular_name' => 'Card Message',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Occasion',
                'edit' => 'Edit',
                'edit_item' => 'Edit Occasion',
                'new_item' => 'New Occasion',
                'view' => 'View',
                'view_item' => 'View Occasion',
                'search_items' => 'Search Occasions',
                'not_found' => 'No Occasions found',
                'not_found_in_trash' => 'No Occasion found in Trash',

            ),
 
            'public' => false,
            'menu_position' => 15,
            'supports' => array( 'title', 'thumbnail', 'custom-fields', 'editor', ),
            'taxonomies' => array( '' ),
            'menu_icon' => 'dashicons-email-alt',
            'has_archive' => false,
            'show_ui' => true, 
        )
    );
}
add_action( 'init', 'create_occasion' );

function my_enqueue() {
    wp_enqueue_script( 'ajax-script', plugin_dir_url(__FILE__) . '/my-script.js', array('jquery') );
    wp_localize_script( 'ajax-script', 'my_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ),
    'nonce' => wp_create_nonce('example_ajax_nonce'), ) );
}
add_action( 'wp_enqueue_scripts', 'my_enqueue' );


function showMessages(){
    $id = $_POST['data']['id'];
    $return = '';
    if($id){
    $args = array(
        'p' => $id,
        'post_type'   => 'Occasions',
        'post_status' => 'publish',
    );
    $occasions = get_posts( $args );
    $return = '<option value="">Select Message</option>';
        foreach($occasions as $occasion){
            $matches = [];
            $text = $occasion->post_content;
            preg_match_all("/\[[^\]]*\]/", $text, $matches);
            foreach($matches[0] as $resu){
                $text = str_replace( array('[',']') , ''  , $resu );
                $return .= '<option  value="'.$text.'" message="'.$text.'">'.$text.'</option>';
            }
        }
    
                   
    }else{
        $return = '';
    }
    wp_send_json_success( $return );
}
add_action('wp_ajax_showMessages', 'showMessages');
add_action('wp_ajax_nopriv_showMessages', 'showMessages');

add_action( 'woocommerce_card_messages', 'shoptimizer_product_card_message', 5 );
function shoptimizer_product_card_message() {
    $args = array(
        'post_type'   => 'occasions',
        'post_status' => 'publish',
        'numberposts' => -1,
      );
       
    $occasions = get_posts( $args );
	echo '<div class="message_box wcpa_row" style="display:none">
            <label for="select-61a7038544d4f">Gift Message!</label>
            
            <div class="select_option_for_y_n">
                <div class="radio">
                    <label for="select_option_for_n">
                        <input type="radio" value="0" class="option_for_y_n" id="select_option_for_n" name="select_option_for_y_n" style="visibility: visible;" >No Gift Message
                    </label>
                </div>
                <div class="radio">
                    <label for="select_option_for_n">
                        <input  type="radio" value="1" class="option_for_y_n" id="select_option_for_y" name="select_option_for_y_n" style="visibility: visible;" checked="checked">Complimentary Gift Message(150 Characters limit)
                    </label>
                </div>
            </div>
            <div class="showNow">
            <div class="select">
                <select data-placeholder="Select Occasion" required name="select-occasion" class="wcpa_use_sumo select_occasion_message">
                    <option value="" class="options">Select Occasion</option>';
                        foreach($occasions as $occasion){
                            echo '<option  value="'.$occasion->post_title.'" message="'.$occasion->ID.'">'.$occasion->post_title.'</option>';
                        }
        
                echo '</select>
            <div class="select_arrow"></div>
            </div>
            <div class="more-options">
                <div class="select_more_option" >
                    <select data-placeholder="Select Occasion"  name="select_message" class="wcpa_use_sumo select_message">
                        
                    </select>   

                    <div class="sym_message_textarea">
                        <textarea  name="sym_message_text" placeholder="Your Special Notes" class="sym_message_card_text"  maxlength="50"></textarea>
                     
                    </div>
                    <div class="message_textarea" >
                        <textarea required name="message_text" placeholder="Your Message" class="message_card_text"  maxlength="150"></textarea>
                        <span>Special Characters are not allowed. Please enter only letters and numbers.</span><br>
                        <span class="counter"></span>
                        <br>
                        <span class="error"></span>
                    </div>
                </div>
            </div>
            </div>
        </div>';
}


/**
 * Removed custom meta box from editing coupon
 **/ 
function remove_meta_boxe() {
  # Removes meta from Posts # 
  remove_meta_box('postcustom','occasions','normal');
}
add_action('admin_init','remove_meta_boxe');




 ?>