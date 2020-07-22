<?php
/*
	Plugin Name: Gravity Forms Entrata Addon
	Plugin URI: https://elod.in
    Description: Just another plugin to add Entrata feeds to Gravity Forms.
	Version: 1.0.1
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

//* Entrata API documentation: https://www.entrata.com/api/v1/documentation/sendLeads
//* Gforms sample feeds addon: https://github.com/gravityforms/simpleaddon
//* Gforms documentation: https://docs.gravityforms.com/gffeedaddon/

/* Prevent direct access to the plugin */
if ( !defined( 'ABSPATH' ) ) {
    die( "Sorry, you are not allowed to access this page directly." );
}

// Plugin directory
define( 'GFORMS_ENTRATA_ADDON', dirname( __FILE__ ) );

// Define the version of the plugin
define( 'GFORMS_ENTRATA_ADDON_VERSION', '1.0.1' );

add_action( 'gform_loaded', array( 'GF_Entrata_AddOn_Bootstrap', 'load' ), 5 );

class GF_Entrata_AddOn_Bootstrap {

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

//* Add the updater
require 'vendor/plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/jonschr/gforms-entrata-addon',
	__FILE__,
	'gforms-entrata-addon'
);

// Optional: Set the branch that contains the stable release.
$myUpdateChecker->setBranch('master');