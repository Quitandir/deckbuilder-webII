import CartaCard from './CartaCard.jsx'

export default function ListaCartas({ cartas, comandanteId, aoAlterarQuantidade }) {
  if (cartas.length === 0) {
    return <p className="text-gray-600">Nenhuma carta no baralho.</p>
  }

  return (
    <ul className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
      {cartas.map((carta) => (
        <li key={carta.id}>
          <CartaCard
            carta={carta}
            ehComandante={carta.id === comandanteId}
            aoAlterarQuantidade={aoAlterarQuantidade}
          />
        </li>
      ))}
    </ul>
  )
}
