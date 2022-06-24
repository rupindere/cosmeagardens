<?php
define('SSS_FBKEY', '1151936305158619');
//define('SSS_GOOGLEKEY', '676810173682-ng4feff6v972g6olce047rfm8e4udtlb.apps.googleusercontent.com');
define('SSS_GOOGLEKEY', '79815115433-r0i9p56f31k9kdhdq9bj8unpo4urvh50.apps.googleusercontent.com');
class SocialLogin
{

    private static $_instance = null;

    /**
     * @return WP_reCaptcha
     */
    public static function instance()
    {
        if (is_null(self::$_instance))
            self::$_instance = new self();
        return self::$_instance;
    }

    private function __construct()
    {
        add_action('wp_head', array($this, 'add_meta'));
        add_action('login_head', array($this, 'add_meta'));
        add_action('wp_footer', array($this, 'fb_footer'));
        //add_action('login_footer', array($this, 'fb_footer'), 100);
        //add_action('register_form', array($this, 'fb_html'));
         add_action('woocommerce_register_form', array($this, 'fbr_html'));
        //add_action('login_form', array($this, 'fb_html'));
        add_action('woocommerce_login_form', array($this, 'fb_html'));

        add_action('wp_ajax_sss_socialLoginAction', array($this, 'socialLoginAction'));
        add_action('wp_ajax_nopriv_sss_socialLoginAction', array($this, 'socialLoginAction'));

    }

    function fb_footer()
    {
        ?>
        <script>

            function statusChangeCallback(response) {  // Called with the results from FB.getLoginStatus().
                if (response.status === 'connected') {   // Logged into your webpage and Facebook.
                    sssFacebookApi();
                }
            }

            function checkLoginState() {

                // Called when a person is finished with the Login Button.
                FB.login(function (response) {   // See the onlogin handler
                    statusChangeCallback(response);
                });
            }

            window.fbAsyncInit = function () {
                FB.init({
                    appId: "<?php echo SSS_FBKEY; ?>",
                    cookie: true,                     // Enable cookies to allow the server to access the session.
                    xfbml: true,                     // Parse social plugins on this webpage.
                    version: 'v6.0'           // Use this Graph API version for this call.
                });

                //    FB.getLoginStatus(function (response) {   // Called after the JS SDK has been initialized.
                //      statusChangeCallback(response);        // Returns the login status.
                // })
            };

            (function (d, s, id) {                      // Load the SDK asynchronously
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s);
                js.id = id;
                js.src = "https://connect.facebook.net/en_US/sdk.js";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));

            function sssFacebookApi() {
                FB.api('/me?fields=id,name,email,first_name,last_name', function (response) {
                    sssSocialLogin(response.email, response.id, 'facebook', response.first_name,
                        response.last_name)
                });
            }



            function sssSocialLogin(email, id, type, firstname, lastname) {

                var fd = new FormData();

                fd.append('action', 'sss_socialLoginAction');
                fd.append('sss_sociallogin', '1');
                fd.append('email', email);
                fd.append('id', id);
                fd.append('type', type);
                fd.append('first_name', firstname);
                fd.append('last_name', lastname);

                xhr = new XMLHttpRequest();

                xhr.open( 'POST', "<?php echo admin_url('admin-ajax.php'); ?>", true );
                xhr.onreadystatechange = function ( response ) {
                    if(response.status =='error'){
                    }else{
                        window.location.href = "/my-account";
                    }
                    //  location.reload(true);
                };
                xhr.send( fd );

            }

