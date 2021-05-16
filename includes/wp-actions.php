<?php
    /**
     * WordPress actions namespace
     */
    namespace Theme\Actions;

    /** Enable post feature image */
    function enable_post_thumbnails(){
        add_theme_support( 'post-thumbnails' );
    }

    add_action('after_setup_theme', 'Theme\Actions\enable_post_thumbnails');

    /** show menus under appearance section on WP admin */
    function register_my_menu() {
        register_nav_menu('header-menu',__( 'Header Menu' ));
    }

    add_action('init', 'Theme\Actions\register_my_menu');


    /** Set with of Gutenberg block editor */
    function gutenberg_admin_styles(){
        echo '<style>
            .wp-block {
                max-width: calc(100% - 80px);

            }
        </style>';
    }

    add_action('admin_head', 'Theme\Actions\gutenberg_admin_styles');

    /** Add Reusable blocks to WP administration navigation */
    add_action('admin_menu', 'Theme\Actions\blocks_menu_item');

    function blocks_menu_item(){
        add_menu_page(
            'Reusable Block',
            'Reusable Blocks',
            'edit_posts',
            'edit.php?post_type=wp_block',
            '',
            'dashicons-editor-insertmore',
            59
        );
    }

    /** Redirect WP search to /search */
    function new_search_url() {
        if (is_search() && ! empty($_GET['s'])){
            wp_redirect(home_url("/search/").urlencode(get_query_var('s')));
            exit();
        }
    }

    add_action('template_redirect', 'Theme\Actions\new_search_url');
?>