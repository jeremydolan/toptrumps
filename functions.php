<?php 
function abandonAndRestart($game_array){
	if ( ! empty($_POST['process_as'])  && $_POST['process_as'] == 'abandon_and_restart'):
		//print_r($game_array);
		//if the right form has been submitted, just wipe the present game.
		//loop through players in array and remove their data
		unset($_POST['process_as']);
		foreach($game_array['all_players'] as $player_id):
			if ( ! delete_user_meta($player_id, '_allocated_cards') ):
  				echo "Ooops! Error while deleting this information!";
			endif;
		endforeach;	
	endif;
	?>
    <form action="" method="post">
    <input type="hidden" name="process_as" value="abandon_and_restart">
	<input class="abandon" type="submit" value="Abandon Game and Restart?"></form><?php 
}
function submittedGameData($user_id, $player_array){
	//is it your turn?
	if($player_array['player_turn'] == $user_id):
	
		//if post data submitted by form, get it and process it
		if ( ! empty($_POST)  && $_POST['process_as'] == 'game_form'):
			unset($_POST['process_as']);
			foreach($_POST as $key => $value):
				$submitted = $key;
			endforeach;	
			
			$submitted = explode("-", $submitted);
			$trump_category = $submitted[0];
			$trump_value = $submitted[1];
			$operator = $submitted[2];
			
			//the intended value of the operator is also needed for winner
				
				//check submitted data against opponents
				$players = $player_array['all_players'];
				
				$hand_winner = '';
				
				//get dealt cards array from each opponent user meta
				foreach($players as $player_id):
					
					//take next card from top and query the data
					$opponent_hand = get_user_meta($player_id, '_allocated_cards', true);
					
					//find and remove first value
					$opponent_card_id = array_shift($opponent_hand['cards']);
					
					//in case its a winner, we should save the data.
					$save_for_later[] = array('player_id'=>$player_id,'rump_hand'=>$opponent_hand['cards']);
					
					//query this card
					$opponent_value = get_field($trump_category, $opponent_card_id);
					
					if($player_id !== $user_id):
						switch($operator):
							case '>';
								if($opponent_value > $trump_value)://compare relevant card and message
									$hand_winner = $player_id;
									echo '<p>'.$player_id.'\'s card beat yours</p>';
								else:
									$hand_winner = $user_id;
									echo '<p>Your card beat '.$player_id.'\'s card</p>';
								endif;
							break;
							case '<';
								if($opponent_value < $trump_value)://compare relevant card and message
									$hand_winner = $player_id;
									echo '<p>'.$player_id.'\'s card beat yours</p>';
								else:
									$hand_winner = $user_id;
									echo '<p>Your card beat '.$player_id.'\'s card</p>';
								endif;
							break;
						endswitch;
						//echo 'opp card id: '.$opponent_card_id.', ';
						$winner_cards[] = $opponent_card_id;
					else:
						//echo 'current card: '.$game_array['cards'][0].'<br>';
						$winner_cards[] = $player_array['cards'][0];
					
					endif;//end if $player_id !== $user_id
					
					$player_id = $hand_winner;//allocate the winner
					
				endforeach;
				
			//can only be sure of winner at end of foreach loop.
			//now the winner is found - reassign to master array and loop into database
			
			foreach($save_for_later as $player_id => $rump_hand):
				$game_array[$player_id] = array(
					'all_players' => '',
					'player_turn' => '',
					'cards' => '',
				);
			
				$game_array[$player_id]['all_players'] = $players;
				$game_array[$player_id]['player_turn'] = $hand_winner;
			
				if($players[$player_id] == $hand_winner)://if hand winner is equal to a user id
				
					$both = array_merge($rump_hand['rump_hand'],$winner_cards);
					$game_array[$player_id]['cards'] = $both;
					
				else:
					$game_array[$player_id]['cards'] = $rump_hand['rump_hand'];
					if(empty($game_array[$player_id]['cards'])):
						echo '<p>Congratulations!<br>You win this hand!</p>';
						echo '<p><a class="button" href="./">PLAY AGAIN?</a></p>';
					endif;
				endif;//endif hand winner is equal to a user id
				
				//add new cards to the end of rump hand
				//other players get the array saved as is
				if(update_user_meta($players[$player_id], '_allocated_cards', $game_array[$player_id] )):
					//echo 'updated '.$players[$player_id].' with:'; print_r($game_array[$player_id]);
				else:
					echo 'wasn\'t able to update user';
				endif;
				
			endforeach;
			
			echo '<a class="button" href="./">CONTINUE?</a>';
			
		endif;
	else:
		'Please wait for your turn.';
	endif;
}

function playerListForm(){
	?>
    <form action="" method="post">
    <?php
	$args = array(
	'orderby' => 'login',
	'order' => 'ASC',
	);
	
	// The Query
	$user_query = new WP_User_Query( $args );
	
	// User Loop
	if ( ! empty( $user_query->results ) ):
	echo '<h4>Who\'s Playing?</h4><p>(Dealer must play but won\'t go first.)</p><table>';
		foreach ( $user_query->results as $user ):
		  $userdata = get_userdata( $user->ID );?>
		  <tr><td><label><?php echo esc_attr( $userdata->user_nicename );?>&nbsp;<input type="checkbox" name="<?php echo $userdata->ID;?>" id="checkbox-<?php echo $userdata->ID;?>"></label></td></tr>
	<?php endforeach;
	echo '</table>'; ?>
    	  <input type="hidden" name="process_as" value="player_list_form">
    <?php
	else:
		echo 'No users found.';
	endif;
	echo '<input type="submit" value="Submit"></form>';
}

