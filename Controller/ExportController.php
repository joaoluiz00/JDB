<?php
session_start();
require_once __DIR__ . '/../Service/AdminDataProvider.php';
require_once __DIR__ . '/../Model/Export/JsonExporter.php';
require_once __DIR__ . '/../Model/Export/CsvFromJsonAdapter.php';

class ExportController {
    public function download($format = 'json') {
        // Restringe somente para admin autenticado
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Acesso negado. Somente administradores podem exportar.';
            exit;
        }

        $provider = new AdminDataProvider();
        // Seleção obrigatória de uma única tabela via ?table=usuarios|cartas|...
        $table = isset($_GET['table']) ? strtolower(trim($_GET['table'])) : null;
        if (!$table) {
            http_response_code(400);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Informe a tabela a exportar via parâmetro ?table=...';
            exit;
        }
        $data = $provider->getDataFor($table);
        if ($data === null) {
            http_response_code(400);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Tabela inválida para exportação.';
            exit;
        }

        $exporter = null;
        if ($format === 'csv') {
            $exporter = new CsvFromJsonAdapter(new JsonExporter());
        } else { // default json
            $exporter = new JsonExporter();
        }

        $payload = $exporter->export($data);
        $suffix = $table ? ($table . '_') : '';
        $filename = 'export_' . $suffix . date('Ymd_His') . '.' . $exporter->extension();

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
