<?php

namespace NGGCustomDisplay\Modules\Flexbox;

/**
 * @mixin \C_Form
 */
class Admin_Form extends \Mixin_Display_Type_Form
{
    /**
     * Used by the form manager when saving updated values; this prevents changes to this form's images_per_page
     * field from changing the basic thumbnails images_per_page setting.
     * @return string
     */
    function get_display_type_name(): string
    {
        return M_Imagely_Flexbox_ID;
    }

    /**
     * Invoked by the form manager when this form is displayed. Place any necessary calls to wp_enqueue_script()
     * or wp_enqueue_style() here
     */
    function enqueue_static_resources()
    {
    }

    /**
     * The form manager will iterate this list invoking _render_{name}_field($display_type)
     *
     * @return array
     */
    function _get_field_names(): array
    {
        return [
            'flexbox_images_per_page'
        ];
    }

    /**
     * @param \C_Display_Type $display_type
     * @return string Generated HTML
     */
    function _render_flexbox_images_per_page_field(\C_Display_Type $display_type): string
    {
        $settings = $display_type->settings;
        return $this->_render_number_field(
            $display_type,
            'images_per_page',                   // name
            __('Images per page', 'ngg-customdisplay'), // label
            $settings['images_per_page']                // value
        );
    }
}