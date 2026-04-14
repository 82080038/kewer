<?php
/**
 * DataTable.js Helper Functions
 * 
 * Provides helper functions for DataTable.js integration
 */

/**
 * Initialize DataTable with default configuration
 */
function initDataTable($tableId, $options = []) {
    $defaultOptions = [
        'pageLength' => 25,
        'lengthMenu' => [[10, 25, 50, 100], [10, 25, 50, 100]],
        'language' => [
            'url' => '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
        ],
        'responsive' => true,
        'dom' => '<"row"<"col-sm-6 col-md-6"l><"col-sm-6 col-md-6"f>>' .
                 '<"row"<"col-sm-12"tr>>' .
                 '<"row"<"col-sm-6 col-md-6"i><"col-sm-6 col-md-6"p>>',
        'order' => [[0, 'desc']]
    ];
    
    $finalOptions = array_merge($defaultOptions, $options);
    
    echo "<script>
    $(document).ready(function() {
        $('#{$tableId}').DataTable(" . json_encode($finalOptions) . ");
    });
    </script>";
}

/**
 * Initialize DataTable with server-side processing
 */
function initDataTableServerSide($tableId, $ajaxUrl, $options = []) {
    $defaultOptions = [
        'processing' => true,
        'serverSide' => true,
        'ajax' => $ajaxUrl,
        'pageLength' => 25,
        'lengthMenu' => [[10, 25, 50, 100], [10, 25, 50, 100]],
        'language' => [
            'url' => '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
        ],
        'responsive' => true
    ];
    
    $finalOptions = array_merge($defaultOptions, $options);
    
    echo "<script>
    $(document).ready(function() {
        $('#{$tableId}').DataTable(" . json_encode($finalOptions) . ");
    });
    </script>";
}

/**
 * Get DataTable CDN links
 */
function getDataTableCDN() {
    return [
        'css' => 'https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css',
        'js' => 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',
        'js_bootstrap' => 'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js'
    ];
}
?>
