<?php

require_once 'models/Baralho.php';
require_once 'models/Arquetipo.php';
require_once 'models/Carta.php';
require_once 'utils/session_utils.php';
require_once 'utils/scryfall_utils.php';
require_once 'utils/ia_utils.php';

class BaralhoController
{
    // No Commander, o baralho tem exatamente 100 cartas, contando o comandante
    private const LIMITE_CARTAS = 100;

    private $db;

    public function __construct($db)
    {
        $this->db = $db;

        // Roda antes de qualquer ação: sem sessão válida, nenhuma tela de baralho abre
        if (!verificar_sessao_ativa()) {
            // Se a sessão expirou, ela já foi destruída: abre uma nova só para levar a mensagem
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
                session_regenerate_id(); // o id antigo teve o cookie apagado; gera um novo e reenvia o cookie
            }

            $_SESSION['erro_login'] = 'Faça login para continuar.';
            header('Location: index.php?modulo=usuario&acao=telaLogin');
            exit;
        }
    }

    // GET index.php?modulo=baralho&acao=telaBaralhos
    public function telaBaralhos()
    {
        $erro = $_SESSION['erro_baralhos'] ?? null;
        unset($_SESSION['erro_baralhos']);

        $baralhos = new Baralho($this->db);
        $baralhosDoUsuario = $baralhos->listarPorUsuario($_SESSION['usuario_id']);

        require 'views/baralho/listaBaralhos.php';
    }

    // GET index.php?modulo=baralho&acao=telaCriarBaralho
    public function telaCriarBaralho()
    {

        $erro = $_SESSION['erro_criar_baralhos'] ?? null;
        unset($_SESSION['erro_criar_baralhos']);

        $arquetipoModel = new Arquetipo($this->db);
        $arquetipos = $arquetipoModel->listarTodos();

        require 'views/baralho/criarBaralhos.php';
    }

    public function telaEditarBaralho()
    {

        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['erro_baralhos'] = 'Baralho não encontrado.';
            header('Location: index.php?modulo=baralho&acao=telaBaralhos');
            exit;
        }

        $baralho = new Baralho($this->db);
        $encontrado = $baralho->buscarPorId($id);

        // Verifica se o baralho existe E se pertence ao usuário logado
        if (!$encontrado || $encontrado->usuario_id != $_SESSION['usuario_id']) {
            $_SESSION['erro_baralhos'] = 'Baralho não encontrado.';
            header('Location: index.php?modulo=baralho&acao=telaBaralhos');
            exit;
        }

        $erro = $_SESSION['erro_editar_baralho'] ?? null;
        unset($_SESSION['erro_editar_baralho']);

        $sucesso = $_SESSION['sucesso_editar_baralho'] ?? null;
        unset($_SESSION['sucesso_editar_baralho']);

        // Ids dos arquétipos já associados a este baralho (pra marcar os checkboxes)
        $idsArquetiposDoBaralho = array_column($baralho->listarArquetipos(), 'id');

        // Todos os arquétipos cadastrados (pra montar a lista completa de checkboxes)
        $arquetipoModel = new Arquetipo($this->db);
        $todosArquetipos = $arquetipoModel->listarTodos();

        require 'views/baralho/editarBaralho.php';
    }

    // GET index.php?modulo=baralho&acao=listar
    // Destino do login; a própria tela de baralhos mostra quando a lista está vazia
    public function listar()
    {
        header('Location: index.php?modulo=baralho&acao=telaBaralhos');
        exit;
    }

    // POST index.php?modulo=baralho&acao=criarBaralho
    public function criarBaralho()
    {

        //var_dump($_POST); // <- comando para testar o que está chegando no post.
        //exit;

        $nome = $this->lerTexto('nome');
        $erroNome = $this->validarNomeBaralho($nome);

        if ($erroNome) {
            $_SESSION['erro_criar_baralhos'] = $erroNome;
            header('Location: index.php?modulo=baralho&acao=telaCriarBaralho');
            exit;
        }

        $comandanteCartaId = $_POST['comandante_carta_id'] ?? '';

        if ($comandanteCartaId === '') {
            $_SESSION['erro_criar_baralhos'] = 'Escolha um comandante antes de criar o baralho.';
            header('Location: index.php?modulo=baralho&acao=telaCriarBaralho');
            exit;
        }

        // O id vem de um campo hidden, então pode ter sido alterado: confere no banco
        $comandante = new Carta($this->db);
        if (!$comandante->buscarPorId($comandanteCartaId) || !$comandante->podeSerComandante()) {
            $_SESSION['erro_criar_baralhos'] = 'O comandante precisa ser uma criatura lendária.';
            header('Location: index.php?modulo=baralho&acao=telaCriarBaralho');
            exit;
        }

        $baralho = new Baralho($this->db);
        $baralho->nome = $nome;
        $baralho->descricao = $this->lerTexto('descricao');
        $baralho->usuario_id = $_SESSION['usuario_id'];
        $baralho->comandante_carta_id = $comandante->id;

        if ($baralho->salvar()) {

            // Agora $baralho->id já existe (foi preenchido dentro de salvar())
            $this->sincronizarArquetipos($baralho, $_POST['arquetipos'] ?? []);

            $baralho->adicionarCarta($baralho->comandante_carta_id);

            header('Location: index.php?modulo=baralho&acao=telaEditarBaralho&id=' . $baralho->id);
            exit;
        }

        $_SESSION['erro_criar_baralhos'] = 'Erro ao cadastrar baralho.';
        header('Location: index.php?modulo=baralho&acao=telaCriarBaralho');
        exit;
    }

    // POST index.php?modulo=baralho&acao=atualizarBaralho
    public function atualizarBaralho()
    {
        $id = $_POST['id'] ?? null;

        $baralho = new Baralho($this->db);
        $encontrado = $id ? $baralho->buscarPorId($id) : null;

        // Verifica se o baralho existe E se pertence ao usuário logado
        if (!$encontrado || $encontrado->usuario_id != $_SESSION['usuario_id']) {
            $_SESSION['erro_baralhos'] = 'Baralho não encontrado.';
            header('Location: index.php?modulo=baralho&acao=telaBaralhos');
            exit;
        }

        $telaEdicao = 'Location: index.php?modulo=baralho&acao=telaEditarBaralho&id=' . $encontrado->id;

        $nome = $this->lerTexto('nome');
        $erroNome = $this->validarNomeBaralho($nome);

        if ($erroNome) {
            $_SESSION['erro_editar_baralho'] = $erroNome;
            header($telaEdicao);
            exit;
        }

        // O comandante não muda: trocar a identidade de cor deixaria cartas fora das regras
        $encontrado->nome = $nome;
        $encontrado->descricao = $this->lerTexto('descricao');

        if (!$encontrado->salvar()) {
            $_SESSION['erro_editar_baralho'] = 'Erro ao salvar o baralho.';
            header($telaEdicao);
            exit;
        }

        $this->sincronizarArquetipos($encontrado, $_POST['arquetipos'] ?? []);

        $_SESSION['sucesso_editar_baralho'] = 'Baralho atualizado.';
        header($telaEdicao);
        exit;
    }

    // Lê um campo de texto do formulário sem os espaços das pontas ('' se não vier texto)
    private function lerTexto($campo)
    {
        $valor = $_POST[$campo] ?? '';
        return is_string($valor) ? trim($valor) : '';
    }

    // Mesma regra para criar e editar; 100 é o tamanho da coluna baralho.nome
    private function validarNomeBaralho($nome)
    {
        if ($nome === '') {
            return 'Dê um nome ao baralho.';
        }

        if (mb_strlen($nome) > 100) {
            return 'O nome do baralho pode ter no máximo 100 caracteres.';
        }

        return null;
    }

    // Deixa no banco exatamente os arquétipos marcados no formulário
    private function sincronizarArquetipos($baralho, $marcados)
    {
        $arquetipoModel = new Arquetipo($this->db);
        $idsValidos = array_map(fn($arquetipo) => $arquetipo->id, $arquetipoModel->listarTodos());

        // Só aceita ids que existem na tabela arquetipo
        $marcados = is_array($marcados) ? array_intersect(array_map('intval', $marcados), $idsValidos) : [];
        $atuais = array_column($baralho->listarArquetipos(), 'id');

        foreach (array_diff($marcados, $atuais) as $arquetipo_id) {
            $baralho->adicionarArquetipo($arquetipo_id);
        }

        foreach (array_diff($atuais, $marcados) as $arquetipo_id) {
            $baralho->removerArquetipo($arquetipo_id);
        }
    }

    // POST index.php?modulo=baralho&acao=deletarBaralho
    public function deletarBaralho()
    {

        $id = $_POST['id'] ?? null;

        if (!$id) {
            $_SESSION['erro_baralhos'] = 'Baralho não encontrado.';
            header('Location: index.php?modulo=baralho&acao=telaBaralhos');
            exit;
        }

        $baralho = new Baralho($this->db);
        $encontrado = $baralho->buscarPorId($id);

        // Verifica se o baralho existe E se pertence ao usuário logado
        if (!$encontrado || $encontrado->usuario_id != $_SESSION['usuario_id']) {
            $_SESSION['erro_baralhos'] = 'Baralho não encontrado ou você não tem permissão para excluí-lo.';
            header('Location: index.php?modulo=baralho&acao=telaBaralhos');
            exit;
        }

        $encontrado->deletar();

        header('Location: index.php?modulo=baralho&acao=telaBaralhos');
        exit;
    }

    // POST index.php?modulo=baralho&acao=adicionarCarta
    public function adicionarCarta()
    {

        header('Content-Type: application/json');

        $baralho_id = $_POST['baralho_id'] ?? null;
        $nomeCarta = $_POST['nome_carta'] ?? null;

        if (!$baralho_id || !$nomeCarta) {
            echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos.']);
            exit;
        }

        $baralho = new Baralho($this->db);
        $encontrado = $baralho->buscarPorId($baralho_id);

        // Confirma que o baralho existe e pertence ao usuário logado
        if (!$encontrado || $encontrado->usuario_id != $_SESSION['usuario_id']) {
            echo json_encode(['sucesso' => false, 'erro' => 'Baralho não encontrado.']);
            exit;
        }

        $dadosScryfall = buscar_carta_scryfall_por_nome($nomeCarta);

        // testa se nome da carta existe
        if ($dadosScryfall === null) {
            echo json_encode(['sucesso' => false, 'erro' => 'Carta não encontrada na Scryfall.']);
            exit;
        }

        // Salva (ou reaproveita) a carta no cache local
        $cartaModel = new Carta($this->db);
        $carta = $cartaModel->buscarOuCriarPorDadosScryfall($dadosScryfall);

        // Bloqueia adicionar a mesma carta que já é o comandante deste baralho
        if ($carta->id == $encontrado->comandante_carta_id) {
            echo json_encode(['sucesso' => false, 'erro' => 'Essa carta já é o comandante deste baralho.']);
            exit;
        }

        $erroRegra = $this->validarNovaCopia($encontrado, $carta);
        if ($erroRegra) {
            echo json_encode(['sucesso' => false, 'erro' => $erroRegra]);
            exit;
        }

        // Vincula ao baralho
        $baralho->adicionarCarta($carta->id, 1);

        echo json_encode([
            'sucesso' => true,
            'carta' => [
                'id' => (int) $carta->id,
                'nome' => $carta->nome,
                'imagem_url' => $carta->imagem_url,
                'custo_mana' => $carta->custo_mana
            ]
        ]);
    }

    //GET index.php?modulo=baralho&acao=listarCartasBaralho
    public function listarCartasBaralho()
    {
        header('Content-Type: application/json');

        $baralho_id = $_GET['baralho_id'] ?? null;

        if (!$baralho_id) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID do baralho não foi encontrado.']);
            exit;
        }

        $baralho = new Baralho($this->db);
        $encontrado = $baralho->buscarPorId($baralho_id);

        if(!$encontrado) {
            echo json_encode(['sucesso' => false, 'erro' => 'Esse baralho não existe.']);
            exit;
        }

        if ($encontrado->usuario_id != $_SESSION['usuario_id']) {
            echo json_encode(['sucesso' => false, 'erro' => 'Esse baralho não pertence ao usuário logado.']);
            exit;
        }


        $listaCartas = $encontrado->listarCartas();
        echo json_encode([
            'sucesso' => true,
            'cartas' => $listaCartas
        ]);
        exit;
    }

    // POST index.php?modulo=baralho&acao=comentarBaralho
    public function comentarBaralho()
    {
        header('Content-Type: application/json');

        $baralho_id = $_POST['baralho_id'] ?? null;

        $baralho = new Baralho($this->db);
        $encontrado = $baralho_id ? $baralho->buscarPorId($baralho_id) : null;

        if (!$encontrado || $encontrado->usuario_id != $_SESSION['usuario_id']) {
            echo json_encode(['sucesso' => false, 'erro' => 'Baralho não encontrado.']);
            exit;
        }

        $comandante = new Carta($this->db);
        if (!$comandante->buscarPorId($encontrado->comandante_carta_id)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Este baralho não tem comandante.']);
            exit;
        }
        $comandante->completarDadosFaltantes();

        $cartas = $encontrado->listarCartas();
        $arquetipos = array_column($encontrado->listarArquetipos(), 'nome');

        // A IA pode levar vários segundos. Liberar a sessão aqui evita que os
        // outros cliques do usuário (como + e −) fiquem esperando essa resposta.
        session_write_close();

        echo json_encode(gerar_comentario_baralho($encontrado, $comandante, $cartas, $arquetipos));
        exit;
    }

    // POST index.php?modulo=baralho&acao=alterarQuantidadeCarta
    public function alterarQuantidadeCarta()
    {

        header('Content-Type: application/json');

        $baralho_id = $_POST['baralho_id'] ?? null;
        $carta_id = $_POST['carta_id'] ?? null;
        $delta = isset($_POST['delta']) ? (int) $_POST['delta'] : 0;

        // A tela só soma ou tira uma cópia por vez; outro valor driblaria as regras
        if (!$baralho_id || !$carta_id || ($delta !== 1 && $delta !== -1)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos.']);
            exit;
        }

        $baralho = new Baralho($this->db);
        $encontrado = $baralho->buscarPorId($baralho_id);

        if (!$encontrado || $encontrado->usuario_id != $_SESSION['usuario_id']) {
            echo json_encode(['sucesso' => false, 'erro' => 'Baralho não encontrado.']);
            exit;
        }

        if ($carta_id == $encontrado->comandante_carta_id) {
            echo json_encode(['sucesso' => false, 'erro' => 'O comandante não pode ter a quantidade alterada.']);
            exit;
        }

        // Tirar uma cópia nunca quebra regra; colocar mais uma precisa passar por elas
        if ($delta === 1) {
            $carta = new Carta($this->db);

            if (!$carta->buscarPorId($carta_id)) {
                echo json_encode(['sucesso' => false, 'erro' => 'Carta não encontrada no baralho.']);
                exit;
            }

            $erroRegra = $this->validarNovaCopia($encontrado, $carta);
            if ($erroRegra) {
                echo json_encode(['sucesso' => false, 'erro' => $erroRegra]);
                exit;
            }
        }

        $resultado = $baralho->alterarQuantidadeCarta($carta_id, $delta);

        if ($resultado === false) {
            echo json_encode(['sucesso' => false, 'erro' => 'Carta não encontrada no baralho.']);
            exit;
        }

        echo json_encode([
            'sucesso' => true,
            'quantidade' => $resultado,
            'removida' => $resultado === 0
        ]);
        exit;
    }

    // Regras do Commander para colocar mais uma cópia de uma carta no baralho.
    // Devolve a mensagem de erro, ou null se a carta pode entrar.
    private function validarNovaCopia($baralho, $carta)
    {
        $comandante = new Carta($this->db);

        if (!$comandante->buscarPorId($baralho->comandante_carta_id)) {
            return 'Este baralho não tem comandante.';
        }

        if (!$carta->completarDadosFaltantes() || !$comandante->completarDadosFaltantes()) {
            return 'Não foi possível consultar a carta na Scryfall. Tente novamente.';
        }

        if ($baralho->contarCartas() >= self::LIMITE_CARTAS) {
            return 'O baralho já tem ' . self::LIMITE_CARTAS . ' cartas.';
        }

        if (!$carta->cabeNaIdentidade($comandante->identidade_cor)) {
            return 'Essa carta tem cores fora da identidade do comandante.';
        }

        if (!$carta->permiteVariasCopias() && $baralho->possuiCartaComOracle($carta->oracle_id)) {
            return 'No Commander só pode haver uma cópia de cada carta, exceto terrenos básicos.';
        }

        return null;
    }

    // POST index.php?modulo=baralho&acao=resolverComandante
    function resolverComandante()
    {

        header('Content-Type: application/json');

        $nomeCarta = $_POST['nome_carta'] ?? null;

        $dadosScryfall = buscar_carta_scryfall_por_nome($nomeCarta);

        if ($dadosScryfall === null) {
            echo json_encode(['sucesso' => false, 'erro' => 'Carta não encontrada na Scryfall.']);
            exit;
        }

        // Salva (ou reaproveita) a carta no cache local
        $cartaModel = new Carta($this->db);
        $carta = $cartaModel->buscarOuCriarPorDadosScryfall($dadosScryfall);

        if (!$carta->podeSerComandante()) {
            echo json_encode(['sucesso' => false, 'erro' => 'O comandante precisa ser uma criatura lendária.']);
            exit;
        }

        echo json_encode([
            'sucesso' => true,
            'carta' => [
                'id' => (int) $carta->id,
                'nome' => $carta->nome,
                'imagem_url' => $carta->imagem_url,
                'custo_mana' => $carta->custo_mana
            ]
        ]);
    }
}
