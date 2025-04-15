<?php
class Administrador {
    private $id;
    private $nome;
    private $email;
    private $senha;
    private $coin;

    public function __construct($id, $nome, $email, $senha, $coin = 0) {
        $this->id = $id;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->coin = $coin;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getNome() {
        return $this->nome;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getSenha() {
        return $this->senha;
    }

    public function getCoin() {
        return $this->coin;
    }

    // Setters
    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setSenha($senha) {
        $this->senha = $senha;
    }

    public function setCoin($coin) {
        $this->coin = $coin;
    }
}