<?php
session_start();

$time_start = microtime(true);

// Vérifier si on travaille avec un fichier chargé (pour les rechargements de page)
if (isset($_SESSION['working_with_loaded_file']) && $_SESSION['working_with_loaded_file']) {
    $loadedFromFile = true;
}

// Fonction pour générer les cartes à partir des données chargées
function generateCardsFromData($gameData, $bingoName = 'Bingo') {
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
            $cardboard_output .= '<div class="print-bingo-name">' . htmlspecialchars($bingoName) . '</div>';
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

// Gestion des actions POST
$currentGameName = '';
$loadedFromFile = false;
$cardsGenerated = false;
$gameData = [];
$players = 0;
$cards = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upload_game']) && isset($_FILES['bingo_file'])) {
        // Chargement d'un fichier local
        $file = $_FILES['bingo_file'];

        if ($file['error'] === UPLOAD_ERR_OK && $file['type'] === 'application/json') {
            $jsonContent = file_get_contents($file['tmp_name']);
            $loadedGame = json_decode($jsonContent, true);

            if ($loadedGame && isset($loadedGame['game_data'])) {
                // Définir directement les variables pour générer les cartes immédiatement
                $currentGameName = $loadedGame['name'];
                $players = $loadedGame['players'];
                $cards = $loadedGame['cards_per_player'];
                $gameData = $loadedGame['game_data'];
                $loadedFromFile = true;
                $_SESSION['working_with_loaded_file'] = true;
                $cardsGenerated = true;

                // Générer les cartes immédiatement
                $cardboard_output = generateCardsFromData($gameData, $currentGameName ?: 'Bingo');


                // Afficher le message de confirmation
                echo '<div style="background-color: #d1ecf1; color: #0c5460; padding: 10px; margin: 10px 0; border-radius: 4px;">' . htmlspecialchars($currentGameName) . '</div>';
            } else {
                echo '<div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 4px;">Erreur: Format de fichier invalide</div>';
            }
        } else {
            echo '<div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 4px;">Erreur lors du téléchargement du fichier</div>';
        }
    }
}

#Number of cards wanted:
//$players = 2;
if (!$loadedFromFile) {
    $players = $_POST["nb_players"] ?? $players;
    $cards = $_POST["nb_cards"] ?? $cards;
}
$columnsPerCard = 5;
$rowsPerCard = 5;

if (!$loadedFromFile && !$cardsGenerated) {
    $cardboard_output = ''; // Réinitialiser seulement pour génération normale
    $gameData = []; // Stockage des données des cartes pour sauvegarde (seulement pour génération normale)
}

function uniqueRandomNumbers($columns,$rows) {
	#How many numbers do we need:
	#$totalNumbersNeeded = $columns * $rows;
	$totalNumbersNeeded = $rows;
	#Create an array to hold them:
	$NumberArray = array();
	#Do following until we have the numbers we need:
	for ($a = 1; $a <= ($totalNumbersNeeded); $a++)
	{
		$randomNumber = rand(1,15);
		#If number is already in the array, try again (and
		#again)
		while(in_array($randomNumber,$NumberArray))
		{
			$randomNumber = rand(1,15);
		}
		#Put number in array:
		$NumberArray[] = $randomNumber;
	}#Return the array:
	return $NumberArray;
}

