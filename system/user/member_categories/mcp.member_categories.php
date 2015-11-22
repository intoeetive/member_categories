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

use EllisLab\ExpressionEngine\Library\CP\Table;

if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}

require_once PATH_THIRD.'member_categories/config.php';

class Member_categories_mcp {

    var $version = MEMBER_CATEGORIES_ADDON_VERSION;
    
    var $menu = array();
    
    var $settings = array();
    
    function __construct() 
    { 
        ee()->lang->loadfile('members');  
        ee()->lang->loadfile('member_categories');  
        
        $settings_q = ee()->db->select('settings')->from('modules')->where('module_name', 'Member_categories')->limit(1)->get(); 
        $this->settings = unserialize(base64_decode($settings_q->row('settings')));
        
        $sidebar = ee('CP/Sidebar')->make();
        $this->menu['members'] = $sidebar->addHeader(lang('members'), ee('CP/URL', 'addons/settings/member_categories/members'));
        $this->menu['settings'] = $sidebar->addHeader(lang('settings'), ee('CP/URL', 'addons/settings/member_categories/settings'));
        
        ee()->view->header = array(
			'title' => lang('member_categories_module_name'),
			//'form_url' => ee('CP/URL', 'addons/settings/member_categories/members'),
			//'search_button_value' => lang('btn_search_files')
		);   
    } 
    
    public function index()
    {
        return $this->members();
    }

    function members()
    {
        $this->menu['members']->isActive();

        $fiter_url = ee('CP/URL', 'addons/settings/member_categories/members');
		
    	$vars = array();

        $member_groups = ee('Model')
            ->get('MemberGroup')
            ->fields('group_id', 'group_title')
            ->all()
            ->getDictionary('group_id', 'group_title');
        $member_groups[] = lang('all_groups');
        
        $categories = ee('Model')
            ->get('Category')
            ->fields('cat_id', 'cat_name')
            ->filter('site_id', ee()->config->item('site_id'))
            ->filter('group_id', 'IN', implode(',', $this->settings[ee()->config->item('site_id')]['category_groups']))
            ->order('cat_order', 'asc')
            ->all()
            ->getDictionary('cat_id', 'cat_name');
        $categories[''] = lang('all_categories');
        
        
        $filters = ee('CP/Filter');
        $groupFilter = ee('CP/Filter')->make('group_id', lang('member_group'), $member_groups);
        $catFilter = ee('CP/Filter')->make('cat_id', lang('category'), $categories);
        $filters->add($groupFilter);
		$filters->add('Username')->withName('member_id');
        $filters->add($catFilter);
        $filter_values = $filters->values();
        
        if ($filter_values['group_id']!='')
        {
            ee()->db->where('members.group_id', $filter_values['group_id']);
        }
        if ($filter_values['cat_id']!='')
        {
            ee()->db->where('category_members.cat_id', $filter_values['cat_id']);
        }
        if ($filter_values['member_id']!='')
        {
            $where = '( exp_members.member_id < 0 ';
            foreach ($filter_values['member_id'] as $member_id)
            {
                $where .= ' OR exp_members.member_id='.$member_id;
            }
            $where .= ' )';
            ee()->db->where($where);
        }
        ee()->db->join('category_members', 'members.member_id=category_members.member_id', 'left');
        $total = ee()->db->count_all_results('members');
        
        $filters->add('Perpage', $total);
        $filter_values = $filters->values();
        
        $vars['filters'] = $filters->render($fiter_url);
        
        $fiter_url->addQueryStringVariables($filter_values);   
        
        $page = ((int) ee()->input->get('page')) ?: 1;
		$offset = ($page - 1) * $filter_values['perpage'];        
        $sort_col = ee()->input->get_post('sort_col') ? ee()->input->get_post('sort_col') : 'member_id';
        $sort_dir = ee()->input->get_post('sort_dir') ? ee()->input->get_post('sort_dir') : 'desc';
        
        ee()->db->distinct();
        ee()->db->select('members.member_id, username, email, group_id');
        ee()->db->from('members');
        if ($filter_values['group_id']!='')
        {
            ee()->db->where('members.group_id', $filter_values['group_id']);
        }
        if ($filter_values['member_id']!='')
        {
            $where = '( exp_members.member_id < 0 ';
            foreach ($filter_values['member_id'] as $member_id)
            {
                $where .= ' OR exp_members.member_id='.$member_id;
            }
            $where .= ' )';
            ee()->db->where($where);
        }
        if ($filter_values['cat_id']!='')
        {
            ee()->db->where('category_members.cat_id', $filter_values['cat_id']);
            ee()->db->join('category_members', 'category_members.member_id=members.member_id', 'left');
        }
        ee()->db->order_by($sort_col, $sort_dir);
        ee()->db->limit($filter_values['perpage'], $offset);

        $query = ee()->db->get();
        
        $table = ee('CP/Table', array('sort_col'=>$sort_col, 'sort_dir'=>$sort_dir));
        
        $table->setColumns(
          array(
            'member_id',
            'username'  => array(
                'encode'    => FALSE
            ),
            'group_id',
            'categories'  => array(
                'sort'  => FALSE,
                'encode'=> FALSE
            ),
            'manage'  => array(
              'type'  => Table::COL_TOOLBAR
            ),
            array(
              'type'  => Table::COL_CHECKBOX
            )
          )
        );
        
        $data = array();
        $i = 0;
        foreach ($query->result_array() as $row)
        {
            $data[$i]['member_id'] = $row['member_id'];
            if (ee()->cp->allowed_group('can_edit_members'))
            {
            	$data[$i]['username'] = "<a href = '" . ee('CP/URL')->make('members/profile/', array('id' => $row['member_id'])) . "'>". $row['username']."</a>";
            }
            else
            {
            	$data[$i]['username'] = $row['username'];
            }
            $data[$i]['username'] .= '<br><span class="meta-info">&mdash; <a href="' . ee('CP/URL')->make('utilities/communicate/member/' . $row['member_id']) . '">'.$row['email'].'</a></span>';
            $data[$i]['group_id'] = $member_groups[$row['group_id']];
            $data[$i]['categories'] = '';
            ee()->db->select('cat_name');
            ee()->db->from('category_members');
            ee()->db->join('categories', 'category_members.cat_id=categories.cat_id', 'left');
            ee()->db->where('member_id', $row['member_id']);
            $q = ee()->db->get();
            if ($q->num_rows()==0)
            {
                $data[$i]['categories'] = '-';
            }
            else
            {
                foreach ($q->result_array() as $cat_row)
                {
                    $data[$i]['categories'] .= $cat_row['cat_name'] . BR;
                }
                $data[$i]['categories'] = trim($data[$i]['categories']);
            }    
            
            
            $data[$i] += array(
            'manage'=> array('toolbar_items' => array(
              'edit' => array(
                'href' => ee('CP/URL')->make('addons/settings/member_categories/edit', array('member_id'  => $row['member_id'])),
                'title' => lang('edit')
              )
            )),
            array(
              'name' => 'member_id[]',
              'value' => $row['member_id']
            )
           );
           $i++;
        }
        
        $table->setData($data);

		$vars['table'] = $table->viewData($fiter_url);
		$vars['filter_url'] = $vars['table']['base_url'];
        $vars['edit_url'] = ee('CP/URL', 'addons/settings/member_categories/edit');;

		$vars['pagination'] = ee('CP/Pagination', (int)$total)
			->perPage($filter_values['perpage'])
			->currentPage($page)
			->render($fiter_url);       
            
        return array(
          'body'       => ee('View')->make('member_categories:members')->render($vars),
          'breadcrumb' => array(
            ee('CP/URL', 'addons/settings/member_categories/members')->compile() => lang('member_categories_module_name')
          ),
          'heading'  => lang('members'),
        );

    }
    
   
    
    

