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


class Member_categories {

    var $return_data	= ''; 				
    
    var $settings 		= array();    
    

    /** ----------------------------------------
    /**  Constructor
    /** ----------------------------------------*/

    function __construct()
    {        
        ee()->lang->loadfile('myaccount');  
        ee()->lang->loadfile('member_categories');  
        
        $settings_q = ee()->db->select('settings')->from('modules')->where('module_name', 'Member_categories')->limit(1)->get(); 
        $this->settings = unserialize(base64_decode($settings_q->row('settings')));
    }
    /* END */
    
    function members($category_id='')
    {
        if ($category_id=='')
        {
            if (ee()->TMPL->fetch_param('category_id')!='')
            {
                $category_id_a = explode("|",ee()->TMPL->fetch_param('category_id'));
            }
            else if (ee()->TMPL->fetch_param('category_url_title')!='')
            {
                ee()->db->select('cat_id');
                ee()->db->where('cat_url_title', ee()->TMPL->fetch_param('category_url_title'));
                $q = ee()->db->get('categories');
                if ($q->num_rows()==0)
                {
                    if (ee()->TMPL->fetch_param('errors')=='off' || ee()->TMPL->fetch_param('errors')=='no')
                    {
                        return;
                    }
                    return ee()->output->show_user_error('general', array(lang('category_does_not_exist')));
                }
                $category_id_a = array($q->row('cat_id'));
            }
            else
            {
                if (ee()->TMPL->fetch_param('errors')=='off' || ee()->TMPL->fetch_param('errors')=='no')
                {
                    return;
                }
                return ee()->output->show_user_error('general', array(lang('no_category_provided')));
            }
        }
        else
        {
            $category_id_a = explode("|",$category_id);
        }
        
        $start = 0;
        $paginate = (ee()->TMPL->fetch_param('paginate')=='top')?'top':((ee()->TMPL->fetch_param('paginate')=='both')?'both':'bottom');
        if (ee()->TMPL->fetch_param('limit')!='')
        {
            ee()->db->select('COUNT(*) AS cnt');
            ee()->db->from('category_members');
            ee()->db->where_in('cat_id', $category_id_a);
            $q = ee()->db->get();
            $total_results = $q->row('cnt');
            $limit = intval(ee()->TMPL->fetch_param('limit'));
            
            $basepath = ee()->functions->create_url(ee()->uri->uri_string);
            $query_string = (ee()->uri->page_query_string != '') ? ee()->uri->page_query_string : ee()->uri->query_string;

			if (preg_match("#^P(\d+)|/P(\d+)#", $query_string, $match))
			{
				$start = (isset($match[2])) ? $match[2] : $match[1];
				$basepath = ee()->functions->remove_double_slashes(str_replace($match[0], '', $basepath));
			}

        }
        
        if (ee()->TMPL->fetch_param('custom_fields')=='yes')
        {
            $custom_fields = array();
            ee()->db->select('m_field_id, m_field_name');
            $q = ee()->db->get('member_fields');
            foreach ($q->result() as $obj)
            {
                $custom_fields[$obj->m_field_id] = $obj->m_field_name;
            }
        }
        
        $what = 'members.*';
        if (ee()->TMPL->fetch_param('custom_fields')=='yes') 
        {
            $what .= ', member_data.*';
        }
        ee()->db->select($what);
        ee()->db->from('category_members');
        ee()->db->join('members', 'category_members.member_id=members.member_id', 'right');
        if (ee()->TMPL->fetch_param('custom_fields')=='yes') 
        {
            ee()->db->join('member_data', 'members.member_id=member_data.member_id', 'left');
        }
        ee()->db->where_in('cat_id', $category_id_a);
        $sort = (ee()->TMPL->fetch_param('sort')=='desc')?'desc':'asc';
        $order = (ee()->TMPL->fetch_param('order_by')!='')?ee()->TMPL->fetch_param('order_by'):'member_id';
        ee()->db->order_by('members.'.$order, $sort);
        if (ee()->TMPL->fetch_param('limit')!='')
        {
            ee()->db->limit($limit, $start);
        }
        $q = ee()->db->get();
        if ($q->num_rows()==0)
        {
            return ee()->TMPL->no_results();
        }
        
        $out = '';
        $data = array();
        $i = 0;
        
        foreach ($q->result_array() as $row)
        {
            $i++;
            $row['absolute_count'] = $i+$start;
            
            if (ee()->session->userdata('display_avatars') == 'n' || $row['avatar_filename'] == '')
			{			
				$row['avatar_url'] = '';
				$row['avatar_image_width'] = '';
				$row['avatar_image_height'] = '';
			}
			else
			{
				$row['avatar_url'] = ee()->config->item('avatar_url').$row['avatar_filename'];
				$row['avatar_image_width'] = $row['avatar_width'];
				$row['avatar_image_height'] = $row['avatar_height'];					
			}
            if (ee()->session->userdata('display_photos') == 'n' || $row['photo_filename'] == '')
			{			
				$row['photo_url'] = '';
				$row['photo_image_width'] = '';
				$row['photo_image_height'] = '';
			}
			else
			{
				$row['photo_url'] = ee()->config->item('photo_url').$row['photo_filename'];
				$row['photo_image_width'] = $row['photo_width'];
				$row['photo_image_height'] = $row['photo_height'];							
			}
            if (ee()->session->userdata('display_signatures') == 'n' || $row['sig_img_filename'] == '')
			{			
				$row['signature_image_url'] = '';
				$row['signature_image_width'] = '';
				$row['signature_image_height'] = '';
			}
			else
			{
				$row['signature_image_url'] = ee()->config->item('sig_img_url').$row['sig_img_filename'];
				$row['signature_image_width'] = $row['sig_img_width'];
				$row['signature_image_height'] = $row['sig_img_height'];									
			}
            
            $row['birthday'] = '';
			if ($row['bday_m'] != '' AND $row['bday_m'] != 0)
			{
				$month = (strlen($row['bday_m']) == 1) ? '0'.$row['bday_m'] : $row['bday_m'];

				$m = ee()->localize->localize_month($month);

				$row['birthday'] .= lang($m['1']);

				if ($row['bday_d'] != '' AND $row['bday_d'] != 0)
				{
					$row['birthday'] .= ' '.$row['bday_d'];
				}
			}
			if ($row['bday_y'] != '' AND $row['bday_y'] != 0)
			{
				if ($row['birthday'] != '')
				{
					$row['birthday'] .= ', ';
				}

				$row['birthday'] .=$row['bday_y'];
			}
            
            
            if (ee()->TMPL->fetch_param('custom_fields')=='yes')
            {
                foreach ($custom_fields as $field_id=>$field_name)
                {
                    $row[$field_name] = $row['m_field_id_'.$field_id];
                }
            } 
            
            $data[] = $row;
             
        }
        
        // Start up pagination
		ee()->load->library('pagination');
		$pagination = ee()->pagination->create();
		ee()->TMPL->tagdata = $pagination->prepare(ee()->TMPL->tagdata);

		// Disable pagination if the limit parameter isn't set
		if (ee()->TMPL->fetch_param('limit')!='')
		{
			$pagination->paginate = FALSE;
		}

		if ($pagination->paginate)
		{
			$pagination->build($q->num_rows(), ee()->TMPL->fetch_param('limit'));
			$data = array_slice($data, $pagination->offset, $pagination->per_page);
		}

		$out = ee()->TMPL->parse_variables(ee()->TMPL->tagdata, array_values($data));

		if ($pagination->paginate === TRUE)
		{
			$out = $pagination->render($out);
		}

        return $out;
    }
    
