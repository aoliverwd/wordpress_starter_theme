<?php
    //button shortcode
    function add_button($attributes){
        $brace = new brace\parser;
        $brace->template_path = \Theme\parts_path;
        return $brace->parse('button', $attributes, false)->return();
    }

    add_shortcode('button', 'add_button');
?>