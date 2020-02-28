<?php

namespace Statamic;

class Exporter
{
    const PREFIX = 'statamic-json';

    protected $filename;
    protected $file;
    protected $collections = array();
    protected $pages       = array();
    protected $settings    = array();
    protected $taxonomies  = array();

    public function __construct()
    {
        $this->filename = 'statamic_' . date('Ymd_His') . '.json';
        $this->file     = ABSPATH . '/' . static::PREFIX . '/' . $this->filename;
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
            $author = null;

            if ($post->post_author) {
                $author = get_userdata($post->post_author)->user_login;
            }

            $this->collections[$slug]["/{$slug}/" . $post->post_name] = array(
                'order' => date("Y-m-d", strtotime($post->post_date)),
                'data'  => array(
                    'title'   => $post->post_title,
                    'content' => wpautop($post->post_content),
                    'author'  => $author,
                    'categories' => wp_list_pluck(get_the_category($post->ID), 'slug'),
                    'tags'       => wp_list_pluck(get_the_tags($post->ID), 'slug'),
                ),
            );

            foreach ($this->metadata('post', $post) as $key => $meta) {
                $this->collections[$slug]["/{$slug}/" . $post->post_name]['data'][$key] = reset($meta);
            }
        }
    }

    private function setPages()
    {
        $pages = get_posts(array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => -1
        ));

        foreach ($pages as $page) {
            $this->pages['/' . $page->post_name] = array(
                'order' => $page->menu_order,
                'data'  => array(
                    'title'        => $page->post_title,
                    'content'      => $page->post_content,
                ),
            );

            foreach ($this->metadata('post', $page) as $key => $meta) {
                $this->pages['/' . $page->post_name]['data'][$key] = reset($meta);
            }
        }
    }

    private function setTaxonomies()
    {
        $categories = get_categories(array('hide_empty' => false));
        $tags       = get_tags(array('hide_empty' => false));

        $this->taxonomies['categories'] = $this->mapWithKeys($categories, function ($category) {
            return array($category->slug => array('title' => $category->name));
        });

        $this->taxonomies['tags'] = $this->mapWithKeys($tags, function ($tag) {
            return array($tag->slug => array('title' => $tag->name));
        });
    }

    private function setSettings()
    {
        $this->settings['site_url']  = get_option( 'siteurl' );
        $this->settings['site_name'] = get_bloginfo('name');
        $this->settings['timezone']  = ini_get('date.timezone');
    }

    private function json()
    {
        $content = array();

        if (! empty($this->collections)) {
            $content['collections'] = $this->collections;
        }

        if (! empty($this->pages)) {
            $content['pages'] = $this->pages;
        }

        if (! empty($this->taxonomies)) {
            $content['taxonomies'] = $this->taxonomies;
        }

        if (! empty($this->settings)) {
            $content['settings'] = $this->settings;
        }

        return json_encode($content, JSON_PRETTY_PRINT);
    }

    public function export()
    {
        $this->createExportDirectory();

        $handle = fopen($this->file, 'w') or die('fail');

        fwrite($handle, $this->json());

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
    }

    private function createExportDirectory()
    {
        if (file_exists(ABSPATH . static::PREFIX)) {
            return;
        }

        mkdir(ABSPATH . static::PREFIX, 0777, true);
    }

    private function metadata($type, $post)
    {
        if (! $metadata = get_metadata($type, $post->ID)) {
            return array();
        }

        if ($featuredImageUrl = get_the_post_thumbnail_url($post->ID)) {
            $metadata['featured_image_url'] = [$featuredImageUrl];
        }

        return $metadata;
    }

    private function mapWithKeys($array, $callable)
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
}
