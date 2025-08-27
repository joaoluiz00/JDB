<?php
require_once __DIR__ . '/ExporterInterface.php';

class JsonExporter implements ExporterInterface {
    public function export(array $data) {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    public function contentType() { return 'application/json; charset=utf-8'; }
    public function extension() { return 'json'; }
}
