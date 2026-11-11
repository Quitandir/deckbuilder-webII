# MTG Deck Builder

Sistema web para montar baralhos de **Magic: The Gathering** no formato **Commander**. O usuário cria uma conta, escolhe um comandante, adiciona cartas buscadas na API da [Scryfall](https://scryfall.com/docs/api) e o sistema aplica as regras do formato. Um botão pede a uma IA generativa um comentário com sugestões sobre o baralho.

---

## Funcionalidades

- **Contas de usuário:** cadastro, login, logout e recuperação de senha por e-mail.
- **Baralhos:** criar, editar nome, descrição e arquétipos, e excluir. A lista de baralhos mostra o comandante e quantas cartas cada um tem.
- **Editor de cartas (React):** busca com autocompletar, adição de cartas, botões **+** e **−** e contador de cartas, sem recarregar a página.
- **Regras do Commander** aplicadas no servidor:
  - o comandante precisa ser uma criatura lendária, ou uma carta que diga que pode ser comandante;
  - uma cópia de cada carta, exceto terrenos básicos e cartas que permitem várias cópias, como Relentless Rats. Impressões diferentes da mesma carta contam como a mesma, pelo `oracle_id` da Scryfall;
  - no máximo 100 cartas, contando o comandante;
  - toda carta precisa estar dentro da identidade de cor do comandante.
- **Comentário da IA:** analisa o baralho atual (sinergia, base de mana, curva, funções essenciais, progresso) e sugere cartas, num parágrafo em português.
- **Interface responsiva:** funciona no computador e no celular.

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Back-end | PHP 8 com MVC próprio (`index.php` como controlador frontal) e PDO |
| Banco de dados | MySQL / MariaDB (XAMPP) |
| Front-end | Views em PHP, React 19 no editor de cartas, Tailwind CSS 4 e Vite 8 |
| Cartas | API pública da Scryfall |
| E-mail | PHPMailer 7, via SMTP do Gmail |
| IA | Claude Haiku 5.5, pela API da Anthropic, com o SDK oficial em PHP |

---

## Instalação

Pré-requisito: **XAMPP** com **PHP 8.1 ou mais recente**. A versão atual do XAMPP já atende.

1. **Copie a pasta do projeto** para dentro de `C:\xampp\htdocs\`. O endereço padrão espera o caminho `C:\xampp\htdocs\web_ii\projeto_final`.
   - Se usar outra pasta, ajuste a constante `URL_BASE` em `config.php`. Ela monta o link enviado no e-mail de recuperação de senha.
2. **Inicie o Apache e o MySQL** no painel do XAMPP.
3. **Importe o banco:** abra `http://localhost/phpmyadmin`, vá em **Importar** e selecione `database/deck_builder_mtg.sql`. O arquivo já cria o banco `deck_builder_mtg` com todas as tabelas e os dados de exemplo.
4. **Acesse** `http://localhost/web_ii/projeto_final/`.

A pasta `vendor/`, com as bibliotecas PHP, e a pasta `assets/`, com o React e o CSS já compilados, vão junto com o projeto. Não é preciso instalar o Composer nem o Node.js para usar o sistema.

### Login de demonstração

Esta conta já tem dois baralhos completos, com 100 cartas cada:

| E-mail | Senha |
|---|---|
| `` | `[]` |

Também é possível criar uma conta nova pela tela de cadastro. Ela começa sem baralhos.

---

## Configurações opcionais: e-mail e IA

O sistema funciona sem elas. Sem as credenciais, a recuperação de senha e o comentário da IA mostram uma mensagem de erro amigável, e o resto continua normal.

| Recurso | Arquivo | O que preencher |
|---|---|---|
| Recuperação de senha | `phpmailer_config.php` | Conta Gmail e uma **senha de app**, gerada em https://myaccount.google.com/apppasswords. É preciso ter a verificação em duas etapas ativada |
| Comentário da IA | `ia_config.php` | Chave da API da Anthropic, criada em https://console.anthropic.com. A API é paga por uso. Com o Claude Haiku 5.5, cada comentário custa uma fração de centavo de dólar |

Os arquivos `phpmailer_config.exemplo.php` e `ia_config.exemplo.php` mostram o formato esperado. Se os arquivos de configuração não estiverem na pasta, copie os exemplos e tire o `.exemplo` do nome.

---

## Estrutura de pastas

```
projeto_final/
├── index.php                  controlador frontal: escolhe o controller e a ação pela URL
├── config.php                 conexão com o banco, fuso horário e URL_BASE
├── ia_config.php              chave da API de IA (não versionar com a chave real)
├── phpmailer_config.php       credenciais do e-mail (não versionar com a senha real)
├── controllers/
│   ├── UsuarioController.php  login, cadastro, logout e recuperação de senha
│   └── BaralhoController.php  telas e ações dos baralhos, regras do Commander e IA
├── models/                    Usuario, Baralho, Carta e Arquetipo (acesso ao banco)
├── views/
│   ├── partials/              cabeçalho, <head> e mensagens comuns a todas as telas
│   ├── usuario/               telas de login, cadastro e recuperação de senha
│   └── baralho/               lista, criação e edição de baralhos
├── utils/
│   ├── session_utils.php      verificação e encerramento de sessão
│   ├── logger.php             log de auditoria em utils/logs/auth.log
│   ├── email_utils.php        envio de e-mail com PHPMailer
│   ├── scryfall_utils.php     consultas à API da Scryfall
│   └── ia_utils.php           prompt e chamada à API de IA
├── js/
│   └── autocomplete_comandante.js   busca do comandante na tela de novo baralho
├── frontend/                  código-fonte do React e do Tailwind
├── assets/                    app.js e app.css gerados pelo build (não editar)
├── database/
│   └── deck_builder_mtg.sql   banco completo para importar
└── vendor/                    bibliotecas do Composer
```

---

## Segurança

- **Senhas:** guardadas com `password_hash` e conferidas com `password_verify`. Mínimo de 8 caracteres.
- **Sessão:** o id é renovado no login. A sessão expira após 15 minutos sem uso e é encerrada se o IP ou o navegador mudarem.
- **Acesso:** todas as ações de baralho exigem login e conferem se o baralho pertence ao usuário.
- **Recuperação de senha:**
  - o token é aleatório (`random_bytes`) e o banco guarda só o hash SHA-256 dele;
  - o link vale 30 minutos e funciona uma única vez;
  - um pedido novo invalida os anteriores;
  - a tela responde do mesmo jeito para e-mails cadastrados e não cadastrados.
- **Banco:** consultas com prepared statements (PDO). A troca de senha e a marcação do token como usado acontecem numa transação.
- **Saída:** o conteúdo vindo do usuário é exibido com `htmlspecialchars`.
- **IA:** a chave fica só no servidor. O nome e a descrição do baralho vão para a IA separados das instruções, e o prompt manda ignorar pedidos escritos nesses campos.
- **Logs:** login, falha de login, logout, sessão expirada, troca de senha e uso da IA são registrados em `utils/logs/auth.log`, uma pasta bloqueada para acesso pelo navegador.
- **Erros:** falhas de conexão com o banco mostram uma mensagem genérica, com código HTTP 503. O detalhe vai para o log de erros do Apache.

---

## Alterando o React ou o CSS

O código-fonte do editor de cartas e do Tailwind fica em `frontend/`. Depois de qualquer alteração, é preciso gerar o build, porque o site usa os arquivos de `assets/`. Isso exige o **Node.js**:

```bash
cd frontend
npm install
npm run build    # gera assets/app.js e assets/app.css uma vez
npm run watch    # ou: gera de novo a cada arquivo salvo
```

Use sempre o site pelo Apache (`http://localhost/...`), **não** pelo `npm run dev`. O servidor de desenvolvimento do Vite não tem PHP, sessão nem banco.

As classes do Tailwind usadas nas views PHP também entram no build, porque o `frontend/src/index.css` aponta para as pastas `views/` e `js/`.
