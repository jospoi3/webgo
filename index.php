<?php

$time_start = microtime(true);

#Number of cards wanted:
//$players = 2;
$players = $_POST["nb_players"];
//$cards = 4;
$cards = $_POST["nb_cards"];
$columnsPerCard = 5;
$rowsPerCard = 5;
$cardboard_output = '';

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

for ($p=1; $p<=$players;$p++) {

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
		#Initiate table:
		$cardboard_output .= '<div class="cardboard">';
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
		
		return bingoFound;
	}
	
	
	
  
  $(document).ready(function() {
  	$('.card-square').on( "click", function() {
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
  		
  		e.stopPropagation();
  		
  		var rand_number = Math.floor((Math.random() * 75) + 1);
  		
  		if (rand_number < 16) { 
	  		var rand_ball = 'b' + rand_number;
	  	} else if (rand_number > 16 && rand_number < 30) {
		  	var rand_ball = 'i' + rand_number;
		} else if (rand_number > 30 && rand_number < 45) {
		  	var rand_ball = 'n' + rand_number;
		} else if (rand_number > 45 && rand_number < 60) {
		  	var rand_ball = 'g' + rand_number;
		} else if (rand_number > 60 && rand_number < 75) {
		  	var rand_ball = 'o' + rand_number;
		}    else {
			 var rand_ball = rand_number;
		}

		// do your dirty stuff here!
  		//alert('On tire le ' + rand_ball);
  		
  		broadcast_last_draw(rand_ball);
  		
  		scan_all_cards(rand_number);
  		
  		// Check for bingo after a short delay to allow highlighting to complete
  		setTimeout(function() {
  			check_bingo();
  		}, 100);
  		
  		return false;
  		
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

<form id="bingo_settings" method="post" action="http://localhost/webgo/">
<input type="hidden" size="5" name="nb_players" value="<?php echo $players ?>">
<input type="hidden" size="5" name="nb_cards" value="<?php echo $cards ?>">
<br />
<input type="submit" name="submit" value="Rechargez le bingo">
</form>

<hr />

<h5>Dernière case jouée:</h5>

<div id="current-square-played"></div>

<h5>Tirer le prochain numéro:</h5>

<div id="draw-btn"><form><input type="submit" value="DRAW"></form></div>

<div id="bingo-notification"></div>

</div>

<div class="rightside">
	<?php echo $cardboard_output; ?>
</div>

</body>
</html>

