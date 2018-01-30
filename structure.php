<?php

//creates custom post type and assigns some custom fields and templates

function create_post_type_toptrumps() {
  register_post_type( 'toptrumps',
    array(
      'labels' => array(
        'name' => __( 'Top Trumps' ),
        'singular_name' => __( 'Trump Card' )
      ),
      'public' => true,
      'has_archive' => true,
	  'menu_position' => 5,
	  'capability_type'    => 'post',
	  'supports' => array('title', 'editor','page-attributes','thumbnail'),
	  'rewrite' => array( 'slug' => 'toptrumps' )
    )
  );
}
add_action( 'init', 'create_post_type_toptrumps' );

//add custom tax for top trumps

function create_toptrumps_tax() {
	register_taxonomy(
		'toptrump-group',
		'toptrumps',
		array(
			'label' => __( 'Top Trump Group' ),
			'rewrite' => array( 'slug' => 'toptrump-group' ),
			'hierarchical' => true,
		)
	);
}

add_action( 'init', 'create_toptrumps_tax' );

//add any necessary custom fields
if( function_exists('acf_add_local_field_group') ):
//for later if I can dynamically populate fields though custom taxonomy page
/*acf_add_options_sub_page(array(
	'title'      => 'Top Trumps Settings',
	'parent'     => 'edit.php?post_type=toptrumps',
	'capability' => 'manage_options'
));

acf_add_local_field_group(array (
	'key' => 'toptrumps_extra_fields',
	'title' => 'Top Trumps Options',
	'fields' => array (
		array (
			'key' => 'toptrumps_deck_title',
			'label' => 'Title of this deck of Top Trumps',
			'name' => 'toptrumps-title',
			'type' => 'text',
		),
		array (
			'key' => 'toptrumps_header_text',
			'label' => 'Description of this deck of Top Trumps',
			'name' => 'toptrumps-text',
			'type' => 'wysiwyg',
			'instructions' => 'Description text will be shown on top trumps archive page',
		),
		array (
			'key' => 'toptrumps_number_columns',
			'label' => 'Number of Duelling Parameters',
			'name' => 'toptrumps_number_duelling_parameters',
			'type' => 'select',
			'instructions' => '',
			'choices' => array (
				'4' => '4',
				'5' => '5',
				'6' => '6',
				'7' => '7',
			),
			'default_value' => array (
				0 => '4',
			),
		),
	),
	'location' => array (
		array (
			array (
				'param' => 'options_page',
				'operator' => '==',
				'value' => 'acf-options-top-trumps-settings',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => 1,
	'description' => '',
));*/

acf_add_local_field_group(array (
	'key' => 'group_toptrump_parameters',
	'title' => 'Top Trump Duelling Parameters',
	'fields' => array (
		array (
			'key' => 'field_temperature',
			'label' => 'Temperature 	(&#8451;)',
			'name' => 'temperature',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
		),
		array (
			'key' => 'field_distance_from_sun',
			'label' => 'Distance From Sun (Million Km)',
			'name' => 'distance_from_sun',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
		),
		array (
			'key' => 'field_diameter',
			'label' => 'Diameter (Km)',
			'name' => 'diameter',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
		),
		array (
			'key' => 'field_gravity_compared_to_earth',
			'label' => 'Gravity compared to Earth',
			'name' => 'gravity_compared_to_earth',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
		),
		array (
			'key' => 'field_year_of_discovery',
			'label' => 'Year of discovery',
			'name' => 'year_of_discovery',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
		),
		array (
			'key' => 'field_rotation_time',
			'label' => 'Rotation time (Earth Days)',
			'name' => 'rotation_time',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
		),
		array (
			'key' => 'field_orbit_time',
			'label' => 'Orbit time (Earth Days)',
			'name' => 'orbit_time',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
		),
	),
	'location' => array (
		array (
			array (
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'toptrumps',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => 1,
	'description' => '',
));
endif;

?>