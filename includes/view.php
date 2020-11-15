<?php
    /**
     * Themes view namespace
     * Methods used for rendering theme templates
     */
    namespace Theme\View;

    /** Render page */
    function render(){

        /** Load header */
        get_header();

        /** Load footer */
        get_footer();
    }
?>