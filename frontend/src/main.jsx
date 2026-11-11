import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import './index.css'
import EditorBaralho from './components/EditorBaralho.jsx'

const raiz = document.getElementById('editor-baralho')

if (raiz) {
  createRoot(raiz).render(
    <StrictMode>
      <EditorBaralho
        baralhoId={raiz.dataset.baralhoId}
        comandanteId={Number(raiz.dataset.comandanteId)}
      />
    </StrictMode>,
  )
}
