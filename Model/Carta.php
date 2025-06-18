<?php

class Carta {
    private $id;
    private $nome;
    private $path;
    private $vida;
    private $ataque1;
    private $ataque1_dano;
    private $ataque2;
    private $ataque2_dano;
    private $esquiva;
    private $critico;
    private $preco;
    private $preco_dinheiro;
    private $cor;

    public function __construct($id, $nome, $path, $vida, $ataque1, $ataque1_dano, $ataque2, $ataque2_dano, $esquiva, $critico, $preco, $preco_dinheiro, $cor = 'neutro') {
        $this->id = $id;
        $this->nome = $nome;
        $this->path = $path;
        $this->vida = $vida;
        $this->ataque1 = $ataque1;
        $this->ataque1_dano = $ataque1_dano;
        $this->ataque2 = $ataque2;
        $this->ataque2_dano = $ataque2_dano;
        $this->esquiva = $esquiva;
        $this->critico = $critico;
        $this->preco = $preco;
        $this->preco_dinheiro = $preco_dinheiro;
        $this->cor = $cor;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNome() { return $this->nome; }
    public function getPath() { return $this->path; }
    public function getVida() { return $this->vida; }
    public function getAtaque1() { return $this->ataque1; }
    public function getAtaque1Dano() { return $this->ataque1_dano; }
    public function getAtaque2() { return $this->ataque2; }
    public function getAtaque2Dano() { return $this->ataque2_dano; }
    public function getEsquiva() { return $this->esquiva; }
    public function getCritico() { return $this->critico; }
    public function getPreco() { return $this->preco; }
    public function getPrecoDinheiro() { return $this->preco_dinheiro; }
    public function getCor() { return $this->cor; }

    // Setters
    public function setNome($nome) { $this->nome = $nome; }
    public function setPath($path) { $this->path = $path; }
    public function setVida($vida) { $this->vida = $vida; }
    public function setAtaque1($ataque1) { $this->ataque1 = $ataque1; }
    public function setAtaque1Dano($ataque1_dano) { $this->ataque1_dano = $ataque1_dano; }
    public function setAtaque2($ataque2) { $this->ataque2 = $ataque2; }
    public function setAtaque2Dano($ataque2_dano) { $this->ataque2_dano = $ataque2_dano; }
    public function setEsquiva($esquiva) { $this->esquiva = $esquiva; }
    public function setCritico($critico) { $this->critico = $critico; }
    public function setPreco($preco) { $this->preco = $preco; }
    public function setPrecoDinheiro($preco_dinheiro) { $this->preco_dinheiro = $preco_dinheiro; }
    public function setCor($cor) { $this->cor = $cor; }

    public static function factory(array $data) {
        return new self(
            $data['id'] ?? null,
            $data['nome'] ?? null,
            $data['path'] ?? $data['imagem'] ?? null,
            $data['vida'] ?? null,
            $data['ataque1'] ?? null,
            $data['ataque1_dano'] ?? null,
            $data['ataque2'] ?? null,
            $data['ataque2_dano'] ?? null,
            $data['esquiva'] ?? null,
            $data['critico'] ?? null,
            $data['preco'] ?? null,
            $data['preco_dinheiro'] ?? null,
            $data['cor'] ?? 'neutro'
        );
    }
}