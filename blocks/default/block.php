<?php
    $brace = new brace\parser;
    $brace->template_path = __DIR__.'/';
    $brace->parse('block',[
        'block' => $block
    ]);
?>