    function edit()
    {
        $this->menu['members']->isActive();
        
        if (ee()->input->post('submit')!==false)
        {
            if (count(ee()->input->post('member_id')==1))
            {
                //get all categories for this member
                ee()->db->select('category_members.cat_id');
                ee()->db->from('category_members');
                ee()->db->join('categories', 'category_members.cat_id=categories.cat_id', 'left');
                ee()->db->where('member_id', ee()->input->post('member_id')[0]);
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
                    ee()->db->where('member_id', ee()->input->post('member_id')[0]);
                    ee()->db->where_in('cat_id', $delete);
                    ee()->db->delete('category_members');
                }
            }
            
            foreach (ee()->input->post('member_id') as $member_id)
            {
            
                $data = array();
                
                if (!empty($_POST['categories']))
                {
        
                    foreach ($_POST['categories'] as $i=>$category)
                    {
                        $category = ee('Security/XSS')->clean($category);
                        $data[] = array(
                                    'cat_id' => $category,
                                    'member_id' => $member_id
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
                                        'member_id' => $member_id
                                    );
                                }
                            }
                            while ($category!=0);
                        }
            
                    }
            
                    $data = array_intersect_key($data, array_unique(array_map('serialize', $data)));
                    
                    //remove duplicates
                    foreach ($data as $arr)
                    {
                        ee()->db->where('cat_id', $arr['cat_id']);
                        ee()->db->where('member_id', $arr['member_id']);
                        ee()->db->delete('category_members');
                    }
                    
