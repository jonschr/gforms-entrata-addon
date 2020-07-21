<?php

//* Based on the simple feed addon for Gravity forms sample code
//* https://github.com/gravityforms/simplefeedaddon/blob/master/class-gfsimplefeedaddon.php

GFForms::include_feed_addon_framework();

class GFSimpleFeedAddOn extends GFFeedAddOn {

	protected $_version = GFORMS_ENTRATA_ADDON_VERSION;
	protected $_min_gravityforms_version = '1.9.16';
	protected $_slug = 'gforms-entrata-addon';
	protected $_path = 'gforms-entrata-addon/gforms-entrata-addon.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Gravity Forms Entrata Feed';
	protected $_short_title = 'Entrata';

	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GFSimpleFeedAddOn
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GFSimpleFeedAddOn();
		}

		return self::$_instance;
	}

	/**
	 * Plugin starting point. Handles hooks, loading of language files and PayPal delayed payment support.
	 */
	public function init() {

		parent::init();

		$this->add_delayed_payment_support(
			array(
				'option_label' => esc_html__( 'Subscribe contact to service x only when payment is received.', 'entrata_gforms_addon' )
			)
		);

	}


	// # FEED PROCESSING -----------------------------------------------------------------------------------------------

	/**
	 * Process the feed e.g. subscribe the user to a list.
	 *
	 * @param array $feed The feed object to be processed.
	 * @param array $entry The entry object currently being processed.
	 * @param array $form The form object currently being processed.
	 *
	 * @return bool|void
	 */
	public function process_feed( $feed, $entry, $form ) {
		$feedName  = $feed['meta']['feedName'];
		$mytextbox = $feed['meta']['mytextbox'];
		$checkbox  = $feed['meta']['mycheckbox'];

		// Retrieve the name => value pairs for all fields mapped in the 'mappedFields' field map.
		$field_map = $this->get_field_map_fields( $feed, 'mappedFields' );

		// Loop through the fields from the field map setting building an array of values to be passed to the third-party service.
		$merge_vars = array();
		foreach ( $field_map as $name => $field_id ) {

			// Get the field value for the specified field id
			$merge_vars[ $name ] = $this->get_field_value( $form, $entry, $field_id );

		}

		// Send the values to the third-party service.
	}

	/**
	 * Custom format the phone type field values before they are returned by $this->get_field_value().
	 *
	 * @param array $entry The Entry currently being processed.
	 * @param string $field_id The ID of the Field currently being processed.
	 * @param GF_Field_Phone $field The Field currently being processed.
	 *
	 * @return string
	 */
	public function get_phone_field_value( $entry, $field_id, $field ) {

		// Get the field value from the Entry Object.
		$field_value = rgar( $entry, $field_id );

		// If there is a value and the field phoneFormat setting is set to standard reformat the value.
		if ( ! empty( $field_value ) && $field->phoneFormat == 'standard' && preg_match( '/^\D?(\d{3})\D?\D?(\d{3})\D?(\d{4})$/', $field_value, $matches ) ) {
			$field_value = sprintf( '%s-%s-%s', $matches[1], $matches[2], $matches[3] );
		}

		return $field_value;
	}

	// # SCRIPTS & STYLES -----------------------------------------------------------------------------------------------

	/**
	 * Return the scripts which should be enqueued.
	 *
	 * @return array
	 */
	public function scripts() {
		$scripts = array(
			array(
				'handle'  => 'my_script_js',
				'src'     => $this->get_base_url() . '/js/my_script.js',
				'version' => $this->_version,
				'deps'    => array( 'jquery' ),
				'strings' => array(
					'first'  => esc_html__( 'First Choice', 'entrata_gforms_addon' ),
					'second' => esc_html__( 'Second Choice', 'entrata_gforms_addon' ),
					'third'  => esc_html__( 'Third Choice', 'entrata_gforms_addon' ),
				),
				'enqueue' => array(
					array(
						'admin_page' => array( 'form_settings' ),
						'tab'        => 'entrata_gforms_addon',
					),
				),
			),
		);

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Return the stylesheets which should be enqueued.
	 *
	 * @return array
	 */
	public function styles() {

		$styles = array(
			array(
				'handle'  => 'my_styles_css',
				'src'     => $this->get_base_url() . '/css/my_styles.css',
				'version' => $this->_version,
				'enqueue' => array(
					array( 'field_types' => array( 'poll' ) ),
				),
			),
		);

		return array_merge( parent::styles(), $styles );
	}

	// # ADMIN FUNCTIONS -----------------------------------------------------------------------------------------------

	/**
	 * Creates a custom page for this add-on.
	 */
	// public function plugin_page() {
    //     echo 'Hello world';
	// }

	/**
	 * Configures the settings which should be rendered on the add-on settings tab.
	 *
	 * @return array
	 */
	// public function plugin_settings_fields() {
	// 	return array(
	// 		array(
	// 			'title'  => esc_html__( 'Simple Add-On Settings', 'entrata_gforms_addon' ),
	// 			'fields' => array(
	// 				array(
	// 					'name'    => 'textbox',
	// 					'tooltip' => esc_html__( 'This is the tooltip', 'entrata_gforms_addon' ),
	// 					'label'   => esc_html__( 'This is the label', 'entrata_gforms_addon' ),
	// 					'type'    => 'text',
	// 					'class'   => 'small',
	// 				),
	// 			),
	// 		),
	// 	);
	// }

	/**
	 * Configures the settings which should be rendered on the feed edit page in the Form Settings > Simple Feed Add-On area.
	 *
	 * @return array
	 */
	public function feed_settings_fields() {
		return array(
			array(
				'title'  => esc_html__( 'Entrata sendLeads API details', 'entrata_gforms_addon' ),
				'fields' => array(
                    array(
						'label'   => esc_html__( 'Feed name', 'entrata_gforms_addon' ),
						'type'    => 'text',
						'name'    => 'feedName',
						'class'   => 'small',
					),
                    array(
						'type'      => 'text',
						'name'      => 'entrata_username',
						'label'     => esc_html__( 'Entrata username', 'gforms_entrata' ),
						'class'		=>  'medium',
					),
					array(
						'type'      => 'text',
						'name'      => 'entrata_password',
						'label'     => esc_html__( 'Entrata password', 'gforms_entrata' ),
						'class'		=>  'medium',
					),
					array(
						'type'      => 'text',
						'name'      => 'entrata_propertyid',
						'label'     => esc_html__( 'Entrata property ID', 'gforms_entrata' ),
						'tooltip'   => esc_html__( 'This should be a seven-digit number, e.g. 1234567', 'gforms_entrata' ),
						'class'		=>  'medium',
					),
					array(
						'name'      => 'mappedFields',
						'label'     => esc_html__( 'Map Fields', 'entrata_gforms_addon' ),
						'type'      => 'field_map',
						'field_map' => array(
							array(
								'name'     => 'first_name',
								'label'    => esc_html__( 'First name', 'entrata_gforms_addon' ),
								'required' => 0,
                            ),
                            array(
								'name'     => 'last_name',
								'label'    => esc_html__( 'Last name', 'entrata_gforms_addon' ),
								'required' => 0,
                            ),
                            array(
								'name'       => 'email',
								'label'      => esc_html__( 'Email', 'entrata_gforms_addon' ),
								'required'   => 0,
								'field_type' => 'email',
							),
							array(
								'name'       => 'phone',
								'label'      => esc_html__( 'Phone', 'entrata_gforms_addon' ),
								'required'   => 0,
								'field_type' => 'phone',
                            ),
                            array(
								'name'       => 'message',
								'label'      => esc_html__( 'Message', 'entrata_gforms_addon' ),
								'required'   => 0,
							),
						),
					),
					// array(
					// 	'name'           => 'condition',
					// 	'label'          => esc_html__( 'Condition', 'entrata_gforms_addon' ),
					// 	'type'           => 'feed_condition',
					// 	'checkbox_label' => esc_html__( 'Enable Condition', 'entrata_gforms_addon' ),
					// 	'instructions'   => esc_html__( 'Process this simple feed if', 'entrata_gforms_addon' ),
					// ),
				),
			),
		);
	}

	/**
	 * Configures which columns should be displayed on the feed list page.
	 *
	 * @return array
	 */
	public function feed_list_columns() {
		return array(
			'feedName'  => esc_html__( 'Feed Name', 'entrata_gforms_addon' ),
            'entrata_propertyid' => esc_html__( 'Property ID', 'entrata_gforms_addon' ),
            'entrata_username' => esc_html__( 'Entrata User', 'entrata_gforms_addon' ),
            'entrata_password' => esc_html__( 'Entrata Pass', 'entrata_gforms_addon' ),
		);
	}
}