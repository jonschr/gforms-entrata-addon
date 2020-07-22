<?php
/*
	Plugin Name: Gravity Forms Entrata Addon
	Plugin URI: https://elod.in
    Description: Just another plugin to add Entrata feeds to Gravity Forms.
	Version: 1.0
    Author: Jon Schroeder
    Author URI: https://elod.in

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.
*/

//* Documentation: https://www.entrata.com/api/v1/documentation/sendLeads
//* Request URL is here: https://cardinal.entrata.com/api/v1/leads
//* Test URL is viewable here: https://pipedream.com/sources/dc_Pnu3Q6
//* Test URL endpoint: https://138ed8bde199a4f69a5abd4fc82b86f1.m.pipedream.net

/* Prevent direct access to the plugin */
if ( !defined( 'ABSPATH' ) ) {
    die( "Sorry, you are not allowed to access this page directly." );
}

// Plugin directory
define( 'GFORMS_ENTRATA_ADDON', dirname( __FILE__ ) );

// Define the version of the plugin
define( 'GFORMS_ENTRATA_ADDON_VERSION', '0.1' );

add_action( 'gform_loaded', array( 'GF_Simple_Feed_AddOn_Bootstrap', 'load' ), 5 );

class GF_Simple_Feed_AddOn_Bootstrap {

	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}

		require_once( 'lib/class-entrata-addon-feed.php' );

		GFAddOn::register( 'GFEntrataFeedAddon' );
	}

}

function gf_simple_feed_addon() {
	return GFEntrataFeedAddon::get_instance();
}