<?php
    /**
     * WordPress Filters
     */

    /**
     * WordPress XML-RPC PingBack Vulnerability Analysis
     * https://www.trustwave.com/Resources/SpiderLabs-Blog/WordPress-XML-RPC-PingBack-Vulnerability-Analysis/
     * Disable Pingback Reqests
     */
    add_filter('xmlrpc_methods', function($methods){
        unset($methods['pingback.ping']);
        return $methods;
    });

    /** Move Yoast SEO metabox to bottom of posts */
    add_filter( 'wpseo_metabox_prio', function() {return 'low';});

    /**
     * Remove absolute paths of post content.
     * This WP Filter triggers before post is saved.
     */
    add_filter( 'wp_insert_post_data' , 'filter_post_data' , '99', 2);
    function filter_post_data($data){
        $data['post_content'] = str_replace(get_site_url().'/', '/', $data['post_content']);
        return $data;
    }
?>