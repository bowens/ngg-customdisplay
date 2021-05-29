<?php

/*** { Product: ngg-customdisplay,
 *     Depends: { photocrati-nextgen }}
 ***/

namespace NGGCustomDisplay;

/**
 * This is our POPE product class; it is responsible for controlling which modules are loaded and in which order. This
 * is a simple plugin however so this product only has one module.
 */
class Product extends \C_Base_Product
{
    static $modules_provided = [
        'imagely-flexbox'
    ];

    function define($id          = 'pope-product',
                    $name        = 'Pope Product',
                    $description = '',
                    $version     = '',
                    $uri         = '',
                    $author      = '',
                    $author_uri  = '',
                    $context     = FALSE)
    {
        parent::define(
            'ngg-customdisplay',
            'NextGen Custom Display',
            'Demonstrates how to add new display types to NextGen Gallery',
            '0.1',
            'https://imagely.com',
            'Imagely',
            'https://imagely.com/'
        );

        // This instructs POPE that modules are to be found in the 'modules' sub-directory. This will find all files
        // named "module.*.php" and include_once() on them.
        $this->get_registry()->set_product_module_path(
            $this->module_id,
            \path_join(dirname(__FILE__), 'modules')
        );

        // The methods of this installer class are run whenever this plugin is activated or when the product
        // version changes. Installers are used to register default settings and create any necessary custom posts.
        \C_Photocrati_Installer::add_handler($this->module_id, __NAMESPACE__ . '\\Installer');
    }

    // This method is triggered by the plugin file's use of $registry->load_product() and it triggers POPE to execute
    // the following methods if they exist on the module class in this order:
    // _register_utilities()
    // _register_adapters()
    // _register_hooks()
    function load()
    {
        foreach (self::$modules_provided as $module_name) {
            $this->get_registry()->load_module($module_name);
        }

        parent::load();
    }
}

// NextGen stores display types settings in custom posts (post_type='display_type'). This class is responsible for
// installing and uninstalling new display types provided by its child modules.
class Installer
{
    function install_display_types()
    {
        foreach (array_keys(Product::$modules_provided) as $module_name) {
            if (($handler = \C_Photocrati_Installer::get_handler_instance($module_name))) {
                if (method_exists($handler, 'install_display_types'))
                    $handler->install_display_types();
            }
        }
    }

    function uninstall($hard = FALSE)
    {
        foreach (array_keys(Product::$modules_provided) as $module_name) {
            if (($handler = \C_Photocrati_Installer::get_handler_instance($module_name))) {
                if (method_exists($handler, 'uninstall'))
                    $handler->uninstall($hard);
            }
        }
    }
}

// Our product class is not created automatically, so we do so now
new Product();