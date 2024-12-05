<?php
include_once IAT_FILE_PATH . '/includes/class-iat.php';
$page = isset($_GET['page']) ? $_GET['page'] : '';
$class_iat_obj = new class_iat();
$with_alt_media_count = $class_iat_obj->fn_iat_with_alt_media_count();
$without_alt_media_count = $class_iat_obj->fn_iat_without_alt_media_count();
?>
<?php if (($page == 'with-alt' && $with_alt_media_count) || ($page == 'without-alt' && $without_alt_media_count)) { ?>
    <div class="iat-button-area d-flex gap-2 mt-3 mb-3 flex-wrap">
        <div class="iat-copy-bulk-post-title-to-alt-text-area">
            <form id="iat-copy-bulk-post-title-to-alt-text-form" method="post">
                <input type="hidden" name="action" value="iat_copy_bulk_post_title_to_alt_text">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('iat_copy_bulk_post_title_to_alt_text'); ?>">
                <input type="hidden" id="iat_ajax_call" name="ajax_call" value="0" />
                <input type="hidden" id="iat_page" name="page" value="<?php echo $page; ?>" />
                <button type="button" class="btn btn-secondary btn-sm" id="iat-copy-bulk-post-title-to-alt-text-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Using this button will copy the Image Title as alt text to all media files.">
                    <i class="loader me-1" id="iat-copy-bulk-post-title-to-alt-text-loader" style="display:none;"></i>
                    <?php _e('Bulk Alt Text By Image Title', IMAGE_ALT_TEXT); ?>
                </button>
            </form>
        </div>
        <div class="iat-copy-bulk-attached-post-title-to-alt-text-area">
            <form id="iat-copy-bulk-attached-post-title-to-alt-text-form" method="post">
                <input type="hidden" name="action" value="iat_copy_bulk_attached_post_title_to_alt_text">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('iat_copy_bulk_attached_post_title_to_alt_text'); ?>">
                <input type="hidden" id="iat_ajax_call" name="ajax_call" value="0" />
                <input type="hidden" id="iat_page" name="page" value="<?php echo $page; ?>" />
                <button type="button" class="btn btn-secondary btn-sm" id="iat-copy-bulk-attached-post-title-to-alt-text-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Using this button will copy the attached page/post title as alt text to all media files.">
                    <i class="loader me-1" id="iat-copy-bulk-attached-post-title-to-alt-text-loader" style="display:none;"></i>
                    <?php _e('Bulk Alt Text By Attached Page/Post Title', IMAGE_ALT_TEXT); ?>
                </button>
            </form>
        </div>
    </div>
<?php } ?>