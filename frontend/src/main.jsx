import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import {HashRouter} from 'react-router'
import './index.css'
import EditorBaralho from './components/EditorBaralho.jsx'

const raiz = document.getElementById('editor-baralho')

// Strict mode para montar duas vezes os componentes React (maior segurança)
// HashRouter porque a URL vem do PHP e as rotas operam depois do # na URL

if (raiz) {
  createRoot(raiz).render(
    <StrictMode>
      <HashRouter>
      <EditorBaralho
        baralhoId={raiz.dataset.baralhoId}
        comandanteId={Number(raiz.dataset.comandanteId)}
      />
      </HashRouter>
    </StrictMode>,
  )
}
