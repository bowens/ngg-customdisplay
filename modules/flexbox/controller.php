<?php

namespace NGGCustomDisplay\Modules\Flexbox;

/**
 * This class is responsible for frontend rendering logic
 */
class Controller extends \Mixin
{
    function initialize()
    {
        // add_mixin() is similar to PHP's native traits: it effectively means that this class gains the
        // 'create_pagination()' method defined in the class Mixin_NextGen_Basic_Pagination
        $this->object->add_mixin('Mixin_NextGen_Basic_Pagination');
    }

    /**
     * The C_Displayed_Gallery object is the sum of all parts of a NextGen shortcode: it contains the source attribute
     * (which can be albums, galleries, individual images, tags..), the list of which containers to display (album id,
     * gallery id, etc), the gallery sort direction if any, and any display type settings. It also provides methods
     * for controllers to automatically fetch the images to be displayed.
     *
     * @param \C_Displayed_Gallery $displayed_gallery
     * @param bool $return When false output is printed instead of returned
     * @return string
     */
    function index_action(\C_Displayed_Gallery $displayed_gallery, $return = FALSE): string
    {
        $router = \C_Router::get_instance();

        $images_per_page = $displayed_gallery->display_settings['images_per_page'];
        $current_page    = (int)$router->get_parameter('nggpage', $displayed_gallery->id(), 1);
        $offset          = $images_per_page * ($current_page - 1);
        $total           = $displayed_gallery->get_entity_count();
        $images          = $displayed_gallery->get_included_entities($images_per_page, $offset);

        /** @var string $pagination */
        $pagination = $this->object->create_pagination(
            $current_page,
            $total,
            $images_per_page
        );

        // The "effect code" is a bit of HTML that is injected into the <a> that wraps each NextGen image. This is
        // used for lightbox effects as every lightbox is different -- some use class, some use the 'rel' attribute..
        /** @var string $effect_code */
        $effect_code = $this->object->get_effect_code($displayed_gallery);

        // prepare_display_parameters() automatically sets a handful of attributes in the array of variables we will
        // be passing to the template for rendering. C_Gallery_Storage is used by the template to determine image
        // URL and dimensions.
        $params = $this->object->prepare_display_parameters(
            $displayed_gallery,
            ['images'      => $images,
             'storage'     => \C_Gallery_Storage::get_instance(),
             'effect_code' => $effect_code,
             'pagination'  => $pagination
            ]
        );

        // Here render_view() is context-aware: by passing 'imagely-flexbox#default' it will use the 'templates/default.php'
        // file provided by this (the imagely-flexbox) module.
        return $this->object->render_view(
            M_Imagely_Flexbox_ID . '#default',
            $params,
            $return
        );
    }

    /**
     * This method is invoked whenever a flexbox gallery has been rendered by the above index_action() and is responsible
     * for loading any necessary styles and javascript.
     *
     * @param \C_Displayed_Gallery $displayed_gallery
     */
    function enqueue_frontend_resources(\C_Displayed_Gallery $displayed_gallery)
    {
        // Necessary so that other display type resources aren't forgotten
        $this->call_parent('enqueue_frontend_resources', $displayed_gallery);

        // Provided by C_Display_Type_Controller
        $this->object->enqueue_ngg_styles();

        \wp_enqueue_style(
            'imagely_flexbox_pagination_style',
            $this->object->get_static_url('photocrati-nextgen_pagination#style.css')
        );

        \wp_enqueue_style(
            'imagely_flexbox_style',
            $this->object->get_static_url(M_Imagely_Flexbox_ID . '#style.css'),
            [],
            M_Imagely_Flexbox_Version,
            'all'
        );

        \wp_enqueue_script(
            'imagely_flexbox_script',
            $this->object->get_static_url(M_Imagely_Flexbox_ID . '#flexbox.js'),
            [],
            M_Imagely_Flexbox_Version,
            TRUE
        );
    }
}
