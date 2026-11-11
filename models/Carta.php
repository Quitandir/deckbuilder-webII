<?php

require_once 'utils/scryfall_utils.php';

class Carta {

    private $db;

    public $id;
    public $scryfall_id;
    public $oracle_id;
    public $nome;
    public $custo_mana;
    public $tipo;
    public $identidade_cor;
    public $texto_oracle;
    public $raridade;
    public $imagem_url;
    public $data_cadastro;

    public function __construct($db) {
        $this->db = $db;
    }

    // Preenche o objeto com os dados de uma linha
    private function preencher($dados) {
        $this->id = $dados['id'];
        $this->scryfall_id = $dados['scryfall_id'];
        $this->oracle_id = $dados['oracle_id'];
        $this->nome = $dados['nome'];
        $this->custo_mana = $dados['custo_mana'];
        $this->tipo = $dados['tipo'];
        $this->identidade_cor = $dados['identidade_cor'];
        $this->texto_oracle = $dados['texto_oracle'];
        $this->raridade = $dados['raridade'];
        $this->imagem_url = $dados['imagem_url'];
        $this->data_cadastro = $dados['data_cadastro'];
    }

    // Copia para o objeto os dados que vêm do JSON da Scryfall
    private function preencherComDadosScryfall($dadosScryfall) {
        // Cartas de duas faces guardam imagem, custo e texto em card_faces
        $frente = $dadosScryfall['card_faces'][0] ?? [];

        $this->oracle_id = $dadosScryfall['oracle_id'] ?? $frente['oracle_id'] ?? null;
        $this->nome = $dadosScryfall['name'];
        $this->custo_mana = $dadosScryfall['mana_cost'] ?? $frente['mana_cost'] ?? null;
        $this->tipo = $dadosScryfall['type_line'] ?? null;
        $this->identidade_cor = implode('', $dadosScryfall['color_identity'] ?? []);
        $this->texto_oracle = $dadosScryfall['oracle_text'] ?? $frente['oracle_text'] ?? null;
        $this->raridade = $dadosScryfall['rarity'] ?? null;
        $this->imagem_url = $dadosScryfall['image_uris']['normal'] ?? $frente['image_uris']['normal'] ?? null;
    }

    // Busca uma carta pelo id interno
    public function buscarPorId($id) {
        $stmt = $this->db->prepare('SELECT * FROM carta WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dados) {
            return null;
        }

        $this->preencher($dados);
        return $this;
    }

    // Busca uma carta pelo UUID da Scryfall
    public function buscarPorScryfallId($scryfall_id) {
        $stmt = $this->db->prepare('SELECT * FROM carta WHERE scryfall_id = :scryfall_id');
        $stmt->bindParam(':scryfall_id', $scryfall_id);
        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dados) {
            return null;
        }

        $this->preencher($dados);
        return $this;
    }

    // Salva o objeto atual: INSERT se não tem id, UPDATE se já tem
    public function salvar() {
        if (empty($this->id)) {
            return $this->inserir();
        }
        return $this->atualizar();
    }

    private function inserir() {
        $stmt = $this->db->prepare(
            'INSERT INTO carta (scryfall_id, oracle_id, nome, custo_mana, tipo, identidade_cor, texto_oracle, raridade, imagem_url)
             VALUES (:scryfall_id, :oracle_id, :nome, :custo_mana, :tipo, :identidade_cor, :texto_oracle, :raridade, :imagem_url)'
        );
        $stmt->bindParam(':scryfall_id', $this->scryfall_id);
        $stmt->bindParam(':oracle_id', $this->oracle_id);
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':custo_mana', $this->custo_mana);
        $stmt->bindParam(':tipo', $this->tipo);
        $stmt->bindParam(':identidade_cor', $this->identidade_cor);
        $stmt->bindParam(':texto_oracle', $this->texto_oracle);
        $stmt->bindParam(':raridade', $this->raridade);
        $stmt->bindParam(':imagem_url', $this->imagem_url);

        $sucesso = $stmt->execute();

        if ($sucesso) {
            $this->id = $this->db->lastInsertId();
        }

        return $sucesso;
    }

    private function atualizar() {
        $stmt = $this->db->prepare(
            'UPDATE carta SET oracle_id = :oracle_id, nome = :nome, custo_mana = :custo_mana, tipo = :tipo,
             identidade_cor = :identidade_cor, texto_oracle = :texto_oracle, raridade = :raridade, imagem_url = :imagem_url
             WHERE id = :id'
        );
        $stmt->bindParam(':oracle_id', $this->oracle_id);
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':custo_mana', $this->custo_mana);
        $stmt->bindParam(':tipo', $this->tipo);
        $stmt->bindParam(':identidade_cor', $this->identidade_cor);
        $stmt->bindParam(':texto_oracle', $this->texto_oracle);
        $stmt->bindParam(':raridade', $this->raridade);
        $stmt->bindParam(':imagem_url', $this->imagem_url);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Recebe os dados já decodificados do JSON da Scryfall e devolve
    // um objeto Carta salvo no banco — reaproveitando se já existir,
    // inserindo se ainda não existir (upsert por scryfall_id)
    public function buscarOuCriarPorDadosScryfall($dadosScryfall) {

        $existente = $this->buscarPorScryfallId($dadosScryfall['id']);

        if ($existente) {
            // Cartas salvas antes das regras do Commander não têm oracle_id nem identidade de cor
            if (empty($this->oracle_id)) {
                $this->preencherComDadosScryfall($dadosScryfall);
                $this->salvar();
            }
            return $existente;
        }

        $this->scryfall_id = $dadosScryfall['id'];
        $this->preencherComDadosScryfall($dadosScryfall);
        $this->salvar();

        return $this;
    }

    // Completa, pela Scryfall, uma carta salva antes das regras do Commander.
    // Devolve false se os dados continuarem faltando.
    public function completarDadosFaltantes() {
        if (!empty($this->oracle_id)) {
            return true;
        }

        $dadosScryfall = buscar_carta_scryfall_por_id($this->scryfall_id);

        if ($dadosScryfall === null) {
            return false;
        }

        $this->preencherComDadosScryfall($dadosScryfall);
        return $this->salvar();
    }

    // ---- Regras do Commander ----

    // Pode ser comandante: criatura lendária (na face da frente) ou carta que diz isso no texto
    public function podeSerComandante() {
        $tipoDaFrente = explode(' // ', $this->tipo ?? '')[0];
        $criaturaLendaria = str_contains($tipoDaFrente, 'Legendary') && str_contains($tipoDaFrente, 'Creature');

        return $criaturaLendaria || str_contains($this->texto_oracle ?? '', 'can be your commander');
    }

    // Terrenos básicos e cartas como Relentless Rats podem ter várias cópias
    public function permiteVariasCopias() {
        return str_starts_with($this->tipo ?? '', 'Basic')
            || str_contains($this->texto_oracle ?? '', 'any number of cards named');
    }

    // Cabe no baralho se todas as cores da carta estiverem na identidade do comandante
    public function cabeNaIdentidade($identidadeComandante) {
        // Carta incolor (identidade vazia) cabe em qualquer baralho
        if ($this->identidade_cor === '') {
            return true;
        }

        foreach (str_split($this->identidade_cor) as $cor) {
            if (!str_contains($identidadeComandante, $cor)) {
                return false;
            }
        }

        return true;
    }
}
