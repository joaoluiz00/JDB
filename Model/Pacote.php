<?php

class Pacote {
    private $id;
    private $nome;
    private $descricao;
    private $path;
    private $preco;
    private $preco_dinheiro;
    private $cor;

    public function __construct($id, $nome, $descricao, $path, $preco, $preco_dinheiro = 0.00, $cor = 'todos') {
        $this->id = $id;
        $this->nome = $nome;
        $this->descricao = $descricao;
        $this->path = $path;
        $this->preco = $preco;
        $this->preco_dinheiro = $preco_dinheiro;
        $this->cor = $cor;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getNome() {
        return $this->nome;
    }

    public function getDescricao() {
        return $this->descricao;
    }

    public function getPath() {
        return $this->path;
    }

    public function getPreco() {
        return $this->preco;
    }

    public function getPrecoDinheiro() {
        return $this->preco_dinheiro;
    }

    public function getCor() {
        return $this->cor;
    }

    // Setters
    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function setPreco($preco) {
        $this->preco = $preco;
    }

    public function setPrecoDinheiro($preco_dinheiro) {
        $this->preco_dinheiro = $preco_dinheiro;
    }

    public function setCor($cor) {
        $this->cor = $cor;
    }

    public static function factory(array $data) {
        return new self(
            $data['id'] ?? null,
            $data['nome'] ?? null,
            $data['descricao'] ?? null,
            $data['path'] ?? $data['imagem'] ?? null,
            $data['preco'] ?? null,
            $data['preco_dinheiro'] ?? 0.00,
            $data['cor'] ?? 'todos'
        );
    }
}