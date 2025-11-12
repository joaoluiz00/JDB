<?php
/**
 * Classe Notificacao
 * Representa uma notificação do sistema
 */
class Notificacao {
    private $id;
    private $idUsuario;
    private $tipo;
    private $titulo;
    private $mensagem;
    private $lida;
    private $icone;
    private $corFundo;
    private $link;
    private $dataHora;
    private $dadosExtra;

    public function __construct(
        $id = null,
        $idUsuario = null,
        $tipo = '',
        $titulo = '',
        $mensagem = '',
        $lida = false,
        $icone = 'bell',
        $corFundo = '#007bff',
        $link = null,
        $dataHora = null,
        $dadosExtra = null
    ) {
        $this->id = $id;
        $this->idUsuario = $idUsuario;
        $this->tipo = $tipo;
        $this->titulo = $titulo;
        $this->mensagem = $mensagem;
        $this->lida = $lida;
        $this->icone = $icone;
        $this->corFundo = $corFundo;
        $this->link = $link;
        $this->dataHora = $dataHora ?? date('Y-m-d H:i:s');
        $this->dadosExtra = $dadosExtra ? json_encode($dadosExtra) : null;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getIdUsuario() { return $this->idUsuario; }
    public function getTipo() { return $this->tipo; }
    public function getTitulo() { return $this->titulo; }
    public function getMensagem() { return $this->mensagem; }
    public function isLida() { return $this->lida; }
    public function getIcone() { return $this->icone; }
    public function getCorFundo() { return $this->corFundo; }
    public function getLink() { return $this->link; }
    public function getDataHora() { return $this->dataHora; }
    public function getDadosExtra() { 
        return $this->dadosExtra ? json_decode($this->dadosExtra, true) : null; 
    }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setIdUsuario($idUsuario) { $this->idUsuario = $idUsuario; }
    public function setTipo($tipo) { $this->tipo = $tipo; }
    public function setTitulo($titulo) { $this->titulo = $titulo; }
    public function setMensagem($mensagem) { $this->mensagem = $mensagem; }
    public function setLida($lida) { $this->lida = $lida; }
    public function setIcone($icone) { $this->icone = $icone; }
    public function setCorFundo($corFundo) { $this->corFundo = $corFundo; }
    public function setLink($link) { $this->link = $link; }
    public function setDataHora($dataHora) { $this->dataHora = $dataHora; }
    public function setDadosExtra($dadosExtra) { 
        $this->dadosExtra = is_array($dadosExtra) ? json_encode($dadosExtra) : $dadosExtra; 
    }

    /**
     * Retorna o tempo decorrido desde a criação da notificação
     * @return string (ex: "há 5 minutos", "há 2 horas")
     */
    public function getTempoDecorrido(): string {
        $agora = new DateTime();
        $data = new DateTime($this->dataHora);
        $diff = $agora->diff($data);

        if ($diff->y > 0) return "há " . $diff->y . " ano" . ($diff->y > 1 ? "s" : "");
        if ($diff->m > 0) return "há " . $diff->m . " " . ($diff->m > 1 ? "meses" : "mês");
        if ($diff->d > 0) return "há " . $diff->d . " dia" . ($diff->d > 1 ? "s" : "");
        if ($diff->h > 0) return "há " . $diff->h . " hora" . ($diff->h > 1 ? "s" : "");
        if ($diff->i > 0) return "há " . $diff->i . " minuto" . ($diff->i > 1 ? "s" : "");
        return "agora mesmo";
    }

    /**
     * Retorna a classe CSS baseada no tipo de notificação
     * @return string
     */
    public function getClasseCSS(): string {
        $classes = [
            'compra' => 'notif-compra',
            'batalha' => 'notif-batalha',
            'conquista' => 'notif-conquista',
            'sistema' => 'notif-sistema',
            'aviso' => 'notif-aviso',
            'presente' => 'notif-presente'
        ];
        return $classes[$this->tipo] ?? 'notif-default';
    }
}
