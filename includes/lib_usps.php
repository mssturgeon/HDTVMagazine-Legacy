<?php

/* USPS Class - a PHP class to interact with the U.S. Postal Service's Web Tools APIs and retrieve real-time shipping quotes.
This class can handle domestic and international shipping requests. It requires DOMXML and cURL to interface with the USPS and
move through the returned XML document. Feel free to use this class in your programs as you see fit, it is freeware. */

class USPS
{
	var $user_id;
	var $password;
	var $api;
	var $request_xml;
	var $package_index = 0;
	var $current_result = array();

	var $country_list = array(
	"United States",
	"Zimbabwe");
	
	var $submit_url = "http://production.shippingapis.com/ShippingAPI.dll";
//	var $submit_url = "http://testing.shippingapis.com/ShippingAPITest.dll";
	
	function USPS($user_id, $password, $api = 'RateV2')
	{
		// Since password is no longer required, I have removed that from the functional requirements
//		if(empty($user_id) || empty($password)) return false;
		if (empty($user_id)) return false;
		else {
			$this->user_id = $user_id;
			$this->password = $password;
			$this->api = $api;			
			$this->request_xml = "<$api" . "Request USERID='$user_id' PASSWORD='xxxxxxx'>";
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
		if($this->api == 'RateV2') {
			// Changing the line below to allow for '0' values for pounds and ounces. The way it was before, !$attrib would return true for a '0' value.  changed to isset() to make it better
//			if(!$attribs['service'] || !$attribs['zip_origin'] || !$attribs['zip_dest'] || !$attribs['pounds'] || !$attribs['ounces'] || !$attribs['size']) return false;
			if(!$attribs['service'] || !$attribs['zip_origin'] || !$attribs['zip_dest'] || !isset($attribs['pounds']) || !isset($attribs['ounces']) || !$attribs['size']) return false;
			
			//Check service type
			if(empty($attribs['service'])) return false;
			else {
				switch(strtolower($attribs['service']))
				{
					case 'express':
					case 'first class':
					case 'priority':
					case 'parcel':
					case 'bpm':
					case 'library':
					case 'media':
					case 'all':
						break;
					default:
						return false;
				}
			}

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

			//Add the package to the XML request
			$this->request_xml .= '<Package ID="' . $this->package_index . '">';
			$this->package_index++;
			$this->request_xml .= '<Service>' . strtoupper($attribs['service']) . '</Service>';
			$this->request_xml .= '<ZipOrigination>' . $attribs['zip_origin'] . '</ZipOrigination>';
			$this->request_xml .= '<ZipDestination>' . $attribs['zip_dest'] . '</ZipDestination>';
			$this->request_xml .= '<Pounds>' . $attribs['pounds'] . '</Pounds><Ounces>' . $attribs['ounces'] . '</Ounces>';
			if(strtolower($attribs['service']) == 'express' || strtolower($attribs['service']) == 'priority')
				$this->request_xml .= '<Container>' . $attribs['container'] . '</Container>';
			$this->request_xml .= '<Size>' . mb_convert_case($attribs['size'], MB_CASE_TITLE) . '</Size>';
			if(strtolower($attribs['service']) == 'parcel' || strtolower($attribs['service']) == 'all')
				$this->request_xml .= '<Machinable>' . $attribs['machinable'] . '</Machinable>';
			$this->request_xml .= '</Package>';
		}
		
		else if($this->api == 'IntlRate')
		{
			if(!$attribs['pounds']) return false;
			if(!$attribs['ounces']) return false;

			if(!$attribs['mail_type']) return false;
			else {
				switch(strtolower($attribs['mail_type']))
				{
					case 'package':
					case 'postcards or aerogrammes':
					case 'matter for the blind':
					case 'envelope':
						break;
					default:
						return false;
				}
			}

			if(!isset($attribs['country'])) return false;
//			if(!in_array($attribs['country'], $this->country_list)) return false;

			//Add the package to the XML request
			$this->request_xml .= '<Package ID="' . $this->package_index . '">';
			$this->package_index++;	
			$this->request_xml .= '<Pounds>' . $attribs['pounds'] . '</Pounds><Ounces>' . $attribs['ounces'] . '</Ounces>';
			$this->request_xml .= '<MailType>' . $attribs['mail_type'] . '</MailType><Country>' . $attribs['country'] . '</Country>';
			$this->request_xml .= '</Package>';
		}
		
		else if($this->api == 'Verify')
		{
			# comment this lines to allow get error message from usps site
			#if(!$attribs['address1'] && !$attribs['address2']) return false;
			#if(!$attribs['city']) return false;
			#if(!$attribs['state']) return false;
			#if(!$attribs['zip5']) return false;

			//Add the package to the XML request
			$this->request_xml .= '<Address ID="' . $this->package_index . '">';
			$this->package_index++;	
			$this->request_xml .= '<Address1>' . $attribs['address1'] . '</Address1><Address2>' . $attribs['address2'] . '</Address2>';
			$this->request_xml .= '<City>' . $attribs['city'] . '</City><State>' . $attribs['state'] . '</State>';
			$this->request_xml .= '<Zip5>' . $attribs['zip5'] . '</Zip5><Zip4>' . $attribs['zip4'] . '</Zip4>';
			$this->request_xml .= '</Address>';
		} 
		else if ($this->api == 'ZipCodeLookup')
		{			
			//Add the package to the XML request
			$this->request_xml .= '<Address ID="' . $this->package_index . '">';
			$this->package_index++;	
			$this->request_xml .= '<Address1>' . $attribs['address1'] . '</Address1><Address2>' . $attribs['address2'] . '</Address2>';
			$this->request_xml .= '<City>' . $attribs['city'] . '</City><State>' . $attribs['state'] . '</State>';
			$this->request_xml .= '</Address>';
		}
		else if ($this->api == 'CityStateLookup')
		{			
			//Add the package to the XML request
			$this->request_xml .= '<ZipCode ID="' . $this->package_index . '">';
			$this->package_index++;	
			$this->request_xml .= '<Zip5>' . $attribs['zip5'] . '</Zip5>';
			$this->request_xml .= '</ZipCode>';
		}

		return true;
	}

	function submit_request()
	{
		$this->request_xml .= '</' . $this->api . 'Request>';

		//Create a cURL instance and retrieve XML response
		if(!is_callable("curl_exec")) die("USPS::submit_request: curl_exec is uncallable");
		$ch = curl_init($this->submit_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "API=" . $this->api . "&XML=" . $this->request_xml);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$return_xml = curl_exec($ch);
		
		//The return XML will be parsed with DOMXML into the current_result array; other accessor functions
		//will be able to return specific information about specific packages
		if(!is_callable("domxml_open_mem")) die("USPS::submit_request: domxml_open_mem is uncallable");

		//All variables prefixed with x_ are DOMXML objects
		$x_doc = domxml_open_mem($return_xml);

		$x_root = $x_doc->document_element();
		
		/*
		I commented this stuff out because I don't believe that errors are ever returned at the root level.
		
		//Is the document root an error message? If so, parse it into current_result and return false
		if($x_root->tagname() == 'Error')
		{
			foreach($x_root->child_nodes() as $x_err_component)
			{
				$key = $x_err_component->tagname();
				$value = $x_err_component->get_content();
				$this->current_result[0][$key] = $value;
			}
			return false;
		}
		*/
		
		if($x_root->tagname() == 'RateV2Response')
		{
			//Domestic return data needs to be parsed differently from international data

			foreach($x_root->child_nodes() as $x_package)
			{
				$package_id = $x_package->get_attribute("ID");
				$x_zip_origin = $x_package->first_child();	//Could be Error or ZipOrigination

				if($x_zip_origin->tagname() == 'Error')
				{
					foreach($x_zip_origin->child_nodes() as $x_err_component)
					{
						$key = $x_err_component->tagname();
						$value = $x_err_component->get_content();
						$this->current_result[$package_id]['Error'][$key] = $value;
					}
				}

				else if($x_zip_origin->tagname() == 'ZipOrigination')
				{
					foreach($x_package->child_nodes() as $x_pkg_info)
					{
						if($x_pkg_info->tagname() == 'Postage')
						{
							$x_mailservice = $x_pkg_info->first_child();
							$x_rate = $x_mailservice->next_sibling();
							
							$key = $x_mailservice->get_content();
							$value = $x_rate->get_content();
							
							$this->current_result[$package_id]['Postage'][$key] = $value;
						}
						
						else
						{
							$key = $x_pkg_info->tagname();
							$value = $x_pkg_info->get_content();
							$this->current_result[$package_id][$key] = $value;
						}
					}
				}
			}
		}

		else if($x_root->tagname() == 'IntlRateResponse')
		{
			//This is international data, so parse it accordingly
			foreach($x_root->child_nodes() as $x_package)
			{
				$package_id = $x_package->get_attribute('ID');
				$x_prohibitions = $x_package->first_child();

				if($x_prohibitions->tagname() == 'Error')
				{
					foreach($x_prohibitions->child_nodes() as $x_err_component)
					{
						$key = $x_err_component->tagname();
						$value = $x_err_component->get_content();
						$this->current_result[$package_id]['Error'][$key] = $value;
					}
				}

				else
				{
					$x_restrictions = $x_prohibitions->next_sibling();
					$x_observations = $x_restrictions->next_sibling();
					$x_customsforms = $x_observations->next_sibling();
					$x_expressmail = $x_customsforms->next_sibling();
					$x_areasserved = $x_expressmail->next_sibling();

					$this->current_result[$package_id]['Prohibitions'] = $x_prohibitions->get_content();
					$this->current_result[$package_id]['Restrictions'] = $x_restrictions->get_content();
					$this->current_result[$package_id]['Observations'] = $x_observations->get_content();
					$this->current_result[$package_id]['CustomsForms'] = $x_customsforms->get_content();
					$this->current_result[$package_id]['ExpressMail'] = $x_expressmail->get_content();
					$this->current_result[$package_id]['AreasServed'] = $x_areasserved->get_content();

					foreach($x_package->child_nodes() as $x_pkg_info)
					{
						if($x_pkg_info->tagname() == 'Service')
						{
							$service_id = $x_pkg_info->get_attribute('ID');
							foreach($x_pkg_info->child_nodes() as $x_svc_info)
							{
								$key = $x_svc_info->tagname();
								$value = $x_svc_info->get_content();
								$this->current_result[$package_id]['Service'][$service_id][$key] = $value;
							}
						}
					}
				}
			}
		} 
	
		else if($x_root->tagname() == 'AddressValidateResponse')
		{
			//This is international data, so parse it accordingly
			foreach($x_root->child_nodes() as $x_package)
			{
				$package_id = $x_package->get_attribute('ID');
				$x_addr1 = $x_package->first_child();
				if($x_addr1->tagname() == 'Error')
				{
					foreach($x_addr1->child_nodes() as $x_err_component)
					{
						$key = $x_err_component->tagname();
						$value = $x_err_component->get_content();
						$this->current_result[$package_id]['Error'][$key] = $value;
					}
				}
				else
				{
					# Address1 maybe empty
					if($x_addr1->tagname() == 'Address2') {
						# means address1 is empty
						$x_addr2 = $x_package->first_child();	# address2 is in first element
						
					} else {
						# Address1 is not empty
						$this->current_result[$package_id]['Address1'] = $x_addr1->get_content();
						$x_addr2 = $x_addr1->next_sibling();	# address2 is in second element
					}
					$x_city = $x_addr2->next_sibling();
					$x_state = $x_city->next_sibling();
					$x_zip5 = $x_state->next_sibling();
					$x_zip4 = $x_zip5->next_sibling();
					
					$this->current_result[$package_id]['Address2'] = $x_addr2->get_content();
					$this->current_result[$package_id]['City'] = $x_city->get_content();
					$this->current_result[$package_id]['State'] = $x_state->get_content();
					$this->current_result[$package_id]['Zip5'] = $x_zip5->get_content();
					$this->current_result[$package_id]['Zip4'] = $x_zip4->get_content();
				}
			}
		}
		else if($x_root->tagname() == 'ZipCodeLookupResponse')
		{
			//This is international data, so parse it accordingly
			foreach($x_root->child_nodes() as $x_package)
			{
				$package_id = $x_package->get_attribute('ID');
				$x_addr1 = $x_package->first_child();
				if($x_addr1->tagname() == 'Error')
				{
					foreach($x_addr1->child_nodes() as $x_err_component)
					{
						$key = $x_err_component->tagname();
						$value = $x_err_component->get_content();
						$this->current_result[$package_id]['Error'][$key] = $value;
					}
				}
				else
				{
					# Address1 maybe empty
					if($x_addr1->tagname() == 'Address2') {
						# means address1 is empty
						$x_addr2 = $x_package->first_child();	# address2 is in first element
						
					} else {
						# Address1 is not empty
						$this->current_result[$package_id]['Address1'] = $x_addr1->get_content();
						$x_addr2 = $x_addr1->next_sibling();	# address2 is in second element
					}
					$x_city = $x_addr2->next_sibling();
					$x_state = $x_city->next_sibling();
					$x_zip5 = $x_state->next_sibling();
					$x_zip4 = $x_zip5->next_sibling();
					
					$this->current_result[$package_id]['Address2'] = $x_addr2->get_content();
					$this->current_result[$package_id]['City'] = $x_city->get_content();
					$this->current_result[$package_id]['State'] = $x_state->get_content();
					$this->current_result[$package_id]['Zip5'] = $x_zip5->get_content();
					$this->current_result[$package_id]['Zip4'] = $x_zip4->get_content();
				}
			}
		}
		else if($x_root->tagname() == 'CityStateLookupResponse')
		{
			//This is international data, so parse it accordingly
			foreach($x_root->child_nodes() as $x_package)
			{
				$package_id = $x_package->get_attribute('ID');
				$x_zip5 = $x_package->first_child();
				if($x_zip5->tagname() == 'Error')
				{
					foreach($x_zip5->child_nodes() as $x_err_component)
					{
						$key = $x_err_component->tagname();
						$value = $x_err_component->get_content();
						$this->current_result[$package_id]['Error'][$key] = $value;
					}
				}
				else
				{
					$x_city = $x_zip5->next_sibling();
					$x_state = $x_city->next_sibling();
					
					$this->current_result[$package_id]['City'] = $x_city->get_content();
					$this->current_result[$package_id]['State'] = $x_state->get_content();
					$this->current_result[$package_id]['Zip5'] = $x_zip5->get_content();
					
				}
			}
		}
		return true;
	}

	# get value returned
	function get_address1($package_id = 0)
	{
		return ($this->current_result[$package_id]['Address1'])? $this->current_result[$package_id]['Address1']:"";
	}
	
	function get_address2($package_id = 0)
	{
		return ($this->current_result[$package_id]['Address2'])? $this->current_result[$package_id]['Address2']:"";
	}
	
	function get_city($package_id = 0)
	{
		return ($this->current_result[$package_id]['City'])? $this->current_result[$package_id]['City']:"";
	}
	
	function get_state($package_id = 0)
	{
		return ($this->current_result[$package_id]['State'])? $this->current_result[$package_id]['State']:"";
	}
	
	function get_zip5($package_id = 0)
	{
		return ($this->current_result[$package_id]['Zip5'])? $this->current_result[$package_id]['Zip5']:"";
	}
	
	function get_zip4($package_id = 0)
	{
		return ($this->current_result[$package_id]['Zip4'])? $this->current_result[$package_id]['Zip4']:"";
	}
	
	function get_package_error($package_id = 0)
	{
		if($this->current_result[$package_id]['Error']) return $this->current_result[$package_id]['Error'];
	}

	####################################
	function get_rates($package_id = 0)
	{
		if($this->current_result[$package_id]['Error']) return $this->current_result[$package_id]['Error']['Description'];

		if($this->api == 'RateV2')
			return $this->current_result[$package_id]['Postage'];
		else if($this->api == 'IntlRate')
		{
			//SvcDescription and Postage
			$result = array();

			foreach($this->current_result[$package_id]['Service'] as $service)
			{
				$key = $service['SvcDescription'];
				$result[$key] = $service['Postage'];
			}

			return $result;
		}
		else return false;
	}

	function get_prohibitions($package_id)
	{
		if($this->api == 'IntlRate') return $this->current_result[$package_id]['Prohibitions'];
		else return false;
	}

	function get_restrictions($package_id)
	{
		if($this->api == 'IntlRate') return $this->current_result[$package_id]['Restrictions'];
		else return false;
	}

	function get_observations($package_id)
	{
		if($this->api == 'IntlRate') return $this->current_result[$package_id]['Observations'];
		else return false;
	}

	function get_areas_served($package_id)
	{
		if($this->api == 'IntlRate') return $this->current_result[$package_id]['AreasServed'];
		else return false;
	}
}

?>
