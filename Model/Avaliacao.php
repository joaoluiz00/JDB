<?php

class Avaliacao {
    private $id;
    private $idUsuario;
    private $tipoItem;
    private $idItem;
    private $nota;
    private $comentario;
    private $sentimento;
    private $dataAvaliacao;
    private $imagens;
    private $nomeUsuario;

    public function __construct($id = null, $idUsuario = null, $tipoItem = null, $idItem = null, 
                                $nota = null, $comentario = null, $sentimento = 'NEUTRO', 
                                $dataAvaliacao = null, $imagens = [], $nomeUsuario = null) {
        $this->id = $id;
        $this->idUsuario = $idUsuario;
        $this->tipoItem = $tipoItem;
        $this->idItem = $idItem;
        $this->nota = $nota;
        $this->comentario = $comentario;
        $this->sentimento = $sentimento;
        $this->dataAvaliacao = $dataAvaliacao;
        $this->imagens = $imagens;
        $this->nomeUsuario = $nomeUsuario;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getIdUsuario() { return $this->idUsuario; }
    public function getTipoItem() { return $this->tipoItem; }
    public function getIdItem() { return $this->idItem; }
    public function getNota() { return $this->nota; }
    public function getComentario() { return $this->comentario; }
    public function getSentimento() { return $this->sentimento; }
    public function getDataAvaliacao() { return $this->dataAvaliacao; }
    public function getImagens() { return $this->imagens; }
    public function getNomeUsuario() { return $this->nomeUsuario; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setIdUsuario($idUsuario) { $this->idUsuario = $idUsuario; }
    public function setTipoItem($tipoItem) { $this->tipoItem = $tipoItem; }
    public function setIdItem($idItem) { $this->idItem = $idItem; }
    public function setNota($nota) { 
        if ($nota >= 1 && $nota <= 5) {
            $this->nota = $nota; 
        }
    }
    public function setComentario($comentario) { $this->comentario = $comentario; }
    public function setSentimento($sentimento) { $this->sentimento = $sentimento; }
    public function setDataAvaliacao($dataAvaliacao) { $this->dataAvaliacao = $dataAvaliacao; }
    public function setImagens($imagens) { $this->imagens = $imagens; }
    public function setNomeUsuario($nomeUsuario) { $this->nomeUsuario = $nomeUsuario; }

    // Métodos auxiliares
    public function getNotaEstrelas() {
        $estrelas = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $this->nota) {
                $estrelas .= '★';
            } else {
                $estrelas .= '☆';
            }
        }
        return $estrelas;
    }

    public function getIconeSentimento() {
        switch ($this->sentimento) {
            case 'POSITIVO':
                return '😊';
            case 'NEGATIVO':
                return '😞';
            case 'NEUTRO':
            default:
                return '😐';
        }
    }

    public function getCorSentimento() {
        switch ($this->sentimento) {
            case 'POSITIVO':
                return '#4CAF50';
            case 'NEGATIVO':
                return '#F44336';
            case 'NEUTRO':
            default:
                return '#9E9E9E';
        }
    }
}
