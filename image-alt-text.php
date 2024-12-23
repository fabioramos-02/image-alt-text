<?php

/*
Plugin Name: Image Alt Text
Plugin URI: https://rswebstudios.com/
Description: Image Alt Text plugin provides facilities to add missing alt text for all image media files. 
Version: 3.0.0
Author: RS WebStudios
Author URI: https://rswebstudios.com
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

defined('ABSPATH') or die('Plugin file cannot be accessed directly.');

class image_alt_text
{

    public function __construct()
    {
        $this->fn_iat_define();
        $this->fn_iat_init();
        add_action('plugins_loaded', array($this, 'iat_plugin_load_textdomain'));
    }

    public function fn_iat_define()
    {
        define('IMAGE_ALT_TEXT', 'image-alt-text');
        define('IAT_FILE_VERSION', '3.0.0');
        define('IAT_PREFIX', 'iat_');
        define('IAT_FILE_PATH', plugin_dir_path(__FILE__));
        define('IAT_FILE_URL', plugin_dir_url(__FILE__));
    }

    public function fn_iat_init()
    {
        include_once('includes/iat-general.php');
        include_once('includes/class-iat.php');
    }

    public function iat_plugin_load_textdomain()
    {
        load_plugin_textdomain('image-alt-text', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
}

new image_alt_text();
