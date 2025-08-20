<?php
class Icone {
    private $id;
    private $nome;
    private $path;
    private $preco;
    private $precoDinheiro;

    public function __construct($id, $nome, $path, $preco, $precoDinheiro) {
        $this->id = $id;
        $this->nome = $nome;
        $this->path = $path;
        $this->preco = $preco;
        $this->precoDinheiro = $precoDinheiro;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getNome() {
        return $this->nome;
    }

    public function getPath() {
        return $this->path;
    }

    public function getPreco() {
        return $this->preco;
    }

    public function getPrecoDinheiro() {
        return $this->precoDinheiro;
    }

    // Setters
    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function setPreco($preco) {
        $this->preco = $preco;
    }

    public function setPrecoDinheiro($precoDinheiro) {
        $this->precoDinheiro = $precoDinheiro;
    }

}
?>