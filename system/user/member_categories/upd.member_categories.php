<?php

/*
=====================================================
 Member categories
-----------------------------------------------------
 http://www.intoeetive.com/
-----------------------------------------------------
 Copyright (c) 2011-2015 Yuri Salimovskiy
=====================================================
 This software is intended for usage with
 ExpressionEngine CMS, version 3.0 or higher
=====================================================
*/

if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}

require_once PATH_THIRD.'member_categories/config.php';

class Member_categories_upd {

    var $version = MEMBER_CATEGORIES_ADDON_VERSION;
    
    function __construct() 
	{ 

    } 
    
    function install() 
	{ 
        
        ee()->load->dbforge(); 
        
        //----------------------------------------
		// MODULES
		// The settings column, Ellislab should have put this one in long ago.
		// No need for a seperate preferences table for each module.
		//----------------------------------------
		if (ee()->db->field_exists('settings', 'modules') == FALSE)
		{
			ee()->dbforge->add_column('modules', array('settings' => array('type' => 'TEXT') ) );
		}
        
        //category_members
		$fields = array(
			'member_id'		=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'cat_id'		=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0)
		);

		ee()->dbforge->add_field($fields);
		ee()->dbforge->add_key('member_id');
        ee()->dbforge->add_key('cat_id');
		ee()->dbforge->create_table('category_members', TRUE);
        
        $settings = array();
        ee()->db->select('site_id');
        $q = ee()->db->get('sites');
        foreach ($q->result() as $obj)
        {
            $settings[$obj->site_id]['category_groups']=array();
            $settings[$obj->site_id]['assign_parent'] = 'y';
        }
        
        $data = array( 'module_name' => 'Member_categories' , 'module_version' => $this->version, 'has_cp_backend' => 'y', 'has_publish_fields' => 'n', 'settings'=> base64_encode(serialize($settings)) ); 
        ee()->db->insert('modules', $data); 
        
        $data = array( 'class' => 'Member_categories' , 'method' => 'categories_save' ); 
        ee()->db->insert('actions', $data); 

        return TRUE; 
        
    } 
    
    function uninstall() 
	{ 
        
        ee()->load->dbforge(); 
        
        ee()->db->select('module_id'); 
        $query = ee()->db->get_where('modules', array('module_name' => 'Member_categories')); 
        
        ee()->db->where('module_id', $query->row('module_id')); 
        ee()->db->delete('module_member_groups'); 
        
        ee()->db->where('module_name', 'Member_categories'); 
        ee()->db->delete('modules'); 
        
        ee()->db->where('class', 'Member_categories'); 
        ee()->db->delete('actions'); 
        
       ee()->dbforge->drop_table('category_members');
        
        return TRUE; 
    } 
    
    function update($current='') 
	{ 
		if (version_compare($current, '3.0.0', '<'))
        {
            $settings_q = ee()->db->select('settings')->from('modules')->where('module_name', 'Member_categories')->limit(1)->get(); 
            
            $data = array('settings' => base64_encode($settings_q->row('settings'))); 
            
            ee()->db->where('module_name', 'Member_categories'); 
            ee()->db->update('modules', $data);
        } 
        return TRUE; 
    } 
	

}
/* END */
?>