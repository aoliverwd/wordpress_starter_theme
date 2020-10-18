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
?>