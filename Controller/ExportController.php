<?php
require_once __DIR__ . '/../Service/AdminDataProvider.php';
require_once __DIR__ . '/../Model/Export/JsonExporter.php';
require_once __DIR__ . '/../Model/Export/CsvFromJsonAdapter.php';

class ExportController {
    public function download($format = 'json') {
        $provider = new AdminDataProvider();
        $data = $provider->getAllData();

        $exporter = null;
        if ($format === 'csv') {
            $exporter = new CsvFromJsonAdapter(new JsonExporter());
        } else { // default json
            $exporter = new JsonExporter();
        }

        $payload = $exporter->export($data);
        $filename = 'export_' . date('Ymd_His') . '.' . $exporter->extension();

        header('Content-Type: ' . $exporter->contentType());
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        echo $payload;
        exit;
    }
}

// Roteamento simples quando acessado diretamente
if (php_sapi_name() !== 'cli') {
    $format = isset($_GET['format']) ? strtolower($_GET['format']) : 'json';
    (new ExportController())->download($format);
}
