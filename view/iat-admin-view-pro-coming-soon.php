<?php
$header_file = IAT_FILE_PATH . '/view/iat-admin-view-header.php';
if (file_exists($header_file)) {
    include_once $header_file;
}
?>

<div class="container-fluid">
    <div class="wrap">
        <div class="row">
            <p style="font-size:18px;margin:20px 0;color:#003566;"><?php _e('We\'re excited to announce the upcoming launch of our new SEO and Accessibility Plugin, designed to optimize your site\'s images and ensure best practices are met seamlessly. This plugin will automatically add customizable prefixes and postfixes to image names, notify you of any images missing alt text, and provide a comprehensive list of images that may need attention—such as those with duplicate names or non-SEO-friendly filenames. Additionally, it will detect and flag images lacking appropriate ARIA labels on the frontend, helping you meet accessibility standards effortlessly. Stay tuned for a smoother, smarter approach to image management and SEO compliance!', IMAGE_ALT_TEXT) ?></p>
            <p style="font-size:18px;margin:20px 0;color:#003566;font-weight:800;"><?php _e('Features', IMAGE_ALT_TEXT) ?></p>
            <ul style="list-style-type: none; font-family: Arial, sans-serif; font-size: 16px; color: #333;">
                <li>&#10003; Prefix and Postfix to Image Alt Text.</li>
                <li>&#10003; Detect images without Alt Text on webpages.</li>
                <li>&#10003; Notify Images without Alt Text Reminder.</li>
                <li>&#10003; List the Images that are not following SEO Standards.</li>
                <li>&#10003; List the Images with Duplicate Names.</li>
                <li>&#10003; List the Images with Duplicate Alt Tags.</li>
                <li>&#10003; List the Duplicate Image File Names.</li>
                <li>&#10003; Detect missing Aria label on webpages.</li>
            </ul>

        </div>
        <div class="row col-lg-2">
            <a href="https://imagealttext.in" target="_blank"><button class="btn" style="margin:0 auto;background-color:#003566;color:#fff;"><?php _e('Subscribe to Get Notified', IMAGE_ALT_TEXT); ?></button></a>
        </div>
    </div>
</div>