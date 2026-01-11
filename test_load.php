<?php

// Fonction pour générer les cartes à partir des données chargées (copiée du fichier principal)
function generateCardsFromData($gameData) {
    $cardboard_output = '';
    $columnsPerCard = 5;
    $rowsPerCard = 5;

    if (!isset($gameData['players']) || empty($gameData['players'])) {
        return '<div style="color: red; text-align: center; margin: 20px;">Erreur: Aucune donnée de jeu trouvée</div>';
    }

    foreach ($gameData['players'] as $playerNum => $playerData) {
        $cardboard_output .= '<div class="cardboard-header">Joueur no ' . $playerNum . '</div>';

        if (!isset($playerData['cards']) || empty($playerData['cards'])) {
            $cardboard_output .= '<div style="color: red; margin: 10px;">Erreur: Aucune carte trouvée pour ce joueur</div>';
            continue;
        }

        foreach ($playerData['cards'] as $cardNum => $cardData) {
            $cardboard_output .= '<div class="cardboard">';
            $cardboard_output .= '<table border="1">';
            $cardboard_output .= '<tr><th>B</th><th>I</th><th>N</th><th>G</th><th>O</th></tr>';

            // Générer les lignes du tableau
            for ($k = 1; $k <= $rowsPerCard; $k++) {
                $cardboard_output .= '<tr>';

                for ($j = 1; $j <= $columnsPerCard; $j++) {
                    if ($j == 3 && $k == 3) {
                        $randomNumber = '*';
                    } elseif ($j == 1 && isset($cardData['c1'][$k-1])) {
                        $randomNumber = $cardData['c1'][$k-1];
                    } elseif ($j == 2 && isset($cardData['c2'][$k-1])) {
                        $randomNumber = $cardData['c2'][$k-1] + 15;
                    } elseif ($j == 3 && isset($cardData['c3'][$k-1])) {
                        $randomNumber = $cardData['c3'][$k-1] + 30;
                    } elseif ($j == 4 && isset($cardData['c4'][$k-1])) {
                        $randomNumber = $cardData['c4'][$k-1] + 45;
                    } elseif ($j == 5 && isset($cardData['c5'][$k-1])) {
                        $randomNumber = $cardData['c5'][$k-1] + 60;
                    } else {
                        $randomNumber = '?'; // Erreur de données
                    }

                    $cardboard_output .= '<td id="p' . $playerNum . 'c' . $cardNum . 'h' . $k . 'v' . $j . '" class="card-square" width="40" height="40" align="center">'.$randomNumber.'</td>';
                }

                $cardboard_output .= '</tr>';
            }

            $cardboard_output .= '</table></div>';
        }

        $cardboard_output .= '<div style="clear: both"></div>';
    }

    return $cardboard_output;
}

// Tester le chargement du fichier demo_bingo.json
$jsonContent = file_get_contents('demo_bingo.json');
$loadedGame = json_decode($jsonContent, true);

if ($loadedGame && isset($loadedGame['game_data'])) {
    echo "<h2>Test de chargement du bingo: " . htmlspecialchars($loadedGame['name']) . "</h2>";
    echo "<p>Joueurs: " . $loadedGame['players'] . ", Cartes par joueur: " . $loadedGame['cards_per_player'] . "</p>";

    $cardboard_output = generateCardsFromData($loadedGame['game_data']);

    echo "<h3>Cartes générées:</h3>";
    echo $cardboard_output;

    echo "<h3>Données brutes du jeu:</h3>";
    echo "<pre>" . print_r($loadedGame['game_data'], true) . "</pre>";
} else {
    echo "Erreur: Impossible de charger le fichier demo_bingo.json";
}

?>