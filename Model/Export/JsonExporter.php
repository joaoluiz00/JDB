<?php
require_once __DIR__ . '/ExporterInterface.php';

/** Exporta dados para JSON (unicode sem escape e pretty print). */
class JsonExporter implements ExporterInterface {
    /** Retorna JSON de $data. */
    public function export(array $data) {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    /** MIME type do JSON. */
    public function contentType() { return 'application/json; charset=utf-8'; }
    /** Extensão padrão. */
    public function extension() { return 'json'; }
}
