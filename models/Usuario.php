<?php

class Usuario
{

    private $db;

    public $id;
    public $nome;
    public $email;
    public $senha;
    public $data_criacao;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Preenche o objeto com os dados de uma linha (usado pelos métodos de busca)
    private function preencher($dados)
    {
        $this->id = $dados['id'];
        $this->nome = $dados['nome'];
        $this->email = $dados['email'];
        $this->senha = $dados['senha'];
        $this->data_criacao = $dados['data_criacao'];
    }

    // Busca por ID e retorna o próprio objeto preenchido (ou null se não achar)
    public function buscarPorId($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM usuario WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dados) {
            return null;
        }

        $this->preencher($dados);
        return $this;
    }

    // Busca por email (usado no login)
    public function buscarPorEmail($email)
    {
        $stmt = $this->db->prepare('SELECT * FROM usuario WHERE email = :email');
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dados) {
            return null;
        }

        $this->preencher($dados);
        return $this;
    }

    // Gera o token de recuperação de senha
    public function gerarTokenDeRecuperacao()
    {
        if (empty($this->id))
            return false;

        $stmt = $this->db->prepare(
            'UPDATE recuperacao_senha SET expira_em = NOW() WHERE usuario_id = :usuario_id AND usado_em IS NULL AND expira_em > NOW()'
        );
        $stmt->bindParam(':usuario_id', $this->id, PDO::PARAM_INT);

        $stmt->execute();

        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);

        $stmt = $this->db->prepare(
            'INSERT INTO recuperacao_senha(usuario_id, token_hash, expira_em) VALUES (:usuario_id, :token_hash, DATE_ADD(NOW(), INTERVAL 30 MINUTE))'
        );
        $stmt->bindParam(':usuario_id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':token_hash', $hash);

        $sucesso = $stmt->execute();

        if ($sucesso === true) {
            return $token;
        }

        return $sucesso;
    }

    // Busca usuário pelo token de recuperação
    public function buscarPorTokenRecuperacao($token)
    {
        $hash = hash('sha256', $token);

        $stmt = $this->db->prepare(
            'SELECT u.id, u.nome, u.email, u.senha, u.data_criacao    
            FROM usuario u
            INNER JOIN recuperacao_senha r ON u.id = r.usuario_id
            WHERE r.token_hash = :token_hash AND r.usado_em IS NULL AND r.expira_em > NOW();
        '
        );
        $stmt->bindParam(':token_hash', $hash);
        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dados) {
            return null;
        }

        $this->preencher($dados);
        return $this;

    }
    // Redefine e salva nova senha
    public function redefinirSenha($token, $novaSenha)
    {
        if (empty($this->id)) {
            return false;
        }

        $novaSenha = password_hash($novaSenha, PASSWORD_DEFAULT);
        $this->senha = $novaSenha;

        $hashToken = hash('sha256', $token);

        try {
            $this->db->beginTransaction();
            // Atualiza senha do usário
            $this->atualizar();

            // Atualiza usado_em do token
            $stmt = $this->db->prepare(
                'UPDATE recuperacao_senha SET usado_em = NOW()
     WHERE token_hash = :token_hash AND usado_em IS NULL'
            );
            $stmt->bindParam(':token_hash', $hashToken);
            $stmt->execute();

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }

    }

    // Lista todos os usuários (retorna array de objetos Usuario)
    public function listarTodos()
    {
        $stmt = $this->db->query('SELECT * FROM usuario ORDER BY nome');
        $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $usuarios = [];
        foreach ($linhas as $linha) {
            $usuario = new Usuario($this->db);
            $usuario->preencher($linha);
            $usuarios[] = $usuario;
        }

        return $usuarios;
    }

    // Salva o objeto atual: INSERT se não tem id, UPDATE se já tem
    public function salvar()
    {
        if (empty($this->id)) {
            return $this->inserir();
        }
        return $this->atualizar();
    }

    private function inserir()
    {
        $stmt = $this->db->prepare(
            'INSERT INTO usuario (nome, email, senha, termos_aceitos_em) VALUES (:nome, :email, :senha, NOW())'
        );
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':senha', $this->senha);

        $sucesso = $stmt->execute();

        if ($sucesso) {
            $this->id = $this->db->lastInsertId();
        }

        return $sucesso;
    }

    private function atualizar()
    {
        $stmt = $this->db->prepare(
            'UPDATE usuario SET nome = :nome, email = :email, senha = :senha WHERE id = :id'
        );
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':senha', $this->senha);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Deleta o registro correspondente ao id atual do objeto
    public function deletar()
    {
        if (empty($this->id)) {
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM usuario WHERE id = :id');
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}