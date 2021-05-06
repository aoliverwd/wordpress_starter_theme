<?php
    /** Theme namespace */
    namespace Theme;

    /** Set theme constants */
    const theme_settings_id = 'wT5jP2qU5nJ5rY4tT7h';
    const theme_path = __DIR__.'/';
    const include_path = __DIR__.'/includes/';
    const parts_path = __DIR__.'/parts/';
    const compiled_assets_path = __DIR__.'/cached/';
    const blocks_path = __DIR__.'/blocks/';
    const admin_path = __DIR__.'/admin/';
    const assets_path = __DIR__.'/assets/';

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

    /** Is WordPress administration area */
    if(is_admin()){
        include_once admin_path.'admin.php';
    }

?>