<?php
    /**
     * Themes view namespace
     * Methods used for rendering theme templates
     */
    namespace Theme\View;
    use \Theme\Functions as Functions;

    /** Render page */
    function render(){

        /** Load WP header */
        get_header();

        /** Load WP Body Bocks */
        Functions\load_post_blocks(get_the_ID());

        /** Load WP footer */
        get_footer();
    }
?>