    function categories($member_id='')
    {
        if ($member_id=='')
        {
            if (ee()->TMPL->fetch_param('member_id')!='')
            {
                if (ee()->TMPL->fetch_param('member_id')=='{member_id}' || ee()->TMPL->fetch_param('member_id')=='{logged_in_member_id}')
                {
                    $member_id_a = array(ee()->session->userdata('member_id'));
                }
                else
                {
                    $member_id_a = explode("|", ee()->TMPL->fetch_param('member_id'));
                }
            }
            else if (ee()->TMPL->fetch_param('username')!='')
            {
                ee()->db->select('member_id');
                ee()->db->where('username', ee()->TMPL->fetch_param('username'));
                $q = ee()->db->get('members');
                if ($q->num_rows()==0)
                {
                    if (ee()->TMPL->fetch_param('errors')=='off' || ee()->TMPL->fetch_param('errors')=='no')
                    {
                        return;
                    }
                    return ee()->output->show_user_error('general', array(lang('member_does_not_exist')));
                }
                $member_id_a = array($q->row('member_id'));
            }
            else if (ee()->session->userdata('member_id')!=0)
            {
                $member_id_a = array(ee()->session->userdata('member_id'));
            }
            else
            {
                if (ee()->TMPL->fetch_param('errors')=='off' || ee()->TMPL->fetch_param('errors')=='no')
                {
                    return;
                }
                return ee()->output->show_user_error('general', array(lang('no_member_provided')));
            }
        }
        else
        {
            $member_id_a = explode("|", $member_id);
        }
        
        if (ee()->TMPL->fetch_param('custom_fields')=='yes')
        {
            $custom_fields = array();
            ee()->db->select('field_id, field_name');
            $q = ee()->db->get('category_fields');
            foreach ($q->result() as $obj)
            {
                $custom_fields[$obj->field_id] = $obj->field_name;
            }
        }
        
        $what = 'categories.cat_id, categories.cat_name, categories.cat_url_title, categories.cat_image, categories.cat_description, categories.parent_id, categories.group_id';
        if (ee()->TMPL->fetch_param('custom_fields')=='yes') 
        {
            $what .= ', category_field_data.*';
        }
        ee()->db->select($what);
        ee()->db->from('category_members');
        ee()->db->join('categories', 'category_members.cat_id=categories.cat_id', 'right');
        if (ee()->TMPL->fetch_param('custom_fields')=='yes') 
        {
            ee()->db->join('category_field_data', 'categories.cat_id=category_field_data.cat_id', 'left');
        }
        ee()->db->where_in('member_id', $member_id_a);
        if (ee()->TMPL->fetch_param('category_group')!='') 
        {
            $category_group_a = explode('|', ee()->TMPL->fetch_param('category_group'));
			ee()->db->where_in('categories.group_id', $category_group_a);
        }
        $sort = (ee()->TMPL->fetch_param('sort')=='desc')?'desc':'asc';
        $order = (ee()->TMPL->fetch_param('order_by')=='order')?'cat_order':'cat_name';
        if (ee()->TMPL->fetch_param('sort_by_tree')!='no') 
        {
            ee()->db->order_by('categories.group_id, categories.parent_id, categories.'.$order, $sort);
        }
        else
        {
            ee()->db->order_by('categories.'.$order, $sort);
        }
        $q = ee()->db->get();
        if ($q->num_rows()==0)
        {
            return ee()->TMPL->no_results();
        }
        
        $out = '';
        $data = array();
        $i = 0;
        
        foreach ($q->result_array() as $row)
        {
            $i++;
            $row['category_id'] = $row['cat_id'];
            $row['category_name'] = $row['cat_name'];
            $row['category_url_title'] = $row['cat_url_title'];
            $row['category_image'] = $row['cat_image'];
            $row['category_description'] = $row['cat_description'];
            $row['parent_id'] = $row['parent_id'];    
			$row['category_group'] = $row['group_id'];            
            
            if (ee()->TMPL->fetch_param('custom_fields')=='yes')
            {
                foreach ($custom_fields as $field_id=>$field_name)
                {
                    $row[$field_name] = $row['m_field_id_'.$field_id];
                }
            } 
            
            $data[] = $row;
        }

		$out = ee()->TMPL->parse_variables(ee()->TMPL->tagdata, array_values($data));
        
        return $out;
    }
    
