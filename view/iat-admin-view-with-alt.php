<?php
$header_file = IAT_FILE_PATH . '/view/iat-admin-view-header.php';
if (file_exists($header_file)) {
    include_once $header_file;
}
?>

<div class="container-fluid p-0">
    <?php include_once IAT_FILE_PATH . '/view/iat-admin-button-view.php'; ?>
    <table class="table table-sm mt-2" id="with-alt-list-table">
        <thead>
            <tr>
                <?php
                $headers = [
                    __('Image', IMAGE_ALT_TEXT),
                    __('Image Title', IMAGE_ALT_TEXT),
                    __('URL', IMAGE_ALT_TEXT),
                    __('Attached To', IMAGE_ALT_TEXT),
                    __('Update Alt Text', IMAGE_ALT_TEXT),
                    __('Date', IMAGE_ALT_TEXT),
                    __('Action', IMAGE_ALT_TEXT)
                ];
                foreach ($headers as $header) {
                    echo "<th>$header</th>";
                }
                ?>
            </tr>
        </thead>
    </table>
</div>