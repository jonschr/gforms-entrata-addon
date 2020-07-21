<?php

// Set the propery ID
$property_id = '1058116';

//* Send the request headers for form ID 1
add_filter( 'gform_webhooks_request_headers_1', 'set_headers', 10, 4 );
function set_headers( $request_headers, $feed, $entry, $form ){
	
	//* This is the username:password but base64 encoded
    $request_headers['Authorization'] = 'Basic YnJpbmRsZV9hcGlAY2FyZGluYWw6QnJpbmRsZTUyMjIwMjBe';
    return $request_headers;
}

//* Set up the body for form ID 1
add_filter( 'gform_webhooks_request_data_1', 'modify_data_test_webhook_form', 10, 4 );
function modify_data_test_webhook_form( $request_data, $feed, $entry, $form ){    
	
	$data = $request_data;
	
	// Property details
	$property_id = '1058114';
	$firstname = $data['1.3'];
	$lastname = $data['1.6'];
	$email = $data['2'];
	$phone = $data['3'];
	$message = $data['4'];
	$date = date( 'm/d/Y') . 'T' . date( 'H:i:s' );
	$date = date( 'm/d/Y') . 'T' . '00:00:00';
	$leadsource = '41474'; // 41474 is the Community Website ID
	
	$request_data = [
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
								// "additionalLeadSourceIds" => "xxxx,xxxx,xxxx" 
							], 
							"createdDate" => $date,
							// "createdDate" => "mm/dd/yyyyT03:47:33", 
							// "leasingAgentId" => "318487", 
							"customers" => [
								"customer" => [
									[
										"name" => [
											"firstName" => $firstname,
											"lastName" => $lastname, 
											// "namePrefix" => "Name Prefix", 
											// "middleName" => "Middle Name", 
											// "maidenName" => "Maiden Name", 
											// "nameSuffix" => "Name Suffix" 
										], 
										// "customerRelationshipTypeId" => "XXXX", 
										// "address" => [
										// 	"addressLine1" => "addressLine1", 
										// 	"addressLine2" => "addressLine2", 
										// 	"addressLine3" => "addressLine3", 
										// 	"city" => "city", 
										// 	"state" => "state code", 
										// 	"postalCode" => "postal code" 
										// ], 
										"phone" => [
											"personalPhoneNumber" => $phone, 
											// "cellPhoneNumber" => "xxx-xxx-xxxx", 
											// "officePhoneNumber" => "xxx-xxx-xxxx", 
											// "faxNumber" => "xxx-xxx-xxxx" 
										], 
										"email" => $email,
									] 
								] 
							], 
							"customerPreferences" => [
								// "desiredMoveInDate" => "mm/dd/yyyy", 
								// "desiredFloorplanId" => "xxxx", 
								// "desiredUnitTypeId" => "xxxx", 
								// "desiredUnitId" => "xxxx", 
								// "desiredRent" => [
								// 	"min" => "200", 
								// 	"max" => "300" 
								// ], 
								// "desiredNumBedrooms" => "2", 
								// "desiredNumBathrooms" => "3", 
								// "desiredLeaseTerms" => "18", 
								// "numberOfOccupants" => "1", 
								"comment" => $message, 
							], 
						// 	"events" => [
						// 		"event" => [
						// 			[
						// 				"date" => "mm/dd/yyyyT03:47:33", 
						// 				"callData" => [
						// 					"callFrom" => "xxx-xxx-xxxx", 
						// 					"ringThrough" => "xxx-xxx-xxxx", 
						// 					"callStatus" => "Answer", 
						// 					"duration" => "900", 
						// 					"audioLink" => "URL for the call" 
						// 				] 
						// 			], 
						// 			[
						// 				"type" => "Appointment", 
						// 				"date" => "mm/dd/yyyyT22:11:34", 
						// 				"appointmentDate" => "mm/dd/yyyy", 
						// 				"timeFrom" => "11:00am", 
						// 				"timeTo" => "11:30am", 
						// 				"eventReasons" => "Appointment Title", 
						// 				"comments" => "Appointment Description" 
						// 			], 
						// 			[
						// 				"type" => "EmailFromProspect", 
						// 				"date" => "mm/dd/yyyyT22:11:34", 
						// 				"emailAddresses" => [
						// 					"from" => "test1@email.com", 
						// 					"to" => "test11@email.com", 
						// 					"cc" => "test11@email.com", 
						// 					"bcc" => "test11@email.com" 
						// 				], 
						// 				"subject" => "Email Subject", 
						// 				"emailBody" => "Email Body" 
						// 			], 
						// 			[
						// 				"type" => "EmailToProspect", 
						// 				"date" => "03-22-2016T22:11:34", 
						// 				"emailAddresses" => [
						// 					"from" => "test12@email.com", 
						// 					"to" => "test1@email.com", 
						// 					"cc" => "test02@email.com", 
						// 					"bcc" => "test02@email.com" 
						// 				], 
						// 				"subject" => "Email Subject", 
						// 				"emailBody" => "Email Body" 
						// 			], 
						// 			[
						// 				"type" => "Show", 
						// 				"date" => "mm/dd/yyyyT03:47:33", 
						// 				"agentId" => "xxxxx", 
						// 				"unitSpaceIds" => "xxxxx,xxxxx", 
						// 				"comments" => "Comments", 
						// 				"eventReasonId" => "xxxxx" 
						// 			], 
						// 			[
						// 				"type" => "Note", 
						// 				"date" => "mm/dd/yyyyT03:47:33", 
						// 				"eventResultId" => "xxxxx", 
						// 				"comments" => "comments" 
						// 			], 
						// 			[
						// 				"type" => "IncomingText", 
						// 				"date" => "04/30/2020T07:20:46", 
						// 				"eventResultId" => "2826839", 
						// 				"comments" => "Incoming_Text" 
						// 			], 
						// 			[
						// 				"type" => "OutgoingText", 
						// 				"date" => "04/30/2020T07:20:46", 
						// 				"eventResultId" => "57428", 
						// 				"comments" => "Outgoing_Text" 
						// 			] 
						// 		] 
						// 	] 
						] 
					] 
				] 
			] 
		] 
	]; 
    
	return $request_data;
	
}