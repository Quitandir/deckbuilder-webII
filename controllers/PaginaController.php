<?php

require_once 'models/Contato.php';
require_once 'utils/email_utils.php';

// Páginas públicas e institucionais: não exigem login
class PaginaController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // GET index.php?modulo=pagina&acao=telaTermos
    public function telaTermos()
    {
        require 'views/pagina/termos.php';
    }

    // GET index.php?modulo=pagina&acao=telaContato
    public function telaContato()
    {
        $erro = $_SESSION['erro_contato'] ?? null;
        unset($_SESSION['erro_contato']);

        $sucesso = $_SESSION['mensagem_contato'] ?? null;
        unset($_SESSION['mensagem_contato']);

        // Guarda todos os erros de validação
        $valores = $_SESSION['valores_contato'] ?? [];
        unset($_SESSION['valores_contato']);

        require 'views/pagina/contato.php';
    }

    // POST index.php?modulo=pagina&acao=enviarContato
    public function enviarContato()
    {
        $nome = $this->lerTexto('nome');
        $email = $this->lerTexto('email');
        $assunto = $this->lerTexto('assunto');
        $mensagem = $this->lerTexto('mensagem');

        // Cada regra só é testada se as anteriores passaram; guarda a primeira que falhar
        $erro = null;

        if ($nome === '' || $email === '' || $assunto === '' || $mensagem === '') {
            $erro = 'Nenhum campo pode estar vazio.';
        } elseif (mb_strlen($nome) > 100) {
            $erro = 'O nome pode ter no máximo 100 caracteres.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            $erro = 'Informe um e-mail válido, com até 150 caracteres.';
        } elseif (mb_strlen($assunto) > 150) {
            $erro = 'O assunto pode ter no máximo 150 caracteres.';
        } elseif (mb_strlen($mensagem) < 10 || mb_strlen($mensagem) > 5000) {
            $erro = 'A mensagem precisa ter entre 10 e 5000 caracteres.';
        }

        if ($erro) {
            $_SESSION['erro_contato'] = $erro;
            $_SESSION['valores_contato'] = compact('nome', 'email', 'assunto', 'mensagem');
            header('Location: index.php?modulo=pagina&acao=telaContato');
            exit;
        }

        $contato = new Contato($this->db);
        $contato->nome = $nome;
        $contato->email = $email;
        $contato->assunto = $assunto;
        $contato->mensagem = $mensagem;

        if ($contato->salvar()) {
            // Se o e-mail falhar, a mensagem já está salva no banco; a função registra a falha no log
            enviar_email_contato($nome, $email, $assunto, $mensagem);

            $_SESSION['mensagem_contato'] = 'Mensagem enviada com sucesso! Responderemos pelo e-mail informado.';
            header('Location: index.php?modulo=pagina&acao=telaContato');
            exit;
        }

        $_SESSION['erro_contato'] = 'Erro ao enviar mensagem de contato.';
        $_SESSION['valores_contato'] = compact('nome', 'email', 'assunto', 'mensagem');
        header('Location: index.php?modulo=pagina&acao=telaContato');
        exit;
    }

    private function lerTexto($campo)
    {
        $valor = $_POST[$campo] ?? '';
        return is_string($valor) ? trim($valor) : '';
    }
}
