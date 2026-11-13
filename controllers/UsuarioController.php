<?php

require_once 'models/Usuario.php';
require_once 'utils/session_utils.php';
require_once 'utils/email_utils.php';


class UsuarioController
{

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // GET index.php?modulo=usuario&acao=telaLogin
    public function telaLogin()
    {
        $erro = $_SESSION['erro_login'] ?? null;
        unset($_SESSION['erro_login']);

        $sucesso = $_SESSION['sucesso_login'] ?? null;
        unset($_SESSION['sucesso_login']);

        require 'views/usuario/login.php';
    }

    // GET index.php?modulo=usuario&acao=telaCadastro
    public function telaCadastro()
    {
        $erro = $_SESSION['erro_cadastro'] ?? null;
        unset($_SESSION['erro_cadastro']);

        require 'views/usuario/cadastro.php';
    }

    // GET index.php?modulo=usuario&acao=telaRecuperacao
    public function telaRecuperacao()
    {
        // Serve tanto para a mensagem genérica quanto para "link inválido", por isso é um aviso
        $aviso = $_SESSION['recuperacao'] ?? null;
        unset($_SESSION['recuperacao']);

        require 'views/usuario/recuperacaoSenha.php';
    }

    // GET index.php?modulo=usuario&acao=telaNovaSenha
    public function telaNovaSenha()
    {

        $token = $_GET['token'] ?? null;
        $usuario = new Usuario($this->db);

        if (!is_string($token) || !$usuario->buscarPorTokenRecuperacao($token)) {
            $_SESSION['recuperacao'] = 'Link inválido ou expirado.';
            header('Location: index.php?modulo=usuario&acao=telaRecuperacao');
            exit;
        }

        $erro = $_SESSION['erro_nova_senha'] ?? null;
        unset($_SESSION['erro_nova_senha']);
        require 'views/usuario/novaSenha.php';
    }

    // POST index.php?modulo=usuario&acao=login
    public function login()
    {
        $usuario = new Usuario($this->db);
        $encontrado = $usuario->buscarPorEmail($_POST['email']);

        if ($encontrado && password_verify($_POST['senha'], $encontrado->senha)) {
            $_SESSION['usuario_id'] = $encontrado->id;
            $_SESSION['logado'] = true;
            session_regenerate_id(true);
            registrar_fingerprint_sessao();
            registrar_log('INFO', 'LOGIN_SUCCESS', $_SESSION['usuario_id']);
            header('Location: index.php?modulo=baralho&acao=listar');
            exit;
        }

        $_SESSION['erro_login'] = 'Email ou senha inválidos.';
        registrar_log('WARNING', 'LOGIN_FAILED', null, ['email' => $_POST['email']]);
        header('Location: index.php?modulo=usuario&acao=telaLogin');
        exit;
    }

    // função para verificar tamanho da senha
    private function verificaTamanhoSenha($senha)
    {
        return is_string($senha) && strlen($senha) >= 8;
    }

    // POST index.php?modulo=usuario&acao=cadastrar
    public function cadastrar()
    {
        if ($_POST['senha'] !== $_POST['confirma_senha']) {
            $_SESSION['erro_cadastro'] = 'As senhas não são iguais.';
            header('Location: index.php?modulo=usuario&acao=telaCadastro');
            exit;
        }

        if (!$this->verificaTamanhoSenha($_POST['senha'])) {
            $_SESSION['erro_cadastro'] = 'A senha precisa de no mínimo 8 caracteres.';
            header('Location: index.php?modulo=usuario&acao=telaCadastro');
            exit;
        }

        if (empty($_POST['aceite_termos'])) {
            $_SESSION['erro_cadastro'] = 'Você precisa aceitar os termos de uso.';
            header('Location: index.php?modulo=usuario&acao=telaCadastro');
            exit;
        }


        $usuario = new Usuario($this->db);

        $encontrado = $usuario->buscarPorEmail($_POST['email']);
        if ($encontrado) {
            $_SESSION['erro_cadastro'] = 'Email já cadastrado no sistema.';
            header('Location: index.php?modulo=usuario&acao=telaCadastro');
            exit;
        }

        $usuario->nome = $_POST['nome'];
        $usuario->email = $_POST['email'];
        $usuario->senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);


        if ($usuario->salvar()) {
            header('Location: index.php?modulo=usuario&acao=telaLogin');
            exit;
        }
        $_SESSION['erro_cadastro'] = 'Erro ao cadastrar usuário.';
        header('Location: index.php?modulo=usuario&acao=telaCadastro');
        exit;
    }

    public function logout()
    {
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        // Registra o logout ANTES de destruir a sessão (senão perdemos o identificador do usuário)
        registrar_log('INFO', 'LOGOUT', $usuario_id);

        finalizar_sessao();

        header('Location: index.php?modulo=usuario&acao=telaLogin');
        exit;
    }

    public function recuperarSenha()
    {
        $usuario = new Usuario($this->db);
        $encontrado = $usuario->buscarPorEmail($_POST['email']);

        if (!$encontrado) {
            $_SESSION['recuperacao'] = 'Se o e-mail estiver cadastrado, você receberá um link.';
            header('Location: index.php?modulo=usuario&acao=telaRecuperacao');
            exit;
        }


        $token = $encontrado->gerarTokenDeRecuperacao();

        if (!$token) {
            $_SESSION['recuperacao'] = 'Erro ao recuperar a senha.';
            header('Location: index.php?modulo=usuario&acao=telaRecuperacao');
            exit;
        }

        $url = URL_BASE . '/index.php?modulo=usuario&acao=telaNovaSenha&token=' . $token;

        enviar_email_recuperacao_senha($encontrado->email, $url);
        $_SESSION['recuperacao'] = 'Se o e-mail estiver cadastrado, você receberá um link.';
        header('Location: index.php?modulo=usuario&acao=telaRecuperacao');
        exit;
    }

    public function salvarNovaSenha()
    {
        $token = $_POST['token'] ?? null;
        $senha = $_POST['senha'] ?? null;
        $confirma_senha = $_POST['confirma_senha'] ?? null;

        $usuario = new Usuario($this->db);

        if (!is_string($token) || !$usuario->buscarPorTokenRecuperacao($token)) {
            $_SESSION['recuperacao'] = 'Link inválido ou expirado.';
            header('Location: index.php?modulo=usuario&acao=telaRecuperacao');
            exit;
        }

        if (!$this->verificaTamanhoSenha($senha)) {
            $_SESSION['erro_nova_senha'] = 'A senha precisa ter oito ou mais caracteres.';
            header('Location: index.php?modulo=usuario&acao=telaNovaSenha&token=' . urlencode($token));
            exit;
        }

        if ($senha !== $confirma_senha) {
            $_SESSION['erro_nova_senha'] = 'As senhas não são iguais.';
            header('Location: index.php?modulo=usuario&acao=telaNovaSenha&token=' . urlencode($token));
            exit;

        }

        $sucesso = $usuario->redefinirSenha($token, $senha);
        if ($sucesso) {
            registrar_log("INFO", "PASSWORD_RESET", $usuario->id);
            $_SESSION['sucesso_login'] = 'Nova senha cadastrada com sucesso.';
            header('Location: index.php?modulo=usuario&acao=telaLogin');
            exit;
        } else {
            $_SESSION['erro_nova_senha'] = 'Erro ao salvar nova senha.';
            header('Location: index.php?modulo=usuario&acao=telaNovaSenha&token=' . urlencode($token));
            exit;
        }
    }
}
