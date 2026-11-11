<?php

/**
 * scryfall_utils.php
 *
 * Consultas à API da Scryfall usadas pelo controller e pelo model Carta.
 * Todas devolvem o JSON da carta já decodificado, ou null se não encontrar ou falhar.
 */

function requisitar_scryfall($url)
{
    // A Scryfall pede que toda requisição se identifique e aceite JSON
    $contexto = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: DeckBuilderWebII/1.0\r\nAccept: application/json\r\n",
            'protocol_version' => '1.1'
        ]
    ]);

    $json = @file_get_contents($url, false, $contexto);

    if ($json === false) {
        return null;
    }

    return json_decode($json, true);
}

// Busca pelo nome exato, que é o que o autocomplete devolve
function buscar_carta_scryfall_por_nome($nomeCarta)
{
    return requisitar_scryfall('https://api.scryfall.com/cards/named?exact=' . urlencode($nomeCarta));
}

// Busca pelo UUID salvo na coluna scryfall_id
function buscar_carta_scryfall_por_id($scryfallId)
{
    return requisitar_scryfall('https://api.scryfall.com/cards/' . urlencode($scryfallId));
}
