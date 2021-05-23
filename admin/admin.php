<?php
    /** Theme namespace */
    namespace Theme\Admin;
    use Theme as Theme;

    // Load administration area script and styles
    function load_script_styles(){

        wp_enqueue_style(
            'theme_admin_style',
            Theme\Functions\return_file_uri_path(__DIR__.'/admin.css'),
            '',
            filectime(__DIR__.'/admin.css')
        );

        wp_enqueue_script(
            'theme_admin_script',
            Theme\Functions\return_file_uri_path(__DIR__.'/admin.js'),
            '',
            filectime(__DIR__.'/admin.js'),
            true
        );
    }

    add_action('admin_enqueue_scripts', 'Theme\Admin\load_script_styles');

    // Add additional meta fields
    function additional_page_meta_fields(){
        add_meta_box(
            'additional_page_meta_fields',
            __( 'Additional Post Meta' ),
            'Theme\Admin\additional_page_meta_fields_box',
            ['page', 'post'],
            'side',
            'low'
        );
    }

    add_action('add_meta_boxes', 'Theme\Admin\additional_page_meta_fields');

    // Render additional meta fields
    function additional_page_meta_fields_box($post){

        // Use nonce for verification
        wp_nonce_field('additional_page_meta_fields_nonce', 'additional_page_meta_fields_noncename');

        // Load template class
        $brace = new \brace\parser;
        $brace->template_path = __DIR__.'/templates/';

        // Render additional fields
        $brace->parse('additional-fiels',[
            'checked' => get_post_meta($post->ID, 'post_is_static', true)
        ]);
    }


    // Update meta on post save
    function save_post_meta($post_id){

        // verify if this is an auto save routine.
        // If it is our form has not been submitted, so we don't want to do anything
        if(defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE){
            return;
        }

        // verify this came from the our screen and with proper authorization,
        // because save_post can be triggered at other times
        if (!wp_verify_nonce($_POST['additional_page_meta_fields_noncename'], 'additional_page_meta_fields_nonce' )){
            return;
        }

        if (isset($_POST['post_is_static'])){
            update_post_meta($post_id, 'post_is_static', $_POST['post_is_static']);
        } else {
            update_post_meta($post_id, 'post_is_static', 'un_checked');
        }

    }

    add_action('save_post', 'Theme\Admin\save_post_meta');
?>