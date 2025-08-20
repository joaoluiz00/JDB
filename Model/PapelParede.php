<?php

class PapelParede {
    private $id;
    private $nome;
    private $path;
    private $preco;
    private $preco_dinheiro;

    public function __construct($id, $nome, $path, $preco, $preco_dinheiro) {
        $this->id = $id;
        $this->nome = $nome;
        $this->path = $path;
        $this->preco = $preco;
        $this->preco_dinheiro = $preco_dinheiro;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNome() { return $this->nome; }
    public function getPath() { return $this->path; }
    public function getPreco() { return $this->preco; }
    public function getPrecoDinheiro() { return $this->preco_dinheiro; }

    // Setters
    public function setNome($nome) { $this->nome = $nome; }
    public function setPath($path) { $this->path = $path; }
    public function setPreco($preco) { $this->preco = $preco; }
    public function setPrecoDinheiro($preco_dinheiro) { $this->preco_dinheiro = $preco_dinheiro; }

}
