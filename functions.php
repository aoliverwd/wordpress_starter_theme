<?php
    /** Theme namespace */
    namespace Theme;

    /** Set theme constants */
    const theme_path = __DIR__.'/';
    const include_path = __DIR__.'/includes/';
    const parts_path = __DIR__.'/parts/';
    const blocks_path = __DIR__.'/blocks/';
    const admin_path = __DIR__.'/admin/';
    const assets_path = __DIR__.'/assets/';
    const pagination_chunk_posts_per_page = 12;
    const pagination_chunk_pages = 4;

    /** Set definitions */
    define('compiled_assets_path', (defined('ABSPATH') ? ABSPATH : __DIR__.'/').'cached/');
    define('theme_settings_id', hash('crc32b', AUTH_KEY.NONCE_KEY.NONCE_SALT));

    /** Global array of static assets that are loaded */
    $GLOBALS['theme_settings_id'] = [];

    /** Load dependencies */
    include_once theme_path.'/vendor/autoload.php';
    include_once include_path.'security.php';
    include_once include_path.'core-functions.php';
    include_once include_path.'view.php';
    include_once include_path.'wp-filters.php';
    include_once include_path.'wp-actions.php';
    include_once include_path.'wp-shortcodes.php';
    include_once include_path.'block-patterns.php';

    /** Register custom post types */
    Functions\custom_post_type('system_pages', 'System Pages');

    /** Is WordPress administration area */
    if(is_admin()){
        include_once admin_path.'admin.php';
    }

?>