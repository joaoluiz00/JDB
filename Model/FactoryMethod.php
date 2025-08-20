<?php

class FactoryMethod {
    public static function create($type, array $data) {
        switch (strtolower($type)) {
            case 'carta':
                require_once __DIR__ . '/Carta.php';
                return new Carta(
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
            case 'icone':
                require_once __DIR__ . '/Icone.php';
                return new Icone(
                    $data['id'] ?? null,
                    $data['nome'] ?? null,
                    $data['path'] ?? null,
                    $data['preco'] ?? null,
                    $data['precoDinheiro'] ?? $data['preco_dinheiro'] ?? null
                );
            case 'pacote':
                require_once __DIR__ . '/Pacote.php';
                return new Pacote(
                    $data['id'] ?? null,
                    $data['nome'] ?? null,
                    $data['descricao'] ?? null,
                    $data['path'] ?? $data['imagem'] ?? null,
                    $data['preco'] ?? null,
                    $data['preco_dinheiro'] ?? 0.00,
                    $data['cor'] ?? 'todos'
                );
            case 'papelparede':
                require_once __DIR__ . '/PapelParede.php';
                return new PapelParede(
                    $data['id'] ?? null,
                    $data['nome'] ?? null,
                    $data['path'] ?? null,
                    $data['preco'] ?? null,
                    $data['preco_dinheiro'] ?? null
                );
            default:
                throw new Exception('Tipo de objeto desconhecido para o FactoryMethod: ' . $type);
        }
    }
}
