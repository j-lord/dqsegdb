<?php

// Get libraries.
require_once('DAO.php');
require_once('GetStructure.php');
require_once('InitVar.php');

// Flag class.
class GetServerData
{
	public $query_form;
	public $server_status;
	public $version_div;
	public $version_span;
	
	// Get current server status.
	public function get_current_server_status($content_id) {
		// Init.
		$this->server_status = NULL;
		// Instantiate.
		$dao = new DAO();
		// Get host array.
		$host_array = $dao->get_value_array(2);
		// Loop host array.
		foreach($host_array as $key=> $host) {
			// Get additional text available for this host.
			$add_info = $dao->get_value_add_info($host);
			// If passed.
			if(isset($add_info)) {
				// Set.
				$add_info = " (".$add_info." data)";
			}
			// Set start time.
			$start_time = microtime(TRUE);
			// Get file contents.
			$a = json_decode(file_get_contents($host.'/dq'), true);
			// Set stop time.
			$stop_time = microtime(TRUE);
			// Set elapsed time.
			$elapsed_time = round($stop_time-$start_time, 5);
			// If returned contents are array.
			if(is_array($a)) {
				// Include with p tag.
				$this->server_status .= '<p><img src="images/green_arrow.png" alt="" title="" id="green_arrow_virgo" class="green_arrow" />'.$host.$add_info.' - instance responding in '.$elapsed_time.' seconds</p>';
			}
			// Otherwise.
			else {
				// Include with p tag.
				$this->server_status .= '<p><img src="images/green_faded_arrow.png" alt="" title="" id="green_arrow_virgo" class="green_arrow" />'.$host.$add_info.' - instance currently unavailable</p>';
			}
		}
	}

	// Get current server statistics.
	public function get_server_statistics($c, $host, $ifo, $tabs) {
		// Init.
		$r = NULL;
		// If in the right place.
		if($c == 26  || $c == 34) {
			// Instantiate.
			$structure = new GetStructure();
			// Add number of tabs required.
		 	$structure->getRequiredTabs($tabs);
		 	// Set title.
		 	$title = $host;
		 	// If IFO passed.
		 	if(isset($ifo)) {
		 		// Set.
		 		$title = $host." (".$ifo.")";
		 		$ifo = '/'.$ifo;
		 	}
			// Get file contents.
			$a = json_decode(file_get_contents($host.'/report/db'.$ifo), true);
			// If array has been returned.
			if(isset($a['results']) && is_array($a['results'])) {
				// Set.
				$i = 0;
				// Open table.
				$r .= $structure->tabStr."<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\" id=\"tbl_query_results\">\n";
				// Headings.
				$r .= $structure->tabStr."	<tr>\n";
				$r .= $structure->tabStr."		<td class=\"query_results_hdr\" colspan=\"2\">".$title."</td>\n";
				$r .= $structure->tabStr."	</tr>\n";
				// Sort array by key.
				ksort($a['results']);
				// Loop URI array.
				foreach($a['results'] as $key => $val) {
					// Set bg.
					$i++;
					$css = NULL;
					if($i == 2) {
						$css = "_hl";
						$i = 0;
					}
					// Output as row.
					$r .= $structure->tabStr."	<tr>\n";
					$r .= $structure->tabStr."		<td class=\"query_results".$css."\" width=\"80%\">".str_replace("_", " ", ucfirst($key))."</td>\n";
					$r .= $structure->tabStr."		<td class=\"query_results".$css."\" width=\"20%\">".$val."</td>\n";
					$r .= $structure->tabStr."	<tr>\n";
				}
				// Close table.
				$r .= $structure->tabStr."</table>\n";
			}
		}
		// Return.
		return $r;
	}
	
	// Get array of available IFO.
	public function get_ifo_array($host) {
		// Get file contents.
		$a = json_decode(file_get_contents($host.'/dq'), true);
		// Return.
		return $a;
	}
	
	// Get query form div.
	public function get_query_form_div($tabs) {
		// Init.
		$r = NULL;
		// Instantiate.
		$structure = new GetStructure();
		// Open DIV.
		$structure->openDiv('query_server', $tabs,'');
		$r .= $structure->div;
		// Get query form.
		$this->get_query_form($tabs);
		$r .= $this->query_form;
		// Close DIV.
		$structure->closeDiv('query_server', $tabs);
		$r .= $structure->div;
		// Return.
		return $r;
	}