// Générer les cartes seulement si elles n'ont pas déjà été générées
if (!$cardsGenerated) {
    if ($loadedFromFile && isset($gameData)) {
        // Si on a chargé depuis un fichier, régénérer les cartes depuis les données sauvegardées
        $cardboard_output = generateCardsFromData($gameData);
    } elseif (isset($_POST["nb_players"]) && isset($_POST["nb_cards"]) && $_POST["nb_players"] > 0 && $_POST["nb_cards"] > 0) {
        // Génération normale de nouvelles cartes aléatoires
        $cardboard_output = ''; // Réinitialiser pour une nouvelle génération
        $gameData['players'] = [];
        // Nettoyer l'indicateur de fichier chargé puisqu'on génère du nouveau
        unset($_SESSION['working_with_loaded_file']);

	for ($p=1; $p<=$players;$p++) {
		$gameData['players'][$p] = ['cards' => []];

		$cardboard_output .= '<div class="cardboard-header">Joueur no ' . $p . '</div>';

            $randomNumbers_c1 = array();
            $randomNumbers_c2 = array();
            $randomNumbers_c3 = array();
            $randomNumbers_c4 = array();
            $randomNumbers_c5 = array();

            for ($i = 1; $i <= $cards; $i++) {
                #Get Random numbers for card, col by col:
                $randomNumbers_c1 = uniqueRandomNumbers($columnsPerCard,$rowsPerCard);
                $randomNumbers_c2 = uniqueRandomNumbers($columnsPerCard,$rowsPerCard);
                $randomNumbers_c3 = uniqueRandomNumbers($columnsPerCard,$rowsPerCard);
                $randomNumbers_c4 = uniqueRandomNumbers($columnsPerCard,$rowsPerCard);
                $randomNumbers_c5 = uniqueRandomNumbers($columnsPerCard,$rowsPerCard);

                #Store card data for saving
                $cardData = [
                    'c1' => array_reverse($randomNumbers_c1), // array_pop prend depuis la fin, donc on inverse pour stocker
                    'c2' => array_reverse($randomNumbers_c2),
                    'c3' => array_reverse($randomNumbers_c3),
                    'c4' => array_reverse($randomNumbers_c4),
                    'c5' => array_reverse($randomNumbers_c5)
                ];
                $gameData['players'][$p]['cards'][$i] = $cardData;
		#Initiate table:
		$cardboard_output .= '<div class="cardboard">';
		$cardboard_output .= '<div class="print-bingo-name">' . htmlspecialchars($currentGameName ?: 'Bingo') . '</div>';
		$cardboard_output .= '<table border="1">';
                $cardboard_output .= '<tr><th>B</th><th>I</th><th>N</th><th>G</th><th>O</th></tr>';
                #Create Row:
                for ($k = 1; $k <= $rowsPerCard; $k++) {
                    $cardboard_output .= '<tr>';

                    #Create column:
                    for ($j = 1; $j <= $columnsPerCard; $j++) {
                        #pop the last number off the array, and print
                        #it:
                        if ($j == 3 && $k == 3) {
                            $randomNumber = '*';
                        }
                        elseif($j == 1) {
                            $randomNumber = array_pop($randomNumbers_c1);
                        }
                        elseif($j == 2) {
                            $randomNumber = array_pop($randomNumbers_c2);
                            $randomNumber = $randomNumber + 15;
                        }
                        elseif($j == 3) {
                            $randomNumber = array_pop($randomNumbers_c3);
                            $randomNumber = $randomNumber + 30;
                        }
                        elseif($j == 4) {
                            $randomNumber = array_pop($randomNumbers_c4);
                            $randomNumber = $randomNumber + 45;
                        }
                        elseif($j == 5) {
                            $randomNumber = array_pop($randomNumbers_c5);
                            $randomNumber = $randomNumber + 60;
                        }
                        $cardboard_output .= '<td id="p' . $p . 'c' . $i . 'h' . $k . 'v' . $j . '" class="card-square" width="40" height="40" align="center">'.$randomNumber.'</td>';
                    }
                    #Close Row
                    $cardboard_output .= '</tr>';
                }
                #Close Table and add a line spacer:
                $cardboard_output .= '</table></div>';
            }

            $cardboard_output .= '<div style="clear: both"></div>';
            //echo '<hr /><br />';

        }
    }
}

$time_end = microtime(true);
$time = $time_end - $time_start;

$process_time = number_format($time, 2, '.', '');

$bingo_control_output .= ($players * $cards) . " cartes de bingo g&eacute;n&eacute;r&eacute;es en  " . $process_time . " seconds<br />Nb joueurs: " . $players . "<br />Nb cartes par joueur: " . $cards;

?>

<!DOCTYPE html>
<html lang="fr-FR">
<head>
	<meta charset="UTF-8" />
	<title>WEBGO</title>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" ></script>

