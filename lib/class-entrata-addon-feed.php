<?php

//* Based on the simple feed addon for Gravity forms sample code
//* https://github.com/gravityforms/simplefeedaddon/blob/master/class-gfsimplefeedaddon.php

GFForms::include_feed_addon_framework();

class GFEntrataFeedAddon extends GFFeedAddOn {

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
	 * @return GFEntrataFeedAddon
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GFEntrataFeedAddon();
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

        // Retrieve the name => value pairs for all fields mapped in the 'mappedFields' field map.
        $field_map = $this->get_field_map_fields( $feed, 'mappedFields' );

		// Loop through the fields from the field map setting building an array of values to be passed to the third-party service.
		$merge_vars = array();
		foreach ( $field_map as $name => $field_id ) {

			// Get the field value for the specified field id
			$merge_vars[ $name ] = $this->get_field_value( $form, $entry, $field_id );

        }
                
        $requesturl = $feed['meta']['entrata_requesturl'];
        $username = $feed['meta']['entrata_username'];
        $password = $feed['meta']['entrata_password'];
        $property_id = $feed['meta']['entrata_propertyid'];
        $leadsource = $feed['meta']['entrata_leadsource'];
        $firstname = $merge_vars['first_name'];
        $lastname = $merge_vars['last_name'];
        $email = $merge_vars['email'];
        $phone = $merge_vars['phone'];
        $message = $merge_vars['message'];
		// $date = date('m/d/Y', mktime(0, 0, 0, date("m"), date("d") -1, date("Y"))) . 'T' . '00:00:00';
		// "createdDate": "mm/dd/yyyyT03:47:33",
		
		// $date = date('m/d/Y', mktime(0, 0, 0, date("m"), date("d") -1, date("Y"))) . 'T' . '00:00:00';
		
		date_default_timezone_set('America/Denver');
		$date = sprintf( '%sT%s', date( 'm/d/Y' ), date( 'G:i:s' ) );
        
        $body = [
            "auth" => [
                "type" => "basic" 
            ], 
            "requestId" => "15", 
            "method" => [
                "name" => "sendLeads", 
                "params" => [
                    "propertyId" => $property_id, 
                    "doNotSendConfirmationEmail" => "1", 
                    "isWaitList" => "0", 
                    "prospects" => [
                        "prospect" => [
                            [
                                "leadSource" => [
                                    "originatingLeadSourceId" => $leadsource, 
                                ], 
                                "createdDate" => $date,
                                "customers" => [
                                    "customer" => [
                                        [
                                            "name" => [
                                                "firstName" => $firstname,
                                                "lastName" => $lastname, 
                                            ], 
                                            "phone" => [
                                                "personalPhoneNumber" => $phone, 
                                            ], 
                                            "email" => $email,
                                        ] 
                                    ] 
                                ], 
                                "customerPreferences" => [
                                    "comment" => $message, 
                                ], 
                            ] 
                        ] 
                    ] 
                ] 
            ] 
        ]; 
        
        $body = json_encode( $body );
        
        //* Set up the auth part of the request
        $auth = $username . ':' . $password;
        $auth = base64_encode( $auth );
        
        $request = array(
            'body'        => $body,
            'headers'     => array(
                'Content-Type'      => 'application/json',
                'Authorization'     => 'Basic ' . $auth,
            ),                
            'timeout'     => 60,
            'redirection' => 5,
            'blocking'    => true,
            'httpversion' => '1.0',
            'sslverify'   => false,
            'data_format' => 'body',
        );
        
        $this->log_debug( 'Outgoing request body: ' . $body );
        
        $response = wp_remote_post( $requesturl, $request );
        
        // Log error or success based on response.
		if ( is_wp_error( $response ) ) {
			$this->add_feed_error( sprintf( esc_html__( 'Webhook was not successfully executed. %s (%d)', 'gforms_entrata' ), $response->get_error_message(), $response->get_error_code() ), $feed, $entry, $form );
		} else {
			$this->log_debug( sprintf( '%s(): Webhook successfully executed. code: %s; body: %s', __METHOD__, wp_remote_retrieve_response_code( $response ), wp_remote_retrieve_body( $response ) ) );
		}
        
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
						'label'   => esc_html__( 'Request URL', 'entrata_gforms_addon' ),
						'type'    => 'text',
                        'name'    => 'entrata_requesturl',
                        'tooltip'   => esc_html__( 'E.g. https://YOURDOMAIN.entrata.com/api/v1/leads', 'gforms_entrata' ),
						'class'   => 'medium',
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
						'tooltip'   => esc_html__( 'This should be a 7-digit number, e.g. 1234567', 'gforms_entrata' ),
						'class'		=>  'medium',
                    ),
                    array(
						'type'      => 'text',
						'name'      => 'entrata_leadsource',
						'label'     => esc_html__( 'Lead source ID', 'gforms_entrata' ),
						'tooltip'   => esc_html__( 'This should be a 5-digit number, e.g. 12345', 'gforms_entrata' ),
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