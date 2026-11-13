<!DOCTYPE html>
<html lang="pt-br">
<?php
$titulo = 'Termos de uso e privacidade';
$descricaoPagina = 'Termos de uso, política de privacidade (LGPD) e direitos autorais do MTG Deck Builder.';
$paginaPublica = true;
require 'views/partials/head.php';
?>

<body>
    <?php require 'views/partials/cabecalho.php'; ?>

    <main class="conteudo max-w-3xl">
        <h1 class="titulo">Termos de uso, privacidade e direitos autorais</h1>
        <p class="mb-6 text-sm text-gray-600">Última atualização: 13 de novembro de 2026.</p>

        <div class="painel space-y-8">
            <section>
                <h2 class="mb-2 text-xl font-semibold">1. Termos de uso</h2>
                <div class="space-y-2">
                    <p>O MTG Deck Builder é um projeto acadêmico, sem fins comerciais, desenvolvido para a disciplina
                        de Desenvolvimento para Web II. Ele permite montar baralhos de Magic: The Gathering no formato
                        Commander.</p>
                    <p>Ao criar uma conta, você concorda em:</p>
                    <ul class="list-disc space-y-1 pl-6">
                        <li>informar dados verdadeiros e manter sua senha em sigilo;</li>
                        <li>não tentar acessar contas ou dados de outras pessoas;</li>
                        <li>não usar o sistema para sobrecarregar o servidor ou os serviços externos que ele consulta.</li>
                    </ul>
                    <p>Os comentários gerados por inteligência artificial são sugestões automáticas e podem conter
                        erros. Use-os como apoio, não como regra.</p>
                    <p>Por ser um projeto acadêmico, o sistema pode ficar indisponível ou ter dados apagados a qualquer
                        momento, sem aviso prévio.</p>
                </div>
            </section>

            <section>
                <h2 class="mb-2 text-xl font-semibold">2. Privacidade e proteção de dados (LGPD)</h2>
                <div class="space-y-2">
                    <p>Este sistema trata dados pessoais de acordo com a Lei Geral de Proteção de Dados
                        (Lei nº 13.709/2018).</p>

                    <h3 class="font-semibold">Dados coletados</h3>
                    <ul class="list-disc space-y-1 pl-6">
                        <li><strong>Cadastro:</strong> nome e e-mail. A senha nunca é guardada como foi digitada,
                            apenas um código criptográfico (hash) que não permite recuperá-la.</li>
                        <li><strong>Registros de acesso:</strong> endereço IP, navegador utilizado, data, hora e as ações
                            executadas no sistema, guardados por segurança.</li>
                        <li><strong>Cookie de sessão:</strong> um único cookie, necessário para manter você conectado.
                            Não usamos cookies de publicidade ou de rastreamento.</li>
                    </ul>

                    <h3 class="font-semibold">Para que usamos</h3>
                    <p>Para identificar você no login, enviar o link de recuperação de senha, guardar seus baralhos e
                        proteger o sistema contra acessos indevidos. A base legal é a execução do serviço que você
                        solicitou ao criar a conta (art. 7º, V, da LGPD).</p>

                    <h3 class="font-semibold">Com quem compartilhamos</h3>
                    <ul class="list-disc space-y-1 pl-6">
                        <li><strong>Scryfall:</strong> ao buscar cartas, seu navegador consulta diretamente a API da
                            Scryfall, que recebe o termo pesquisado e o seu endereço IP.</li>
                        <li><strong>Anthropic:</strong> ao pedir um comentário da IA, enviamos o nome, a descrição, os
                            arquétipos e a lista de cartas do baralho. Seu nome e seu e-mail não são enviados. Evite
                            colocar dados pessoais no nome ou na descrição dos baralhos.</li>
                        <li><strong>Google (Gmail):</strong> usado para enviar o e-mail de recuperação de senha, que
                            contém seu endereço de e-mail.</li>
                    </ul>
                    <p>Não vendemos nem cedemos seus dados para nenhuma outra finalidade.</p>

                    <h3 class="font-semibold">Seus direitos</h3>
                    <p>Você pode pedir a qualquer momento a confirmação de que tratamos seus dados, o acesso a eles, a
                        correção de dados incorretos e a exclusão da sua conta e dos seus baralhos (art. 18 da LGPD).
                        Para isso, escreva para <a href="mailto:deckbuilderwebii@gmail.com"
                            class="link">deckbuilderwebii@gmail.com</a>.</p>

                    <h3 class="font-semibold">Por quanto tempo guardamos</h3>
                    <p>Os dados da conta ficam guardados enquanto ela existir. Ao excluir a conta, os baralhos são
                        apagados junto. Os registros de acesso são mantidos apenas pelo tempo necessário à segurança
                        do sistema.</p>
                </div>
            </section>

            <section>
                <h2 class="mb-2 text-xl font-semibold">3. Direitos autorais e plágio</h2>
                <div class="space-y-2">
                    <p>O código-fonte deste sistema é original, foi desenvolvido para fins acadêmicos e não pode ser
                        copiado, reproduzido ou apresentado como trabalho de outra pessoa sem autorização do autor.
                        As bibliotecas de terceiros utilizadas (como PHPMailer, React e Tailwind CSS) seguem as suas
                        próprias licenças.</p>
                    <p>O MTG Deck Builder é um conteúdo de fã não oficial, permitido pela Política de Conteúdo de Fãs
                        da Wizards of the Coast. Ele não é aprovado nem endossado pela Wizards. Partes do material
                        utilizado são propriedade da Wizards of the Coast. © Wizards of the Coast LLC.
                        Magic: The Gathering, os nomes e as imagens das cartas pertencem à Wizards of the Coast.</p>
                    <p>Os dados e as imagens das cartas são fornecidos pela <a href="https://scryfall.com"
                            class="link" target="_blank" rel="noopener">Scryfall</a>.</p>
                </div>
            </section>
        </div>
    </main>

    <?php require 'views/partials/rodape.php'; ?>
</body>
</html>