                    ee()->db->insert_batch('category_members', $data);
    
                    
                }
            }
            
            ee('CP/Alert')->makeStandard('member_categories')
                          ->asSuccess()
                          ->withTitle(lang('success'))
                          ->addToBody(lang('categories_assigned'))
                          ->defer();
            
            ee()->functions->redirect(ee('CP/URL', 'addons/settings/member_categories/members')->compile());
        }
        
        
        
        if (empty($this->settings[ee()->config->item('site_id')]['category_groups']))
        {
            ee('CP/Alert')->makeStandard('member_categories')
                  ->asWarning()
                  ->withTitle(lang('error'))
                  ->addToBody(lang('provide_settings'))
                  ->defer();
            ee()->functions->redirect(ee('CP/URL', 'addons/settings/member_categories/settings')->compile());
        }
        
        ee()->lang->loadfile('channel');  
        
        $member_ids = array();
        if (ee()->input->get('member_id')!==false)
        {
            $member_ids[] = ee()->input->get('member_id');
        }
        else if (isset($_POST['member_id']) && !empty($_POST['member_id']))
        {
            foreach ($_POST['member_id'] as $member_id)
            {
                $member_ids[] = ee('Security/XSS')->clean($member_id);
            }
        }
        
        if (empty($member_ids))
        {
            ee('CP/Alert')->makeStandard('member_categories')
                  ->asWarning()
                  ->withTitle(lang('error'))
                  ->addToBody(lang('no_member_selected'))
                  ->defer();

            ee()->functions->redirect(ee('CP/URL', 'addons/settings/member_categories/members')->compile());
        }
        
        $selected = array();
        if (count($member_ids)==1)
        {
            ee()->db->select('cat_id');
            ee()->db->from('category_members');
            ee()->db->where('member_id', $member_ids[0]);
            $query = ee()->db->get();
            $delete = array();
            foreach ($query->result_array() as $row)
            {
                $selected[] = $row['cat_id'];
            }
        }
        
        $members = ee('Model')
            ->get('Member')
            ->fields('member_id', 'username', 'screen_name', 'email', 'group_id', 'MemberGroup')
            ->filter('member_id', 'IN', $member_ids)
            ->all();
        
        $vars = array(
            'base_url'      => ee('CP/URL', 'addons/settings/member_categories/edit'),
            'cp_page_title' => lang('assign_to_members'),
            'save_btn_text' => lang('assign'),
            'save_btn_text_working' => lang('btn_saving'),
            'members'   => $members,
            'selected'  => $selected
        );
        
        $CategoryGroupColl = ee('Model')
            ->get('CategoryGroup')
			->filter('group_id', 'IN', $this->settings[ee()->config->item('site_id')]['category_groups'])
			->all();
        
        // Get the category tree with a single query
		ee()->load->library('datastructures/tree');
        foreach ($CategoryGroupColl as $CategoryGroup)
        {
            $vars['categories_set'][] = $CategoryGroup->getCategoryTree(ee()->tree);
        }
        
        return array(
          'body'       => ee('View')->make('member_categories:edit')->render($vars),
          'breadcrumb' => array(
            ee('CP/URL', 'addons/settings/member_categories/members')->compile() => lang('member_categories_module_name')
          ),
          'heading'  => lang('assign'),
        );
        
	
    }        

    
    function settings()
    {
    	$current_site_id = ee()->config->item('site_id');
        
        if (ee()->input->post('assign_parent')!==false)
        {
            $settings = array();
            ee()->db->select('site_id');
            $q = ee()->db->get('sites');
            foreach ($q->result() as $obj)
            {
                if ($obj->site_id != $current_site_id)
                {
                    $settings[$obj->site_id] = $this->settings[$obj->site_id];
                }
                else
                {
                    $settings[$obj->site_id]['assign_parent'] = ee()->input->post('assign_parent');
                    $category_groups = array();
                    foreach ($_POST['category_groups'] as $key=>$val)
                    {
                        $category_groups[ee('Security/XSS')->clean($key)] = ee('Security/XSS')->clean($val);
                    }
                    $settings[$obj->site_id]['category_groups'] = $category_groups;
                }
            }
            
            ee()->db->where('module_name', 'Member_categories');
            ee()->db->update('modules', array('settings' => base64_encode(serialize($settings))));
            
            ee('CP/Alert')->makeStandard('member_categories')
                  ->asSuccess()
                  ->withTitle(lang('success'))
                  ->addToBody(lang('preferences_updated'))
                  ->now();
        }
 
        $category_groups = ee('Model')
            ->get('CategoryGroup')
            ->fields('group_id', 'group_name')
            ->filter('site_id', ee()->config->item('site_id'))
            ->all()
            ->getDictionary('group_id', 'group_name');

        $vars['sections'] = array(
          array(
            array(
              'title' => 'assign_parent',
              'fields' => array(
                'assign_parent' => array(
                  'type'    => 'yes_no',
                  'value'   => $this->settings[$current_site_id]['assign_parent']
                )
              )
            ),
            array(
              'title' => 'category_groups',
              'fields' => array(
                'category_groups' => array(
                  'type'    => 'checkbox',
                  'choices'   => $category_groups,
                  'value'   => $this->settings[$current_site_id]['category_groups']
                )
              )
            )
          )
        );
            
        
    	$vars += array(
          'base_url' => ee('CP/URL', 'addons/settings/member_categories/settings'),
          'cp_page_title' => lang('settings'),
          'save_btn_text' => sprintf(lang('btn_save'), lang('settings')),
          'save_btn_text_working' => lang('btn_saving')
        );
        
        return array(
          'body'       => ee('View')->make('member_categories:settings')->render($vars),
          'breadcrumb' => array(
            ee('CP/URL', 'addons/settings/member_categories/members')->compile() => lang('member_categories_module_name')
          ),
          'heading'  => lang('settings'),
        );
	
    }    

}
/* END */
?>