	// Get query form.
	public function get_query_form($tabs) {
		// Instantiate.
		$dao = new DAO();
		$structure = new GetStructure();
		// OPEN FORM.
		$this->query_form .= "	<form method=\"POST\" id=\"frm_query_server\">\n";
		// GET IFO.
		$f = "IFO";
		$s = "	<select id=\"ifo\" onchange=\"update_div_query_server()\">\n";
		// If the default host has not been set, e.g. from a Jquery request.
		if(!isset($_SESSION['default_host'])) {
			// Set default host.
			$_SESSION['default_host'] = $this->get_quickest_host();
		}
		// Get IFO array.
		$a = json_decode(file_get_contents($_SESSION['default_host'].'/dq'), true);
		// If IFO array has been returned.
		if(is_array($a)) {
			// Init counter.
			$n = 0;
			// Add all IFO.
			array_unshift($a['Ifos'], 'Use_all_IFO');
			// Loop host array.
			foreach($a['Ifos'] as $key => $ifo) {
				$n++;
				// If on first loop and no default has yet been set.
				if($n == 1 && !isset($_SESSION['ifo'])) {
					// Set default host.
					$_SESSION['ifo'] = $ifo;
				}
				// Set selected.
				$sel = NULL;
				if($ifo == $_SESSION['ifo']) {
					$sel = " selected=\"selected\"";
				}
				// Set.
				$s .= "		<option value=\"$ifo\"".$sel.">".str_replace('_', ' ', $ifo)."</option>\n";
			}
		}
		// Close select.
		$s .= "	</select>\n";
		// Add to form.
		$this->query_form .= $structure->get_form_structure($f, $s, NULL);
		// GET FLAGS.
		$f = "Flags";
		$alt = "<span id=\"span_dq_flag_options\" class=\"span_change_host\" onclick=\"alternate_flag_option()\">Change Flag-selection option</span>";
		// Get flag choice option in use.
		$s = $this->get_choose_flag_option($tabs);
		// Add flags to form.
		$this->query_form .= $structure->get_form_structure($f, $s, $alt);
		// Open div.
		$structure->openDiv('version_div', $tabs,'');
		$this->query_form .= $structure->div;		
		// Add version information.
		$this->get_version_div_contents($tabs);
		$this->query_form .= $this->version_div;
		// Close div.
		$structure->closeDiv('version_div', $tabs);
		$this->query_form .= $structure->div;
		// Add remaining fields, starting with GPS.
		$gps_inputs = "Start: <input id=\"gps_start_time\" class=\"inp_med\" value=\"".$_SESSION['default_gps_start']."\" /> End: <input id=\"gps_stop_time\" class=\"inp_med\" value=\"".$_SESSION['default_gps_stop']."\" />";
		$this->query_form .= $structure->get_form_structure('GPS Times', $gps_inputs, NULL);
		// Set form submit button and retrieving segments message.
		$button = $structure->get_button('submit_segment_form', 'Retrieve segments', 'frm_query_server', NULL, NULL, 'retrieve_segments()', $tabs, NULL);
		// Open div.
		$structure->openDiv('retrieval_msg', $tabs,'');
		$button .= $structure->div;		
		$button .= "<img id=\"img_retrieval_msg\" name=\"img_retrieval_msg\" src=\"images/retrieving_segments.gif\" />\n";
		// Close div.
		$structure->closeDiv('retrieval_msg', $tabs);
		$button .= $structure->div;
		// Add break.
	 	$structure->getBreak($tabs+1);
		$button .= $structure->brk;
		// Actually get the button and message.
		$this->query_form .= $structure->get_form_structure('retrieve_segments', $button, NULL);
		// Close form.
		$this->query_form .= "	</form>\n";
	}

