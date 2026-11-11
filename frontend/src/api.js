const URL_BARALHO = 'index.php?modulo=baralho&acao='

async function chamarPhp(url, opcoes) {
  let resposta

  try {
    resposta = await fetch(url, opcoes)
  } catch {
    throw new Error('Não foi possível falar com o servidor.')
  }

  // O PHP sempre responde JSON nessas rotas. HTML aqui é o redirecionamento para a tela de login, que acontece quando a sessão expira.
  const tipo = resposta.headers.get('content-type') ?? ''
  if (!tipo.includes('application/json')) {
    throw new Error('Sua sessão expirou. Entre novamente para continuar.')
  }

  const dados = await resposta.json()
  if (!dados.sucesso) {
    throw new Error(dados.erro)
  }

  return dados
}

function enviarFormulario(acao, campos) {
  return chamarPhp(URL_BARALHO + acao, {
    method: 'POST',
    body: new URLSearchParams(campos),
  })
}

export async function listarCartas(baralhoId) {
  const url = `${URL_BARALHO}listarCartasBaralho&baralho_id=${encodeURIComponent(baralhoId)}`
  const dados = await chamarPhp(url)
  return dados.cartas
}

export function adicionarCarta(baralhoId, nomeCarta) {
  return enviarFormulario('adicionarCarta', {
    baralho_id: baralhoId,
    nome_carta: nomeCarta,
  })
}

export function alterarQuantidade(baralhoId, cartaId, delta) {
  return enviarFormulario('alterarQuantidadeCarta', {
    baralho_id: baralhoId,
    carta_id: cartaId,
    delta,
  })
}

export async function pedirComentarioIA(baralhoId) {
  const dados = await enviarFormulario('comentarBaralho', { baralho_id: baralhoId })
  return dados.comentario
}

export async function buscarSugestoes(termo, signal) {
  const url = `https://api.scryfall.com/cards/autocomplete?q=${encodeURIComponent(termo)}`
  const resposta = await fetch(url, { signal })
  const dados = await resposta.json()
  return dados.data ?? []
}
