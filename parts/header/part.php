<?php

    /** Return static asset */
    $assets = Theme\Functions\cache_static_assets([
        __DIR__.'/header.scss',
        __DIR__.'/header.js'
    ]);

    //yoast
    ob_start();
    do_action('wpseo_head');
    $yoast = ob_get_clean();

    /** Render template */
    $brace = new brace\parser;
    $brace->template_path = __DIR__.'/';
    $brace->parse('header',[
        'charset' => get_bloginfo('charset'),
        'lang' => get_bloginfo('language'),
        'title' => get_bloginfo('name'), //wp_title(' - ', false)
        'assets' => $assets,
        'yoast' => $yoast
    ]);
?>