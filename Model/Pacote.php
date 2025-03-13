<?php

class Pacote {
    private $id;
    private $nome;
    private $descricao;
    private $imagem;
    private $preco;

    public function __construct($id, $nome, $descricao, $imagem, $preco) {
        $this->id = $id;
        $this->nome = $nome;
        $this->descricao = $descricao;
        $this->imagem = $imagem;
        $this->preco = $preco;
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

    public function getImagem() {
        return $this->imagem;
    }

    public function getPreco() {
        return $this->preco;
    }

    // Setters
    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    public function setImagem($imagem) {
        $this->imagem = $imagem;
    }

    public function setPreco($preco) {
        $this->preco = $preco;
    }
}