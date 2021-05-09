<?php
    print_r($block);
    exit;

    $brace = new brace\parser;
    $brace->remove_comment_blocks = false;
    $brace->template_path = __DIR__.'/';
    $brace->parse('block',[
        'block' => $block
    ]);
?>