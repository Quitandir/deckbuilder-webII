<?php

class Contato
{
    private $db;

    public $id;
    public $nome;
    public $email;
    public $assunto;
    public $mensagem;
    public $criado_em;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Salva o objeto atual. Mensagens de contato só são criadas, nunca editadas.
    public function salvar()
    {
        $stmt = $this->db->prepare(
            'INSERT INTO contato (nome, email, assunto, mensagem) VALUES (:nome, :email, :assunto, :mensagem)'
        );
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':assunto', $this->assunto);
        $stmt->bindParam(':mensagem', $this->mensagem);

        $sucesso = $stmt->execute();

        if ($sucesso) {
            $this->id = $this->db->lastInsertId();
        }

        return $sucesso;
    }
}
