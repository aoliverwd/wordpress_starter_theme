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
    //     if($theme_settings = get_option(theme_settings_id)){
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

        if(file_exists($asset_file) && !isset($GLOBALS[theme_settings_id][$asset_file])){
            /** Add to global loaded asset array */
            $GLOBALS[theme_settings_id][$asset_file] = true;

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

        if(!is_dir(compiled_assets_path)){
            mkdir(compiled_assets_path);
        }


        if(is_dir(compiled_assets_path)){

            $compiled_path = compiled_assets_path;
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

    //return navigation
    function return_navigation(string $nav_title){
        if($menu = wp_get_nav_menu_items($nav_title)){

            $url = (isset($_SERVER['SCRIPT_URL']) ? $_SERVER['SCRIPT_URL'] : $_SERVER['REQUEST_URI']);

            $slugs = explode('/', substr($url, 1));
            $this_page_slug = $slugs[0];
            $site_url = get_site_url();
            $nav_items = [];

            foreach($menu as $item){

                $item->url_processed = substr(str_replace($site_url, '', $item->url), 1);
                $item->url_processed = (substr($item->url_processed, -1) === '/' ? substr($item->url_processed, 0, -1) : $item->url_processed);

                $item->url = str_replace(get_site_url().'/', '/', $item->url);

                //add class
                $item->class = ($item->url_processed === $this_page_slug ? 'selected' : 'not_selected');

                //is child
                if($item->menu_item_parent){
                    $item->class = ($item->url_processed === rtrim(implode('/', $slugs), '/') ? 'selected' : 'not_selected');
                    $nav_items[$item->menu_item_parent]->subnav[] = $item;
                } else {
                    //sub nav
                    $item->subnav = [];

                    //add to nav items
                    $nav_items[$item->ID] = $item;
                }
            }

            return json_decode(json_encode($nav_items), true);
        }
    }

    //return image meta
    function return_image_meta(int $image_id){
        if($image_id !== 0 && $image_meta = wp_get_attachment_metadata($image_id)){
            $upload_dir = wp_get_upload_dir();
            $image_post = get_post($image_id);
            $image_meta['alt'] = get_post_meta($image_id, '_wp_attachment_image_alt', true);
            $image_meta['caption'] = $image_post->post_excerpt;
            $image_meta['description'] = $image_post->post_content;
            $image_meta['upload_path'] = str_replace(get_site_url(), '', $upload_dir['baseurl']).'/';
            $image_meta['image_url'] = $image_meta['upload_path'].$image_meta['file'];
            $image_meta['html'] = '<img src="'.$image_meta['image_url'].'" width="'.$image_meta['width'].'" height="'.$image_meta['height'].'" alt="'.$image_meta['alt'].'" loading="auto">';
            return $image_meta;
        }
    }

    //post page pagination
    function post_pagination(array $posts = [], bool $is_query = false){

        if($posts) {

            //current page ID
            //$selectPage = (isset($_GET[$this::pagination_page_query]) && strlen($_GET[$this::pagination_page_query]) > 0 ? intval($_GET[$this::pagination_page_query]) : 1);
            $selectPage = (get_query_var('paged') > 0 ? get_query_var('paged') : 1);

            $selectPage = ($is_query && isset($_GET['pge']) ? intval($_GET['pge']) : $selectPage);


            $pagination = [];

            //chunk posts into x number per chunk
            foreach(array_chunk($posts, pagination_chunk_posts_per_page) as $thisPage){
                $pagination[] = [
                    'id' => count($pagination) + 1,
                    'addclass' => '',
                    'each' => [
                        'posts' => $thisPage
                    ]
                ];
            }

            //current posts chunk from selected page
            $page_posts = (isset($pagination[$selectPage -1]) ? $pagination[$selectPage -1]['each']['posts'] : $posts);

            //add class
            if(isset($pagination[$selectPage -1])){
                $pagination[$selectPage -1]['addclass'] = 'active_container';
            } else {
                $pagination[0]['addclass'] = 'active_container';
            }

            //page to pagination index
            $pagination_index = [];

            //chunk post chunks into grouped chunks
            foreach($pagi_chunk = array_chunk($pagination, pagination_chunk_pages) as $chunk_id => $chunk_value){
                foreach($chunk_value as $this_page){
                    $pagination_index[$this_page['id']] = $chunk_id;
                }
            }

            //block count
            $block_count = count($pagi_chunk) -1;

            $prev_page = false;
            $next_page = false;


            //is page in first block
            if(!isset($pagination_index[$selectPage]) || $pagination_index[$selectPage] === 0){
                $next_page = $selectPage + 1;
                $next_page = (isset($pagination_index[$selectPage+1]) ? $next_page : false);
                $pagination = $pagi_chunk[0];

                //is not first page
                $prev_page = ($next_page > 0 ? $selectPage -1 : false);

            } else if($pagination_index[$selectPage] === $block_count){
                //is page in last block
                $prev_page = $selectPage - 1;
                $prev_page = (isset($pagination_index[$prev_page]) ? $prev_page : false);
                $pagination = $pagi_chunk[count($pagi_chunk) -1];

                //is not last page
                $next_page = ($selectPage < count($pagination_index) ? $selectPage + 1 : false);

            } else {
                //what block is page in
                $pagination = (isset($pagi_chunk[$pagination_index[$selectPage]]) ? $pagi_chunk[$pagination_index[$selectPage]] : []);
                $next_page = $selectPage + 1;
                $prev_page = $selectPage - 1;
            }


            $current_pagination_block_id = $pagination_index[$selectPage];
            $previous_pagination_block_id = $pagination_index[$selectPage] - 1;
            $next_pagination_block_id = $pagination_index[$selectPage] + 1;

            $next_pagi = (isset($pagi_chunk[$next_pagination_block_id]) ? $pagi_chunk[$next_pagination_block_id][0]['id'] : false);
            $prev_pagi = (isset($pagi_chunk[$previous_pagination_block_id]) ? $pagi_chunk[$previous_pagination_block_id][count($pagi_chunk[$previous_pagination_block_id]) - 1]['id'] : false);

            return [
                'previous_pagination_id' => $prev_pagi,
                'next_pagination_id' => $next_pagi,
                'previous_page' => $prev_page,
                'pagination' => $pagination,
                'next_page' => $next_page,
                'posts' => $page_posts
            ];

        }

        return [
            'previous_pagination_id' => false,
            'next_pagination_id' => false,
            'previous_page' => false,
            'next_page' => false,
            'pagination' => [],
            'posts' => []
        ];
    }

    //share this buttons
    function share_this_post(int $this_post_id = 0, string $type = 'twitter'){
        if($this_post_id && $this_post = get_post($this_post_id)){

            $post_thumb = urlencode(get_the_post_thumbnail_url($this_post->ID, "medium"));
            $excerpt = urlencode(strtok(wordwrap($this_post->post_excerpt, 137, "...\n"), "\n"));
            $post_link = urlencode(get_permalink($this_post->ID));

            switch($type){
            case 'twitter':
                return "https://twitter.com/intent/tweet?url=".$post_link."&text=".$excerpt;
                break;
            case 'facebook':
                return "https://www.facebook.com/sharer/sharer.php?u=".$post_link;
                break;
            case 'pinterest':
                return "http://pinterest.com/pin/create/button/?url=".$post_link."&media=".$post_thumb."&description=".$excerpt;
                break;
            case 'whatsapp':
                return "whatsapp://send?text=".$post_link.'" data-action="share/whatsapp/share"';
                break;
            }
        }
    }

    //register custom post types
    function custom_post_type(string $name, string $title, bool $hasCategorys = false, bool $revisions = true){
        $cats = [];
        $revisions = '';

        if($hasCategorys){ $cats = array('category', 'post_tag'); }
        if($revisions){$revisions = 'revisions'; }

        register_post_type(
            $name,
            array(
                 'labels'             =>
                 array(
                     'name'               => $title,
                     'singular_name'      => $title,
                     'add_new'            => 'Add New',
                     'add_new_item'       => 'Add New '.$title,
                     'edit_item'          => 'Edit '.$title,
                     'new_item'           => 'New '.$title,
                     'all_items'          => 'All '.$title,
                     'view_item'          => 'View '.$title,
                     'search_items'       => 'Search '.$title,
                     'not_found'          => 'No '.$title.' found',
                     'not_found_in_trash' => 'No '.$title.' found in Trash',
                     'parent_item_colon'  => '',
                     'menu_name'          => $title
                 ),
                 'taxonomies' => $cats,
                 'public'             => true,
                 'publicly_queryable' => true,
                 'show_ui'            => true,
                 'show_in_menu'       => true,
                 'query_var'          => true,
                 'rewrite'            => array( 'slug' => $name ),
                 'capability_type'    => 'page',
                 'has_archive'        => false,
                 'hierarchical'       => false,
                 'menu_position'      => NULL,
                 'show_in_rest'       => true,
                 'supports'           =>
                 array(
                     'title', 'editor', 'author', 'page-attributes', 'thumbnail', 'excerpt', $revisions
                 )
            )
        );
    }
?>