    function check($member_id='', $category_id='')
    {

        if ($member_id=='')
        {
            if (ee()->TMPL->fetch_param('member_id')!='')
            {
                if (ee()->TMPL->fetch_param('member_id')=='{member_id}' || ee()->TMPL->fetch_param('member_id')=='{logged_in_member_id}')
                {
                    $member_id = ee()->session->userdata('member_id');
                }
                else
                {
                    $member_id = ee()->TMPL->fetch_param('member_id');
                }
            }
            else if (ee()->TMPL->fetch_param('username')!='')
            {
                ee()->db->select('member_id');
                ee()->db->where('username', ee()->TMPL->fetch_param('username'));
                $q = ee()->db->get('members');
                if ($q->num_rows()==0)
                {
                    if (ee()->TMPL->fetch_param('errors')=='off' || ee()->TMPL->fetch_param('errors')=='no')
                    {
                        return;
                    }
                    return ee()->output->show_user_error('general', array(lang('member_does_not_exist')));
                }
                $member_id = $q->row('member_id');
            }
            else if (ee()->session->userdata('member_id')!=0)
            {
                $member_id = ee()->session->userdata('member_id');
            }
            else
            {
                if (ee()->TMPL->fetch_param('errors')=='off' || ee()->TMPL->fetch_param('errors')=='no')
                {
                    return;
                }
                return ee()->output->show_user_error('general', array(lang('no_member_provided')));
            }
        }
        
        if ($category_id=='')
        {
            if (ee()->TMPL->fetch_param('category_id')!='')
            {
                $category_id = ee()->TMPL->fetch_param('category_id');
            }
            else if (ee()->TMPL->fetch_param('category_url_title')!='')
            {
                ee()->db->select('cat_id');
                if (strpos(ee()->TMPL->fetch_param('category_url_title'), '|')===FALSE)
		        {
		            ee()->db->where('cat_url_title', ee()->TMPL->fetch_param('category_url_title'));
		        }
                else
                {
                	$category_url_title_a = explode("|", ee()->TMPL->fetch_param('category_url_title'));
					ee()->db->where_in('cat_url_title', $category_url_title_a);
                }
                $q = ee()->db->get('categories');
                if ($q->num_rows()==0)
                {
                    if (ee()->TMPL->fetch_param('errors')=='off' || ee()->TMPL->fetch_param('errors')=='no')
                    {
                        return;
                    }
                    return ee()->output->show_user_error('general', array(lang('category_does_not_exist')));
                }
                else if ($q->num_rows()==1)
                {
                	$category_id = $q->row('cat_id');
               	}
               	else
               	{
               		$category_id_a = array();
		   			foreach ($q->result_array() as $row)
			        {
			            $category_id_a[] = $row['cat_id'];
			        }
               	}
            }
            else
            {
                if (ee()->TMPL->fetch_param('errors')=='off' || ee()->TMPL->fetch_param('errors')=='no')
                {
                    return;
                }
                return ee()->output->show_user_error('general', array(lang('no_category_provided')));
            }
        }
        
        ee()->db->select('*');
        ee()->db->from('category_members');
        if (strpos($category_id, '|')===FALSE && !isset($category_id_a))
        {
            ee()->db->where('cat_id', $category_id);
        }
        else
        {
            if (!isset($category_id_a))
            {
				$category_id_a = explode("|", $category_id);
			}
            ee()->db->where_in('cat_id', $category_id_a);
        }
        ee()->db->where('member_id', $member_id);
        $q = ee()->db->get();
        if ($q->num_rows()==0)
        {
            return ee()->TMPL->no_results();
        }
        return ee()->TMPL->tagdata;
    }
    
    
	
