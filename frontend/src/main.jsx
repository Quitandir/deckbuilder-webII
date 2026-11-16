import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import {HashRouter} from 'react-router'
import './index.css'
import EditorBaralho from './components/EditorBaralho.jsx'

const raiz = document.getElementById('editor-baralho')

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
