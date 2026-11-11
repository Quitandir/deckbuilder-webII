import { useState } from 'react'
import { pedirComentarioIA } from '../api.js'

export default function ComentarioIA({ baralhoId }) {
  const [comentario, setComentario] = useState(null)
  const [carregando, setCarregando] = useState(false)
  const [erro, setErro] = useState(null)

  async function pedirComentario() {
    setCarregando(true)
    setErro(null)

    try {
      setComentario(await pedirComentarioIA(baralhoId))
    } catch (falha) {
      setErro(falha.message)
    } finally {
      setCarregando(false)
    }
  }

  let textoBotao = 'Pedir comentário da IA'
  if (carregando) textoBotao = 'Gerando comentário...'
  else if (comentario) textoBotao = 'Gerar outro comentário'

  return (
    <section className="painel mt-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <h2 className="text-xl font-semibold">Comentário da IA</h2>
        <button type="button" className="botao" onClick={pedirComentario} disabled={carregando}>
          {textoBotao}
        </button>
      </div>

      <p className="mt-2 text-sm text-gray-600">
        A IA analisa a lista atual do baralho e sugere melhorias. Pode levar alguns segundos.
      </p>

      {erro && (
        <p role="alert" className="mensagem-erro mt-3">
          {erro}
        </p>
      )}

      {/* aria-live avisa leitores de tela quando o comentário chega */}
      <div aria-live="polite">
        {comentario && (
          <p className="mt-3 rounded-md border border-blue-200 bg-blue-50 px-4 py-3 leading-relaxed">
            {comentario}
          </p>
        )}
      </div>
    </section>
  )
}
