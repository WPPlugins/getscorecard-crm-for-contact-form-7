
<?php
/**
 * File to display the view in CF7
 * Created: 2013-11-12
 * Company: GetScorecard.com
 * Updated: 20140206
 * */
extract($data);
// check if this plugin has been activated and validated with CU , otherwise don't show this error message
$cf7_gs_activated = get_option('cf7_gs_database_active');

$apiClient = new GetScorecardApiClient();
//$cUsComGsAPI_Cloud = new cUsComGsAPI_Cloud();

$callback_uri = site_url() . '/wp-admin/admin.php?page=cf7-crm&action=auth_callback';
$oauth_redirect_uri = site_url() . '/wp-admin/admin.php?page=cf7-crm&action=auth_redirect';

?>
<script>
//<![CDATA[	
    var CF7_GS_ADMIN_AJAX_URL = "<?php echo CF7_GS_ADMIN_AJAX_URL; ?>";
    var template_url = "<?php echo get_bloginfo('template_url'); ?>";
//]]>
</script>

<!-- page loader -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript">
    $(window).load(function() {
        //$(".loader").fadeOut("slow");
    })
</script>

<div class="loader"></div>
<!---->

<!-- plugin admin header -->
<div class="getscorecard_banner">
        <!--<h1>Contact Form 7 CRM <br> By GetScorecard</h1>
        <br>-->

        <a href="<?php echo CF7_GS_FACEBOOK_URL; ?>" target="_blank" title="Follow Us on Facebook for new product updates"><img src="<?php echo plugins_url('assets/images/facebook_icon.png', dirname(__FILE__)) ?>" width="32" height="34" alt="Facebook" /></a>
        <a href="<?php echo CF7_GS_GOOGLE_PLUS_URL; ?>" target="_blank" title="Follow Us on Google+"><img src="<?php echo plugins_url('assets/images/googeplus_icon.png', dirname(__FILE__)) ?>" width="32" height="34" alt="Google+" /></a>
        <a href="<?php echo CF7_GS_LINKED_IN_URL; ?>" target="_blank" title="Follow Us on LinkedIn"><img src="<?php echo plugins_url('assets/images/linkedin_icon.png', dirname(__FILE__)) ?>" width="32" height="34" alt="Linked In" /></a>
        <a href="<?php echo CF7_GS_TWITTER_URL; ?>" target="_blank" title="Follow Us on Twitter"><img src="<?php echo plugins_url('assets/images/twitter_icon.png', dirname(__FILE__)) ?>" width="32" height="34" alt="Twitter" /></a>
        <a href="<?php echo CF7_GS_YOUTUBE_URL; ?>" target="_blank" title="Find tutorials on our Youtube channel"><img src="<?php echo plugins_url('assets/images/youtube_icon.png', dirname(__FILE__)) ?>" width="32" height="34" alt="Youtube" /></a>
</div>
<!--<div id="getscorecard_background" style="background-color: #003399"></div>-->
    <!--<div id="getscorecard_buttons"style="background-color: #003399"></div>-->

<!-- / plugin admin header -->

