<?php

class Baralho
{

    private $db;
    public $id;
    public $usuario_id;
    public $nome;
    public $descricao;
    public $data_criacao;
    public $comandante_carta_id;

    // Preenchidos só pelo listarPorUsuario, para os cards da lista de baralhos
    public $comandante_nome;
    public $comandante_imagem;
    public $total_cartas;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Preenche o objeto com os dados de uma linha
    private function preencher($dados)
    {
        $this->id = $dados['id'];
        $this->usuario_id = $dados['usuario_id'];
        $this->nome = $dados['nome'];
        $this->descricao = $dados['descricao'];
        $this->data_criacao = $dados['data_criacao'];
        $this->comandante_carta_id = $dados['comandante_carta_id'];
        $this->comandante_nome = $dados['comandante_nome'] ?? null;
        $this->comandante_imagem = $dados['comandante_imagem'] ?? null;
        $this->total_cartas = $dados['total_cartas'] ?? null;
    }

    // Busca um baralho por ID
    public function buscarPorId($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM baralho WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dados) {
            return null;
        }

        $this->preencher($dados);
        return $this;
    }

    // Lista todos os baralhos de um usuário específico
    public function listarPorUsuario($usuario_id)
    {
        $stmt = $this->db->prepare(
            'SELECT b.*, c.nome AS comandante_nome, c.imagem_url AS comandante_imagem,
                (SELECT COALESCE(SUM(bc.quantidade), 0) FROM baralho_carta bc WHERE bc.baralho_id = b.id) AS total_cartas
             FROM baralho b
             LEFT JOIN carta c ON c.id = b.comandante_carta_id
             WHERE b.usuario_id = :usuario_id
             ORDER BY b.nome'
        );
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();

        $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $baralhos = [];
        foreach ($linhas as $linha) {
            $baralho = new Baralho($this->db);
            $baralho->preencher($linha);
            $baralhos[] = $baralho;
        }

        return $baralhos;
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
            'INSERT INTO baralho (usuario_id, nome, descricao, comandante_carta_id) VALUES (:usuario_id, :nome, :descricao, :comandante_carta_id)'
        );
        $stmt->bindParam(':usuario_id', $this->usuario_id, PDO::PARAM_INT);
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':descricao', $this->descricao);
        $stmt->bindParam(':comandante_carta_id', $this->comandante_carta_id);

        $sucesso = $stmt->execute();

        if ($sucesso) {
            $this->id = $this->db->lastInsertId();
        }

        return $sucesso;
    }

    private function atualizar()
    {
        $stmt = $this->db->prepare(
            'UPDATE baralho SET nome = :nome, descricao = :descricao, comandante_carta_id = :comandante_carta_id WHERE id = :id'
        );
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':descricao', $this->descricao);
        $stmt->bindParam(':comandante_carta_id', $this->comandante_carta_id);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Deleta o baralho atual
    public function deletar()
    {
        if (empty($this->id)) {
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM baralho WHERE id = :id');
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // ---- Métodos relacionados a arquétipos (relação N:N) ----

    // Associa um arquétipo (por id) a este baralho
    public function adicionarArquetipo($arquetipo_id)
    {
        if (empty($this->id)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO baralho_arquetipo (baralho_id, arquetipo_id) VALUES (:baralho_id, :arquetipo_id)'
        );
        $stmt->bindParam(':baralho_id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':arquetipo_id', $arquetipo_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Remove a associação de um arquétipo deste baralho
    public function removerArquetipo($arquetipo_id)
    {
        if (empty($this->id)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'DELETE FROM baralho_arquetipo WHERE baralho_id = :baralho_id AND arquetipo_id = :arquetipo_id'
        );
        $stmt->bindParam(':baralho_id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':arquetipo_id', $arquetipo_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Lista os arquétipos (nome e id) associados a este baralho
    public function listarArquetipos()
    {
        if (empty($this->id)) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT a.id, a.nome
             FROM arquetipo a
             INNER JOIN baralho_arquetipo ba ON ba.arquetipo_id = a.id
             WHERE ba.baralho_id = :baralho_id
             ORDER BY a.nome'
        );
        $stmt->bindParam(':baralho_id', $this->id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Adiciona uma carta ao baralho. Se já existir, soma a quantidade.
    public function adicionarCarta($carta_id, $quantidade = 1)
    {
        if (empty($this->id)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO baralho_carta (baralho_id, carta_id, quantidade)
         VALUES (:baralho_id, :carta_id, :quantidade)
         ON DUPLICATE KEY UPDATE quantidade = quantidade + :quantidade_incremento'
        );
        $stmt->bindParam(':baralho_id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':carta_id', $carta_id, PDO::PARAM_INT);
        $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
        $stmt->bindParam(':quantidade_incremento', $quantidade, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Lista as cartas do baralho, já com quantidade e dados da carta (JOIN)
    public function listarCartas()
    {
        if (empty($this->id)) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT c.id, c.nome, c.custo_mana, c.tipo, c.imagem_url, bc.quantidade
         FROM carta c
         INNER JOIN baralho_carta bc ON bc.carta_id = c.id
         WHERE bc.baralho_id = :baralho_id
         ORDER BY (c.id = :comandante_id) DESC, c.nome'
        );
        $stmt->bindParam(':baralho_id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':comandante_id', $this->comandante_carta_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function alterarQuantidadeCarta($carta_id, $delta)
    {
        if (empty($this->id)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT quantidade FROM baralho_carta WHERE baralho_id = :baralho_id AND carta_id = :carta_id'
        );
        $stmt->bindParam(':baralho_id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':carta_id', $carta_id, PDO::PARAM_INT);
        $stmt->execute();
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$linha) {
            return false;
        }

        $novaQuantidade = $linha['quantidade'] + $delta;

        if ($novaQuantidade <= 0) {
            $stmt = $this->db->prepare(
                'DELETE FROM baralho_carta WHERE baralho_id = :baralho_id AND carta_id = :carta_id'
            );
            $stmt->bindParam(':baralho_id', $this->id, PDO::PARAM_INT);
            $stmt->bindParam(':carta_id', $carta_id, PDO::PARAM_INT);
            $stmt->execute();
            return 0;
        }

        $stmt = $this->db->prepare(
            'UPDATE baralho_carta SET quantidade = :quantidade WHERE baralho_id = :baralho_id AND carta_id = :carta_id'
        );
        $stmt->bindParam(':quantidade', $novaQuantidade, PDO::PARAM_INT);
        $stmt->bindParam(':baralho_id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':carta_id', $carta_id, PDO::PARAM_INT);
        $stmt->execute();

        return $novaQuantidade;
    }

    public function obterComandante()
    {
        if (empty($this->comandante_carta_id)) {
            return null;
        }

        $stmt = $this->db->prepare('SELECT * FROM carta WHERE id = :id');
        $stmt->bindParam(':id', $this->comandante_carta_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ---- Métodos usados pelas regras do Commander ----

    // Soma as quantidades de todas as cartas do baralho, incluindo o comandante
    public function contarCartas()
    {
        if (empty($this->id)) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(quantidade), 0) FROM baralho_carta WHERE baralho_id = :baralho_id'
        );
        $stmt->bindParam(':baralho_id', $this->id, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    // Diz se o baralho já tem alguma impressão da carta (o oracle_id é igual em todas)
    public function possuiCartaComOracle($oracle_id)
    {
        if (empty($this->id)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
         FROM baralho_carta bc
         INNER JOIN carta c ON c.id = bc.carta_id
         WHERE bc.baralho_id = :baralho_id AND c.oracle_id = :oracle_id'
        );
        $stmt->bindParam(':baralho_id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':oracle_id', $oracle_id);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }
}