function shuffleanddeal(){
		$game_array = array(
			'all_players' => '',
			'player_turn' => ''
		);
		//test for submitted data & log it if its there
		if ( ! empty($_POST) && $_POST['process_as'] == 'player_list_form' ):
			foreach($_POST as $key => $value):
				if(is_numeric($key)):
					//assign user ids of all players
					$game_array['all_players'][] = $key;
				endif;
			endforeach;
		endif;
		
		if(count($game_array['all_players'])>=2):
		
		else:
			echo '<p>This is a game for two or more players, <br>please choose at least two players.</p>';
			//select players 
			playerListForm();
		endif;
		
		//randomize the order of the items, assign new order and save to DB
		$args = array(
			'post_type' => 'toptrumps',
			'order' => 'ASC',
			'orderby' => 'rand',
			'post_status' => 'publish',
		);
		
		$query = new WP_Query( $args );
		if( $query->have_posts() ): while( $query->have_posts() ):$query->the_post();
			$initial_IDs_array[] = get_the_ID(); 
		endwhile; endif;
		
		//allocate every other card to each of these two users
		if(!empty($game_array['all_players'])):
			$player_count = count($game_array['all_players']);
			$trump_count = count($initial_IDs_array);
			for($i = 0;$i <= ($trump_count -1);):
				//deal according to number of players
				for($p = 1; $p <= $player_count; $p++):
					if(!empty($initial_IDs_array[$i])):
						$player_dealt[$p][] = $initial_IDs_array[$i];
						$i ++;
					else:
						//echo 'run out of $i';
					endif;
				endfor;
			endfor;
			
			$turn_flag = 0; //when triggered, allows solitary player 
			foreach($game_array['all_players'] as $player_id):
				//allocate whos turn.
				if($player_id == get_current_user_id()):
				//if $player_id = current user don't add $player_turn = $player_id
					//do nothing
					$game_array['player_turn'] = '';
				else:
				//else add one instance of $player_turn only
					if($turn_flag == 0):
						$game_array['player_turn'] = $player_id;
						$turn_flag = 1;
					else:
						$game_array['player_turn'] = '';
					endif;
				endif;
				
				$game_array['cards'] = $player_dealt[$player_id];
				
				//allocate their $game_array to the user meta table
				if(get_user_meta($player_id, '_allocated_cards', true)):
					update_user_meta($player_id, '_allocated_cards', $game_array );
				else:
					//add the processed data to the User's meta table.
					add_user_meta( $player_id, '_allocated_cards', $game_array );
				endif;
			endforeach;
		endif;
}

function cardChoiceLayout($user_id, $player_array){
	//get the next $game_cards and display the options
	//echo 'Your current card\'s ID is: '.$player_array['cards'][0];
	
	$args = array(
		'post_type' => 'toptrumps',
		'order' => 'ASC',
		'orderby' => 'menu_order',
		'post_status' => 'publish',
		'posts_per_page' => 1,
		'post__in' => array($player_array['cards'][0])
	);
	
	$query = new WP_Query( $args );
	
	if( $query->have_posts() ): while( $query->have_posts() ): $query->the_post();
	
		?><div class=" toptrumps text-center">
		<?php echo get_the_post_thumbnail(get_the_id(), 'full-size', array('class'=>'img-responsive'));?>
		<h3 class="toptrumps-title"><?php the_title();?></h3><hr>
		<form action="" method="post">
			<input type="hidden" name="process_as" value="game_form">
			<table>
				<tr><td>Temperature:&nbsp;<?php the_field('temperature');?>&#8451;</td><td><input type="radio" name="temperature-<?php the_field('temperature');?>->" id="radioButton-temperature"></td></tr>
				<tr><td>Distance from Sun:&nbsp;<?php the_field('distance_from_sun');?> Million Km</td><td><input type="radio" name="distance_from_sun-<?php the_field('distance_from_sun');?>-<" id="radioButton-distance_from_sun"></td></tr>
				<tr><td>Diameter:&nbsp;<?php the_field('diameter');?></td><td><input type="radio" name="diameter-<?php the_field('diameter');?>->" id="radioButton-diameter"></td></tr>
				<tr><td>Gravity Compared to Earth:&nbsp;<?php the_field('gravity_compared_to_earth');?></td><td><input type="radio" name="gravity_compared_to_earth-<?php the_field('gravity_compared_to_earth');?>->" id="radioButton-gravity_compared_to_earth"></td></tr>
				<tr><td>Year of Discovery:&nbsp;<?php the_field('year_of_discovery');?></td><td><input type="radio" name="year_of_discovery-<?php the_field('year_of_discovery');?>-<" id="radioButton-year_of_discovery"></td></tr>
				<tr><td>Rotation Time:&nbsp;<?php the_field('rotation_time');?></td><td><input type="radio" name="rotation_time-<?php the_field('rotation_time');?>->" id="radioButton-rotation_time"></td></tr>
				<tr><td>Orbit Time:&nbsp;<?php the_field('orbit_time');?></td><td><input type="radio" name="orbit_time-<?php the_field('orbit_time');?>->" id="radioButton-orbit_time"></td></tr>
				<tr><td><input type="submit" value="Submit"></td><td></td></tr>
			</table>
		</form>
		<?php
		
	endwhile; endif;
}
?>