	public function get_choose_flag_option($tabs) {
		// Init.
		$s = NULL;
		// If using select.
		if($_SESSION['flag_choice_option'] == 0) {
			// Open select.
			$s = "	<select multiple size=\"8\" id=\"dq_flag\" onchange=\"update_div_flag_versions()\">\n";
			// If selecting all flags.
			if($_SESSION['ifo'] == 'Use_all_IFO') {
				$res_uri = $_SESSION['default_host'].'/report/flags';
			}
			else {
				$res_uri = $_SESSION['default_host'].'/dq/'.$_SESSION['ifo'];
			}
			// Get URI array.
			$a = json_decode(file_get_contents($res_uri), true);
			// If URI array has been returned.
			if(isset($a['results']) && is_array($a['results'])) {
				// Loop URI array.
				foreach($a['results'] as $key => $uri) {
					// If selecting all flags.
					if($_SESSION['ifo'] == 'Use_all_IFO') {
						// Explode to array.
						$u = explode('/',$uri);
						// If actually at the Use_all_Flags key.
						if($u[2] == 'IFO') {
							$flag_uri_txt = str_replace('_',' ',$u[3]);
							$flag_uri_txt = str_replace('IFO/',' ',$flag_uri_txt);
						}
						if($u[2] != 'IFO') {
							$flag_uri_txt = $u[2].' - '.$u[3];
						}
					}
					// Or, if selecting flags associated to a specific IFO.
					else {
						// Set simply to flag name.
						$flag_uri_txt = $uri;
						// Reset URI.
						$uri = '/dq/'.$_SESSION['ifo'].'/'.$uri;
					}
					// If the DQ Flag session exists, set selected.
					$sel = NULL;
					if(isset($_SESSION['dq_flag'])) {
						// Explode flags.
						$fa = explode(',',$_SESSION['dq_flag']);
						// If URI is in array.
						if(in_array($uri, $fa)) {
							$sel = " selected=\"selected\"";
						}
					}
					// Set.
					$s .= "		<option value=\"$uri\"$sel>".$flag_uri_txt."</option>\n";
				}
			}
			// Close select.
			$s .= "	</select>\n";
		}
		// Otherwise, if textarea.
		elseif($_SESSION['flag_choice_option'] == 1) {
			// Get textarea.
			$s .= "	<textarea id=\"ta_dq_flag\" onchange=\"update_div_flag_versions_from_ta()\"></textarea>\n";
		}
		// Return.
		return $s;
	}
	
	// Set flag session.
	public function set_ta_flags($f) {
		// Init.
		$r = NULL;
		// If arg passed.
		if(isset($f)) {
			// Separate content by new line.
			$a = explode("[[[BREAK]]]", $f);
			// Loop through.
			foreach($a as $key => $flag) {
				// If using all IFO.
				if($_SESSION['ifo'] == 'Use_all_IFO') {
					// Get the IFO from the flag.
					$b = explode(' - ', $flag);
					// Add flag URI to result.
					$r .= '/dq/'.$b[0].'/'.$b[1].',';
				}
				// Otherwise, if using specific IFO.
				else {
					// Add flag URI to result.
					$r .= '/dq/'.$_SESSION['ifo'].'/'.$flag.',';
				}
			}
			// Remove final comma.
			$r = substr($r, 0, -1);
		}
		// Return.
		return $r;
	}
	
	// Get the contents of the div containing flag versions.
	public function get_version_div_contents($tabs) {
		// Instantiate.
		$structure = new GetStructure();
		// If the DQ Flag session exists.
		if(isset($_SESSION['dq_flag'])) {
			// Add version information.
			$f = "Versions";
			$s = NULL;
			$i = 0;
			// Explode flags.
			$da = explode(',',$_SESSION['dq_flag']);
			// Loop through selected URI.
			foreach($da as $key => $uri) {
				$i++;
				// Explode to get flag.
				$fa = explode('/', $uri);
				$u = $fa[2];
				// If the flag name exists.
				if(isset($fa[3]) && !empty($fa[3])) {
					$flag_name = $fa[3];
					$span_name = str_replace('-','_',str_replace(' ','',$fa[3]));
					// Get div.
					$cover_div = NULL;
					// Set div colour.
					if($i == 2) {
						$cover_div = "_shaded";
						$i = 0;
					}
					$structure->openDiv('flag_'.$flag_name, $tabs,'div_f_v_cover'.$cover_div);
					$s .= $structure->div;
					// If selecting from all flags.
					if($_SESSION['ifo'] == 'Use_all_IFO') {
						$flag_name = $fa[2].' - '.$flag_name;
						$span_name = $fa[2].'_'.$span_name;
					}
					$s .= $flag_name;
					// Add version information after flag name.
					$this->get_flag_version_span_contents($uri);
					$s .= "<span id=\"span_".$span_name."\" class=\"span_versions\">".$this->version_span."</span>";
					// Close div.
					$structure->closeDiv('flag_'.$flag_name, $tabs);
					$s .= $structure->div;
				}
			}
			// Ensure that only flags that have been selected by the user are in the call to the server.
			foreach($_SESSION['uri_deselected'] as $i => $uri) {
				// Explode to get flag.
				$fa = explode('/', $uri);
				$u = $fa[2];
				$fn = $fa[3];
				// If flag not found in call to server array.
				if(!preg_match("/".$u."\/".$fn."/i", $_SESSION['dq_flag'])) {
//					echo "Not in call, but still in array: ".$u."/".$fn."<br />\n";
					// Remove from array.
					unset($_SESSION['uri_deselected'][$i]);
				}
//				else {
//					echo "In call and in array: ".$u."/".$fn."<br />\n";
//				}
			}
			// Add to div.
			$this->version_div = $structure->get_form_structure($f, $s, 'select version numbers to add them to the query');
		}
	}
	
