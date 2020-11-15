<?php
    /**
     * Themes core functions namespace
     * Functions used for core theme functionality
     */
    namespace Theme\Functions;

    /** Return theme settings */
    function return_theme_settings(){
        if($theme_settings = get_option(\Theme\theme_settings_id)){
            return json_decode($theme_settings, true);
        }
    }

    /** return theme setting */
    function theme_setting(string $setting_name):bool{

    }

    /** Cache static asset */
    function cache_static_assets(array $files_to_cache){

        /** Get theme settings */
        $settings = return_theme_settings();

        foreach($files_to_cache as $this_asset){

        }
    }

    /** Load section */
    function load_parts(array $theme_parts){
        foreach($theme_parts as $part_name){
           $part_file = \Theme\parts_path.$part_name.'/part.php';
            if(file_exists($part_file)){
                include $part_file;
            }
        }
    }
?>