<?php

class CPPS_Config
{
    const NAME = 'concat-parent-page-slugs';
    const DOMAIN = 'concat-parent-page-slugs';
    const OPTION_NAME = 'concat_parent_page_slugs_options';
    const OPTION_GROUP_NAME = 'concat_parent_page_slugs_options_group';

    private static $version = null;

    public static function version()
    {
        if(is_null(self::$version)) {
            $file_path = WP_CPPS_PLUGIN_DIR . 'concat-parent-page-slugs.php';
            $data = get_file_data($file_path, array('version' => 'Version'));
            self::$version = $data['version'];
        }

        return self::$version;
    }

}
