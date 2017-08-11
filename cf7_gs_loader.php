<?php

require_once(dirname(__FILE__) . '/models/interfaces/icf7_gs_interface.php');
require_once(dirname(__FILE__) . '/includes/class-tgm-plugin-activation.php');

require_once dirname(__FILE__) . '/includes/GetScorecardApi.php';
require_once dirname(__FILE__) . '/includes/GetScorecardApiClient.php';
require_once dirname(__FILE__) . '/includes/GetScorecardForm.php';
require_once dirname(__FILE__) . '/includes/GetScorecardFormTemplate.php';

class CF7_gs_loader extends CF7_gs_interface {

    // Don't change this private values unless you know what you are doing
    private $cf7_gs_db_version = '1.4.1';
    private $cf7_gs_version = '1.4.1';

    private $apiClient;

    /**
     * just the constructor for the action settings
     */
    public function __construct() {
        $this->apiClient = new GetScorecardApiClient();

        add_action('admin_menu', array(&$this, 'cf7_gs_database_menu'));

        // contact form 7 hooks/actions binding
        add_action("wpcf7_before_send_mail", array(&$this, 'wpcf7_gs_send_all'));
        add_action("wpcf7_admin_after_mail", array(&$this, 'show_cf7gs_metabox'));

        add_action('wpcf7_after_save', array(&$this, 'cf7gs_save_form'));

        /*if($this->apiClient->authorized && $this->checkPostId($_GET['post'])){
            add_filter( 'wpcf7_editor_panels', array( &$this, 'cf7gs_editor_panels') );
        }*/

        if($this->apiClient->authorized){
            add_filter( 'wpcf7_editor_panels', array( &$this, 'cf7gs_editor_panels') );
        }

        add_filter("plugin_action_links", array(&$this, 'cf7gs_plugin_action_links'), 10, 4);
        add_filter("plugin_row_meta", array(&$this, 'cf7gs_plugin_links'), 10, 2);

        add_action('admin_enqueue_scripts', array(&$this, 'Load_scripts'));
        add_action('admin_enqueue_scripts', array(&$this, 'Load_styles'));
    }

    /**
     * @param $postId
     * @return bool
     */
    private function checkPostId($postId){
        if($postId && $postId != -1){
            return true;
        }

        return false;
    }
    
    /**
	 * Add panels in Contact Form 7 4.2+
	 *
	 * @since 2.1
	 *
	 * @param array $panels registered tabs in Form Editor
	 *
	 * @return array tabs with CTCTCF7 tab added
	 */
	function cf7gs_editor_panels( $panels = array() ) {
		if ( wpcf7_admin_has_edit_cap() ) {
            if($this->checkPostId($_GET['post'])){
                $panels['cf7gs'] = array(
                    'title'    => __( 'GetScorecard', 'cf7-crm' ),
                    'callback' => array( &$this, 'wpcf7_cf7gs_add_getscorecard_analytics' )
                );
            }
            else{
                $panels['cf7gs'] = array(
                    'title'    => __( 'GetScorecard', 'cf7-crm' ),
                    'callback' => array( &$this, 'wpcf7_cf7gs_add_getscorecard_analytics_form_not_exists' )
                );
            }

		}

		return $panels;
	}

