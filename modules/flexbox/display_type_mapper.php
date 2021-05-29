<?php

namespace NGGCustomDisplay\Modules\Flexbox;

/**
 * This simple class ensures that the flexbox custom post will be created with the 'images_per_page' attribute
 * its its settings. Without this the Admin_Form and Controller classes will have to use isset() and provide defaults
 * for every possible setting this display type may have.
 */
class Display_Type_Mapper extends \Mixin
{
    /**
     * @param \stdClass $entity Result from C_Display_Type_Mapper
     */
    function set_defaults(\stdClass $entity)
    {
        // Without this other display type mapper adapters may fail to run. We must always invoke call_parent() here!
        $this->call_parent('set_defaults', $entity);

        // Because this method will be invoked on every display type object we must check first that we are currently
        // working with this flexbox display type.
        if ($entity->name == M_Imagely_Flexbox_ID)
        {
            $this->object->_set_default_value($entity, 'settings', 'images_per_page', 40);
        }
    }
}