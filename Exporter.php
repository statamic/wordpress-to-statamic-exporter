<?php

namespace Statamic;

class Exporter
{
    const PREFIX = 'statamic-json';

    protected $content   = array();

    protected $filename;

    protected $file;

    public function __construct()
    {
        $timestamp = date('Ymd_His');

        $this->filename  = 'statamic_' . date('Ymd_His') . '.json';
        $this->file = ABSPATH . '/' . static::PREFIX . '/' . $this->filename;
    }

    public function content($types)
    {
        array_walk($types, function ($type) {
            call_user_func([$this, 'set' . ucfirst($type)]);
        });

        return $this;
    }

    public function customPostTypes($postTypes)
    {
        array_walk($postTypes, function ($type) {
            $this->setPosts($type);
        });

        return $this;
    }

    private function setPosts($type = 'post')
    {
        $postType = get_post_type_object($type);
        $slug     = $postType->name;

        if ($postType->rewrite) {
            $slug = $postType->rewrite['slug'];
        }

        $posts = get_posts(array(
            'post_type'      => $postType->name,
            'post_status'    => 'publish',
            'posts_per_page' => -1
        ));

        foreach ($posts as $post) {
            $metadata = get_metadata('post', $post->ID);

            $this->content['collections'][$slug]["/{$slug}/" . $post->post_name]['order']           = date("Y-m-d",strtotime($post->post_date));
            $this->content['collections'][$slug]["/{$slug}/" . $post->post_name]['data']['title']   = $post->post_title;
            $this->content['collections'][$slug]["/{$slug}/" . $post->post_name]['data']['content'] = $post->post_content;

            $this->content['collections'][$slug]["/{$slug}/" . $post->post_name]['categories'] = array_map(function ($category) {
                return $category->slug;
            }, get_the_category($post->ID));


            if (! $metadata) {
                continue;
            }

            foreach ($metadata as $i => $meta) {
                $this->content['collections'][$slug]["/{$slug}/" . $post->post_name]['data'][$i] = $meta[0];
            }
        }
    }

    private function setTaxonomies()
    {
        $this->content['taxonomies']['categories'] = array_map(function ($category) {
            return $category->name;
        }, get_categories(array('hide_empty' => 0)));

        $this->content['taxonomies']['tags'] = array_map(function ($tag) {
            return $tag->name;
        }, get_tags(array('hide_empty' => 0)));
    }

    private function setSettings()
    {
        $this->content['settings']['site_url']  = get_option( 'siteurl' );
        $this->content['settings']['site_name'] = get_bloginfo('name');
        $this->content['settings']['timezone']  = ini_get('date.timezone');
    }

    private function setPages()
    {
        $pages = get_posts(array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => -1
        ));

        foreach ($pages as $key => $page) {
            $metadata = get_metadata('page', $page->ID);

            $this->content['pages']['/' . $page->post_name]['order']           = $page->menu_order;
            $this->content['pages']['/' . $page->post_name]['data']['title']   = $page->post_title;
            $this->content['pages']['/' . $page->post_name]['data']['content'] = $page->post_content;

            if (! $metadata) {
                continue;
            }

            foreach ($metadata as $subkey => $subpage) {
                $this->content['pages'][$page->post_name]['data'][$subkey] = $subpage[0];
            }
        }
    }

    public function export()
    {
        $this->createExportDirectory();

        $handle = fopen($this->file, 'w') or die('fail');

        fwrite($handle, json_encode($this->content, JSON_PRETTY_PRINT));

        return $this;
    }

    public function download()
    {
        header("Content-Type: application/octet-stream");
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename={$this->filename}");

        ob_clean();

        flush();

        readfile($this->file);

        exit; // We have to exit to avoid adding the markup to the file.
    }

    private function createExportDirectory()
    {
        if (file_exists(ABSPATH . static::PREFIX)) {
            return;
        }

        mkdir(ABSPATH . static::PREFIX, 0777, true);
    }
}