	// Get flag version span.
	public function get_flag_version_span_contents($uri) {
		// Reset the version_span variable.
		$this->version_span = NULL;
		// If related session not set.
		if(!isset($_SESSION['uri_deselected'])) {
			// Set session.
			$_SESSION['uri_deselected'] = array();
		}
		// If args passed.
		if(isset($uri)) {
			// Explode the URI.
			$e = explode('/', $uri);
			// If flag passed.
			if($e[3]) {
				// If it already contains a version number, N.B. this occurs when 'Use all IFO' is selected.
				if(is_numeric(end($e))) {
					// Remove the last element, i.e. the version number.
					array_pop($e);
					// Re-assemble the URI.
					$uri = implode('/', $e);
				}
				// Set URI for GET versions call.
				$res_uri = $_SESSION['default_host'].$uri;
				// Get version array.
				$a = json_decode(file_get_contents($res_uri), true);
				// If array set.
				if(isset($a['version']) && is_array($a['version'])) {
					// Loop through versions.
					foreach($a['version'] as $key => $v) {
						// Set URI with version.
						$uri_v = $uri.'/'.$v;
						// Set span name.
						$span_name = 'span_'.$e[2].'_'.$e[3].'_'.$v;
						// Set class.
						$class = NULL;
						if(in_array($uri_v, $_SESSION['uri_deselected'])) {
							$class = '_deselected';
						}
						// Output versions.
						$this->version_span .= "<span id=\"".$span_name."\" class=\"span_version_no".$class."\" onclick=\"deselect_version_uri('".$span_name."','".$uri_v."')\">".$v."</span>\n";
					}
				}
			}
		}
	}	
	
	// Get the quickest-replying server host.
	public function get_quickest_host() {
		// Init
		$r = NULL;
		$quickest_reply = 10;
		$elapsed_time = 10;
		$timeout = 1;
		// Instantiate.
		$dao = new DAO();
		// Set start time.
		$start_time = microtime(TRUE);
		// Get host array.
		$host_array = $dao->get_value_array(2);
		// Loop host array.
		foreach($host_array as $key=> $host) {
			// Get file contents.
			$a = json_decode(file_get_contents($host.'/dq'), true);
			// If an array is returned.
			if(is_array($a)) {
				// Set stop time.
				$stop_time = microtime(TRUE);
				// Set elapsed time.
				$elapsed_time = round($stop_time-$start_time, 5);
			}
			// Check.
			if($elapsed_time < $quickest_reply) {
				// Set as quickest replying host.
				$r = $host;
				// Reset quickest reply.
				$quickest_reply = $elapsed_time;
			}
		}
		// Return.
		return $r;
	}

	//////////////////////
	// SEGMENT RELATED //
	////////////////////
	
	// Retrieve segments.
	public function retrieve_segments($s, $e) {
		// Init.
		$r = NULL;
		$args = $this->get_uri_args($s, $e);
		// Loop through each flag.
		foreach($_SESSION['uri_deselected'] as $i => $uri) {
			// Get resultant array.
			$r .= file_get_contents($_SESSION['default_host'].$uri.$args);
		}
		// Return.
		return $r;
	}
	
	// Get URI args.
	public function get_uri_args($s, $e) {
		// Init.
		$args = NULL;
		// If start GPS passed.
		if(isset($s)) {
			$args .= '&s='.$s;
			$_SESSION['default_gps_start'] = $s;
		}
		// If stop GPS passed.
		if(isset($e)) {
			$args .= '&e='.$e;
			$_SESSION['default_gps_stop'] = $e;
		}
		// If args have been passed.
		if(!empty($args)) {
			$args = substr($args, 1);
			$args = '?'.$args;
		}
		// Return.
		return $args;
	}
	
}

?>
