<?php

class Arquetipo {

    private $db;

    public $id;
    public $nome;

    public function __construct($db) {
        $this->db = $db;
    }

    // Preenche o objeto com os dados de uma linha
    private function preencher($dados) {
        $this->id = $dados['id'];
        $this->nome = $dados['nome'];
    }

    // Busca um arquétipo por ID
    public function buscarPorId($id) {
        $stmt = $this->db->prepare('SELECT * FROM arquetipo WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dados) {
            return null;
        }

        $this->preencher($dados);
        return $this;
    }

    // Lista todos os arquétipos cadastrados (usado para montar os checkboxes)
    public function listarTodos() {
        $stmt = $this->db->query('SELECT * FROM arquetipo ORDER BY nome');
        $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $arquetipos = [];
        foreach ($linhas as $linha) {
            $arquetipo = new Arquetipo($this->db);
            $arquetipo->preencher($linha);
            $arquetipos[] = $arquetipo;
        }

        return $arquetipos;
    }

    // Salva o objeto atual: INSERT se não tem id, UPDATE se já tem
    public function salvar() {
        if (empty($this->id)) {
            return $this->inserir();
        }
        return $this->atualizar();
    }

    private function inserir() {
        $stmt = $this->db->prepare('INSERT INTO arquetipo (nome) VALUES (:nome)');
        $stmt->bindParam(':nome', $this->nome);

        $sucesso = $stmt->execute();

        if ($sucesso) {
            $this->id = $this->db->lastInsertId();
        }

        return $sucesso;
    }

    private function atualizar() {
        $stmt = $this->db->prepare('UPDATE arquetipo SET nome = :nome WHERE id = :id');
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Deleta o arquétipo atual
    public function deletar() {
        if (empty($this->id)) {
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM arquetipo WHERE id = :id');
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}