<script>

	var gameOver = false;
	var drawnNumbers = []; // Liste des numéros déjà tirés

	// Fonction pour réinitialiser le jeu
	function resetGame() {
		gameOver = false;
		drawnNumbers = [];
		$('#current-square-played').html('');
		$('#draw-status').html('Numéros tirés: 0/75 - Restants: 75');
		$('#bingo-notification').html('');
		// Réactiver le bouton draw si nécessaire
		$('#draw-btn input[type="submit"]').prop('disabled', false);
		// Réactiver les clics sur les cartes
		$('.card-square').off('click').on('click', function() {
			if (gameOver) {
				return;
			}
			$(this).css('background', '#0c0');
			var square_value = $(this).text();
			var square_id = $(this).attr('id');

			$('#current-square-played').html(square_id + ' >>> ' + square_value);

			// Check for bingo after marking a square
			setTimeout(function() {
				check_bingo();
			}, 100);
		});
	}
	
	function scan_all_cards(current_ball) {
	
		//alert('Scanning all cards');
		$(".card-square").each(function(index, value) { 
	
			//console.log('div' + index + ':' + $(this).html()); 
	
			if(current_ball == $(this).html())	{
				$(this).css('background-color', '#ff0000');
				
				setTimeout(function() {
					$(this).css('background-color', '#00cc00');
				}, 5000);
				
				//setTimeout( $(this).css('background-color', '#ff00ff'), 3000 );
			}

		});
	}
	
	function broadcast_last_draw (which_ball) {
	
		$('#current-square-played').html(which_ball);
	}
	
	function check_bingo() {
		var bingoFound = false;
		var bingoMessages = [];
		var winningSquares = []; // Store IDs of squares that are part of winning patterns
		
		// Get all unique player-card combinations
		var cards = {};
		$('.card-square').each(function() {
			var id = $(this).attr('id');
			if (id) {
				// Extract player and card number from ID (format: p1c1h1v1)
				var match = id.match(/p(\d+)c(\d+)/);
				if (match) {
					var player = match[1];
					var card = match[2];
					var key = 'p' + player + 'c' + card;
					if (!cards[key]) {
						cards[key] = {
							player: player,
							card: card,
							squares: []
						};
					}
					
					// Check if square is marked (green background, red background, or center *)
					var bgColor = $(this).css('background-color');
					var bg = $(this).css('background');
					var text = $(this).text().trim();
					var isMarked = text === '*' || 
						bgColor === 'rgb(0, 204, 0)' || 
						bgColor === 'rgb(255, 0, 0)' ||
						bg === 'rgb(0, 204, 0)' ||
						bg.indexOf('#0c0') !== -1 ||
						bg.indexOf('#00cc00') !== -1 ||
						bg.indexOf('#ff0000') !== -1;
					
					cards[key].squares.push({
						id: id,
						row: parseInt(id.match(/h(\d+)/)[1]),
						col: parseInt(id.match(/v(\d+)/)[1]),
						marked: isMarked
					});
				}
			}
		});
		
		// Check each card for bingo
		for (var cardKey in cards) {
			var card = cards[cardKey];
			var grid = [];
			var idGrid = []; // Store square IDs for highlighting
			
			// Initialize 5x5 grid
			for (var r = 1; r <= 5; r++) {
				grid[r] = [];
				idGrid[r] = [];
				for (var c = 1; c <= 5; c++) {
					grid[r][c] = false;
					idGrid[r][c] = '';
				}
			}
			
			// Fill grid with marked status and square IDs
			for (var i = 0; i < card.squares.length; i++) {
				var sq = card.squares[i];
				grid[sq.row][sq.col] = sq.marked;
				idGrid[sq.row][sq.col] = sq.id;
			}
			
			// Check horizontal lines
			for (var row = 1; row <= 5; row++) {
				var allMarked = true;
				for (var col = 1; col <= 5; col++) {
					if (!grid[row][col]) {
						allMarked = false;
						break;
					}
				}
				if (allMarked) {
					bingoFound = true;
					bingoMessages.push('BINGO! Joueur ' + card.player + ', Carte ' + card.card + ' (Ligne ' + row + ')');
					// Add all squares in this row to winning squares
					for (var col = 1; col <= 5; col++) {
						winningSquares.push(idGrid[row][col]);
					}
				}
			}
			
			// Check vertical lines
			for (var col = 1; col <= 5; col++) {
				var allMarked = true;
				for (var row = 1; row <= 5; row++) {
					if (!grid[row][col]) {
						allMarked = false;
						break;
					}
				}
				if (allMarked) {
					bingoFound = true;
					bingoMessages.push('BINGO! Joueur ' + card.player + ', Carte ' + card.card + ' (Colonne ' + col + ')');
					// Add all squares in this column to winning squares
					for (var row = 1; row <= 5; row++) {
						winningSquares.push(idGrid[row][col]);
					}
				}
			}
			
			// Check diagonal (top-left to bottom-right)
			var diag1Marked = true;
			for (var d = 1; d <= 5; d++) {
				if (!grid[d][d]) {
					diag1Marked = false;
					break;
				}
			}
			if (diag1Marked) {
				bingoFound = true;
				bingoMessages.push('BINGO! Joueur ' + card.player + ', Carte ' + card.card + ' (Diagonale principale)');
				// Add all squares in this diagonal to winning squares
				for (var d = 1; d <= 5; d++) {
					winningSquares.push(idGrid[d][d]);
				}
			}
			
			// Check diagonal (top-right to bottom-left)
			var diag2Marked = true;
			for (var d = 1; d <= 5; d++) {
				if (!grid[d][6-d]) {
					diag2Marked = false;
					break;
				}
			}
			if (diag2Marked) {
				bingoFound = true;
				bingoMessages.push('BINGO! Joueur ' + card.player + ', Carte ' + card.card + ' (Diagonale secondaire)');
				// Add all squares in this diagonal to winning squares
				for (var d = 1; d <= 5; d++) {
					winningSquares.push(idGrid[d][6-d]);
				}
			}
		}
		
		// Highlight winning squares in green
		$('.card-square').each(function() {
			var id = $(this).attr('id');
			if (winningSquares.indexOf(id) !== -1) {
				$(this).css('background-color', '#81c784');
			}
		});
		
		// Display bingo notification
		if (bingoFound) {
			$('#bingo-notification').html('<div style="background-color: #81c784; color: #000; padding: 15px; margin-top: 10px; border-radius: 5px; font-weight: bold; font-size: 18px; border: 2px solid #4caf50;">' + 
				bingoMessages.join('<br>') + '</div>');
		} else {
			$('#bingo-notification').html('');
		}
		
		// If bingo is found, show custom popup to end or continue
		if (bingoFound && !gameOver) {
			$('#bingo-popup').fadeIn(200);
		}
		
		return bingoFound;
	}
	
	
	
  
  $(document).ready(function() {
  	// Initialiser le jeu
  	resetGame();

  	$('.card-square').on( "click", function() {
  		if (gameOver) {
  			return;
  		}
  		$(this).css('background', '#0c0');
	  	var square_value = $(this).text();
	  	var square_id =  $(this).attr('id');
	  	
	  	//alert('La valeur de la case ' + square_id + ' est ' +  square_value);
	  	$('#current-square-played').html(square_id + ' >>> ' + square_value);
	  	
	  	// Check for bingo after marking a square
	  	setTimeout(function() {
	  		check_bingo();
	  	}, 100);
	 });
    	
    $('#draw-btn').on( "click", function(e) {
    	if (gameOver) {
    		return false;
    	}

  		e.stopPropagation();

  		// Vérifier si tous les numéros ont été tirés
  		if (drawnNumbers.length >= 75) {
  			alert('Tous les numéros ont été tirés ! Le jeu est terminé.');
  			return false;
  		}

  		// Générer un numéro qui n'a pas encore été tiré
  		var rand_number;
  		var attempts = 0;
  		do {
  			rand_number = Math.floor((Math.random() * 75) + 1);
  			attempts++;
  			// Sécurité : éviter une boucle infinie (très peu probable)
  			if (attempts > 1000) {
  				alert('Erreur: Impossible de générer un numéro unique.');
  				return false;
  			}
  		} while (drawnNumbers.indexOf(rand_number) !== -1);

  		// Ajouter le numéro à la liste des tirés
  		drawnNumbers.push(rand_number);

  		// Formater le numéro avec sa lettre
  		var rand_ball;
  		if (rand_number <= 15) {
	  		rand_ball = 'B' + rand_number;
	  	} else if (rand_number <= 30) {
		  	rand_ball = 'I' + rand_number;
		} else if (rand_number <= 45) {
		  	rand_ball = 'N' + rand_number;
		} else if (rand_number <= 60) {
		  	rand_ball = 'G' + rand_number;
		} else if (rand_number <= 75) {
		  	rand_ball = 'O' + rand_number;
		}

  		broadcast_last_draw(rand_ball);

  		scan_all_cards(rand_number);

  		// Mettre à jour l'affichage du statut
  		var remaining = 75 - drawnNumbers.length;
  		$('#draw-status').html('Numéros tirés: ' + drawnNumbers.length + '/75 - Restants: ' + remaining);

  		// Check for bingo after a short delay to allow highlighting to complete
  		setTimeout(function() {
  			check_bingo();
  		}, 100);

  		return false;
  		
	 });

	// Gestion des boutons du popup bingo
	$('#btn-end-game').on('click', function() {
		gameOver = true;
		$('#bingo-popup').fadeOut(200);
		$('#draw-btn input[type="submit"]').prop('disabled', true);
		$('.card-square').off('click');
		$('#bingo-notification').append('<div style="margin-top: 10px; font-weight: bold; font-size: 16px;">Partie terminée. Un bingo a été déclaré.</div>');
	});

	$('#btn-continue-game').on('click', function() {
		$('#bingo-popup').fadeOut(200);
	});

// Gestion du bouton d'impression
$('#print-btn').on('click', function() {
	// Créer une fenêtre d'impression dédiée
	var printWindow = window.open('', '_blank');
	var bingoName = '<?php echo addslashes($currentGameName ?: 'Bingo'); ?>';
	var cardsHtml = '<?php echo addslashes($cardboard_output); ?>';

	printWindow.document.write(`
		<!DOCTYPE html>
		<html>
		<head>
			<title>Cartes de Bingo - ${bingoName}</title>
			<style>
				body {
					font-family: Arial, sans-serif;
					margin: 20px;
					text-align: center;
				}
				.cardboard {
					page-break-before: always;
					margin: 20px auto;
					display: block;
				}
				.cardboard:first-child {
					page-break-before: avoid;
				}
				.cardboard-header {
					display: none;
				}
				table {
					border-collapse: collapse;
					margin: 20px auto;
				}
				th, td {
					border: 2px solid #000;
					padding: 8px;
					text-align: center;
					width: 40px;
					height: 40px;
					font-weight: bold;
				}
				th {
					background-color: #f0f0f0;
				}
				.print-bingo-name {
					font-size: 18px;
					font-weight: bold;
					margin-bottom: 15px;
					padding-bottom: 8px;
					border-bottom: 2px solid #000;
				}
			</style>
		</head>
		<body>
			${cardsHtml}
		</body>
		</html>
	`);

	printWindow.document.close();
	printWindow.focus();
	printWindow.print();
});
  });
