<?php
//add a shortcode to do the dirty work
add_shortcode( 'toptrumps-display', 'toptrumps_display_function' );
function toptrumps_display_function($atts, $content = null){
	//instantiate empty string to return as object
	$toptrumps_string = '';
	
	//does the player already have a game they're in?
	$user_id = get_current_user_id();
	
	ob_start();
		//is player logged in?
		if ( is_user_logged_in() ):
			
			if(get_user_meta($user_id, '_allocated_cards', true)):
				$game_array = get_user_meta($user_id, '_allocated_cards', true);
				
				if($game_array['player_turn'] == $user_id)://is it your turn?
				
					if ( ! empty($_POST)  && $_POST['process_as'] == 'game_form'):
						//if submitted, process this turn and determine hand winner.
						submittedGameData($user_id, $game_array);
					else:
						//card layout function
						cardChoiceLayout($user_id, $game_array);	
					endif;
					
				else:
					echo 'Please wait for your turn.';
				endif;
				
				//option to reset
				abandonAndRestart($game_array);
			else:
				//if there's no dealt array in the user's meta, shuffle and deal
				shuffleanddeal();
			endif;//endif get user meta
		else://endif is_user_logged_in
			 ?><a href="/wp-login.php" title="Members Area Login" rel="home">Please login to play</a><?php
		endif;

	$toptrumps_string = ob_get_clean();
	wp_reset_postdata();
	return $toptrumps_string;
}
?>