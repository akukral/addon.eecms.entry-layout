<?
class Entrylayout_ext {

	var $name			= 'Entry Layout';
	var $version 		= '1.0';
	var $description	= 'Entry specific layout in your publish pages';
	var $settings_exist	= 'n';
	var $docs_url		= 'http://duktee.com/addons/entrylayout/';
	var $settings 		= array();
    
    
	/* constructor */
	
	function __construct($settings = '')
	{
		$this->EE =& get_instance();

	}
	
	/* activate extension */
	
	function activate_extension() {
		
		/* hook : publish_form_channel_preferences  */
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'publish_form_channel_preferences',
			'hook'		=> 'publish_form_channel_preferences',
			'priority'	=> 10,
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);
		
		$this->EE->db->insert('extensions', $data);
		
		/* hook : publish_form_channel_preferences  */
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'sessions_end',
			'hook'		=> 'sessions_end',
			'priority'	=> 10,
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);
		
		$this->EE->db->insert('extensions', $data);
		

	}
	
    
	/* disable_extension */
	
    function disable_extension() 
    {
        /* Delete xtension from database */
        $this->EE->db->where('class', __CLASS__);
        $this->EE->db->delete('exp_extensions');
        
        
        /* Delete entry layouts created with this extension */
        $this->EE->db->where('member_group', '> 1000000'); //one million baby
        $this->EE->db->delete('layout_publish');
        
    }
    
	
	/* publish_form_channel_preferences */
	
	function publish_form_channel_preferences($data) {
		/* insert javascript in the page */
		$this->EE->cp->add_to_foot('<script type="text/javascript">'.$this->javascript().'</script>');
		
		return $data;
	}
	
	
	/* sessions_end */
	
    function sessions_end($sess)
    {

    	/* get entry_id */
        $entry_id = $this->EE->input->get_post('entry_id');
        
        /* get member group id */
		$member_group = $sess->userdata['group_id'];

		/* generate a fake group id */
    	$layout_group = ($member_group).($entry_id + 1000000); // do not cross the int(4) limit : 	2147483647 max (max groups: 899) (max entries : 8 999 999)

    	if($this->EE->db->where('member_group', $layout_group)->get('layout_publish')->num_rows() > 0) {
			/* use this group to display the form */
	        if($layout_group) {
	            $_GET['layout_preview'] = isset($_GET['layout_preview']) ? $_GET['layout_preview'] : $layout_group;
	        }
        }
        
    }
	
	
	/* javascript */
	
	function javascript() {
	
		/* load language file */
		$this->EE->lang->loadfile('entrylayout');
	
		/* get entry id */
		$entry_id = $this->EE->input->get('entry_id');
		
		if(!$entry_id) {
			return false; // entries need to be saved at least one because we need the entry_id
		}

		/* hack the control panel to insert :
			- save entry layout button
			- remove entry layout button
		*/
		
		$javascript = '
            jQuery(function(){
            
            	
            	/* MEMBER GROUP VALUE TO REL */
				$("#layout_groups_holder .toggle_member_groups").each(function() {
					$(this).attr("rel", $(this).attr("value"));
				});
				
				
				
				/* SAVE ENTRY LAYOUT */
				
				var entry_layout_group_submit = $("#layout_group_submit").clone();
				entry_layout_group_submit.attr("id", "entry_layout_group_submit");
				entry_layout_group_submit.html(" &nbsp;'.$this->EE->lang->line('entrylayout_save_entry_layout').'");
				entry_layout_group_submit.css("margin-top", 15);
				
				$("#layout_groups_holder").append(entry_layout_group_submit);
				
				$("#layout_group_submit img").clone().prependTo(entry_layout_group_submit);
				
				entry_layout_group_submit.click(function() {
					$("#layout_groups_holder .toggle_member_groups").each(function() {
						var fakeId = ($(this).attr("rel") - 1 + 1)+""+ ('.$entry_id.' + 1000000);
						$(this).attr("value", fakeId);
					});
					
					$("#layout_group_submit").trigger("click");
					
					$("#layout_groups_holder .toggle_member_groups").each(function() {
						$(this).attr("value", $(this).attr("rel"));
					});
					
					return false;
				});


				/* REMOVE ENTRY LAYOUT */
				
				var entry_layout_group_remove = $("#layout_group_remove").clone();
				entry_layout_group_remove.attr("id", "entry_layout_group_remove");
				entry_layout_group_remove.html(" &nbsp;'.$this->EE->lang->line('entrylayout_remove_entry_layout').'");
				entry_layout_group_remove.css("margin-bottom", 5);
				
				$("#layout_groups_holder").append(entry_layout_group_remove);
				
				$("#layout_group_remove img").clone().prependTo(entry_layout_group_remove);
				
				$("#entry_layout_group_remove").click(function() {
					$("#layout_groups_holder .toggle_member_groups").each(function() {
						var fakeId = ($(this).attr("rel") - 1 + 1)+""+ ('.$entry_id.' + 1000000);
						$(this).attr("value", fakeId);
					});
					
					$("#layout_group_remove").trigger("click");
					
					$("#layout_groups_holder .toggle_member_groups").each(function() {
						$(this).attr("value", $(this).attr("rel"));
					});
					
					return false;
				});


            });
		
		';
		
		$javascript = preg_replace("/\s+/", " ", $javascript);
		
		return $javascript;
	}
	
	
	
	
}

/* END Class */

/* End of file ext.entrylayout.php */
/* Location: ./system/expressionengine/third_party/entrylayout/ext.entrylayout.php */