</script>

<style>
.leftside {
	float: left;
	width: 20%;
	height: 100%;
	min-height: 600px;
	border: 1px solid #000;
	margin-right: 1%;
	border-radius: 10px;
	padding: 5px;
}
.rightside {
	float: left;
	min-width: 400px;
	width: 70%;
}
.cardboard-header{
	margin: 30px;
	border-bottom: 1px solid #000;
}
.cardboard {
	float:left;
	margin: 30px;
}

.print-bingo-name {
	display: none;
}



.bingo-popup-overlay {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: rgba(0,0,0,0.5);
	display: none;
	align-items: center;
	justify-content: center;
	z-index: 9999;
}

.bingo-popup {
	background: #ffffff;
	padding: 20px;
	border-radius: 8px;
	box-shadow: 0 2px 10px rgba(0,0,0,0.3);
	max-width: 400px;
	width: 90%;
	text-align: center;
	font-family: Arial, sans-serif;
}

.bingo-popup h3 {
	margin-top: 0;
	margin-bottom: 10px;
}

.bingo-popup p {
	margin-bottom: 20px;
}

.bingo-popup-buttons {
	display: flex;
	justify-content: space-between;
	gap: 10px;
}

.bingo-popup-buttons button {
	flex: 1;
	padding: 10px;
	border: none;
	border-radius: 4px;
	font-size: 14px;
	cursor: pointer;
}

