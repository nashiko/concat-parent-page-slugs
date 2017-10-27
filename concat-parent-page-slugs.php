<?php
/*
Plugin Name: Concat Parent Page Slugs
Plugin URI: http://uniation.jp/wp-plugins/concat-parent-page-slugs/
Description: Add template file loading rule for 'Pages'. When loading the template of the child page, preferentially loading the template file that concatenated parent page slugs.
Version: 0.5.1
Author: nashiko
Author URI: http://uniation.jp
License: GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: concat-parent-page-slugs
Domain Path: /languages

{Plugin Name} is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

{Plugin Name} is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with {Plugin Name}. If not, see {License URI}.
*/

define('WP_CPPS_PLUGIN_DIR', plugin_dir_path( __FILE__ ));

require_once WP_CPPS_PLUGIN_DIR . 'includes/config.php';
require_once WP_CPPS_PLUGIN_DIR . 'includes/option.php';

class ConcatParentPageSlugs
{
    /**
     * @var array
     * CPPS_Option::get()
     */
    private $option;

    /**
     * @var CPPS_Admin
     */
    private $admin_page;

    public function __construct()
    {
        $this->option = CPPS_Option::get();

        if ((int)$this->option['use_pages']) {
            add_filter('page_template_hierarchy', array($this, 'page_template_hierarchy'));
        }

        if ((int)$this->option['use_single']) {
            add_filter('single_template_hierarchy', array($this, 'single_template_hierarchy'));
        }


        if(is_admin()) {
            load_plugin_textdomain(CPPS_Config::DOMAIN, false, basename(dirname(__FILE__)) . '/languages');
            add_action('admin_init', array($this, 'admin_init'));
            add_action('admin_menu', array($this, 'add_options_page'));

            register_uninstall_hook(__FILE__, array(self::class, 'uninstall'));
        }
    }


    /**
     * init admin
     */
    public function admin_init()
    {
        require_once WP_CPPS_PLUGIN_DIR . 'admin/admin.php';
        $this->admin_page = new CPPS_Admin();
    }


    /**
     * add admin menu
     */
    public function add_options_page()
    {
        add_options_page(
            'Concat Parent Page Slugs',
            'Concat Parent Page Slugs',
            'manage_options',
            CPPS_Config::NAME,
            array($this, 'create_admin_page')
        );
    }


    public function create_admin_page()
    {
        $this->admin_page->create();
    }


    /**
     * page_template_hierarchy hook
     *
     * @param $templates
     * @return mixed
     */
    public function page_template_hierarchy($templates)
    {
        $ans = $this->get_post_ancestors();
        $slugs = $ans['slugs'];
        $ids = $ans['ids'];

        $delimiter = esc_attr($this->option['delimiter']);

        if (!empty($ids)) {
            array_unshift($templates, 'page-' . implode($delimiter, $ids) . '.php');
        }

        if (!empty($slugs)) {
            array_unshift($templates, 'page-' . implode($delimiter, $slugs) . '.php');
        }

        return $templates;
    }


    /**
     * single_template_hierarchy hook
     *
     * @param $templates
     * @return mixed
     */
    public function single_template_hierarchy($templates)
    {
        $ans = $this->get_post_ancestors();
        $slugs = $ans['slugs'];

        $object = get_queried_object();
        $delimiter = esc_attr($this->option['delimiter']);

        if (!empty($slugs)) {
            array_unshift($templates, 'single-' . $object->post_type . '-' . implode($delimiter, $slugs) . '.php');
        }

        return $templates;
    }


    /**
     * @return array
     */
    private function get_post_ancestors()
    {
        $id = get_queried_object_id();
        $post = get_post();

        $ids = [];
        $slugs = [];

        if (!empty($id) && !empty($post) && $post->ancestors) {
            foreach (array_reverse($post->ancestors) as $ancestor_id) {
                /** @var WP_Post $ancestor_post */
                $ancestor_post = get_post($ancestor_id);
                $ancestor_page_name = $ancestor_post->post_name;
                $ancestor_id = $ancestor_post->ID;

                if ($ancestor_page_name) {
                    $ancestor_page_name_decoded = urldecode($ancestor_page_name);
                    if ($ancestor_page_name_decoded !== $ancestor_page_name) {
                        $slugs[] = $ancestor_page_name_decoded;
                    }
                    $slugs[] = $ancestor_page_name;
                }

                if ($ancestor_id) {
                    $ids[] = $ancestor_id;
                }
            }


            // current
            $template = get_page_template_slug();
            $pagename = get_query_var('pagename');

            if (!$pagename && $id) {
                // If a static page is set as the front page, $pagename will not be set. Retrieve it from the queried object
                $p = get_queried_object();
                if ($p) $pagename = $p->post_name;
            }

            if ($template && 0 === validate_file($template)) {
                $slugs[] = $template;
            }

            if ($pagename) {
                $pagename_decoded = urldecode($pagename);
                if ($pagename_decoded !== $pagename) {
                    $slugs[] = $pagename_decoded;
                }
                $slugs[] = $pagename;
            }

            if ($id) $ids[] = $id;

        }

        return array(
            'ids' => $ids,
            'slugs' => $slugs
        );
    }


    /**
     * uninstall plugin
     */
    public static function uninstall()
    {
        CPPS_Option::delete();
    }
}

new ConcatParentPageSlugs();