<div id="container_cf7gsdatabase">

    <?php
    if (is_plugin_active('contact-form-7/wp-contact-form-7.php')) {
        ?>

        <!-- left side container -->
        <div id="GSintegrations_toleft">
        <!--<div id="GSintegrations_toleft_wide">-->
            <?php if (!$apiClient->authorized) { ?>
                <div class="first_step_gsdb">

                    <div id="cf7gs_welcome">
                        <p>
                            GetScorecard CRM for Contact Form 7 is an add-on solution for Contact Form 7 users to enhance their contact form capabilities with GetScorecard Proactive CRM. Once integrated, you can add data to you GetScorecard account using Contact Form 7.
                        </p>

                        <!--<h3>Create your <?php /*echo CF7_GS_GETSCORECARD_WEBSITE_LABEL; */?> account here! Or login with your existing account</h3><br />-->

                        <!-- step 1 create free account -->
                        <!--<a href="<?php /*echo WPGETSCORECARD7_GS_BASE_URL; */?>/register.php?registerType=contact-form-7-getscorecard-v2&return_url=<?php /*echo urlencode(CF7_GS_ADMIN_AJAX_URL.'admin.php?page=cf7-crm'); */?>">
                            <button id="cUsCloud_no" class="btn_gsdb_sign_up">
                                Step 1: Set Up Free Account
                            </button>
                        </a>-->

                        <!--<a href="<?php /*echo WPGETSCORECARD7_GS_BASE_URL; */?>/register.php?registerType=contact-form-7-getscorecard&return_url=<?php /*echo urlencode(CF7_GS_ADMIN_AJAX_URL.'admin.php?page=cf7-crm'); */?>">
                            <button id="cUsCloud_no" class="btn_gsdb_sign_up">
                                Step 1: Set Up Free Account
                            </button>
                        </a>-->

                        <br>
                        <iframe width="560" height="400" src="https://www.youtube.com/embed/SUlz67hQMdU" frameborder="0" allowfullscreen></iframe>
                        <br>
                        <br>

                        <button id="cf7_gs_button_no" class="btn_gsdb_sign_up">
                            Step 1: Set Up Free Account
                        </button>

                        <!-- register form -->

                        <?php
                        $registerFormStyle = 'display:none';
                        if(isset($_GET['error']) && $_GET['error']=='create_account_error'){
                            $register_error = true;
                            $message = isset($_GET['message']) ? $_GET['message'] : 'Can\'t create account';
                            $registerFormStyle = 'display:block';
                        }
                        ?>

                        <!--<div id="register_form" style="display:none;">-->
                        <div id="register_form" style="<?php echo $registerFormStyle; ?>">
                            <h3><?php echo CF7_GS_GETSCORECARD_WEBSITE_LABEL; ?> Register</h3>

                            <?php if($register_error): ?>
                                <div class="advice_notice_custom_alert"><?php echo $message; ?></div>
                            <?php endif; ?>

                            <form method="post" action="<?php echo CF7_GS_GETSCORECARD_BASE_URL; ?>/register/user_register_api.php" id="gs_registerform" name="gs_registerform">
                                <table class="form-table">
                                    <tr>
                                        <th><label class="labelform" for="login_name">Full Name</label><br>
                                        <td><input class="inputform" id="login_name" type="text" aria-required="true" value="" name="fullname"></td>
                                    </tr>
                                    <tr>
                                        <th><label class="labelform" for="login_email">Email</label><br>
                                        <td><input class="inputform" id="login_email" type="email" aria-required="true" value="" name="emailaddress"></td>
                                    </tr>
                                    <tr>
                                        <th><label class="labelform" for="user_pass">Password</label></th>
                                        <td><input id="user_pass" class="inputform" type="password" value="" name="password"></td>
                                    </tr>

                                    <tr><th></th>
                                        <td>
                                            <input type="hidden" name="plugin_signIn" value="1">
                                            <input type="hidden" name="plugin_type" value="contact-form-7-getscorecard">
                                            <input type="hidden" name="registerType" value="contact-form-7-getscorecard">

                                            <input type="hidden" name="form_version" value="2">

                                            <input type="hidden" name="return_url" value="<?php echo CF7_GS_ADMIN_AJAX_URL; ?>admin.php?page=cf7-crm">

                                            <input type="hidden" name="callback_uri" value="<?php echo $callback_uri; ?>">
                                            <input type="hidden" name="oauth_redirect_uri" value="<?php echo $oauth_redirect_uri; ?>">

                                            <!--<input type="hidden" name="callback_uri" value="<?php /*echo site_url(); */?>/wp-admin/admin.php?page=cf7-crm&action=auth_callback">
                                            <input type="hidden" name="oauth_redirect_uri" value="<?php /*echo site_url(); */?>/wp-admin/admin.php?page=cf7-crm&action=auth_redirect">-->

                                            <input id="registerbtn" class="action_red_button" value="Register" type="submit">
                                        </td>
                                    </tr>

                                </table>
                            </form>
                        </div>
                        <!---->

                        <!-- /step 1 create free account -->

                        <br>
                        <br>

                        <button id="cf7_gs_button_yes" class="btn_gsdb_sign_in" type="button" >
                            <!--<span>Step 2:</span>-->
                            Step 2: Login
                        </button>

                        <br>
                        <br>



                        <?php
                        $loginFormStyle = 'display:none';
                        if(isset($_GET['error']) && $_GET['error']=='authorize_error'){
                            $authorize_error = true;
                            $loginFormStyle = 'display:block';
                        }

                        if(isset($_GET['status']) && $_GET['status']=='authCanceled'){
                            $auth_canceled = true;
                            $loginFormStyle = 'display:block';
                        }
                        ?>

                        <!-- login form -->
                        <div id="login_form" style="<?php echo $loginFormStyle; ?>">
                            <h3><?php echo CF7_GS_GETSCORECARD_WEBSITE_LABEL; ?> Login</h3>

                            <?php if($authorize_error): ?>
                                <div class="advice_notice_custom_alert">Wrong email or password</div>
                            <?php endif; ?>

                            <?php if($auth_canceled): ?>
                                <div class="advice_notice_custom_alert">Authorization canceled by user</div>
                            <?php endif; ?>

                            <form method="post" action="<?php echo CF7_GS_GETSCORECARD_BASE_URL; ?>/login-process.php" id="gs_loginform" name="gs_loginform">
                                <table class="form-table">
                                    <tr>
                                        <th><label class="labelform" for="login_email">Email</label><br>
                                        <td><input class="inputform" id="login_email" type="email" aria-required="true" value="" name="email"></td>
                                    </tr>
                                    <tr>
                                        <th><label class="labelform" for="user_pass">Password</label></th>
                                        <td><input id="user_pass" class="inputform" type="password" value="" name="password"></td>
                                    </tr>

                                    <tr><th></th>
                                        <td>
                                            <input type="hidden" name="plugin_signIn" value="1">
                                            <input type="hidden" name="plugin_type" value="contact-form-7-getscorecard">
                                            <input type="hidden" name="return_url" value="<?php echo CF7_GS_ADMIN_AJAX_URL; ?>admin.php?page=cf7-crm">

                                            <input type="hidden" name="callback_uri" value="<?php echo $callback_uri; ?>">
                                            <input type="hidden" name="oauth_redirect_uri" value="<?php echo $oauth_redirect_uri; ?>">

                                            <!--<input type="hidden" name="callback_uri" value="<?php /*echo site_url(); */?>/wp-admin/admin.php?page=cf7-crm&action=auth_callback">
                                            <input type="hidden" name="oauth_redirect_uri" value="<?php /*echo site_url(); */?>/wp-admin/admin.php?page=cf7-crm&action=auth_redirect">-->

                                            <input id="loginbtn" class="action_blue_button" value="Login" type="submit">
                                        </td>
                                    </tr>

                                </table>
                            </form>
                        </div>
                        <!---->

                        <button class="btn_gsdb_setup_form generateForm" type="button" >
                             Step 3: Set Up A Standard Form
                        </button>

                        <br>
                        <br>

                        <a href="<?php echo WPGETSCORECARD7_GS_BASE_URL; ?>" target="_blank">
                            <button class="btn_gsdb_open_gs_link" type="button" >
                                Open GetScorecard
                            </button>
                        </a>

                    </div>
                </div>

                <?php
            } else {
                $userId = $apiClient->getOption(GetScorecardApi::OPTION_USER_ID);
                $userInfo = $apiClient->getUserById($userId);
                $userInfoLabels = $apiClient->getUserInfoLabels();
                ?>

                <br><div class="notice_visible"> <h1 class="cf7_gs_welcome_header">Welcome to GetScorecard CRM for Contact Form 7</h1>

                <p>
                    Welcome,Contact Form 7 users!
                    <br/><br/>
                    GetScorecard CRM for Contact Form 7 is an add-on solution for Contact Form 7 users to enhance their contact form capabilities with GetScorecard proactive CRM.  Once integrated, you can add information to your GetScorecard account.
                </p>

                <br>
                <iframe width="560" height="400" src="https://www.youtube.com/embed/SUlz67hQMdU" frameborder="0" allowfullscreen></iframe>
                <br>


                <!--<p>
                    <a href="<?php /*echo WPGETSCORECARD7_GS_BASE_URL; */?>" target="_blank">Open GetScorecard</a>
                </p>-->

                <p>
                    <a href="<?php echo WPGETSCORECARD7_GS_BASE_URL; ?>" target="_blank">
                        <button class="btn_gsdb_open_gs_link" type="button" >
                            Open GetScorecard
                        </button>
                    </a>
                </p>

                <p>
                    Next Step - Configure the GetScorecard tab on the contact form 7 Edit form page.
                </p>

                <button class="btn_gsdb_setup_form generateForm" type="button" >
                    Set Up A Standard Form
                </button>

                <br>
                <br>

                <h2>You are authorized</h2>

                <table id="loginForm" class="form-table">
                    <tbody>
                        <?php foreach($userInfo as $key=>$value): ?>
                            <?php if(in_array($key,['id','fullname','email']) && $value):?>
                                <tr class="form-field form-required">
                                    <td style="width: 150px; text-align:left;" scope="row">
                                        <strong>
                                            <?php echo $userInfoLabels[$key]; ?>:
                                        </strong>
                                    </td>
                                    <td><?php echo $value; ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <br>

                <a class="LogoutUser button-primary button-hero" href="javascript:;">
                    Logout
                </a>
                <br><br>
                <?php
            }
            ?>
        </div>
        <!-- / left side container -->
        <?php
    } else {
        ?>

        <div id="GSintegrations_toleft_wide">
            <h1><img src="<?php echo plugins_url('../assets/images/engranaje.png', __FILE__); ?>" width="42" height="55" alt="GetScorecard.com" />It seems you don’t have Contact Form 7 (CF7) installed</h1>
            <p style="margin:-20px 0px 10px 52px;"><?php echo CF7_GS_PLUGIN_NAME; ?> is an extension for the CF7 plugin to integrate with <?php echo CF7_GS_GETSCORECARD_WEBSITE_LABEL; ?>. It requires an installation of CF7 to work.
            </p>

            <p class="cf7_download_link">To download <strong>"Contact Form 7"</strong>, <a class="thickbox" href="<?php echo get_bloginfo('url'); ?>/wp-admin/plugin-install.php?tab=plugin-information&plugin=Contact-Form-7&TB_iframe=true&width=640&height=565">click here</a></p>

            <p class="cf7_download_link">(Once you install "Contact form 7", you can click on <strong>"GetScorecard CRM for Contact Form 7"</strong> settings and continue Contact Form 7 integrations setup).</p>

        </div>

        <!-- <div id="GSintegrations_toright2"></div> -->

        <?php
    } // end else that check if Contact Form 7 is active.
    ?>
</div>
