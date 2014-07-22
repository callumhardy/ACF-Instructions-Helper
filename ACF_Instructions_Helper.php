<?php

	class ACF_Instructions_Helper {

		public $default_args = array(
			'activate_settings_page' => true,
			'allowed_fields' => array(),
			'allowed_users' => array()
		);

	 /**
	  * An array of Fields and Instructions that will overwrite existing field instructions in the back end
	  */
		public $helper_instructions = array(

			array(
				"instruction" => "<p>The instruction you type here can be inserted into an ACF using the two fields below.</p>",
				"field_name" => array('instruction')
			),

			array(
				"instruction" => "<p>Enter the name or names of ACFs that you wish the above instruction text to appear on.</br></br>Multiple names must be separated by a comma.</br></br>They also must be the 'slug' like version of the ACF name. EG: they must have be lower-case letters with underscores</p>",
				"field_name" => array('field_name')
			),

			array(
				"instruction" => "<p>Here you can specify if this instruction can only appear on a field with a certain parent field.</br></br>For instance this could be the name of a Flexible Content field or Repeater.</br></br>As with the field name above this can be multiple names separated by commas and must conform to the 'slug' like format</p>",
				"field_name" => array('field_parents')
			)
		);

	 /**
	  * The path to the current directory
	  */
  		public $path_to_dir;

	 /**
	  * -> create()
	  * 
	  * Creates an instance of the CLASS using a static method
	  *
	  * @return string
	  */

		public static function create( $config_args = array() ) {
			
			$obj = new static( $config_args );

			return $obj;

		}

	 /**
	  * -> init()
	  *
	  * Initialise the setup for the ACF_Instructions_Helper
	  * 
	  * @return string
	  */

		public function init() {

			//	Get the Current Path to this Directory
	  		$this->path_to_dir = substr( __DIR__, strpos( __DIR__, '/wp-content' ));

	  		$args = array();

	  		$args = apply_filters( 'acf_instructions_helper_args', $args );

	  		$args = array_merge( $this->default_args, $args );

			//	Enqueue ACF admin scripts and Styles
			add_action('acf/input/admin_head', array( &$this, 'enqueue_admin_scripts' ));

			//	Store the acf helper instructions
			$acf_instructions = get_field('helper_instructions','options');

			if( $acf_instructions ) {
				$this->helper_instructions = array_merge( $this->helper_instructions, $acf_instructions );
			}

			$this->helper_instructions = apply_filters( 'acf_instructions', $this->helper_instructions );

			if( empty($args['allowed_fields']) ) {

				//	Filter through all ACFs that load
				add_filter('acf/load_field', array( &$this, 'filter_acf_fields') );

			} else {

				foreach ( $args['allowed_fields'] as $key => $field_name) {
					//	Filter through all ACFs that load
					add_filter('acf/load_field/name={$field_name}', array( &$this, 'filter_acf_fields') );
				}
			}

			if( $args['activate_settings_page'] ) {

				//	Add Options Pages
				if( function_exists('acf_add_options_sub_page') )
				{

					//	User data
					$user = wp_get_current_user();
					$user_data = $user->data;

					if( empty($args['allowed_users']) || in_array( $user->ID, $args['allowed_users'] ) || in_array( $user_data->user_login, $args['allowed_users'] ) )
						acf_add_options_sub_page(array(
					        'title' => 'ACF Instructions Helper',
					        'parent' => 'options-general.php',
					        'capability' => 'manage_options'
					    ));
				}

				//	Import any ACF fields needed
				$this->import_acf_php_fields();				
			}

		}

	 /**
	  * enqueue_admin_scripts()
	  * 
	  * Enqueue admin scripts and style sheets.
	  *
	  * @return void
	  */

		public function enqueue_admin_scripts() {

			//	acf-instructions-helper js
			wp_register_script( 'acf-instructions-helper-js', $this->path_to_dir . '/assets/js/acf-instructions-helper.js', array( 'jquery' ) );
			wp_enqueue_script( 'acf-instructions-helper-js' );

			//	acf-instructions-helper css
			wp_register_style( 'acf-instructions-helpers-css', $this->path_to_dir . '/assets/css/acf-instructions-helper.css', '', '', 'screen' );
	        wp_enqueue_style( 'acf-instructions-helpers-css' );

		}

	/**
	 * filter_acf_fields()
	 *
	 * This function allows you to manipulate the an ACF using the ACF load field filter. It is called in the `init()` method of this CLASS
	 * 
	 * @return Array
	 */
	
		public function filter_acf_fields( $field ) {

			//	Filter through the instructions of all ACFs in the Content Block Field
		   	$field = $this->filter_sub_fields_instructions( $field );
		 	
		 	//	The field array is returned either altered or unaffected
		    return $field;
		}

	/**
	 * filter_sub_fields_instructions()
	 *
	 * Filters recursively through an array of fields and replaces any field instructions with those that match from the `$acf_helper_text` array
	 * 
	 * @return Array
	 */
	
		public function filter_sub_fields_instructions( $field, $current_layout = null, $current_parents = '' ) {

			$field_instruction_text = null;

			foreach ( $this->helper_instructions as $key => $value ) {

				$allowed_field_names = ( is_array($value['field_name']) )
					? $value['field_name']
					: explode( ',', str_replace( ' ', '', $value['field_name'] ) );

				if( is_array($value['field_parents']) )
					$allowed_parent_names = $value['field_parents'];
				elseif( is_string($value['field_parents']) && $value['field_parents'] != '' )
					$allowed_parent_names = explode( ',', str_replace( ' ', '', $value['field_parents'] ) );
				else
					$allowed_parent_names = null;
						
				$continue = false;
				
				if( $allowed_parent_names ) {
					foreach ( $allowed_parent_names as $key => $parent_name ) {
						if( in_array( $parent_name, $current_parents ) )
							$continue = true;	
					}
				} else {
					$continue = true;
				}
					
				if( in_array( $field['name'], $allowed_field_names ) && $continue )
					if( in_array('overwrite', $value['settings']) )
						$field_instruction_text = trim( $value['instruction'] );
					else
						$field_instruction_text .= trim( $value['instruction'] );

			}

			if( $field_instruction_text )
				$field['instructions'] = $field_instruction_text;

			$current_parents = array();

			//	Filter through Flexible content layouts
			if( isset( $field['layouts'] ) ) {

				//$current_parent = 'flexible_content';
				array_push( $current_parents, $field['name'] );

				foreach ( $field['layouts'] as $key => $layout) {

					//$current_layout = $layout['name'];
					array_push( $current_parents, $layout['name'] );

					//	This function calls itself to recursively loop through nested Flexible Content fields 
					$field['layouts'][$key] = $this->filter_sub_fields_instructions( $layout, $current_layout, $current_parents );
				}
			}

			//	Filter through Repeater sub fields
			if( isset( $field['sub_fields'] ) ) {

				//$current_parent = 'repeater';
				array_push( $current_parents, $field['name'] );

				foreach ( $field['sub_fields'] as $key => $value) {
				
					//	This function calls itself to recursively loop through nested Repeater fields 
					$field['sub_fields'][$key] = $this->filter_sub_fields_instructions( $value, $current_layout, $current_parents );
				}
			}

			return $field;
		}

	 /**
	  * get_acf_field_group()
	  *
	  * Checks for the existence of an ACF field Group in the database
	  *
	  * @param 	String $field_name The name of the ACF Field Group
	  * 
	  * @return Null/Object Returns the Field Group Object or NULL
	  */

		public function get_acf_field_group( $field_name = null  ) {

	  		global $wpdb;

	  		$table = $wpdb->prefix . "posts";

	  		$row = $wpdb->get_row("SELECT * FROM $table  WHERE post_title = '$field_name'");

	  		return $row;
		}

	 /**
	  * import_acf_php_fields()
	  *
	  * Imports some basic ACFs to construct the Instruction Helpers Toggle buttons in the back end
	  * 
	  * @return void
	  */

		public function import_acf_php_fields() {

	  		if(!$this->get_acf_field_group('Options: ACF Instructions Helper'))
	  		{
	  			if(function_exists("register_field_group"))
	  			{
	  				register_field_group(array (
	  					'id' => 'acf_options-acf-instructions-helper',
	  					'title' => 'Options: ACF Instructions Helper',
	  					'fields' => array (
	  						array (
	  							'key' => 'field_53cd61add5ca0',
	  							'label' => 'Helper Instructions',
	  							'name' => 'acf_helper_instructions',
	  							'type' => 'flexible_content',
	  							'layouts' => array (
	  								array (
	  									'label' => 'Instruction Entry',
	  									'name' => 'instruction_entry',
	  									'display' => 'row',
	  									'min' => '',
	  									'max' => '',
	  									'sub_fields' => array (
	  										array (
	  											'key' => 'field_53ce813a634ff',
	  											'label' => 'Instruction',
	  											'name' => 'instruction',
	  											'type' => 'wysiwyg',
	  											'instructions' => '<p>The instruction you type here can be inserted into an ACF using the two fields below.</p><p>Test Instruction One</p>',
	  											'column_width' => '',
	  											'default_value' => '',
	  											'toolbar' => 'full',
	  											'media_upload' => 'yes',
	  										),
	  										array (
	  											'key' => 'field_53ce972fbdeb7',
	  											'label' => 'Settings',
	  											'name' => 'settings',
	  											'type' => 'checkbox',
	  											'column_width' => '',
	  											'choices' => array (
	  												'chose_parents' => 'Choose Parent Fields',
	  												'overwrite' => 'Overwrite existing Instructions',
	  											),
	  											'default_value' => '',
	  											'layout' => 'vertical',
	  										),
	  										array (
	  											'key' => 'field_53ce815a63500',
	  											'label' => 'Field Name',
	  											'name' => 'field_name',
	  											'type' => 'text',
	  											'instructions' => '<p>Enter the name or names of ACFs that you wish the above instruction text to appear on.</br></br>Multiple names must be separated by a comma.</br></br>They also must be the \'slug\' like version of the ACF name. EG: they must have be lower-case letters with underscores</p>',
	  											'column_width' => '',
	  											'default_value' => '',
	  											'placeholder' => '',
	  											'prepend' => '',
	  											'append' => '',
	  											'formatting' => 'html',
	  											'maxlength' => '',
	  										),
	  										array (
	  											'key' => 'field_53ce816663501',
	  											'label' => 'Field Parents',
	  											'name' => 'field_parents',
	  											'type' => 'text',
	  											'instructions' => '<p>Here you can specify if this instruction can only appear on a field with a certain parent field.</br></br>For instance this could be the name of a Flexible Content field or Repeater.</br></br>As with the field name above this can be multiple names separated by commas and must conform to the \'slug\' like format</p>',
	  											'conditional_logic' => array (
	  												'status' => 1,
	  												'rules' => array (
	  													array (
	  														'field' => 'field_53ce972fbdeb7',
	  														'operator' => '==',
	  														'value' => 'chose_parents',
	  													),
	  												),
	  												'allorany' => 'all',
	  											),
	  											'column_width' => '',
	  											'default_value' => '',
	  											'placeholder' => '',
	  											'prepend' => '',
	  											'append' => '',
	  											'formatting' => 'html',
	  											'maxlength' => '',
	  										),
	  									),
	  								),
	  							),
	  							'button_label' => 'Add Entry',
	  							'min' => '',
	  							'max' => '',
	  						),
	  					),
	  					'location' => array (
	  						array (
	  							array (
	  								'param' => 'options_page',
	  								'operator' => '==',
	  								'value' => 'acf-options-acf-instructions-helper',
	  								'order_no' => 0,
	  								'group_no' => 0,
	  							),
	  						),
	  					),
	  					'options' => array (
	  						'position' => 'normal',
	  						'layout' => 'no_box',
	  						'hide_on_screen' => array (
	  						),
	  					),
	  					'menu_order' => 0,
	  				));
	  			}



	  		}
		}

	}

	ACF_Instructions_Helper::create()->init();

	