    function my_plugin_register_required_plugins() {
        /**
         * Array of plugin arrays. Required keys are name and slug.
         * If the source is NOT from the .org repo, then source is also required.
         */
        $plugins = array(
            // This is an example of how to include a plugin from the WordPress Plugin Repository
            array(
                'name' => 'Contact Form 7',
                'slug' => 'Contact-Form-7',
                'force_activation' => true, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
                'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
                'required' => true,
            ),
        );

        // Change this to your theme text domain, used for internationalising strings
        $theme_text_domain = 'cf7-crm';

        /**
         * Array of configuration settings. Amend each line as needed.
         * If you want the default strings to be available under your own theme domain,
         * leave the strings uncommented.
         * Some of the strings are added into a sprintf, so see the comments at the
         * end of each line for what each argument will be.
         */
        $config = array(
            'domain' => $theme_text_domain, // Text domain - likely want to be the same as your theme.
            'default_path' => '', // Default absolute path to pre-packaged plugins
            'parent_menu_slug' => 'plugins.php', // Default parent menu slug
            'parent_url_slug' => 'plugins.php', // Default parent URL slug
            'menu' => 'install-required-plugins', // Menu slug
            'has_notices' => true, // Show admin notices or not
            'is_automatic' => true, // Automatically activate plugins after installation or not
            'message' => '', // Message to output right before the plugins table
            'strings' => array(
                'page_title' => __('Install Required Plugins', $theme_text_domain),
                'menu_title' => __('Install Plugins', $theme_text_domain),
                'installing' => __('Installing Plugin: %s', $theme_text_domain), // %1$s = plugin name
                'oops' => __('Something went wrong with the plugin API.', $theme_text_domain),
                'notice_can_install_required' => _n_noop('The Contact Form 7 Integrations plugin requires %1$s plugin.  If you already have Contact Form 7 installed, please dismiss this notice.', 'This Contact Form 7 Integrations plugin requires %1$s plugin.  If you already have Contact Form 7 installed, please dismiss this notice.'), // %1$s = plugin name(s)
                'notice_can_install_recommended' => _n_noop('This plugin recommends the following plugin: %1$s.', 'This plugin recommends the following plugin: %1$s.'), // %1$s = plugin name(s)
                'notice_cannot_install' => _n_noop('Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.'), // %1$s = plugin name(s)
                'notice_can_activate_required' => _n_noop('The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.'), // %1$s = plugin name(s)
                'notice_can_activate_recommended' => _n_noop('The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.'), // %1$s = plugin name(s)
                'notice_cannot_activate' => _n_noop('Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.'), // %1$s = plugin name(s)
                'notice_ask_to_update' => _n_noop('The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.'), // %1$s = plugin name(s)
                'notice_cannot_update' => _n_noop('Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.'), // %1$s = plugin name(s)
                'install_link' => _n_noop('Begin installing Contact Form 7', 'Begin installing plugins'),
                'activate_link' => _n_noop('Activate installed plugin', 'Activate installed plugins'),
                'return' => __('Return to Required Plugins Installer', $theme_text_domain),
                'plugin_activated' => __('Plugin activated successfully.', $theme_text_domain),
                'complete' => __('All plugins installed and activated successfully. %s', $theme_text_domain), // %1$s = dashboard link
                'nag_type' => 'updated' // Determines admin notice type - can only be 'updated' or 'error'
            )
        );

        tgmpa($plugins, $config);
    }

    function cf7gs_plugin_action_links($links, $file) {
        $plugin_file = pathinfo(dirname(__FILE__),PATHINFO_BASENAME) . '/cf7-cloud-database.php';
        //make sure it is our plugin we are modifying
        if ($file == $plugin_file) {
            $settings_link = '<a href="' .
                    admin_url('admin.php?page=cf7-crm') . '">Settings</a>';
            array_unshift($links, $settings_link);
        }
        return $links;
    }

    /**
     * create the support link in plugins
     *
     * @param $links
     * @param $file
     * @return array
     */
    function cf7gs_plugin_links($links, $file) {
        $plugin_file = pathinfo(dirname(__FILE__),PATHINFO_BASENAME) . '/cf7-cloud-database.php';
        if ($file == $plugin_file) {
            $links[] = '<a target="_blank" style="color: #42a851; font-weight: bold;" href="http://support.getscorecard.com/">' . __("Get Support", "cus_plugin") . '</a>';
        }
        return $links;
    }

    /**
     * Private method to create the required options in database
     * @params none
     * @return none
     * @since 0.1
     * */
    private function create_cf7_gs_options() {
        // set options to be used along the system
        update_option('cf7_gs_db_version', $this->cf7_gs_db_version);
        update_option('cf7_gs_version', $this->cf7_gs_version);
        update_option('cf7_gs_database_active', 0); // this is to know if user has signup/login to CU API system
    }

    /**
     * Method en charge to create DB tables and version control options
     * @params none
     * @return none
     * @since 0.1
     * */
    public function activate() {
        // Perform any databases modifications related to plugin activation here, if necessary
        require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
        global $wpdb;

        // create plugin in options table.
        $this->create_cf7_gs_options();
    }

