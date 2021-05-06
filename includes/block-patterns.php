<?php
    /**
     * Gutenberg Block Patterns
     * More information on block patterns can be found here:
     * https://developer.wordpress.org/block-editor/reference-guides/block-api/block-patterns/#register_block_pattern
     */

    namespace Theme\Patterns;

    function register_block_patterns(){
        if(class_exists('WP_Block_Patterns_Registry')){


            // Register banner pattern
            register_block_pattern(
                'blank_theme/simple_banner',
                array(
                    'title'       => __('Simple Banner', 'textdomain'),
                    'description' => _x('A simple banner.', 'Block pattern description', 'textdomain'),
                    'content'     => "<!-- wp:group {\"style\":{\"color\":{\"background\":\"#efefef\"}}} --> <div class=\"wp-block-group has-background simple_banner\" style=\"background-color:#efefef\"><div class=\"wp-block-group__inner-container\"><!-- wp:heading --> <h2>Banner</h2> <!-- /wp:heading --> <!-- wp:paragraph --> <p>Sint excepteur tempor ex in et nisi magna consequat elit excepteur velit dolor minim irure qui non.</p> <!-- /wp:paragraph --></div></div> <!-- /wp:group -->",
                    'categories'  => ['text'],
                )
            );

        }
    }

    add_action( 'init', 'Theme\Patterns\register_block_patterns' );
?>