.bingo-popup-buttons .end-game {
	background-color: #d32f2f;
	color: #fff;
}

.bingo-popup-buttons .continue-game {
	background-color: #388e3c;
	color: #fff;
}

/* Styles pour l'impression */
</style>	
		
</head>

<body>

<div class="leftside">

<form id="bingo_settings" method="post" action="http://localhost/webgo/">

<label for="nb_players">nombre de joueurs</label><br />
<input type="text" size="5" name="nb_players" value="">

<br />

<label for="nb_cards">nombre de carte par joueur</label><br /> 
<input type="text" size="5" name="nb_cards" value="">

<br />

<input type="submit" name="submit" value="Générer le bingo">

</form>

<br /><br />

<?php echo $bingo_control_output; ?>

<?php if (!$loadedFromFile): ?>
<form id="bingo_settings" method="post" action="http://localhost/webgo/">
<input type="hidden" size="5" name="nb_players" value="<?php echo $players ?>">
<input type="hidden" size="5" name="nb_cards" value="<?php echo $cards ?>">
<br />
<input type="submit" name="submit" value="Rechargez le bingo">
</form>
<?php endif; ?>

<hr />

<?php if (isset($players) && isset($cards) && $players > 0 && $cards > 0): ?>
<h5>Sauvegarder ce bingo:</h5>
<div style="margin-bottom: 10px;">
    <button id="save-bingo-btn" style="padding: 10px 15px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
        💾 Sauvegarder le bingo
    </button>
