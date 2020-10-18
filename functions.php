<?php
    /** Theme namespace */
    namespace Theme;

    /** Set theme constants */
    const theme_path = __DIR__.'/';
    const include_path = __DIR__.'/includes/';
    const compiled_assets_path = __DIR__.'/compiled/';
    const admin_path = __DIR__.'/admin/';

    /** Load dependencies */
    include_once theme_path.'/vendor/autoload.php';
    include_once include_path.'model.php';
    include_once include_path.'view.php';
    include_once include_path.'filters.php';
    include_once include_path.'actions.php';

    if(is_admin()){
        include_once admin_path.'admin.php';
    }

?>