	function form()
    {
    	if (ee()->session->userdata('member_id')==0)
        {
        	return ee()->TMPL->no_results();
        }
        
        $categories_checkboxes = '';

        ee()->load->helper('form');
        
        ee()->db->select('cat_id');
        ee()->db->from('category_members');
        ee()->db->where('member_id', ee()->session->userdata('member_id'));
        $cat_query = ee()->db->get();
        $selected = array();
        foreach ($cat_query->result_array() as $row)
        {
            $selected[] = $row['cat_id'];
        }
        
        $CategoryGroupColl = ee('Model')
            ->get('CategoryGroup')
			->filter('group_id', 'IN', $this->settings[ee()->config->item('site_id')]['category_groups'])
			->all();
        
        // Get the category tree with a single query
        ee()->load->library('datastructures/tree');
        $view_vars = array(
            'selected'  => $selected
        );

        foreach ($CategoryGroupColl as $CategoryGroup)
        {
            $view_vars['categories_set'][] = $CategoryGroup->getCategoryTree(ee()->tree);
        }
        
        $categories_checkboxes = ee('View')->make('member_categories:frontend/categories')->render($view_vars);            

        $tagdata = ee()->TMPL->tagdata;        
        $tagdata = ee()->TMPL->swap_var_single('categories', $categories_checkboxes, $tagdata);
        

        if (ee()->TMPL->fetch_param('return')=='')
        {
            $return = ee()->functions->fetch_site_index();
        }
        else if (ee()->TMPL->fetch_param('return')=='SAME_PAGE')
        {
            $return = ee()->functions->fetch_current_uri();
        }
        else if (strpos(ee()->TMPL->fetch_param('return'), "http://")!==FALSE || strpos(ee()->TMPL->fetch_param('return'), "https://")!==FALSE)
        {
            $return = ee()->TMPL->fetch_param('return');
        }
        else
        {
            $return = ee()->functions->create_url(ee()->TMPL->fetch_param('return'));
        }
        
        $data['hidden_fields'] = array(
										'ACT'	=> ee()->functions->fetch_action_id('Member_categories', 'categories_save'),
										'RET'	=> $return
									  );            
        $data['id']		= (ee()->TMPL->fetch_param('id')!='') ? ee()->TMPL->fetch_param('id') : 'member_categories_form';
        $data['name']		= (ee()->TMPL->fetch_param('name')!='') ? ee()->TMPL->fetch_param('name') : 'member_categories_form';
        $data['class']		= (ee()->TMPL->fetch_param('class')!='') ? ee()->TMPL->fetch_param('class') : 'member_categories_form';

        $out = ee()->functions->form_declaration($data).$tagdata."\n"."</form>";
        
        return $out;
	
    }
    
