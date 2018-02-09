<?php

/**
 * Plugin Name: Statamic JSON Export
 * Description: Export all the Wordpress data to be imported into Statamic.
 * Version:     1.0
 * Author:      Statamic
 * Author URI:  https://statamic.com
 */

add_action( 'admin_menu', 'statamic_json_export_register_menu' );

function statamic_json_export_register_menu() {
    add_submenu_page( 'tools.php', 'Export Statamic JSON', 'Export Statamic JSON', 'manage_options', 'export-statamic-json', 'statamic_json_export_view' );
}

function statamic_json_export_view() {
    // Query all the custom post types.
    // Query all the authors of the said posts.
    require_once __DIR__ . '/form.php';
}
?>

