import { useEffect, useId, useState } from 'react'
import { buscarSugestoes } from '../api.js'

const TAMANHO_MINIMO = 2
const ESPERA_MS = 300

export default function BuscaCarta({ aoAdicionar }) {
  const [termo, setTermo] = useState('')
  const [sugestoes, setSugestoes] = useState([])
  const [aberta, setAberta] = useState(false)
  // Id único para ligar o rótulo ao campo
  const idCampo = useId()

  const termoLimpo = termo.trim()
  const buscaValida = termoLimpo.length >= TAMANHO_MINIMO

  useEffect(() => {
    if (!buscaValida) return

    const controle = new AbortController()

    const espera = setTimeout(async () => {
      try {
        setSugestoes(await buscarSugestoes(termoLimpo, controle.signal))
      } catch (falha) {
        if (falha.name !== 'AbortError') {
          console.error('Erro ao buscar cartas:', falha)
        }
      }
    }, ESPERA_MS)

    // Cancela a busca anterior para uma resposta lenta não substituir a mais nova
    return () => {
      clearTimeout(espera)
      controle.abort()
    }
  }, [termoLimpo, buscaValida])

  function escolher(nomeCarta) {
    setTermo('')
    setSugestoes([])
    aoAdicionar(nomeCarta)
  }

  const mostrarLista = aberta && buscaValida && sugestoes.length > 0

  return (
    <div
      className="relative max-w-md"
      onBlur={(evento) => {
        if (!evento.currentTarget.contains(evento.relatedTarget)) setAberta(false)
      }}
    >
      <label htmlFor={idCampo} className="rotulo">
        Adicionar carta
      </label>
      <input
        id={idCampo}
        type="text"
        placeholder="Buscar carta..."
        autoComplete="off"
        className="campo"
        value={termo}
        onChange={(evento) => {
          setTermo(evento.target.value)
          setAberta(true)
        }}
        onFocus={() => setAberta(true)}
      />

      {mostrarLista && (
        // Sem o preventDefault, o clique tiraria o foco do campo e fecharia a lista
        <ul className="lista-sugestoes" onMouseDown={(evento) => evento.preventDefault()}>
          {sugestoes.map((nome) => (
            <li key={nome}>
              <button
                type="button"
                className="item-sugestao"
                onClick={() => escolher(nome)}
              >
                {nome}
              </button>
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}
