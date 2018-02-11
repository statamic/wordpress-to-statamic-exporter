<?php

/**
 * Plugin Name: Statamic JSON Export
 * Description: Export all the Wordpress data to be imported into Statamic.
 * Version:     0.1.0
 * Author:      Statamic
 * Author URI:  https://statamic.com
 */

add_action( 'admin_menu', 'statamic_json_export_register_menu' );

function statamic_map_with_keys($array, $callable)
{
    return array_reduce(
        $array,
        function ($collection, $item) use ($callable) {
            $result = $callable($item);

            $collection[key($result)] = reset($result);

            return $collection;
        },
        array()
    );
}

function statamic_request_input($key, $default = null)
{
    if (! isset($_POST[$key])) {
        return $default;
    }

    return $_POST[$key];
}

function statamic_json_export_register_menu() {
    add_submenu_page( 'tools.php', 'Export Statamic JSON', 'Export Statamic JSON', 'manage_options', 'export-statamic-json', 'statamic_json_export_view' );
}

function statamic_json_export_view() {
    // Query all the custom post types.
    $postTypes = get_post_types([
        '_builtin' => false,
    ], 'object');

    // Query all the authors of the said posts.
    require_once __DIR__ . '/form.php';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
}

add_action( 'admin_init', 'statamic_json_export_run' );

function statamic_json_export_run() {
    if ( ! current_user_can('export') ) {
        wp_die( 'You do not have sufficient permissions to export the content of this site.' );
    }

    require_once __DIR__ . '/Exporter.php';

    (new Statamic\Exporter)
        ->content(statamic_request_input('content', array()))
        ->customPostTypes(statamic_request_input('post_types', array()))
        ->export()
        ->download();
}

?>

