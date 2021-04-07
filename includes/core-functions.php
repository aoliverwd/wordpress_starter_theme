<?php
    /**
     * Themes core functions namespace
     * Functions used for core theme functionality
     */
    namespace Theme\Functions;
    use \ScssPhp\ScssPhp\Compiler;
    use \Theme as Theme;

    /** Return theme settings */
    // function return_theme_settings(){
    //     if($theme_settings = get_option(Theme\theme_settings_id)){
    //         return json_decode($theme_settings, true);
    //     }
    // }

    /** return theme setting */
    function theme_setting(string $setting_name){

        $settings = [
            'asset_max_kb' => 2,
            'dev_mode' => getenv('ENVIRONMENT') ?? 'development'
        ];

        return (isset($settings[$setting_name]) ? $settings[$setting_name] : false);
    }

    /** Cache static asset */
    function cache_static_assets(array $files_to_cache){

        /** Get theme settings */
        //$settings = return_theme_settings();

        /** Return cached ID location */
        $cache_id = check_for_cached_version(hash('crc32b', implode(',', $files_to_cache)));

        $asset_types = [
            'css' => [],
            'js' => []
        ];

        /** Sort assets */
        foreach($files_to_cache as $this_asset){
            $file_info = pathinfo($this_asset);
            switch($file_info['extension']){
            case 'css':
            case 'scss':
                array_push($asset_types['css'], $this_asset);
                break;
            case 'js':
                array_push($asset_types['js'], $this_asset);
                break;
            }
        }

        /** return compiled assets */
        return [
            'css' => compile_static_asset($asset_types['css'], $cache_id, '.css'),
            'js' => compile_static_asset($asset_types['js'], $cache_id, '.js')
        ];
    }

    /** Compile static asset */
    function compile_static_asset(array $assets, string $cache_id, string $extension):string {

        /** Compiled asset filename */
        $asset_file = $cache_id.$extension;

        /** Compile file */
        if(!file_exists($asset_file)){

            $compiled_css = [];
            $compiled_js = [];

            /** Init sass compiler */
            $scss = new Compiler();

            foreach($assets as $this_asset){

                /** Check assets exists */
                if(file_exists($this_asset)){
                    $file_info = pathinfo($this_asset);
                    switch($file_info['extension']){
                    case 'css':
                        $compiled_css[] = compact_string(file_get_contents($this_asset));
                        break;
                    case 'scss':
                        $compiled_css[] = compact_string($scss->compile(file_get_contents($this_asset)));
                        break;
                    case 'js':
                        $compiled_js[] = compact_string(file_get_contents($this_asset));
                        break;
                    }
                }
            }

            /** Create compiled CSS file */
            if(count($compiled_css) > 0){
                file_put_contents($asset_file, implode(' ',$compiled_css));
            }

            /** Create compiled JS file */
            if(count($compiled_js) > 0){
                file_put_contents($asset_file, implode(' ',$compiled_js));
            }
        }

        if(file_exists($asset_file) && !isset($GLOBALS[Theme\theme_settings_id][$asset_file])){
            /** Add to global loaded asset array */
            $GLOBALS[Theme\theme_settings_id][$asset_file] = true;

            /** Get filesize in KB */
            $file_kb = number_format(filesize($asset_file) / 1024, 2);

            /** Get asset max size preference from settings */
            $kb_setting = theme_setting('asset_max_kb');

            if($file_kb <= $kb_setting){
                switch($extension){
                case '.css':
                    $asset_file = '<style>'.file_get_contents($asset_file).'</style>';
                    break;
                case '.js':
                    $asset_file = '<script>'.file_get_contents($asset_file).'</script>';
                    break;
                }
            } else {
                switch($extension){
                case '.css':
                    $asset_file = '<link rel="stylesheet" href="'.return_file_uri_path($asset_file).'">';
                    break;
                case '.js':
                    '<script src="'.return_file_uri_path($asset_file).'" defer></script>';
                    break;
                }
            }

            return $asset_file;
        }

        return '';
    }

    /** Return file URI path */
    function return_file_uri_path(string $file_name):string{

        /** Get WP content dir name */
        $content_dir = explode('/', WP_CONTENT_DIR);
        $content_dir_name = array_pop($content_dir);

        /** URI relative path */
        $uri_folder = '/';

        /** Check for sub directories */
        if(count($url_dir = explode('/', preg_replace('/(http|https):\/\//', '', get_site_url()))) === 2){
            array_shift($url_dir);
            $uri_folder = implode('/', $url_dir);
            $uri_folder .= (substr($uri_folder, -1, 1) !== '/' ? '/' : '');
        }

        /** Return asset URI */
        if(count($file_location = explode($content_dir_name, $file_name)) === 2){
            return $uri_folder.$content_dir_name.$file_location[1];
        }

        /** Return empty string if asset is not in the WP content folder */
        return '';
    }

    /** Compact string */
    function compact_string(string $input_string, string $type = 'css'):string{
        switch($type){
        case 'css':
            $round_one = preg_replace('/\/\*(.*?)\*\/|\/\/(.*?)$|\r?\n|\r/ms', '', $input_string);
            return str_replace(['  ', ' }', ' {',': '],['', '}','{',':'], $round_one);
            break;
        case 'js':
            $round_one = preg_replace('/\/\*(.*?)\*\/|\/\/(.*?)$/ms', '', $input_string);
            return $round_one;
            break;
        }

        return $input_string;
    }

    /** return cached filename */
    function check_for_cached_version(string $identifier):string{
        if(is_dir(Theme\compiled_assets_path)){

            $compiled_path = Theme\compiled_assets_path;
            $compile_index_file = $compiled_path.'compile-index.json';
            $compile_index = (file_exists($compile_index_file) ? json_decode(file_get_contents($compile_index_file), true) : []);

            if(isset($compile_index[$identifier])){

                /** Check dev mode status */
                if(theme_setting('dev_mode') === 'development'){

                    /** Remove old compiled file */
                    foreach(['.css', '.js'] as $extension){
                        $old_compiled_file = $compiled_path.$identifier.'-'.$compile_index[$identifier].$extension;
                        if(file_exists($old_compiled_file)){
                            unlink($old_compiled_file);
                        }
                    }

                    /** New compile file time */
                    $compile_index[$identifier] = time();

                    /** Save index file */
                    file_put_contents($compile_index_file, json_encode($compile_index));
                }

                /** Compile file name */
                $filename = $compiled_path.$identifier.'-'.$compile_index[$identifier];
            } else {
                /** New compile file time */
                $compile_index[$identifier] = time();

                /** Save index file */
                file_put_contents($compile_index_file, json_encode($compile_index));

                /** Compile file name */
                $filename = $compiled_path.$identifier.'-'.$compile_index[$identifier];
            }

            /** Return compiled file filename */
            return $filename;
        }
    }

    /** Load section */
    function load_parts(array $theme_parts){
        foreach($theme_parts as $part_name){
           $part_file = Theme\parts_path.$part_name.'/part.php';
            if(file_exists($part_file)){
                include $part_file;
            }
        }
    }

    /** Load blocks */
    function load_post_blocks(int $post_id){
        if($blocks = parse_blocks(get_post_field('post_content', $post_id))){
            foreach($blocks as $this_block){

                /** is resealable block */
                if(isset($this_block['attrs']['ref'])){
                    if($block_post = get_post($this_block['attrs']['ref'])){
                        $this_blocks = parse_blocks($block_post->post_content);
                        $this_block = $this_blocks[0];
                    }
                }

                load_block($this_block);
            }
        }
    }

    /** Load block */
    function load_block(array $block){
        if(is_array($block) && isset($block['blockName']) && $block['blockName']){

            $blocks_classes = (isset($block['attrs']['className']) ? explode(' ', $block['attrs']['className']) : []);
            $blocks_class_id = (count($blocks_classes) > 0 && strlen($blocks_classes[0]) > 0 ? Theme\Theme\blocks_path.trim($blocks_classes[0]).'/block.php' : false);
            $default_block = (file_exists($blocks_class_id) ? $blocks_class_id : false);


            $block['block_id'] = str_replace('/', '-', $block['blockName']);
            $block_name_ids = explode('/', $block['blockName']);

            $theme_block = Theme\blocks_path.$block['block_id'].'/block.php';

            //$default_block = ($default_block ? $default_block : Theme\blocks_path.$block['block_id'].'/block.php');
            $default_block = ($default_block ? $default_block : Theme\blocks_path.'default/block.php');

            //block lab
            $theme_block = ($block_name_ids[0] === 'block-lab' ? Theme\blocks_path.$block_name_ids[1].'/block.php' : $theme_block);

            $block_file = (file_exists($theme_block) ? $theme_block : (file_exists($default_block) ? $default_block : false));

            $block['theme_block_path'] = $block_file;
            $block['theme_block_file_exists'] = file_exists($block_file);

            if($block_file){
                include $block_file;
            }
        }
    }
?>