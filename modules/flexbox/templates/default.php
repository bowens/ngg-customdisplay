<?php
/**
 * @var array $images
 * @var array $pagination
 * @var C_Displayed_Gallery $displayed_gallery
 * @var string $effect_code
 */

// start_element() is used to allow other products / modules to inject or even alter the output of templates at render
// time. For example NextGen Pro hooks into these elements to add additional links under images to launch the Pro
// Lightbox directly to having an open cart sidebar as well as adding a single button underneath gallery displays
// to allow users to submit proofing selections.

$this->start_element('nextgen_gallery.gallery_container', 'container', $displayed_gallery); ?>
    <div class="imagely-flexbox-container">
        <?php
        $this->start_element('nextgen_gallery.image_list_container', 'container', $images);
            foreach ($images as $counter => $image) {
                $thumb_size = $storage->get_image_dimensions($image, 'thumb');
                $this->start_element('nextgen_gallery.image_panel', 'item', $image);
                    $this->start_element('nextgen_gallery.image', 'item', $image); ?>
                        <a href="<?php echo esc_attr($storage->get_image_url($image, 'full')); ?>"
                           title="<?php echo esc_attr($image->description); ?>"
                           <?php echo $effect_code; ?>>
                            <img title="<?php echo esc_attr($image->alttext); ?>"
                                 alt="<?php echo esc_attr($image->alttext); ?>"
                                 src="<?php echo esc_attr($storage->get_image_url($image, 'full')); ?>"
                                 width="<?php echo esc_attr($thumb_size['width']); ?>"
                                 height="<?php echo esc_attr($thumb_size['height']); ?>"/>
                        </a>
                    <?php
                    $this->end_element(); // image_panel
                $this->end_element(); // image
            }

        $this->end_element(); // image_list_container
        ?>
    </div>
<?php
$this->end_element(); // gallery_container

if (!empty($pagination['output']))
    echo $pagination['output'];