    /**
     * Method to deactive the plugin, we will not delete DB tables nor reset options.
     * @params none;
     * @return none;
     * @since 0.1
     */
    public function deactivate() {
        delete_option('cf7_gs_settings_userCredentials');
        delete_option('cf7_gs_settings_form_key');
        delete_option('cf7_gs_database_active');
        delete_option('cf7_gs_settings_userData');
        delete_option('cf7_gs_settings_form_keys');
        delete_option('cf7_gs_db_version');
        delete_option('cf7_gs_version');

        delete_option('cf7_gs_db_version');
        delete_option('cf7_gs_version');
        delete_option('cf7_gs_database_active');  // this is to know if user has signup/login to the system
        // delete dependant plugins flag when deactivate so it is shown again on activate
        delete_user_meta(get_current_user_id(), 'tgmpa_dismissed_notice');
    }

    /*
     * create main menu and its options for CF7 Extension
     * @params none
     * @since 0.1
     * @return html that conforms the menus for the sidebar
     */
    public function cf7_gs_database_menu() {
        if (current_user_can('level_10')) {

            add_menu_page('GetScorecard CRM for Contact Form 7', 'GetScorecard', 0, 'cf7-crm', array($this, 'cf7_gs_settings'), plugins_url('assets/images/admin-icon.png', __FILE__));

            /*$edit = add_submenu_page(
                'cf7-crm',
                'GetScorecard.com',
                'GetScorecard.com',
                0,
                'cf7-crm-redirect',
                array($this, 'cf7_crm_redirect')
            );*/
        }
    }

    public function Load_scripts() {

        global $current_screen; // check we are in our CF7 integrations plugin page
        if ($current_screen->id == 'toplevel_page_cf7-crm' || $current_screen->id == 'toplevel_page_wpcf7') {

            wp_register_script('jquery_validate', plugins_url('assets/js/jquery.validate.min.js', __FILE__));
            wp_register_script('jquery_ajaxloader', plugins_url('assets/js/jquery.ajaxloader.1.5.0.min.js', __FILE__));

            wp_register_script('my-scripts', plugins_url('assets/js/scripts.js', __FILE__));

            wp_enqueue_style('colorbox', plugins_url('includes/colorbox/colorbox.css', __FILE__), false, '1');
            wp_enqueue_style('other_info_styles', plugins_url('assets/css/styles2.css', __FILE__), false, '1');
            wp_enqueue_style('thickbox');

            wp_register_script('other_info_scripts', plugins_url('assets/js/main.js?pluginurl=' . dirname(__FILE__), __FILE__), array('jquery'), '1.0', true);
            wp_register_script('colorbox', plugins_url('includes/colorbox/jquery.colorbox-min.js', __FILE__), array('jquery'), '1.4.1.33', true);

            wp_enqueue_script('jquery_validate');
            wp_enqueue_script('jquery_ajaxloader');
            wp_enqueue_script('my-scripts');
            wp_enqueue_script('other_info_scripts');
            wp_enqueue_script('colorbox');
            wp_enqueue_script('thickbox');
        }
    }

    /**
     * Method in charge to load plugin specific styles
     * @since version 1
     * @params none
     * @return none 
     * */
    public function Load_styles() {
        global $current_screen; // check we are in our CF7 integrations plugin page
        if ($current_screen->id == 'toplevel_page_cf7-crm' || $current_screen->id == 'toplevel_page_wpcf7') {
            wp_enqueue_style('cf7_gs-styles', plugins_url('assets/css/styles.css', __FILE__));
        }
    }

    /*
     * display admin page to manage requisitions
     * @params none
     * @since 0.1
     */
    public function cf7_gs_settings() {
        require_once('controllers/settings.php');
    }

    /**
     * This is the method in charge to create the metabox for integration with Contact Form 7
     * @params none
     * @since 0.1 
     * return null
     * DEPRECATED SINCE 1.4.1
     * */
    public function add_cf7gs_meta() {

        global $wpcf7;

        if (wpcf7_admin_has_edit_cap()) {

            add_meta_box('cf7cf7gsdiv', __('GetScorecard CRM for Contact Form 7', 'wpcf7'), array($this, 'wpcf7_cf7gs_add_getscorecard_analytics'), 'cf7gsdatabase', 'cf7_cf7gs', 'core', array(
                'id' => 'wpcf7-cf7-crm',
                'name' => 'cf7_cf7gs',
                'use' => __('Turn On GetScorecard CRM for Contact Form 7', 'wpcf7')));
        }
    }

