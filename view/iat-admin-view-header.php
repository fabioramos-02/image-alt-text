<div class="heading">
    <div class="row align-items-center">
        <div class="col-md-6 col-sm-6">
            <img src="<?php echo esc_url(IAT_FILE_URL . 'assets/images/image-alt-text-logo.png'); ?>" alt="<?php esc_attr_e('Image Alt Text', IMAGE_ALT_TEXT); ?>">
        </div>
        <div class="col-md-6 col-sm-6">
            <a href="https://mediahygiene.com" target="_blank">
                <img style="float:right;" src="<?php echo esc_url(IAT_FILE_URL . 'assets/images/media-hygiene-promotion.jpg'); ?>" alt="<?php esc_attr_e('Image Alt Text', IMAGE_ALT_TEXT); ?>">
            </a>
        </div>
    </div>
</div>
<div class="wrap">
    <h2 class="nav-tab-wrapper">
        <?php
        $pages = [
            'with-alt'     => __('Com Texto', IMAGE_ALT_TEXT),
            'without-alt'  => __('Sem Texto', IMAGE_ALT_TEXT),
            'pro-coming-soon' => __('Pro Em breve', IMAGE_ALT_TEXT)
        ];
        foreach ($pages as $slug => $title) {
            $class = isset($_GET['page']) && $_GET['page'] === $slug ? ' nav-tab-active' : '';
            echo sprintf('<a href="%s" class="nav-tab%s">%s</a>', esc_url(admin_url("admin.php?page=$slug")), $class, esc_html($title));
        }
        ?>
    </h2>
</div>