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
?>