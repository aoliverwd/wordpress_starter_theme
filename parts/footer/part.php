<?php

    // ob_start();
    // wp_footer();
    // $wp_footer = ob_get_clean();

    $brace = new brace\parser;
    $brace->template_path = __DIR__.'/';
    $brace->parse('footer',[]);
?>