    function categories_save()
    {
        ee()->lang->loadfile('member');
        
		if (ee()->session->userdata('member_id')==0)
        {
            return ee()->output->show_user_error('submission', lang('log_in'));		
        }
        
        //get all categories for this member
        ee()->db->select('category_members.cat_id');
        ee()->db->from('category_members');
        ee()->db->join('categories', 'category_members.cat_id=categories.cat_id', 'left');
        ee()->db->where('member_id', ee()->session->userdata('member_id'));
        ee()->db->where('site_id', ee()->config->item('site_id'));
        $query = ee()->db->get();
        $delete = array();
        foreach ($query->result() as $obj)
        {
            //mark records for deleting
            $delete[] = $obj->cat_id;
        }
        
        if (!empty($delete))
        {
            ee()->db->where('member_id', ee()->session->userdata('member_id'));
            ee()->db->where_in('cat_id', $delete);
            ee()->db->delete('category_members');
        }
        
        $data = array();
        
        if (!empty($_POST['categories']))
        {

            foreach ($_POST['categories'] as $i=>$category)
            {
                $category = ee('Security/XSS')->clean($category);
                $data[] = array(
                            'cat_id' => $category,
                            'member_id' => ee()->session->userdata('member_id')
                        );
                
                if ($this->settings[ee()->config->item('site_id')]['assign_parent'] == 'y')
                {
                    do 
                    {
                        ee()->db->select('parent_id');
                        ee()->db->from('categories');
                        ee()->db->where('cat_id', $category);
                        $q = ee()->db->get();
                        if ($q->num_rows()==0)
                        {
                            continue;
                        }
                        $category = $q->row('parent_id');
                        if ($category!=0)
                        {
                            $data[] = array(
                                'cat_id' => $category,
                                'member_id' => ee()->session->userdata('member_id')
                            );
                        }
                    }
                    while ($category!=0);
                }
    
            }
    
            $data = array_intersect_key($data, array_unique(array_map('serialize', $data)));
            
            ee()->db->insert_batch('category_members', $data);
            
        }
        
        $site_name = (ee()->config->item('site_name') == '') ? lang('back') : stripslashes(ee()->config->item('site_name'));
        $return = ($_POST['RET']!='')?$_POST['RET']:ee()->config->item('site_url');
		
        $data = array(	'title' 	=> lang('profile_updated'),
        				'heading'	=> lang('profile_updated'),
        				'content'	=> lang('mbr_profile_has_been_updated'),
        				'redirect'	=> $return,
        				'link'		=> array($return, $site_name),
                        'rate'		=> 5
        			 );
			
		ee()->output->show_message($data);
    }    
        

}
/* END */

?>