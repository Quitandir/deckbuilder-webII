# MTG Deck Builder

Sistema web para montar baralhos de **Magic: The Gathering** no formato **Commander**. O usuário cria uma conta, escolhe um comandante, adiciona cartas buscadas na API da [Scryfall](https://scryfall.com/docs/api) e o sistema aplica as regras do formato. Um botão pede a uma IA generativa um comentário com sugestões sobre o baralho.

Esse projeto é  trabalho final da disciplina de Desenvolvimento para Web II, no curso Tecnólogo em Análise e Desenvolvimento de Sistemas.

## Links

| | Endereço |
|---|---|
| **Sistema online** | https://deckbuilderprovi1.websiteseguro.com |
| **Repositório** | https://github.com/Quitandir/deckbuilder-webII |

---

## Funcionalidades

- **Contas de usuário:** cadastro com aceite dos termos de uso, login, logout e recuperação de senha por e-mail.
- **Baralhos:** criar, editar nome, descrição e arquétipos, e excluir. A lista de baralhos mostra o comandante e quantas cartas cada um tem.
- **Editor de cartas (React):** busca com autocompletar, adição de cartas, botões **+** e **−** e contador de cartas, sem recarregar a página.
- **Regras do Commander** aplicadas no servidor:
  - o comandante precisa ser uma criatura lendária, ou uma carta que diga que pode ser comandante;
  - uma cópia de cada carta, exceto terrenos básicos e cartas que permitem várias cópias, como Relentless Rats. Impressões diferentes da mesma carta contam como a mesma, pelo `oracle_id` da Scryfall;
  - no máximo 100 cartas, contando o comandante;
  - toda carta precisa estar dentro da identidade de cor do comandante.
- **Comentário da IA:** analisa o baralho atual (sinergia, base de mana, curva, funções essenciais, progresso) e sugere cartas, num parágrafo em português.
- **Termos de uso, privacidade (LGPD) e direitos autorais:** página pública, com aceite obrigatório no cadastro e a data do aceite registrada.
- **Fale conosco:** formulário público com validação no navegador e no servidor. A mensagem é salva no banco e enviada por e-mail.
- **SEO:** meta tags de descrição, autoria e direitos, Open Graph e dados estruturados (JSON-LD) nas páginas públicas. As páginas que exigem login não são indexadas.
- **Log de todas as ações:** cada requisição fica registrada em arquivo, com a ação, o usuário, o IP e o navegador.
- **Interface responsiva:** funciona no computador e no celular.

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Back-end | PHP 8 com MVC próprio (`index.php` como controlador frontal) e PDO |
| Banco de dados | MySQL / MariaDB |
| Front-end | Views em PHP, React 19 no editor de cartas, Tailwind CSS 4 e Vite 8 |
| Cartas | API pública do Scryfall |
| E-mail | PHPMailer 7, via SMTP do Gmail |
| IA | Claude Haiku 5.5, pela API da Anthropic, com o SDK oficial em PHP |
| Hospedagem | Locaweb, com deploy automático pelo GitHub Actions |

## Banco de dados

O arquivo `database/deck_builder_mtg.sql` cria o banco com 8 tabelas:

| Tabela | Tipo de cadastro |
|---|---|
| `usuario` | Cadastro simples |
| `contato` | Cadastro simples (mensagens do Fale conosco) |
| `baralho` | 1..n: cada usuário tem vários baralhos |
| `baralho_carta` | n..n: baralhos e cartas, com a quantidade de cada carta |
| `baralho_arquetipo` | n..n: baralhos e arquétipos |
| `carta` | Cache das cartas consultadas na Scryfall |
| `arquetipo` | Lista fixa de arquétipos |
| `recuperacao_senha` | Tokens de recuperação de senha |

---

## Instalação local

Pré-requisito: **XAMPP** com **PHP 8.1 ou mais recente**. A versão atual do XAMPP já atende.

1. **Copie a pasta do projeto** para dentro de `C:\xampp\htdocs\`. O endereço padrão espera o caminho `C:\xampp\htdocs\web_ii\projeto_final`.
2. **Configure o ambiente:** se o arquivo `config_local.php` não estiver na raiz, copie o `config_local.exemplo.php` com esse nome. Ele tem os dados do banco e a constante `URL_BASE`, que monta o link enviado no e-mail de recuperação de senha. Se usar outra pasta, ajuste a `URL_BASE`.
3. **Inicie o Apache e o MySQL** no painel do XAMPP.
4. **Importe o banco:** abra `http://localhost/phpmyadmin`, vá em **Importar** e selecione `database/deck_builder_mtg.sql`. O arquivo já cria o banco `deck_builder_mtg` com todas as tabelas e os dados de exemplo.
5. **Acesse** `http://localhost/web_ii/projeto_final/`.

No arquivo enviado pelo Moodle, a pasta `vendor/`, com as bibliotecas PHP, vai junto com o projeto. Quem baixar pelo GitHub precisa rodar `composer install` na raiz, porque o `vendor/` não fica no repositório. A pasta `assets/`, com o React e o CSS já compilados, está nos dois. Não é preciso instalar o Node.js para usar o sistema.

### Login de demonstração

O e-mail e a senha de uma conta com dois baralhos completos, de 100 cartas cada, foram enviados junto com a entrega no Moodle.

Também é possível criar uma conta nova pela tela de cadastro. Ela começa sem baralhos.

---

## Configurações opcionais: e-mail e IA

O sistema funciona sem elas. Sem as credenciais, a recuperação de senha, o envio do Fale conosco e o comentário da IA mostram uma mensagem de erro amigável, e o resto continua normal.

| Recurso | Arquivo | O que preencher |
|---|---|---|
| Recuperação de senha e Fale conosco | `phpmailer_config.php` | Conta Gmail e uma **senha de app**, gerada em https://myaccount.google.com/apppasswords. É preciso ter a verificação em duas etapas ativada |
| Comentário da IA | `ia_config.php` | Chave da API da Anthropic, criada em https://console.anthropic.com. A API é paga por uso. Com o Claude Haiku 5.5, cada comentário custa uma fração de centavo de dólar |

Os arquivos `phpmailer_config.exemplo.php` e `ia_config.exemplo.php` mostram o formato esperado. Se os arquivos de configuração não estiverem na pasta, copie os exemplos e tire o `.exemplo` do nome.

---

## Deploy na Locaweb

O deploy é feito pelo workflow `.github/workflows/deploy.yml`, com a action oficial da Locaweb (`locaweb/ftp-deploy`):

- **A cada push no ramo `main`,** o GitHub envia o projeto por FTP para a pasta `public_html` da hospedagem.
- **O `vendor/` só é enviado quando pedido,** porque tem milhares de arquivos e quase nunca muda. Para enviá-lo, use **Actions → Deploy na Locaweb → Run workflow** e marque a opção "Enviar também a pasta vendor/". Isso só é necessário quando o `composer.lock` muda.
- **Nada com senha passa pelo Git.** O host, o usuário e a senha do FTP ficam nos *secrets* do repositório (`HOST`, `USER` e `PASS`). Os arquivos `config_local.php`, `phpmailer_config.php` e `ia_config.php` do servidor foram criados à mão, pelo WebFTP, com os dados de produção. Como a action nunca apaga arquivos no servidor, eles continuam lá entre um deploy e outro.
- **Não vão para o servidor:** `database/`, `frontend/`, este README e os arquivos de configuração do Git.

---

## Estrutura de pastas

```
projeto_final/
├── index.php                  controlador frontal: escolhe o controller e a ação pela URL e registra cada ação no log
├── config.php                 fuso horário e conexão com o banco
├── config_local.php           dados do banco e URL_BASE de cada ambiente (não versionado)
├── ia_config.php              chave da API de IA (não versionado)
├── phpmailer_config.php       credenciais do e-mail (não versionado)
├── *.exemplo.php              modelos dos três arquivos acima
├── controllers/
│   ├── UsuarioController.php  login, cadastro, logout e recuperação de senha
│   ├── BaralhoController.php  telas e ações dos baralhos, regras do Commander e IA
│   └── PaginaController.php   páginas públicas: termos de uso e Fale conosco
├── models/                    Usuario, Baralho, Carta, Arquetipo e Contato (acesso ao banco)
├── views/
│   ├── partials/              <head> com meta tags, cabeçalho, rodapé e mensagens comuns a todas as telas
│   ├── usuario/               telas de login, cadastro e recuperação de senha
│   ├── baralho/               lista, criação e edição de baralhos
│   └── pagina/                termos de uso e Fale conosco
├── utils/
│   ├── session_utils.php      verificação e encerramento de sessão
│   ├── logger.php             log em arquivo, em utils/logs/auth.log
│   ├── email_utils.php        envio de e-mail com PHPMailer
│   ├── scryfall_utils.php     consultas à API da Scryfall
│   └── ia_utils.php           prompt e chamada à API de IA
├── js/
│   └── autocomplete_comandante.js   busca do comandante na tela de novo baralho
├── img/                       imagem de prévia para links compartilhados (Open Graph)
├── frontend/                  código-fonte do React e do Tailwind
├── assets/                    app.js e app.css gerados pelo build (não editar)
├── database/
│   └── deck_builder_mtg.sql   banco completo para importar
├── .github/workflows/         deploy automático na Locaweb
└── vendor/                    bibliotecas do Composer (não versionado)
```

---

## Segurança

- **Senhas:** guardadas com `password_hash` e conferidas com `password_verify`. Mínimo de 8 caracteres.
- **Sessão:** o id é renovado no login. A sessão expira após 15 minutos sem uso e é encerrada se o IP ou o navegador mudarem.
- **Acesso:** todas as ações de baralho exigem login e conferem se o baralho pertence ao usuário. Métodos privados dos controllers não podem ser chamados pela URL.
- **Recuperação de senha:**
  - o token é aleatório (`random_bytes`) e o banco guarda só o hash SHA-256 dele;
  - o link vale 30 minutos e funciona uma única vez;
  - um pedido novo invalida os anteriores;
  - a tela responde do mesmo jeito para e-mails cadastrados e não cadastrados.
- **Validação:** os formulários são validados no navegador (HTML) e de novo no servidor: no cadastro, senhas iguais e com tamanho mínimo, aceite dos termos e e-mail sem duplicidade; no Fale conosco, campos obrigatórios, tamanhos máximos e formato do e-mail; nos baralhos, nome obrigatório e com tamanho máximo.
- **Banco:** consultas com prepared statements (PDO). A troca de senha e a marcação do token como usado acontecem numa transação.
- **Saída:** o conteúdo vindo do usuário é exibido com `htmlspecialchars`, inclusive nos e-mails enviados pelo Fale conosco.
- **IA:** a chave fica só no servidor. O nome e a descrição do baralho vão para a IA separados das instruções, e o prompt manda ignorar pedidos escritos nesses campos.
- **Logs:** cada requisição é registrada em `utils/logs/auth.log` (eventos `ROUTE`, `ACTION_NOT_FOUND` e `PAGE_NOT_FOUND`), junto com login, falha de login, logout, sessão expirada, troca de senha, uso da IA e falhas no envio de e-mail. A pasta é bloqueada para acesso pelo navegador.
- **Erros:** falhas de conexão com o banco mostram uma mensagem genérica, com código HTTP 503. O detalhe vai para o log de erros do servidor.

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
