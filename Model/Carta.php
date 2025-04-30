<?php

class Carta {
    private $id;
    private $nome;
    private $imagem;
    private $vida;
    private $ataque1;
    private $ataque1_dano;
    private $ataque2;
    private $ataque2_dano;
    private $esquiva;
    private $critico;
    private $preco;
    private $preco_dinheiro; // Novo campo

    public function __construct($id, $nome, $imagem, $vida, $ataque1, $ataque1_dano, $ataque2, $ataque2_dano, $esquiva, $critico, $preco, $preco_dinheiro) {
        $this->id = $id;
        $this->nome = $nome;
        $this->imagem = $imagem;
        $this->vida = $vida;
        $this->ataque1 = $ataque1;
        $this->ataque1_dano = $ataque1_dano;
        $this->ataque2 = $ataque2;
        $this->ataque2_dano = $ataque2_dano;
        $this->esquiva = $esquiva;
        $this->critico = $critico;
        $this->preco = $preco;
        $this->preco_dinheiro = $preco_dinheiro; // Novo campo
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getNome() {
        return $this->nome;
    }

    public function getImagem() {
        return $this->imagem;
    }

    public function getVida() {
        return $this->vida;
    }

    public function getAtaque1() {
        return $this->ataque1;
    }

    public function getAtaque1Dano() {
        return $this->ataque1_dano;
    }

    public function getAtaque2() {
        return $this->ataque2;
    }

    public function getAtaque2Dano() {
        return $this->ataque2_dano;
    }

    public function getEsquivaCritico() {
        return $this->esquiva_critico;
    }

    public function getPreco() {
        return $this->preco;
    }

    public function getEsquiva() {
        return $this->esquiva;
    }

    public function getCritico() {
        return $this->critico;
    }

    public function getPrecoDinheiro() {
        return $this->preco_dinheiro;
    }

    // Setters
    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setImagem($imagem) {
        $this->imagem = $imagem;
    }

    public function setVida($vida) {
        $this->vida = $vida;
    }

    public function setAtaque1($ataque1) {
        $this->ataque1 = $ataque1;
    }

    public function setAtaque1Dano($ataque1_dano) {
        $this->ataque1_dano = $ataque1_dano;
    }

    public function setAtaque2($ataque2) {
        $this->ataque2 = $ataque2;
    }

    public function setAtaque2Dano($ataque2_dano) {
        $this->ataque2_dano = $ataque2_dano;
    }

    public function setEsquivaCritico($esquiva_critico) {
        $this->esquiva_critico = $esquiva_critico;
    }

    public function setPreco($preco) {
        $this->preco = $preco;
    }

    public function setEsquiva($esquiva) {
        $this->esquiva = $esquiva;
    }

    public function setCritico($critico) {
        $this->critico = $critico;
    }

    public function setPrecoDinheiro($preco_dinheiro) {
        $this->preco_dinheiro = $preco_dinheiro;
    }
}