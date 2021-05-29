<?php

namespace NGGCustomDisplay\Modules\Flexbox;

define('M_Imagely_Flexbox_ID',      'imagely-flexbox');
define('M_Imagely_Flexbox_Version', '0.1');

/*
 * This module class is responsible for registering its various classes that compose a display type like the
 * rendering controller and admin settings form.
 */
class Module extends \C_Base_Module
{
    function define($id          = 'pope-module',
                    $name        = 'Pope Module',
                    $description = '',
                    $version     = '',
                    $uri         = '',
                    $author      = '',
                    $author_uri  = '',
                    $context     = FALSE)
    {
        parent::define(
            M_Imagely_Flexbox_ID,
            'Flexbox Display Type',
            'A Display Type that uses CSS flexbox for layout',
            M_Imagely_Flexbox_Version,
            'https://imagely.com/',
            'Imagely',
            'https://imagely.com/',
            $context
        );

        // This installer class will create the custom post that stores this display type's name and settings
        \C_Photocrati_Installer::add_handler($this->module_id, __NAMESPACE__ . '\\Installer');
    }

    // This method is invoked by the Product when the plugin file calls $registry->load_product()
    function initialize()
    {
        parent::initialize();

        // While _register_adapters() adds our Admin_Form class to the I_Form interface we still need to register
        // that form context as one of the display settings forms. This is how display settings are kept separate
        // from lightbox settings (NGG_LIGHTBOX_OPTIONS_SLUG)
        if (\M_Attach_To_Post::is_atp_url() || \is_admin())
            \C_Form_Manager::get_instance()->add_form(NGG_DISPLAY_SETTINGS_SLUG, $this->module_id);
    }

    // This method is invoked by the Product when the plugin file calls $registry->load_product()
    function _register_adapters()
    {
        $registry = $this->get_registry();

        // The display type mapper is responsible for finding display type custom posts. By adapting it with this
        // following class we can ensure that default settings are always provided for our display type object.
        $registry->add_adapter('I_Display_Type_Mapper',     __NAMESPACE__ . '\\Display_Type_Mapper');

        // The display type controller is responsible for rendering content. By adapting it with our own Controller
        // class we ensure that our own rendering logic is used when the display type controller is created with the
        // "imagely-flexbox" context.
        $registry->add_adapter('I_Display_Type_Controller', __NAMESPACE__ . '\\Controller', $this->module_id);

        // Lastly we adapt the I_Form interface with our Admin_Form class so that when we request an I_Form object
        // with the "imagely-flexbox" context we get the C_Form object that has our needed admin settings methods added.
        if (\M_Attach_To_Post::is_atp_url() || \is_admin())
            $registry->add_adapter('I_Form', __NAMESPACE__ . '\\Admin_Form', $this->module_id);
    }

    // This method is invoked by the Product when the plugin file calls $registry->load_product()
    function _register_hooks()
    {
        // NextGen Gallery uses its own router code and logic. Because this display type makes use of pagination
        // we must register with NextGen the URL we are adopting and how it is 'rewritten' to be available code-side.
        add_action('ngg_routes', function() {
            $slug = '/' . \C_NextGen_Settings::get_instance()->get('router_param_slug');
            \C_Router::get_instance()->rewrite("{*}{$slug}{*}/page/{\\d}", "{1}{$slug}{2}/nggpage--{3}");
        });
    }
}

// This class is responsible for the creation of the flexbox display type custom post where its attributes and settings
// are stored. Note that in uninstall() we don't actually remove the custom post but rather change the 'hidden_from_ui'
// attribute so that settings aren't lost when this plugin is deactivated.
class Installer extends \C_Gallery_Display_Installer
{
    function install($reset = FALSE)
    {
        $this->install_display_type(
            M_Imagely_Flexbox_ID, [
                'title'                 => __('Flexbox Gallery Display', 'ngg-customdisplay'),
                'entity_types'          => ['image'],
                'default_source'        => 'galleries',
                'preview_image_relpath' => M_Imagely_Flexbox_ID . '#preview.png',
                'hidden_from_ui'        => FALSE,
                'view_order'            => NGG_DISPLAY_PRIORITY_BASE + (NGG_DISPLAY_PRIORITY_STEP * 10) + 220,
                'aliases' => [
                    'flexbox'
                ]
            ]
        );
    }

    function uninstall()
    {
        $mapper = \C_Display_Type_Mapper::get_instance();
        if (($entity = $mapper->find_by_name(M_Imagely_Flexbox_ID)))
        {
            $entity->hidden_from_ui = TRUE;
            $mapper->save($entity);
        }
    }
}

// The product class is not created automatically, so we do that now
new Module();