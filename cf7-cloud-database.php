<?php

/**
 * Plugin Name: GetScorecard CRM for Contact Form 7
 * Plugin URI: https://wordpress.org/plugins/getscorecard-crm-for-contact-form-7/
 * Description: This plugin extends Contact From 7 into a full CRM with a GetScorecard cloud based CRM. Supported and maintained by GetScorecard.com
 * Author: http://www.getscorecard.com/
 * Author URI: http://www.getscorecard.com/
 * Version: 1.0.6
 * Stable tag: 1.0.6
 * License: GPLv2 or later
 * */
/*
  Copyright 2015 getscorecard.com  ( getscorecard.com )
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

//DEBUG MODE OFF
error_reporting(0);
error_reporting(E_ERROR);

// configuration directives
require_once(dirname(__FILE__) . '/config.php');

// Ajax controllers
require_once(dirname(__FILE__) . '/controllers/ajax_response.php');

// require initialization file
require_once (dirname(__FILE__) . '/cf7_gs_loader.php');

// auth controllers
require_once(dirname(__FILE__) . '/controllers/auth.php');

// register the hooks
register_activation_hook(__FILE__, 'cf7_gs_activate');
register_deactivation_hook(__FILE__, 'cf7_gs_deactivate');

/**
 * Activate Initialization function, called in plugin file
 * */
function cf7_gs_activate() {
    $loader = new CF7_gs_loader();
    $loader->activate();
}

/**
 * Deactivate Initialization function, called in plugin file
 * */
function cf7_gs_deactivate() {
    $loader = new CF7_gs_loader();
    $loader->deactivate();
}