    public function show_cf7gs_metabox($cf) {
        do_meta_boxes('cf7gsdatabase', 'cf7_cf7gs', $cf);
    }

    public function getApiModulesFields(){
        /*$apiModulesFields = get_option('cf7_gs_settings_api_fields');
        if(!$apiModulesFields){
            $this->apiClient = new GetScorecardApiClient();
            $apiModulesFields = $this->apiClient->getModulesFields();
            update_option('cf7_gs_settings_api_fields', $apiModulesFields);
        }*/

        $this->apiClient = new GetScorecardApiClient();
        $apiModulesFields = $this->apiClient->getModulesFields();

        return $apiModulesFields;
    }

    public function wpcf7_cf7gs_add_getscorecard_analytics_form_not_exists($args){
        ?>
        <div class="mail-field">
            <div id="cf7gs-formdata">
                <strong>
                    Please save the contact form to enable GetScorecard. After saving, edit the form and return to this tab.
                </strong>
            </div>
        </div>

        <?php
    }

    /**
     * @param $args
     * @throws Exception
     */
    public function wpcf7_cf7gs_add_getscorecard_analytics($args) {

        ?>
        <script>
            //<![CDATA[
            jQuery(document).ready(function() {
                jQuery('body').bind('mousemove', function(e){
                    jQuery(window).off('beforeunload');
                });
            });

            var CF7_GS_ADMIN_AJAX_URL = "<?php echo CF7_GS_ADMIN_AJAX_URL; ?>";
            var template_url = "<?php echo get_bloginfo('template_url'); ?>";
            //]]>
        </script>

        <?php

        // get the custom data for this contact form
        $the_data = get_option('GS_cf7gs_database_data_' . $_GET['post']);
        $the_cf7_fields = get_option('GS_cf7_gs_mapped_fields_' . $_GET['post']);
        $is_active_form = get_option('GS_cf7gs_database_form_' . $_GET['post'] . '_active');

        /* get api modules fields*/
        $apiModulesFields = $this->getApiModulesFields();
        $apiModules = $this->apiClient->formatModules($apiModulesFields);

        $apiFieldsFormatted = array();
        foreach($apiModules as $key => $value){
            $apiFieldsFormatted[$key] = $this->apiClient->formatModuleFields($apiModulesFields,$key);
        }
        /**/


        ?>

        <input type="hidden" name="trcount" id="trcount" value="<?php echo (is_array($the_data['customs']) ? count($the_data['customs']) : 1 ); ?>" />
        <div class="mail-field">

            <div class="cf7gs-active">

                <a href="<?php echo WPGETSCORECARD7_GS_BASE_URL; ?>" target="_blank">Open GetScorecard</a>
                <br>
                <br>

                <!--<input type="checkbox" id="wpcf7-cf7gs-active" name="wpcf7-cf7gs-active" value="1" <?php /*echo ( $is_active_form ) ? "checked" : ""; */?> />-->

                <input type="checkbox" id="wpcf7-cf7gs-active" name="wpcf7-cf7gs-active" <?php echo ( $is_active_form ) ? 'checked="checked"' : ''; ?> />

                <label for="wpcf7-cf7gs-active"><?php echo esc_html(__('Enable GetScorecard for this form', 'wpcf7')); ?></label>
                <a name="cf7gs_errors"></a>
                <?php
                // CF7 cloud errors in fields
                if (isset($_GET['cf7gs_errors'])) {
                    echo('<div class="cf7_gs_errors">' . $_GET['cf7gs_errors'] . '</div>');
                }
                ?>
            </div>

            <div id="cf7gs-formdata" <?php echo ($is_active_form) ? 'style="display:block"' : ""; ?>>
                <input type="submit" name="map_button" id="map_button" class="button-primary" value="Map Form Fields"/>

                <!-- update fields list -->
                <!--<form action="<?php /*echo ADMIN_AJAX_URL; */?>admin-ajax.php" method="post">
                    <input type="hidden" name="action" value="cUsCloud_updateFields">
                    <input type="submit" id="refresh_fields_button" class="button-primary" value="Refresh fields" style="margin-left: 30px;"/>
                </form>-->
                <!-- -->

                <br/>
                <strong>
                    Click here before mapping or editing your mapped fields (Required).
                </strong>
                <hr/>

                <strong>
                    Map your form fields to the GetScorecard fields. Select fields type as either Contact, Sales, Note, Tasks. Contact fields will be used to make the new contact records. Sales, Note, and Task Fields will be consolidated to create a Sales, Note or Task record linked to the contact in GetScorecard.
                </strong>


                <!-- map cf7 fields with getscorecard modules/fields -->
                <table id="cf7_gs_table" <?php echo ($is_active_form) ? 'style="display:block"' : 'style="display:none"'; ?>>
                    <tbody>
                        <tr>
                            <td colspan="3">
                                <!--<h4>
                                   Map your form fields to the GetScorecard fields. Select fields type as either Contact, Sales, Note, Tasks. Contact fields will be used to make the new contact records. Sales, Note, and Task Fields will be consolidated to create a Sales, Note or Task record linked to the contact in GetScorecard.
                                </h4>-->

                                <!--<strong>
                                    Map your form fields to the GetScorecard fields. Select fields type as either Contact, Sales, Note, Tasks. Contact fields will be used to make the new contact records. Sales, Note, and Task Fields will be consolidated to create a Sales, Note or Task record linked to the contact in GetScorecard.
                                </strong>-->
                            </td>
                        </tr>

                        <?php
                        $counter = 1; // counter to create row ids
                        $count_mapper = 0; // variable to compare how many custom fields are being displayed already and not allow more than $total-2

                        /* fix issue with add new fields */
                        $diff = array_diff_key($the_data['customs'],$the_data['customs_modules']);
                        foreach($diff as $dkey => $dval){
                            $the_data['customs_modules'][$dkey] = 'people';
                        }
                        /**/

                        if (isset($the_data['customs']) && is_array($the_data['customs'])) {
                            foreach ($the_data['customs'] as $key => $value) {
                                $moduleValue = $the_data['customs_modules'][$key];
                                ?>
                                <tr id="row_<?php echo $counter; ?>">
                                    <!-- cf 7 fields -->
                                    <td>
                                        Select Contact Form 7 field:<br />
                                        <select name="cf7gs_custom_field_name[]">
                                            <?php
                                            // list the CF7 fields names
                                            foreach ($the_cf7_fields as $xkey => $xvalue) {
                                                if ($xvalue == $key)
                                                    echo('<option value="' . $xvalue . '" selected="selected">' . $xvalue . '</option>');
                                                else {
                                                    echo('<option value="' . $xvalue . '">' . $xvalue . '</option>');
                                                }
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <!-- / cf 7 fields-->

                                    <!-- getscorecard associated field -->
                                    <td id="select associateField">
                                        Select GetScorecard field to associate:<br />

                                        <?php
                                        foreach($apiModules as $mkey => $mvalue){
                                            if ($moduleValue == $mkey) {
                                                $style = '';
                                                $name = 'cf7gs_custom_field_select[]';
                                            } else {
                                                $style = 'display:none;';
                                                $name = 'unused';
                                            }

                                            $isSelected = (count($apiFieldsFormatted[$mkey]) > 1) ? '' : 'selected="selected"';
                                            ?>

                                            <!-- select field for each module -->
                                            <select id="<?php echo 'select_' . $mkey . '_' . $counter; ?>" data-module="<?php echo $mkey; ?>" class="<?php echo 'select_field_' . $counter; ?>" name="<?php echo $name; ?>" style="<?php echo $style; ?>">
                                                <option class="unmapped" value="unmapped">-- Unmapped --</option>

                                                <?php
                                                // list and select current select value
                                                foreach ($apiFieldsFormatted[$mkey] as $skey => $svalue) {
                                                    if ($value == $skey) {
                                                        echo('<option value="' . $skey . '" selected="selected">' . $svalue . '</option>');
                                                    } else {
                                                        echo('<option value="' . $skey . '" ' . $isSelected .'>' . $svalue . '</option>');
                                                    }
                                                }
                                                ?>
                                            </select>
                                            <!-- / select field for each module -->
                                            <?php
                                        }
                                        ?>
                                    </td>
                                    <!-- / GetScorecard associated field -->

                                    <!-- getscorecard associated module -->
                                    <td>
                                        Select Field Type:<br />

                                        <select id="<?php echo 'field_module_' . $counter; ?>" data-row="<?php echo $counter; ?>" class="custom_field_module" name="cf7gs_custom_field_module[]">
                                            <?php foreach($apiModules as $mkey => $mvalue){
                                                if ($moduleValue == $mkey) {
                                                    echo('<option value="' . $mkey . '" selected>' . $mvalue . '</option>');
                                                } else {
                                                    echo('<option value="' . $mkey . '">' . $mvalue . '</option>');
                                                }
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <!-- /getscorecard associated module -->

                                    <td>&nbsp;
                                    </td>
                                </tr>

                                <?php
                                $counter++; // increment the counter for row identification
                                $count_mapper++;
                            } // end foreach
                        }
                        ?>

                        <tr>
                            <td colspan="3" style="text-align: right">
                                <?php if ( current_user_can( 'wpcf7_edit_contact_form', $_GET['post'] ) ) : ?>
                                    <p id="cf7_gs_fields_map_save_changes" class="submit"><?php wpcf7_admin_save_button( $_GET['post'] ); ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- / map cf7 fields with getscorecard modules/fields -->
            </div>
        </div>
        <br class="clear" />

        <?php
    }

    /*
     * Method in charge to get the inputs from the CF7 textarea
     * @params string
     * @since 0.1
     * @returns Array
     */
    private function _get_cf7_inputs($cf7_form) {
        $cf7_shortcodes = preg_match_all('#\[[text|select|checkbox|radio|tel|email|url|number|textarea]\s*.*?\]#s', $cf7_form, $matches);
        $the_values = Array();

        // loop the fields found in CF7 textarea
        foreach ($matches[0] as $key => $value) {
            $the_values[] = explode(" ", $value);
            $the_names[$key] = str_replace(']', '', $the_values[$key][1]);
        }

        // delete the submit button of the end, TODO: hope always is in the end xD otherwise we must change this procedure.
        array_pop($the_names);
        return $the_names;
    }

    /*
     * Method in charge to save the relationships between contact form 7 and GetScorecard
     * @params Array all the actual editing form data being submitted
     * @since 0.1
     * @returns Null
     */
    public function cf7gs_save_form($args) {

        if(!$this->checkPostId($_POST['post_ID'])){
            return true;
        }

        // create an option for the custom_cf7_fields that come
        $cf7_customs = $this->_get_cf7_inputs($args->form);

        $prev_url = $_SERVER["HTTP_REFERER"];

        $error_main = 'The Email and Name cannot have the same CF7  values. Please change one and try again.';
        $error_customs = 'The following CF7 fields were detected duplicates in your selects: ';
        $error_CUapi = 'The following GetScorecard fields were detected as duplicates in your selection: ';
        $string_error = '';

        if ((int) $_POST['post_ID']) {

            $integrationActive = false;
            if($_POST['wpcf7-cf7gs-active'] == 'on'){
                $integrationActive = true;
            }

            // save if this form is active as an option
            //update_option('GS_cf7gs_database_form_' . $_POST['post_ID'] . '_active', 1);
            update_option('GS_cf7gs_database_form_' . $_POST['post_ID'] . '_active', $integrationActive ? 1 : 0);

            $the_data['Full_Name'] = esc_sql($_POST['cf7gs_name']);
            $the_data['Email'] = esc_sql($_POST['cf7gs_email']);

            // *********************************
            // check email and name dont have equal select values
            if (trim($_POST['cf7gs_name']) != '' && trim($_POST['cf7gs_email']) != '') {
                if ((string) $_POST['cf7gs_name'] == (string) $_POST['cf7gs_email']) {
                    header('Location:' . $prev_url . '&cf7gs_errors=' . urlencode($error_main) . '#cf7gs_errors');
                    exit;
                }
            }

            // check if any of the above fields is already selected in custom fields. This is when user changes default field names for name and email.
            if (isset($_POST['cf7gs_custom_field_name']) && in_array($_POST['cf7gs_name'], $_POST['cf7gs_custom_field_name']) ||
                    in_array($_POST['cf7gs_email'], $_POST['cf7gs_custom_field_name'])){
                header('Location:' . $prev_url . '&cf7gs_errors=' . urlencode($error_main) . '#cf7gs_errors');
                exit;
            }

            // ***********************************
            // prefilter here to see which ones are to be unmapped
            foreach ($_POST['cf7gs_custom_field_select'] as $key => $value) {
                if ($value == 'unmapped')
                    $_POST['cf7gs_custom_field_select'][$key] = 'unmappedCUAPI_' . $key;
                //echo $value . "\n";
            }

            // **************************
            // THIS IS TO AVOID DUPLICATES IN CF7 FIELDS.
            //$counts = array_count_values( $_POST['cf7gs_custom_field_name'] );
            $cf7_customs_duplicate = array_flip(array_filter(array_count_values($_POST['cf7gs_custom_field_name']), create_function('$x', 'return $x > 1; ')));

            // check if duplicates for cf7 customs
            if (!empty($cf7_customs_duplicate)) {
                foreach ($cf7_customs_duplicate as $key => $value)
                    $string_error .= urlencode($value . ', ');
                header('Location:' . $prev_url . '&cf7gs_errors=' . urlencode($error_customs) . urlencode($string_error) . urlencode(' only one field for relationship allowed') . "#cf7gs_errors");
                exit;
            }

            // **************************
            // THIS IS TO AVOID DUPLICATES IN CUAPI FIELDS.
            $modulesFieldsCombined = array();
            $modules = $_POST['cf7gs_custom_field_module'];
            $fields = $_POST['cf7gs_custom_field_select'];

            if(count($modules) === count($fields)){
                for($i=0; $i<count($modules); $i++){
                    $modulesFieldsCombined[$i] = $modules[$i] . '_' . $fields[$i];
                }
            }
            else{
                header('Location:' . $prev_url . '&cf7gs_errors=' . urlencode(' Modules and fields quantity mismatch.') . "#cf7gs_errors");
                exit;
            }

            $counts = array_count_values($modulesFieldsCombined);
            $moreThenOne = create_function('$x', 'return $x > 1; ');
            $cf7_CUapi_duplicate = array_flip(array_filter($counts, $moreThenOne));
            /**/

            //$cf7_CUapi_duplicate = array_flip(array_filter(array_count_values($_POST['cf7gs_custom_field_select']), create_function('$x', 'return $x > 1; ')));

            // check if duplicates for cf7 customs
            if (!empty($cf7_CUapi_duplicate)){
                $duplicatesAllowedModules = array('opportunities','notes');

                foreach ($cf7_CUapi_duplicate as $key => $value){
                    $string_error .= urlencode($value . ', ');

                    //allow duplicates for specified modules (use fields concatenation)
                    $module = explode('_',$value);
                    $module = $module[0];
                    if(!in_array($module,$duplicatesAllowedModules)){
                        header('Location:' . $prev_url . '&cf7gs_errors=' . $error_CUapi . $string_error . urlencode(' Please try again.') . "#cf7gs_errors");
                        exit;
                    }
                }
            }

            /* check if required fields mapped */
            /*$requiredFields = array('people_lastname');
            $diff = array_diff($requiredFields,$modulesFieldsCombined);

            if(!empty($diff)){
                $error_string_required = 'The required fields ';

                foreach($diff as $item){
                    $error_string_required .= $item . ', ';
                }

                $error_string_required = trim($error_string_required,',');
                $error_string_required .= ' is not mapped';

                header('Location:' . $prev_url . '&cf7gs_errors=' . $error_string_required . "#cf7gs_errors");
                exit;
            }*/
            /**/

            $the_data = array(); // array to store data as option for each form.

            $the_data['Full_Name'] = esc_sql($_POST['cf7gs_name']);
            $the_data['Email'] = esc_sql($_POST['cf7gs_email']);

            // ***********************************************
            // check first if custom fields have been created
            if (isset($_POST['cf7gs_custom_field_name']) && isset($_POST['cf7gs_custom_field_select'])) {

                //print_r( get_option('GS_cf7_gs_mapped_fields_'.$_POST['post_ID'] ) ); exit;
                foreach ($_POST['cf7gs_custom_field_name'] as $xkey => $xvalue) {
                    //if( $xvalue != 'your-name' && $xvalue != 'your-email' ){
                    $the_data['customs'][$xvalue] = ( isset($_POST['cf7gs_custom_field_select'][$xkey]) ? esc_sql($_POST['cf7gs_custom_field_select'][$xkey]) : '' );

                    $the_data['customs_modules'][$xvalue] = ( isset($_POST['cf7gs_custom_field_module'][$xkey]) ? esc_sql($_POST['cf7gs_custom_field_module'][$xkey]) : '' );
                    //$field_count++;
                    //}
                }

                // check to see if no other fields have been added to CF7 textarea
                $simplify = array();
                $p = get_option('GS_cf7gs_database_data_' . $_POST['post_ID']);
                $pc = $p['customs'];

                // current data stored in database array
                foreach ($p as $item_id => $item_value) {
                    if (!is_array($item_value))
                        $simplify[] = $item_value;
                }

                // current customs stored in database array
                foreach ($pc as $item_id => $item_value) {
                    $simplify[] = $item_id;
                }

                // get the number of fields in CF7 textarea
                $cf7_fields_quantity = count($cf7_customs);
                $actual_custom_amount = count($simplify);

                // ***************************************
                // if actual quantity of stored custom fields is not the same as the ones comming, some where added or deleted.
                if ((int) $cf7_fields_quantity != (int) $actual_custom_amount) {

                    // check for fields to be deleted in stack
                    $to_delete = array();
                    foreach ($simplify as $key => $value) {
                        if (!in_array($value, $cf7_customs))
                            $to_delete[] = $value;
                    }

                    // check for fields to be added to stack
                    $to_add = array();
                    foreach ($cf7_customs as $key => $value) {
                        if (!in_array($value, $simplify))
                            $to_add[] = $value;
                    }

                    // delete the erased fields
                    foreach ($to_delete as $key => $value) {
                        if (array_key_exists($value, $the_data['customs']))
                            unset($the_data['customs'][$value]);
                    }

                    // add the new fields
                    foreach ($to_add as $key => $value) {
                        if (!array_key_exists($value, $the_data['customs']))
                            $the_data['customs'][$value] = '';
                    }
                }
            }else { // ***** this else will create the customs fields for the first time ********
                foreach ($cf7_customs as $xkey => $xvalue) {
                    $the_data['customs'][$xvalue] = $xvalue;
                    $the_data['customs_modules'][$xvalue] = 'people';
                }
            }

            update_option('GS_cf7gs_database_data_' . $_POST['post_ID'], $the_data);
            update_option('GS_cf7_gs_mapped_fields_' . $_POST['post_ID'], $cf7_customs);
            update_option('GS_cf7gs_database_data_' . $_POST['post_ID'] . '_amount', count($cf7_customs)); // the number of actual stored custom fields.
        } else {
            // update this form to inactive
            update_option('GS_cf7gs_database_form_' . $_POST['post_ID'] . '_active', 0);
            delete_option('GS_cf7gs_database_data_' . $_POST['post_ID']); // deleted fields to avoid any conflict when mapping
        }
    }

    /*
     * This is the method in charge to pre-process CF7 submitted data
     * @params none
     * @since 0.1
     * @returns void
     */
    public function wpcf7_gs_send_all(){
        /* Use WPCF7_Submission object's get_posted_data() method to get it. */
        $submission = WPCF7_Submission::get_instance();

        if ($submission) {
            $posted_data = $submission->get_posted_data();
        }

        // get the option for this specific form and see which fields to send to CU API
        $cf7gs_data = get_option('GS_cf7gs_database_data_' . $posted_data['_wpcf7']);
        // get if this form is active to send data to getscorecard.com
        $is_active = get_option('GS_cf7gs_database_form_' . $posted_data['_wpcf7'] . '_active');

        // **************************
        // check for unmapped fields and delete from array that is used to send to CU API
        $cf7gs_data['customs'] = $this->_clear_unmapped($cf7gs_data['customs']);

        // **************************************
        // first check if this form has any Analytics associated
        if ($cf7gs_data && is_array($cf7gs_data) && $is_active && $this->apiClient->authorized){
            $this->apiClient->processCF7FormData($cf7gs_data,$posted_data);
        }
    }

    /*
     * This method is in charge to clear the unmapped fields from the array
     * @params Array customs fields to check for unmapped
     * @since 0.1
     * @return Array with unmapped field unset
     */
    private function _clear_unmapped($customs) {

        foreach ($customs as $key => $value) {
            if (strpos($value, 'unmappedCUAPI') !== FALSE)
                unset($customs[$key]); // delete element from array to avoid sending it to CU Api.
        }

        return $customs;
    }
}
// end class definition

/* CF7 Cloud Database loader  */
$CF7_gs_loader = new CF7_gs_loader();