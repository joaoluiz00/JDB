<?php
require_once __DIR__ . '/ExporterInterface.php';
require_once __DIR__ . '/JsonExporter.php';

class CsvFromJsonAdapter implements ExporterInterface {
    private $jsonExporter;

    public function __construct(JsonExporter $jsonExporter) {
        $this->jsonExporter = $jsonExporter;
    }

    public function export(array $data) {
        $json = $this->jsonExporter->export($data);
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            $decoded = [];
        }

        $rows = $this->normalize($decoded);
        if (empty($rows)) {
            return '';
        }

        $headers = array_keys($rows[0]);

        $fp = fopen('php://temp', 'r+');
        // BOM UTF-8 para Excel
        fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($fp, $headers, ';');

        foreach ($rows as $row) {
            $ordered = [];
            foreach ($headers as $h) {
                $value = $row[$h] ?? '';
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }
                $ordered[] = $value;
            }
            fputcsv($fp, $ordered, ';');
        }

        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        return $csv ?: '';
    }

    public function contentType() { return 'text/csv; charset=utf-8'; }
    public function extension() { return 'csv'; }

    private function normalize($decoded) {
        if (!isset($decoded[0]) || !is_array($decoded[0])) {
            $decoded = [$decoded];
        }

        $flatRows = [];
        foreach ($decoded as $row) {
            $flatRows[] = $this->flatten($row);
        }

        $allKeys = [];
        foreach ($flatRows as $r) {
            $allKeys = array_unique(array_merge($allKeys, array_keys($r)));
        }

        $normalized = [];
        foreach ($flatRows as $r) {
            $complete = [];
            foreach ($allKeys as $k) {
                $complete[$k] = $r[$k] ?? '';
            }
            $normalized[] = $complete;
        }
        return $normalized;
    }

    private function flatten($item, $prefix = '') {
        $result = [];
        if (is_array($item)) {
            foreach ($item as $k => $v) {
                $key = $prefix === '' ? (string)$k : $prefix . '.' . $k;
                if (is_array($v)) {
                    $result += $this->flatten($v, $key);
                } else {
                    $result[$key] = $v;
                }
            }
        } else {
            $result[$prefix === '' ? 'value' : $prefix] = $item;
        }
        return $result;
    }
}
