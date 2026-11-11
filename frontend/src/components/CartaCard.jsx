const ESTILO_BOTAO =
  'flex h-8 w-8 cursor-pointer items-center justify-center rounded-full text-xl leading-none hover:bg-white/25'

export default function CartaCard({ carta, ehComandante, aoAlterarQuantidade }) {
  return (
    <article className="text-center">
      <div className="relative">
        {carta.imagem_url ? (
          // A proporção reserva o espaço da carta enquanto a imagem carrega
          <img
            src={carta.imagem_url}
            alt={carta.nome}
            className="aspect-[488/680] w-full rounded-lg bg-gray-200 object-cover"
          />
        ) : (
          <div className="flex aspect-[488/680] items-center justify-center rounded-lg bg-gray-200 p-2 text-sm">
            Sem imagem
          </div>
        )}

        {ehComandante ? (
          <span
            className="absolute inset-x-0 bottom-0 rounded-b-lg bg-black/70 py-2 text-sm font-semibold text-white"
            title="Comandante"
          >
            👑 Comandante
          </span>
        ) : (
          <div className="absolute inset-x-0 bottom-0 flex items-center justify-center gap-3 rounded-b-lg bg-black/70 py-1 text-white">
            <button
              type="button"
              aria-label={`Remover uma cópia de ${carta.nome}`}
              className={ESTILO_BOTAO}
              onClick={() => aoAlterarQuantidade(carta.id, -1)}
            >
              −
            </button>
            <span className="min-w-4 font-semibold">{carta.quantidade}</span>
            <button
              type="button"
              aria-label={`Adicionar uma cópia de ${carta.nome}`}
              className={ESTILO_BOTAO}
              onClick={() => aoAlterarQuantidade(carta.id, 1)}
            >
              +
            </button>
          </div>
        )}
      </div>

      <p className="mt-1 text-sm">{carta.nome}</p>
    </article>
  )
}
