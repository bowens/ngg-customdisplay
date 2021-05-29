<?php
/**
 * Plugin Name: NextGen Gallery / Custom Display Type
 * Plugin URI: https://imagely.com/
 * Description: Demonstrates how to create a custom display type for NextGen Gallery
 * Version: 0.1
 * Author: Imagely
 * Author URI: https://imagely.com/
 * Text Domain: ngg-customdisplay
 * License: GPLv2
 * Requires PHP: 7.2
 */

namespace NGGCustomDisplay;

/**
 * NextGen Gallery uses a library called POPE to organize itself into modules. Collections of modules are called
 * "Products" -- this simple class only handles the loading of our POPE product.
 */
class Loader
{
    static $product_loaded = FALSE;

    function __construct()
    {
        // Avoid taking any actions during plugin activation: this can cause PHP warnings that interrupt the process
        if (!$this->is_activating())
        {
            // Because it's possible NextGen Gallery will be loaded by WP before or after this plugin is loaded we
            // first check if NextGen is already active and load ourselves right away if so. If not it means this
            // plugin has been loaded before NextGen Gallery and we must wait on the 'load_nextgen_gallery_modules'
            // action to fire.
            if (class_exists('C_NextGEN_Bootstrap') && did_action('load_nextgen_gallery_modules'))
                $this->load_product();
            else
                \add_action('load_nextgen_gallery_modules', [$this, 'load_product']);
        }

        // This de-activation hook de-registers our POPE product from the installed POPE product list when this plugin
        // is deactivated. A list of what products & modules along with their versions are stored in wp_options
        // under the key "pope_modules_list" in order to determine when and what installer methods need to be run.
        \add_action('deactivate_' . \plugin_basename(__FILE__), [$this, 'deactivate']);

        // Register an autoloader for this plugins namespace
        spl_autoload_register(function($class) {
            $parts = explode('\\', $class);
            if (count($parts) < 1 || $parts[0] !== __NAMESPACE__)
                return;
            unset($parts[0]);
            $path = array_map(function($part) {
                return strtolower($part);
            }, $parts);
            $path = path_join(dirname(__FILE__), implode(DIRECTORY_SEPARATOR, $path) . '.php');
            if (is_file($path))
                require_once($path);
        });
    }

    function load_product(\C_Component_Registry $registry = NULL): bool
    {
        if (!self::$product_loaded)
        {
            if (!$registry)
                $registry = \C_Component_Registry::get_instance();

            // Add this directory to POPE's module path's so that it can discover our product file
            $registry->add_module_path(dirname(__FILE__), 2, FALSE);

            // Have POPE execute our product class' initialization methods
            $registry->load_product('ngg-customdisplay');

            // Lastly we have POPE initialize any modules discovered that aren't already initialized
            $registry->initialize_all_modules();

            $retval = self::$product_loaded = TRUE;
        }
        else {
            $retval = self::$product_loaded;
        }

        return $retval;
    }

    /**
     * If WP_DEBUG is enabled and another POPE based plugin (such as the Photocrati theme or NextGEN Pro) PHP can
     * output strict warnings which may interfere with this as well as those other plugins activation. This plugin
     * doesn't need to do anything on the plugins page, so don't do anything when the user is viewing /wp-admin/plugins.php
     */
    function is_activating(): bool
    {
        return strpos($_SERVER['REQUEST_URI'], 'wp-admin/plugins.php') !== FALSE;
    }

    /**
     * Runs when deactivating this plugin
     */
    static function deactivate()
    {
        if (class_exists('\C_Photocrati_Installer'))
            \C_Photocrati_Installer::uninstall('ngg-customdisplay');
    }

}

new Loader();