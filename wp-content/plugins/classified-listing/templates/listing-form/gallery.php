<?php
/**
 * Login Form Gallery
 *
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 * @var integer $post_id
 */

use Rtcl\Resources\Gallery;
$post = $post_id ? get_post($post_id): null;
?>
<div class="rtcl-post-gallery rtcl-post-section">
    <div class="rtcl-post-section-title">
        <h3><i class="rtcl-icon rtcl-icon-users"></i><?php esc_html_e("Images", "classified-listing"); ?></h3>
    </div>
    <?php Gallery::rtcl_gallery_content($post, array('post_id_input' => '#_post_id')); ?>
</div>