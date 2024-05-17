<?php

/* UPS Class - a PHP class to interact with UPS Online Tools APIs and retrieve real-time shipping quotes.
This class can handle domestic and international shipping requests. It requires DOMXML and cURL to interface with the USPS and
move through the returned XML document. Feel free to use this class in your programs as you see fit, it is freeware. */

class UPS
{
	var $access_license_number;
	var $user_id;
	var $password;
	var $api;
	var $request_xml;
	var $package_index = 0;
	var $current_result = array();

	var $country_list = array(
	"United States",
	"Zimbabwe");
	
	var $submit_url = "https://www.ups.com/ups.app/xml/Rate";
//	var $submit_url = "https://wwwcie.ups.com/ups.app/xml/Rate";
	
	function UPS($access_license_number, $user_id, $password, $api = 'RatingServiceSelectionRequest')
	{
		// Since password is no longer required, I have removed that from the functional requirements
//		if(empty($user_id) | empty($password)) return false;
		if (empty($access_license_number) || empty($user_id) || empty($password)) return false;
		else {
			$this->access_license_number = $access_license_number;
			$this->user_id = $user_id;
			$this->password = $password;
			$this->api = $api;
			$this->request_xml = '<?xml version="1.0"?>'.
			'<AccessRequest xml:lang="en-US">'.
				'<AccessLicenseNumber>'. $access_license_number .'</AccessLicenseNumber>'.
				'<UserId>'. $user_id .'</UserId>'.
				'<Password>'. $password .'</Password>'.
			'</AccessRequest>'.
			'<?xml version="1.0"?>'.
			'<'. $api .' xml:lang="en-US">';
		}
	}

	function reset()
	{
		$this->api = '';
		$this->current_result = '';
		$this->request_xml = '';
		$this->package_index = 0;
	}

	function add_package($attribs = '')
	{
		if(!is_array($attribs)) return false;

		//Check to make sure array has required values for API
		if($this->api == 'RatingServiceSelectionRequest') {
			// Verify required package attributes
			$required = array('request_action', 'shipment', 'shipper', 'shipper_address', 'country_code');
			foreach ($required as $reqtag) {
//				if (!isset($attribs[$reqtag])) return false;
			}
			
			// Verify allowable values for attributes

			//Check ZIP codes
			if(!isset($attribs['zip_origin'])) return false;
			if(!isset($attribs['zip_dest'])) return false;

			//Check weight
			if(!isset($attribs['pounds'])) return false;
			if(!isset($attribs['ounces'])) return false;

			//Check container for Express and Priority
			if(strtolower($attribs['service']) == 'express' || strtolower($attribs['service']) == 'priority')
			{
				if(!isset($attribs['container'])) return false;
				else {
					switch(strtolower($attribs['container']))
					{
						case 'flat rate envelope':
						case 'flat rate box':
							break;
						default:
							return false;
					}
				}
			}

			//Check size
			if(!$attribs['size']) return false;
			else {
				switch(strtolower($attribs['size']))
				{
					case 'regular':
					case 'large':
					case 'oversize':
						break;
					default:
						return false;
				}
			}

			//Check machinable for parcel post
			if(strtolower($attribs['service']) == 'parcel') {
				if(empty($attribs['machinable'])) return false;
			}

			// Compute weight
			$weight = number_format($attribs['pounds'] + ($attribs['ounces'] / 16), 2);
			
			//Add the package to the XML request
			$this->request_xml .= '<Request><RequestAction>Rate</RequestAction><RequestOption>Shop</RequestOption></Request>'.
			'<PickupType><Code>01</Code></PickupType>'.
			'<Shipment>'.
				'<Shipper><Address><PostalCode>'. $attribs['zip_origin'] . '</PostalCode><CountryCode>US</CountryCode></Address></Shipper>'.
				'<ShipTo><Address>'.
					'<City>'. $attribs['city'] .'</City>'.
					'<PostalCode>'. $attribs['zip_dest'] . '</PostalCode>'.
					'<CountryCode>'. $attribs['country'] .'</CountryCode>'.
				'</Address></ShipTo>'.
				'<Package>'.
					'<PackagingType><Code>02</Code></PackagingType>'.
/*
					'<Dimensions>'.
						'<UnitOfMeasurement>'.
							'<Code>IN</Code>'.
						'</UnitOfMeasurement>'.
						'<Length>20</Length>'.
						'<Width>20</Width>'.
						'<Height>20</Height>'.
					'</Dimensions>'.
*/
					'<PackageWeight>'.
						'<UnitOfMeasurement><Code>LBS</Code></UnitOfMeasurement>'.
						'<Weight>'. $weight .'</Weight>'.
					'</PackageWeight>'.
				'</Package>'.
			'</Shipment>';
		}
//		echo nl2br(print_r($this->request_xml));
		return true;
	}

	function submit_request()
	{
		$this->request_xml .= '</' . $this->api . '>';

		//Create a cURL instance and retrieve XML response
		if(!is_callable("curl_exec")) die("UPS::submit_request: curl_exec is uncallable");
		$ch = curl_init($this->submit_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->request_xml);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$return_xml = curl_exec($ch);
		
		//The return XML will be parsed with DOMXML into the current_result array; other accessor functions
		//will be able to return specific information about specific packages
		if(!is_callable("domxml_open_mem")) die("UPS::submit_request: domxml_open_mem is uncallable");

		//All variables prefixed with x_ are DOMXML objects
		$x_doc = domxml_open_mem($return_xml);

		$x_root = $x_doc->document_element();
		
		if($x_root->tagname() == 'RatingServiceSelectionResponse') {
			//Domestic return data needs to be parsed differently from international data

			// May need to set this if we need to shop for rates given multiple packages
			$package_id = 0;
			
			foreach($x_root->get_elements_by_tagname('RatedShipment') as $rated_shipment) {
				$x_node_current = $rated_shipment->first_child();
				$x_container = $x_node_current->first_child();
				$key = $x_container->get_content();
				
				while ($x_node_current->node_name() != 'TotalCharges') $x_node_current = $x_node_current->next_sibling();
				$x_container = $x_node_current->first_child();
				$x_container = $x_container->next_sibling();
				$value = $x_container->get_content();
				
				$this->current_result[$package_id]['Postage'][$key] = $value;
			}
		} else {
			$key = $x_pkg_info->tagname();
			$value = $x_pkg_info->get_content();
			$this->current_result[$package_id][$key] = $value;
		}

		return true;
	}

	function get_package_error($package_id = 0)
	{
		if($this->current_result[$package_id]['Error']) return $this->current_result[$package_id]['Error'];
	}

	####################################
	function get_rates($package_id = 0) {
		if($this->current_result[$package_id]['Error']) return $this->current_result[$package_id]['Error']['Description'];

		return $this->current_result[$package_id]['Postage'];
	}

}

?>
