<?php

class Carta {
    private $id;
    private $nome;
    private $imagem;
    private $vida;
    private $ataque_basico_nome;
    private $ataque_basico_dano;
    private $ataque_forte_nome;
    private $ataque_forte_dano;
    private $habilidade_recuperacao_nome;
    private $habilidade_recuperacao_valor;
    private $preco;

    public function __construct($id, $nome, $imagem, $vida, $ataque_basico_nome, $ataque_basico_dano, $ataque_forte_nome, $ataque_forte_dano, $habilidade_recuperacao_nome, $habilidade_recuperacao_valor, $preco) {
        $this->id = $id;
        $this->nome = $nome;
        $this->imagem = $imagem;
        $this->vida = $vida;
        $this->ataque_basico_nome = $ataque_basico_nome;
        $this->ataque_basico_dano = $ataque_basico_dano;
        $this->ataque_forte_nome = $ataque_forte_nome;
        $this->ataque_forte_dano = $ataque_forte_dano;
        $this->habilidade_recuperacao_nome = $habilidade_recuperacao_nome;
        $this->habilidade_recuperacao_valor = $habilidade_recuperacao_valor;
        $this->preco = $preco;
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

    public function getAtaqueBasicoNome() {
        return $this->ataque_basico_nome;
    }

    public function getAtaqueBasicoDano() {
        return $this->ataque_basico_dano;
    }

    public function getAtaqueForteNome() {
        return $this->ataque_forte_nome;
    }

    public function getAtaqueForteDano() {
        return $this->ataque_forte_dano;
    }

    public function getHabilidadeRecuperacaoNome() {
        return $this->habilidade_recuperacao_nome;
    }

    public function getHabilidadeRecuperacaoValor() {
        return $this->habilidade_recuperacao_valor;
    }

    public function getPreco() {
        return $this->preco;
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

    public function setAtaqueBasicoNome($ataque_basico_nome) {
        $this->ataque_basico_nome = $ataque_basico_nome;
    }

    public function setAtaqueBasicoDano($ataque_basico_dano) {
        $this->ataque_basico_dano = $ataque_basico_dano;
    }

    public function setAtaqueForteNome($ataque_forte_nome) {
        $this->ataque_forte_nome = $ataque_forte_nome;
    }

    public function setAtaqueForteDano($ataque_forte_dano) {
        $this->ataque_forte_dano = $ataque_forte_dano;
    }

    public function setHabilidadeRecuperacaoNome($habilidade_recuperacao_nome) {
        $this->habilidade_recuperacao_nome = $habilidade_recuperacao_nome;
    }

    public function setHabilidadeRecuperacaoValor($habilidade_recuperacao_valor) {
        $this->habilidade_recuperacao_valor = $habilidade_recuperacao_valor;
    }

    public function setPreco($preco) {
        $this->preco = $preco;
    }
}