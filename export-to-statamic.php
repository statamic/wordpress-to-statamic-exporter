<?php

/**
 * Plugin Name: Export to Statamic
 * Description: Export all the Wordpress data to be imported into Statamic.
 * Version:     0.1.0
 * Author:      Statamic
 * Author URI:  https://statamic.com
 */

add_action( 'admin_menu', 'statamic_export_register_menu' );

function statamic_request_input($key, $default = null)
{
    if (! isset($_POST[$key])) {
        return $default;
    }

    return $_POST[$key];
}

function statamic_export_register_menu() {
    add_submenu_page( 'tools.php', 'Export to Statamic', 'Export to Statamic', 'manage_options', 'export-to-statamic', 'statamic_export_view' );
}

function statamic_export_view() {
    // Query all the custom post types.
    $postTypes = get_post_types([
        '_builtin' => false,
    ], 'object');

    // Query all the authors of the said posts.
    require_once __DIR__ . '/form.php';
}

add_action(
    'admin_post_statamic_export_run',
    'statamic_export_run_admin_action'
);

function statamic_export_run_admin_action() {
    if ( ! current_user_can('export') ) {
        wp_die( 'You do not have sufficient permissions to export the content of this site.' );
    }

    require_once __DIR__ . '/Exporter.php';

    (new Statamic\Exporter)
        ->content(statamic_request_input('content', array()))
        ->customPostTypes(statamic_request_input('post_types', array()))
        ->export()
        ->download();

    exit;
}

?>
