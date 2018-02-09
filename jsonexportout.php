<?php

require_once( $_POST['pathname'].'/wp-admin'. '/admin.php' );

if ( ! current_user_can('export') ) {
	wp_die( 'You do not have sufficient permissions to export the content of this site.' );
}

$title          = __('Export');
$postsData      = array();
$pagesData      = array();
$get_categories = array();
$get_tags       = array();
$json           = array();

if (isset($_POST['settings'])) {
	$json["settings"]["site_url"]  = get_option( 'siteurl' );
	$json["settings"]["site_name"] = get_bloginfo('name');
	$json["settings"]["timezone"]  = ini_get('date.timezone');
}

if (isset($_POST['taxonomies'])) {
    $get_categories = get_categories( array('hide_empty'   => 0));
    $get_tags = get_tags( array('hide_empty'   => 0));
}

if (!empty($get_categories)) {
	foreach ($get_categories as $key => $value) {
		$category[] = $value->name;
	}

	$json["taxonomies"]["categories"] = $category;
}

if (!empty($get_tags)) {
	foreach ($get_tags as $key => $value) {
		$tags[] = $value->name;
	}

	$json["taxonomies"]["tags"] = $tags;
}

if (isset($_POST['post'])) {
	$postsData = get_posts(array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => -1
	));
}

if (isset($_POST['page'])) {
    $pagesData = get_posts(array(
        'post_type'   => 'page',
        'post_status' => 'publish',
        'posts_per_page' => -1
    ));
}

if (!empty($postsData)) {
    foreach ($postsData as $key => $value) {
        $metadata = get_metadata('post', $value->ID);

        $json['collections']['posts']['/posts/'.$value->post_name]['order'] = date("Y-m-d",strtotime($value->post_date));
        $json['collections']['posts']['/posts/'.$value->post_name]['data']['title'] = $value->post_title;
        $json['collections']['posts']['/posts/'.$value->post_name]['data']['content'] = $value->post_content;

        if (!empty($metadata)) {
            foreach ($metadata as $subkey => $subvalue) {
                $json['collections']['posts']['/posts/'.$value->post_name]['data'][$subkey] = $subvalue[0];
            }
        }

    }
}
if (!empty($pagesData)) {
foreach ($pagesData as $key => $value) {
	$metadata = get_metadata('page', $value->ID);
	$json['pages']['/'.$value->post_name]['order'] = $value->menu_order;
	$json['pages']['/'.$value->post_name]['data']['title'] = $value->post_title;
	$json['pages']['/'.$value->post_name]['data']['content'] = $value->post_content;
	if (!empty($metadata)) {
		foreach ($metadata as $subkey => $subvalue) {
		$json['pages'][$value->post_name]['data'][$subkey] = $subvalue[0];
		}
	}
}
}

if (!file_exists($_POST['pathname'].'json_file/')) {
    mkdir($_POST['pathname'].'json_file/', 0777, true);
}
$date = date('Ymd');
$time = date('His');
$filename = "post_".$date."_".$time.".json";

$file=$_POST['pathname'].'/json_file/'.$filename;
$fp = fopen($file, 'w') or die('fail');
fwrite($fp, json_encode($json,JSON_PRETTY_PRINT));
$fullPath = $file;
header("Content-Type: application/octet-stream");
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=$filename");
 ob_clean();
 flush();
readfile($fullPath);

exit;
