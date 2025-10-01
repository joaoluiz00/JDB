<?php
/**
 * Interface de exportação de dados (ex.: JSON/CSV).
 * Define: export() conteúdo, contentType() MIME e extension() padrão.
 */
interface ExporterInterface {
    /** Serializa $data para o formato do exportador. */
    public function export(array $data);

    /** Retorna o MIME type (ex.: application/json, text/csv). */
    public function contentType();

    /** Extensão padrão (sem ponto). Ex.: json, csv. */
    public function extension();
}
