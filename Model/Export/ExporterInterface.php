<?php
interface ExporterInterface {
    /**
     * Exporta os dados em formato específico (JSON/CSV).
     * @param array $data
     * @return string
     */
    public function export(array $data);

    /** @return string */
    public function contentType();

    /** @return string */
    public function extension();
}
