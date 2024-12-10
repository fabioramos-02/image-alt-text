<?php
$header_file = IAT_FILE_PATH . '/view/iat-admin-view-header.php';
if (file_exists($header_file)) {
    include_once $header_file;
}
?>

<div class="container-fluid p-0">
    <?php include_once IAT_FILE_PATH . '/view/iat-admin-button-view.php'; ?>
    <table class="table table-sm mt-2" id="without-alt-list-table">
        <thead>
            <tr>
                <?php
                $headers = [
                    __('Imagem', IMAGE_ALT_TEXT),
                    __('Título da Imagem', IMAGE_ALT_TEXT),
                    __('URL', IMAGE_ALT_TEXT),
                    __('Anexado a', IMAGE_ALT_TEXT),
                    __('Atualizar texto alternativo', IMAGE_ALT_TEXT),
                    __('Data', IMAGE_ALT_TEXT),
                    __('Ações', IMAGE_ALT_TEXT)
                ];
                foreach ($headers as $header) {
                    echo "<th>$header</th>";
                }
                ?>
            </tr>
        </thead>
    </table>
</div>