</div>

<script>
// Fonction pour sauvegarder le bingo avec boîte de dialogue native
async function saveBingo() {
    try {
        // Demander le nom du bingo
        const gameName = prompt('Entrez un nom pour ce bingo:');
        if (!gameName || gameName.trim() === '') {
            return;
        }

        // Préparer les données
        const gameData = {
            name: gameName.trim(),
            created_at: new Date().toISOString().slice(0, 19).replace('T', ' '),
            players: <?php echo $players; ?>,
            cards_per_player: <?php echo $cards; ?>,
            game_data: <?php echo json_encode($gameData ?? []); ?>
        };

        const jsonContent = JSON.stringify(gameData, null, 2);
        const blob = new Blob([jsonContent], { type: 'application/json' });

        // Essayer d'utiliser l'API moderne File System Access API
        if ('showSaveFilePicker' in window) {
            try {
                const handle = await window.showSaveFilePicker({
                    suggestedName: gameName.replace(/[^a-zA-Z0-9_-]/g, '_') + '_' + new Date().toISOString().slice(0, 10) + '.json',
                    types: [{
                        description: 'Fichier Bingo JSON',
                        accept: { 'application/json': ['.json'] }
                    }]
                });

                const writable = await handle.createWritable();
                await writable.write(blob);
                await writable.close();

                alert('Bingo sauvegardé avec succès !');
                return;
            } catch (err) {
                // L'utilisateur a annulé ou il y a eu une erreur, continuer avec la méthode classique
                console.log('File System Access API non disponible ou annulé, utilisation de la méthode classique');
            }
        }

        // Méthode classique : téléchargement direct
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = gameName.replace(/[^a-zA-Z0-9_-]/g, '_') + '_' + new Date().toISOString().slice(0, 10) + '.json';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        alert('Bingo téléchargé avec succès !');

    } catch (error) {
        console.error('Erreur lors de la sauvegarde:', error);
        alert('Erreur lors de la sauvegarde du bingo.');
    }
}

// Attacher l'événement au bouton
document.getElementById('save-bingo-btn').addEventListener('click', saveBingo);
</script>
<?php endif; ?>

<hr />

<h5>Charger depuis un fichier local:</h5>
<div style="border: 1px solid #ddd; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
    <form method="post" action="http://localhost/webgo/" enctype="multipart/form-data" style="margin-bottom: 10px;">
        <input type="file" name="bingo_file" accept=".json" required style="margin-bottom: 5px;">
        <input type="submit" name="upload_game" value="📤 Charger fichier JSON" style="padding: 8px 12px; background-color: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer;">
    </form>
    <small style="color: #666;">Sélectionnez un fichier .json de bingo précédemment téléchargé</small>
</div>

<hr />

<h5>Dernière case jouée:</h5>

<div id="current-square-played"></div>

<h5>État du tirage:</h5>

<div id="draw-status">Numéros tirés: 0/75 - Restants: 75</div>

<h5>Tirer le prochain numéro:</h5>

<div id="draw-btn"><form><input type="submit" value="Prochain numéro"></form></div>

<h5>Imprimer les cartes:</h5>

<button id="print-btn" style="padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">🖨️ Imprimer les cartes</button>

<div id="bingo-notification"></div>

<!-- Popup personnalisé pour fin / continuation de partie -->
<div id="bingo-popup" class="bingo-popup-overlay">
	<div class="bingo-popup">
		<h3>BINGO détecté !</h3>
		<p>Voulez-vous terminer la partie ou continuer à jouer ?</p>
		<div class="bingo-popup-buttons">
			<button type="button" class="end-game" id="btn-end-game">Terminer la partie</button>
			<button type="button" class="continue-game" id="btn-continue-game">Continuer</button>
		</div>
	</div>
</div>

</div>

<div class="rightside">
	<?php echo $cardboard_output; ?>
</div>

</body>
</html>

