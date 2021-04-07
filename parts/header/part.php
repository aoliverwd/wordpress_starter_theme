<?php

    /** Return static asset */
    $assets = Theme\Functions\cache_static_assets([
        __DIR__.'/header.scss',
        __DIR__.'/header.js'
    ]);

    /** Render template */
    $brace = new brace\parser;
    $brace->template_path = __DIR__.'/';
    $brace->parse('header',[
        'assets' => $assets
    ]);
?>