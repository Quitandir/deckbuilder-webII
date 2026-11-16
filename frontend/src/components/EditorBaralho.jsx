import { useCallback, useEffect, useState } from 'react'
import { adicionarCarta, alterarQuantidade, listarCartas } from '../api.js'
import BuscaCarta from './BuscaCarta.jsx'
import ComentarioIA from './ComentarioIA.jsx'
import ListaCartas from './ListaCartas.jsx'
import { NavLink, Navigate, Route, Routes } from 'react-router'

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

  const estiloAba = ({ isActive }) =>
    `rounded-t-md px-4 py-2 font-medium ${
      isActive ? 'border border-b-0 border-gray-200 bg-white text-blue-800' : 'text-gray-600 hover:text-blue-800'
    }`

  return (
    <>
      <nav className="flex gap-1 border-b border-gray-200">
        <NavLink to="/cartas" className={estiloAba}>
          Cartas ({totalCartas}/100)
        </NavLink>
        <NavLink to="/ia" className={estiloAba}>
          Comentário da IA
        </NavLink>
      </nav>

      <Routes>
        <Route
          path="/cartas"
          element={
            <section className="painel rounded-t-none">
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
          }
        />
        <Route path="/ia" element={<ComentarioIA baralhoId={baralhoId} />} />
        {/* Sem # ou com uma rota desconhecida, abre a aba de cartas */}
        <Route path="*" element={<Navigate to="/cartas" replace />} />
      </Routes>
    </>
  )
}
