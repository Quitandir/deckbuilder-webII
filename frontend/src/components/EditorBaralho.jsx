import { useCallback, useEffect, useState } from 'react'
import { adicionarCarta, alterarQuantidade, listarCartas } from '../api.js'
import BuscaCarta from './BuscaCarta.jsx'
import ComentarioIA from './ComentarioIA.jsx'
import ListaCartas from './ListaCartas.jsx'

export default function EditorBaralho({ baralhoId, comandanteId }) {
  const [cartas, setCartas] = useState([])
  const [carregando, setCarregando] = useState(true)
  const [erro, setErro] = useState(null)

  // Calculado a cada renderização a partir da lista, então nunca fica desatualizado
  const totalCartas = cartas.reduce((soma, carta) => soma + carta.quantidade, 0)

  const carregarCartas = useCallback(async () => {
    try {
      setCartas(await listarCartas(baralhoId))
    } catch (falha) {
      setErro(falha.message)
    } finally {
      setCarregando(false)
    }
  }, [baralhoId])

  useEffect(() => {
    carregarCartas()
  }, [carregarCartas])

  async function aoAdicionar(nomeCarta) {
    setErro(null)

    try {
      await adicionarCarta(baralhoId, nomeCarta)
      // Busca de novo porque uma carta repetida só aumenta a quantidade da existente
      await carregarCartas()
    } catch (falha) {
      setErro(falha.message)
    }
  }

  async function aoAlterarQuantidade(cartaId, delta) {
    setErro(null)

    try {
      const { quantidade, removida } = await alterarQuantidade(baralhoId, cartaId, delta)

      setCartas((atuais) =>
        removida
          ? atuais.filter((carta) => carta.id !== cartaId)
          : atuais.map((carta) => (carta.id === cartaId ? { ...carta, quantidade } : carta)),
      )
    } catch (falha) {
      setErro(falha.message)
    }
  }

  return (
    <>
      <section className="painel">
        <h2 className="mb-4 text-xl font-semibold">Cartas no baralho ({totalCartas}/100)</h2>

        <BuscaCarta aoAdicionar={aoAdicionar} />

        {erro && (
          <p role="alert" className="mensagem-erro mt-3">
            {erro}
          </p>
        )}

        <div className="mt-4">
          {carregando ? (
            <p className="text-gray-600">Carregando cartas...</p>
          ) : (
            <ListaCartas
              cartas={cartas}
              comandanteId={comandanteId}
              aoAlterarQuantidade={aoAlterarQuantidade}
            />
          )}
        </div>
      </section>

      <ComentarioIA baralhoId={baralhoId} />
    </>
  )
}
