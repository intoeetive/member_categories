<?php

/*
=====================================================
 Member categories
-----------------------------------------------------
 http://www.intoeetive.com/
-----------------------------------------------------
 Copyright (c) 2011-2016 Yuri Salimovskiy
=====================================================
*/

if ( ! defined('BASEPATH'))
{
	exit('Invalid file request');
}

require_once PATH_THIRD.'member_categories/config.php';

class Member_categories_ext {

	var $name	     	= MEMBER_CATEGORIES_ADDON_NAME;
	var $version 		= MEMBER_CATEGORIES_ADDON_VERSION;
	var $settings_exist	= 'n';
    var $settings       = array();
    
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	function __construct($settings = '')
    {
        $this->settings = $settings;
	}
    
    /**
     * Activate Extension
     */
    function activate_extension()
    {
        
        $hooks = array(
    		array(
    			'hook'		=> 'cp_members_member_delete_end',
    			'method'	=> 'delete_record',
    			'priority'	=> 10
    		)
            
    	);
    	
        foreach ($hooks AS $hook)
    	{
    		$data = array(
        		'class'		=> __CLASS__,
        		'method'	=> $hook['method'],
        		'hook'		=> $hook['hook'],
        		'settings'	=> '',
        		'priority'	=> $hook['priority'],
        		'version'	=> $this->version,
        		'enabled'	=> 'y'
        	);
            ee()->db->insert('extensions', $data);
    	}	
    }
    
    function settings()
    {
        $settings = array();

        return $settings;
    }
    
    /**
     * Update Extension
     */
    function update_extension($current = '')
    {
    	if ($current == '' OR $current == $this->version)
    	{
    		return FALSE;
    	}

    	
    	ee()->db->where('class', __CLASS__);
    	ee()->db->update(
    				'extensions', 
    				array('version' => $this->version)
    	);
    }
    
    
    /**
     * Disable Extension
     */
    function disable_extension()
    {
    	ee()->db->where('class', __CLASS__);
    	ee()->db->delete('extensions');
    }
    
    
    function delete_record()
    {
        $ids = array();
		
		foreach (ee()->input->post('delete') as $key => $val)
		{		
			if ($val != '')
			{
				$ids[$val] = $val;
			}		
		}
        
        ee()->db->where_in('member_id', $ids);
        ee()->db->delete('category_members');
    }


}
// END CLASS
?>