            // function onSignIn(googleUser) {
            //     var profile = googleUser.getBasicProfile();
            //     console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
            //     console.log('Name: ' + profile.getName());
            //     console.log('Image URL: ' + profile.getImageUrl());
            //     console.log('Email: ' + profile.getEmail()); // This is null if the 'email' scope is not present.
            // }
        </script>
        <?php

    }

    function add_meta()
    {

        ?>
<style>
 div.sssbutton{
                width:100%;
                float:left;
                margin-bottom: 10px;

            }
.woocommerce div.sssbutton{
width:49%;
}
            div.sssbutton img{
                width: 100%;
            }
            .woocommerce  .sssSocialButton .sssbutton:first-child{
                margin-right: 2%;
            }
            .woocommerce div.sssbutton {
    width: 49%;
}
.abcRioButtonBlue, .abcRioButtonBlue:hover {
    background-color: #4285f4;
}
.abcRioButtonBlue {
    border: none;
    color: #fff;
}
.abcRioButton {
    border-radius: 1px;
    box-shadow: 0 2px 4px 0 rgb(0 0 0 / 25%);
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    -webkit-transition: background-color .218s,border-color .218s,box-shadow .218s;
    transition: background-color .218s,border-color .218s,box-shadow .218s;
    -webkit-user-select: none;
    -webkit-appearance: none;
    background-color: #4285f4;
    background-image: none;
    color: #fff;
    cursor: pointer;
    outline: none;
    overflow: hidden;
    position: relative;
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
    width: auto;
}
.abcRioButtonBlue .abcRioButtonContentWrapper {
    border: 1px solid transparent;
}
.abcRioButtonContentWrapper {
    height: 100%;
    width: 100%;
}
.abcRioButtonBlue .abcRioButtonIcon {
    background-color: #fff;
    border-radius: 1px;
}

.abcRioButtonIcon {
    float: left;
}
.abcRioButtonContents {
    font-family: Roboto,arial,sans-serif;
    font-size: 14px;
    font-weight: 500;
    letter-spacing: .21px;
    margin-left: 6px;
    margin-right: 6px;
    vertical-align: top;
}
</style>
        <!-- <meta name="google-signin-client_id"
               content="676810173682-ng4feff6v972g6olce047rfm8e4udtlb.apps.googleusercontent.com"> -->

        <?php
    }
 function fbr_html()
    {
        ?>
      

        <div class="sssSocialButton">




          <div id="facebookSignIn" class="sssbutton abc" onclick="checkLoginState()"><div style="height:50px;width:240px;" class="abcRioButton abcRioButtonBlue"><div class="abcRioButtonContentWrapper"><div class="abcRioButtonIcon" style="padding:15px"><div style="width:18px;height:18px;" class="abcRioButtonSvgImageWithFallback abcRioButtonIconImage abcRioButtonIconImage18"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 333333 333333" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd"><path d="M197917 62502h52080V0h-52080c-40201 0-72909 32709-72909 72909v31250H83337v62507h41659v166667h62506V166666h52080l10415-62506h-62496V72910c0-5648 4768-10415 10415-10415v6z" fill="#3b5998"></path></svg></div></div><span style="font-size:16px;line-height:48px;" class="abcRioButtonContents"><span id="not_signed_inhlxnoacow01i">Signup with Facebook</span><span id="connectedhlxnoacow01i" style="display:none">Signup in with Facebook</span></span></div></div></div>
            <div id="my-signin2"></div>
               <script>
    function onSuccess(googleUser) {
      console.log('Logged in as: ' + googleUser.getBasicProfile().getName());
       var profile1 = googleUser.getBasicProfile();
      sssSocialLogin(profile1.getEmail(), profile1.getId(), 'google', profile1.getGivenName(),
                                profile1.getFamilyName())
    }
    function onFailure(error) {
      console.log(error);
    }
    function renderButton() {
      gapi.signin2.render('my-signin2', {
        'scope': 'profile email',
        'width': 240,
        'height': 50,
        'longtitle': true,
        'theme': 'dark',
        'onsuccess': onSuccess,
        'onfailure': onFailure
      });
    }
   
  </script>
<script type="text/javascript">
     jQuery(document).ready(function(){
   jQuery('#my-signin2 .abcRioButtonContents span').each(function() {
        var text = jQuery(this).text();
        jQuery(this).text(text.replace('Sign in with Google', 'Signup with Google')); 
    });
});
</script>
  <script src="https://apis.google.com/js/platform.js?onload=renderButton" async defer></script>
        </div>
     <div class="clearfix"></div>
        <?php
    }

    function fb_html()
    {
        ?>
      

        <div class="sssSocialButton">




            <div id="facebookSignIn" class="sssbutton" onClick="checkLoginState()" >
<div style="height:50px;width:240px;" class="abcRioButton abcRioButtonBlue"><div class="abcRioButtonContentWrapper"><div class="abcRioButtonIcon" style="padding:15px"><div style="width:18px;height:18px;" class="abcRioButtonSvgImageWithFallback abcRioButtonIconImage abcRioButtonIconImage18"><svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 333333 333333" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd"><path d="M197917 62502h52080V0h-52080c-40201 0-72909 32709-72909 72909v31250H83337v62507h41659v166667h62506V166666h52080l10415-62506h-62496V72910c0-5648 4768-10415 10415-10415v6z" fill="#3b5998"/></svg></div></div><span style="font-size:16px;line-height:48px;" class="abcRioButtonContents"><span id="not_signed_inhlxnoacow01i">Sign in with Facebook</span><span id="connectedhlxnoacow01i" style="display:none">Sign in with Facebook</span></span></div></div>
                <!-- <img src="<?php //echo get_stylesheet_directory_uri() ?>/sss/images/login-with-facebook.png" alt="Log in With Facebook" title="Log in With Facebook" /> --></div>

            <div id="googleSignIn" class="sssbutton">
<div style="height:50px;width:240px;" class="abcRioButton abcRioButtonBlue"><div class="abcRioButtonContentWrapper"><div class="abcRioButtonIcon" style="padding:15px"><div style="width:18px;height:18px;" class="abcRioButtonSvgImageWithFallback abcRioButtonIconImage abcRioButtonIconImage18"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="18px" height="18px" viewBox="0 0 48 48" class="abcRioButtonSvg"><g><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path><path fill="none" d="M0 0h48v48H0z"></path></g></svg></div></div><span style="font-size:16px;line-height:48px;" class="abcRioButtonContents"><span id="not_signed_inhlxnoacow01i">Sign in with Google</span><span id="connectedhlxnoacow01i" style="display:none">Signed in with Google</span></span></div></div>
                <!-- <img src="<?php //echo get_stylesheet_directory_uri() ?>/sss/images/signingoogle.png" alt="Sign In With Google" title="Sign In With Google" /> --></div>
            
        </div>
<div class="clearfix"></div>
        <script src="https://apis.google.com/js/platform.js?onload=onSSSLoadGoogleCallback" async defer></script>
        <script>
            window.onSSSLoadGoogleCallback= function() {
                gapi.load('auth2', function () {
                    auth2 = gapi.auth2.init({
                        client_id: '<?php echo SSS_GOOGLEKEY ?>',
                        cookiepolicy: 'single_host_origin',
                        scope: 'profile'
                    });

                    auth2.attachClickHandler(element, {},
                        function (googleUser) {
                            var profile = googleUser.getBasicProfile();
                            console.log(profile);
                            sssSocialLogin(profile.getEmail(), profile.getId(), 'google', profile.getGivenName(),
                                profile.getFamilyName())

                        }, function (error) {
                            message = JSON.stringify(error, undefined, 2);
                            console.log(error);
                        }
                    );
                });


                element = document.getElementById('googleSignIn');
            }
        </script>
        <?php
    }


    function socialLoginAction()
    {


        if (is_user_logged_in()) {
            $return['status'] = 'error';
            $return['msg'] = __("You cant accesss this directly", ETHEME_DOMAIN);
            echo json_encode($return);
            wp_die();
        }

        if (isset($_REQUEST['sss_sociallogin']) && isset($_REQUEST['id'])) {

            if (!isset($_REQUEST['email'])) $_REQUEST['email'] = $_REQUEST['id'] . '@' . $_REQUEST['type'] . 'com';
            $ID = email_exists($_REQUEST['email']);

            if ($ID == false) { // Real register
                require_once(ABSPATH . WPINC . '/registration.php');
                $random_password = wp_generate_password($length = 12, $include_standard_special_chars = false);

                $username = strtolower($_REQUEST['first_name'] . " " . $_REQUEST['last_name']);
                $sanitized_user_login = sanitize_user($username);
                if (!validate_username($sanitized_user_login)) {
                    $sanitized_user_login = sanitize_user($_REQUEST['type'] . "_" . $_REQUEST['id']);
                }
                $defaul_user_name = $username;
                $i = 1;
                while (username_exists($sanitized_user_login)) {
                    $sanitized_user_login = sanitize_user($defaul_user_name . "_" . $i);
                    $i++;
                }

                $ID = wp_create_user($sanitized_user_login, $random_password, $_REQUEST['email']);
                if (!is_wp_error($ID)) {
                    wp_new_user_notification($ID, $random_password);
                    $user_info = get_userdata($ID);
                    wp_update_user(array(
                        'ID' => $ID,
                        'display_name' => $defaul_user_name,
                        'first_name' => $_REQUEST['first_name'],
                        'last_name' => $_REQUEST['last_name']
                    ));

                    update_user_meta($ID, $_REQUEST['type'] . '_unique_id', $_REQUEST['id']);

                } else {
                    $return['status'] = 'error';
                    $return['msg'] = __("Something went wrong!. Please try again later", ETHEME_DOMAIN);
                    echo json_encode($return);
                    wp_die();
                }
            }


            if ($ID) { // Login


                $user_info = get_userdata($ID);


                wp_set_current_user($ID, $user_info->user_login);
                wp_set_auth_cookie($ID, true);
                update_user_meta($ID, $_REQUEST['type'] . '_unique_id', $_REQUEST['id']);
                do_action('wp_login', $user_info->user_login, $user_info);

                $return['status'] = 'success';
                $return['msg'] = __("", ETHEME_DOMAIN);
                echo json_encode($return);
            }


        }
        $return['status'] = 'error';
        $return['msg'] = __("Something went wrong!. Please try again later", ETHEME_DOMAIN);
        echo json_encode($return);
        wp_die();
    }
}
SocialLogin::instance();
?>