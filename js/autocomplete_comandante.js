const inputBusca = document.getElementById('buscaCarta');
const listaSugestoes = document.getElementById('listaSugestoes');
const erroComandante = document.getElementById('erroComandante');
const campoComandante = document.getElementById('comandanteCartaId');
const quadroComandante = document.getElementById('comandante-frame');

let timeoutId = null;

inputBusca.addEventListener('input', () => {
    const termo = inputBusca.value.trim();

    clearTimeout(timeoutId);

    if (termo.length < 2) {
        listaSugestoes.style.display = 'none';
        return;
    }

    timeoutId = setTimeout(() => buscarSugestoes(termo), 300);
});

// Fecha a lista de sugestões ao clicar fora do campo
document.addEventListener('click', (evento) => {
    if (evento.target !== inputBusca) {
        listaSugestoes.style.display = 'none';
    }
});

async function buscarSugestoes(termo) {
    try {
        const resposta = await fetch(`https://api.scryfall.com/cards/autocomplete?q=${encodeURIComponent(termo)}`);
        const dados = await resposta.json();

        exibirSugestoes(dados.data || []);
    } catch (erro) {
        console.error('Erro ao buscar cartas:', erro);
    }
}

function exibirSugestoes(nomes) {
    listaSugestoes.innerHTML = '';

    if (nomes.length === 0) {
        listaSugestoes.style.display = 'none';
        return;
    }

    nomes.forEach(nome => {
        const item = document.createElement('li');
        const botao = document.createElement('button');
        botao.type = 'button';
        botao.className = 'item-sugestao';
        botao.textContent = nome;

        botao.addEventListener('click', () => {
            inputBusca.value = '';
            listaSugestoes.style.display = 'none';
            capturarComandante(nome);
        });

        item.appendChild(botao);
        listaSugestoes.appendChild(item);
    });

    listaSugestoes.style.display = 'block';
}

function mostrarErro(mensagem) {
    // Limpa a escolha anterior para a tela mostrar exatamente o que será enviado
    campoComandante.value = '';
    quadroComandante.innerHTML = '';

    erroComandante.textContent = mensagem;
    erroComandante.hidden = false;
}

async function capturarComandante(nomeCarta) {
    erroComandante.hidden = true;

    try {
        const corpo = new URLSearchParams();
        corpo.append('nome_carta', nomeCarta);

        const resposta = await fetch('index.php?modulo=baralho&acao=resolverComandante', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: corpo
        });

        const dados = await resposta.json();

        if (!dados.sucesso) {
            mostrarErro(dados.erro);
            return;
        }

        campoComandante.value = dados.carta.id;
        quadroComandante.innerHTML = '';

        if (dados.carta.imagem_url) {
            const img = document.createElement('img');
            img.src = dados.carta.imagem_url;
            img.alt = dados.carta.nome;
            img.className = 'w-full rounded-lg';
            quadroComandante.appendChild(img);
        }

        const legenda = document.createElement('p');
        legenda.className = 'mt-1 text-sm text-gray-700';
        legenda.textContent = 'Comandante: ' + dados.carta.nome;
        quadroComandante.appendChild(legenda);
    } catch (erro) {
        console.error('Erro ao escolher comandante:', erro);
        mostrarErro('Não foi possível escolher o comandante. Tente novamente.');
    }
}
