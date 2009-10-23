<?php

if(!defined('EXT'))
{
	exit('Invalid file request');
}

class File_field_audio
{
	var $settings        = array();
	var $name            = 'File Field Audio Preview';
	var $version         = '1.0.0';
	var $description     = 'Adds audio previews to nGen File Field MP3 uploads in the control panel.';
	var $settings_exist  = 'y';
	var $docs_url        = '';

	
	// -------------------------------
	//   Constructor - Extensions use this for settings
	// -------------------------------
	
	function File_field_audio($settings='')
	{
	    $this->settings = $settings;
	}
	// END
	
	// --------------------------------
	//  Settings
	// --------------------------------  
	
	function settings()
	{	    
		$settings = array();
	    $settings['lib_url'] = '';
	    return $settings;
	}
	// END


	// --------------------------------
	//  Control panel changes
	// -------------------------------- 
	    
	function show_full_control_panel_end($out)
	{

		global $EXT;
		if ($EXT->last_call !== FALSE)
		{
			$out = $EXT->last_call;
		}

		if($this->settings['lib_url']
			&& isset($_GET['C']) && ($_GET['C'] == 'edit' || $_GET['C'] == 'publish')
			&& isset($_GET['M']) && ($_GET['M'] == 'edit_entry' || $_GET['M'] == 'new_entry' || $_GET['M'] == 'entry_form'))
		{
			$ffa_path = (substr($this->settings['lib_url'], -1) != '/')
				? $this->settings['lib_url'] . '/'
				: $this->settings['lib_url'];
			
			$target = array('</head>', '</body>');
			
			$js = array('
			<script type="text/javascript" src="'.$ffa_path.'file_field_audio/swfobject.js"></script>
			<script type="text/javascript" src="'.$ffa_path.'file_field_audio/1bit.js"></script>
	
			<style type="text/css">
			span.onebit_mp3 span { position: relative; top: 3px; margin-right: 5px; }
			a.onebit_live { visibility: hidden; }
			div.ngen-file-existing span.onebit_mp3 { margin-left: 10px; }
			</style>
			</head>
			',
			'
			<script type="text/javascript">
				oneBit = new OneBit("'.$ffa_path.'file_field_audio/1bit.swf");
				oneBit.specify("color", "#1D7FD5");
				oneBit.specify("playerSize", "12");
				oneBit.specify("background", "transparent");
				oneBit.specify("position", "before");				
				
				oneBit.ready(function() {
					oneBit.apply("a.ngen-file-link");
				});
				
				$(".ngen-file-existing select").livequery("change", function()
				{
					scope = $(this).parent();
					$(".onebit_mp3", scope).remove();

					if( /(\.mp3)$/.test($(this).val()) ) {
						fieldName = $(this).parent().prevAll("input[type=file]").attr("name");
						fieldName_array = /(.*?)(\[.+\]\[.+\])?$/.exec(fieldName);
						fieldName = fieldName_array[1];
						thisFile = $(this).val();
						$(this).after("<a href=\"" + nGenFile.thumbpaths[fieldName] + thisFile + "\" class=\"onebit_live\">Preview</a>");
						oneBit.apply("a.onebit_live");
					}
				});
				
			</script>
			</body>
			');		

			$out = str_replace($target, $js, $out);
		}		
			
		return $out;
		
	}   
	// END 
	
   
	// --------------------------------
	//  Activate Extension
	// --------------------------------
	
	function activate_extension()
	{
	    global $DB;
		
	    $DB->query($DB->insert_string('exp_extensions',
	    	array(
				'extension_id' => '',
		        'class'        => "File_field_audio",
		        'method'       => "show_full_control_panel_end",
		        'hook'         => "show_full_control_panel_end",
		        'settings'     => "",
		        'priority'     => 10,
		        'version'      => $this->version,
		        'enabled'      => "y"
				)
			)
		);
	}
	// END


	// --------------------------------
	//  Update Extension
	// --------------------------------  
	
	function update_extension($current='')
	{
	    global $DB;
	    
	    if ($current == '' OR $current == $this->version)
	    {
	        return FALSE;
	    }
	    	    
	    $DB->query("UPDATE exp_extensions 
	                SET version = '".$DB->escape_str($this->version)."' 
	                WHERE class = 'File_field_audio'");
	}
	// END
	
	
	// --------------------------------
	//  Disable Extension
	// --------------------------------
	
	function disable_extension()
	{
	    global $DB;
	    
	    $DB->query("DELETE FROM exp_extensions WHERE class = 'Itee_bitee_audio'");
	}
	// END


}
// END CLASS