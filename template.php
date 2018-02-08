<?php
// $Id: template.php,v 1.21 2009/08/12 04:25:15 johnalbin Exp $
include_once 'clickrule.inc';

/**
 * Implementation of HOOK_theme().
 */
function andrew_harper_theme(&$existing, $type, $theme, $path) {
	$hooks = zen_theme($existing, $type, $theme, $path);
	/** 
	 * Add your theme hooks like this:
	 *
	 * $hooks['hook_name_here'] = array( // Details go here );
	 */
	return $hooks;
}

/**
 * Override or insert variables into the page templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("page" in this case.)
 */
function andrew_harper_preprocess_page(&$vars, $hook) {
 	global $user;
	if(arg(0) == 'user' && (!$user->uid)){
		drupal_goto('login');
	}
	
	static $once = 1;
 	if($once) {
		if(arg(0) == 'node' && is_numeric(arg(1))){
			$node = node_load(arg(1));
			if($node->type == 'hideaway_report'){
				menu_set_active_item('current-hideaway-report');
			}
		}	
 		$pmenu = menu_tree_page_data('primary-links');
 		$pmenu = andrew_harper_menu_tree_output($pmenu);
 		$vars['primary_links'] = $pmenu;
 		$once = 0;
 	}

 	//Test for the worldwide map nodes & template file change
 	if(arg(1) == '9010' && $_GET['map']){
 		$vars['template_files'][] = 'map';
 		$vars['scripts'] = str_replace('<script type="text/javascript" defer="defer" src="/harper/sites/all/modules/admin_menu/admin_menu.js?v"></script>', '', $vars['scripts']);
 	}	
}
function andrew_harper_preprocess_comment(&$variables) {
	global $user;
	$comment = $variables['comment'];
	$node = $variables['node'];
	$author = andrew_harper_fetch_comment_fullname($comment->uid);
	$variables['author']    = ucwords($author['name']);
	$variables['content']   = $comment->comment;
	$variables['date']      = format_date($comment->timestamp);
	if(!$user->uid){
		$variables['links']   = andrew_harper_comment_post_forbidden($node);
	} 
	else{
		$variables['links']   = isset($variables['links']) ? theme('links', $variables['links']) : '';
	}
	$variables['new']       = $comment->new ? t('new') : '';
	$variables['picture']   = theme_get_setting('toggle_comment_user_picture') ? theme('user_picture', $comment) : '';
	$variables['signature'] = $comment->signature;
	$variables['submitted'] = theme('comment_submitted', $comment);
	$variables['title']     = l($comment->subject, $_GET['q'], array('fragment' => "comment-$comment->cid"));
	$variables['template_files'][] = 'comment-'. $node->type;
	// set status to a string representation of comment->status.
	if (isset($comment->preview)) {
		$variables['status']  = 'comment-preview';
	}
	else {
		$variables['status']  = ($comment->status == COMMENT_NOT_PUBLISHED) ? 'comment-unpublished' : 'comment-published';
	}
}

function andrew_harper_comment_submitted($comment) {
  return t('@date | @time',
    array(
      '@date' => format_date($comment->timestamp, 'custom', 'F j, Y'),
      '@time' => format_date($comment->timestamp, 'custom', 'G:i:s')
    ));
}

// ================
// rbanh function
// ================
function andrew_harper_fetch_comment_fullname($uid)
{
    $sql_in = "
        select ifNull(PV.value, 'Andrew Harper Member') as name
        from users U
            left join profile_values PV
             on U.uid = PV.uid
              and PV.fid = 5
        where U.uid = '%s'
			";
    return db_fetch_array(db_query($sql_in, $uid));
}

function andrew_harper_form_element($element, $value) {
	// This is also used in the installer, pre-database setup.
	$t = get_t();

	$output = '<div class="form-item"';
	if (!empty($element['#id'])) {
		$output .= ' id="' . $element['#id'] . '-wrapper"';
	}
	$output .= ">\n";
	$required = !empty($element['#required']) ? '<span class="form-required" title="' . $t('This field is required.') . '">*</span>' : '';

	if (!empty($element['#title'])) {
		$title = $element['#title'];
		if (!empty($element['#id']) && !empty($element['#labelid'])) {
			$output .= ' <label for="' . $element['#id'] . '" id="'. $element['#labelid'].'">' . $t('!title !required', array('!title' => filter_xss_admin($title), '!required' => $required)) . "</label>\n";
		}
		else if (!empty($element['#id'])) {
			$output .= ' <label for="' . $element['#id'] . '">' . $t('!title: !required', array('!title' => filter_xss_admin($title), '!required' => $required)) . "</label>\n";
		}
		else {
			$output .= ' <label>' . $t('!title: !required', array('!title' => filter_xss_admin($title), '!required' => $required)) . "</label>\n";
		}
	}
	$output .= " $value\n";

	if (!empty($element['#description'])) {
		$output .= ' <div class="description">' . $element['#description'] . "</div>\n";
	}
	$output .= "</div>\n";

	return $output;
}
function andrew_harper_views_slideshow_singleframe_control_previous($vss_id, $view, $options) {
	return l(t(''), '#', array(
		'attributes' => array(
		  'class' => 'views_slideshow_singleframe_previous views_slideshow_previous',
		  'id' => "views_slideshow_singleframe_prev_" . $vss_id,
		),
		'fragment' => ' ',
		'external' => TRUE,
		)
	);
}
function andrew_harper_views_slideshow_singleframe_control_next($vss_id, $view, $options) {
	return l(t(''), '#', array(
		'attributes' => array(
		  'class' => 'views_slideshow_singleframe_next views_slideshow_next',
		  'id' => "views_slideshow_singleframe_next_" . $vss_id,
		),
		'fragment' => ' ',
		'external' => TRUE,
		)
	);
}

function andrew_harper_menu_item_link($link){
	if($link['title'] == 'Welcome' && $link['menu_name'] == 'menu-utility-links'){
		global $user;
		
		// There is room for 34 characters in the message
		// "Welcome," is 9 so that leaves 25 for the name
		$link['title'] = 'Welcome, '.$user->member_data->FirstName.' '.$user->member_data->LastName;
		if(strlen($link['title'] > 34)){
			$link['title'] = 'Welcome, '.$user->member_data->FirstName;
		}
		$link['options']['attributes']['id'] = 'welcome-message';
		$link['localized_options']['attributes']['id'] = 'welcome-message';
	}
	
	if($link['title'] == 'Help' && $link['menu_name'] == 'menu-utility-links'){
		$link['options']['attributes']['id'] = 'help-menu-item';
		$link['localized_options']['attributes']['id'] = 'help-menu-item';
	}
	
	if($link['title'] == 'Map' && $link['menu_name'] == 'menu-utility-links'){
		$link['options']['attributes']['id'] = 'map-menu-item';
		$link['localized_options']['attributes']['id'] = 'map-menu-item';
	}
	
	if($link['title'] == 'Hideaway Report'){		
		$sql = "select max(nid) as 'nid' from node where type = 'hideaway_report' and status = 1;";
		$nid = db_fetch_object(db_query($sql));
		$hr = node_load($nid->nid);
		$link['href'] = 'node/'.$hr->nid;	
	}
	
	return theme_menu_item_link($link);
}

function andrew_harper_menu_tree_output($tree) {
	$output = '';
	$items = array();

	// Pull out just the menu items we are going to render so that we
	// get an accurate count for the first/last classes.
	foreach ($tree as $data) {
		if (!$data['link']['hidden']) {
			$items[] = $data;
		}
	}
	
	$num_items = count($items);
	foreach ($items as $i => $data) {
		if($data['link']['depth'] < 3){
			$extra_class = array();
			if ($i == 0) {
				$extra_class[] = 'first';
			}
			if ($i == $num_items - 1) {
				$extra_class[] = 'last';
			}
			$extra_class = implode(' ', $extra_class);
			$link = theme('menu_item_link', $data['link']);
			if ($data['below']) {
				$output .= theme('menu_item', $link, $data['link']['has_children'], andrew_harper_menu_tree_output($data['below']), $data['link']['in_active_trail'], $extra_class);
			}
			else {
				$output .= theme('menu_item', $link, $data['link']['has_children'], '', $data['link']['in_active_trail'], $extra_class);
			}
		}
	}
	return $output ? theme('menu_tree', $output) : '';
}

/**
 * Display the simple view of rows one after another
 */
function andrew_harper_preprocess_views_view_unformatted(&$vars) {
	$view     = $vars['view'];
	$rows     = $vars['rows'];
	$vars['classes'] = array();
	
	// Set up striping values.
	foreach ($rows as $id => $row) {
		$row_classes = array();
		if($view->name == 'hideaway_report' && $view->current_display == 'panel_pane_2'  ){
			if($view->result[$id]->node_data_field_hideaway_report_field_featured_harper_report_value == 'No' && !$user->uid){
				$row_classes[] = 'locked';
			}
		}
		elseif($view->name == 'hideaway_report' && $view->current_display == 'panel_pane_3' && !user->uid){
			$row_classes[] = 'locked';
		}
		$row_classes[] = 'views-row';
		$row_classes[] = 'views-row-' . ($id + 1);
		$row_classes[] = 'views-row-' . ($id % 2 ? 'even' : 'odd');
		if ($id == 0) {
			$row_classes[] = 'views-row-first';
		}
		if ($id == count($rows) -1) {
			$row_classes[] = 'views-row-last';
		}
		
		// Flatten the classes to a string for each row for the template file.
		$vars['classes'][$id] = implode(' ', $row_classes);
	}
}

function andrew_harper_nodereference_formatter_default($element) {
	$output = '';
	if (!empty($element['#item']['safe']['nid'])) { 
		$output = l('', 'node/'. $element['#item']['safe']['nid'], array('fragment' => "mini-panel-hotels-tabs-middle-tab-4"));		
	}
	return $output;
}

function andrew_harper_comment_post_forbidden($node) {
	global $user;
	static $authenticated_post_comments;

	if (!$user->uid) {
		// We cannot use drupal_get_destination() because these links
		// sometimes appear on /node and taxonomy listing pages.
		$destination = 'destination='. rawurlencode("node/$node->nid#comment-form");
		return t('<a href="@login">Login to Share Your Experience</a>', array('@login' => url('user', array('query' => $destination))));
	}
 }
 
function andrew_harper_text_formatter_default($element){
	return $element['#item']['value'];
}

/**
 * Fixes Bug with villa tabs bug of displaying empty tabs*/
 */
function andrew_harper_panels_tabs_style_render_region($display, $owner_id, $panes, $settings) {  
	$output = '';

	// Generate a unique id based on the CSS ID and the name of the panel in the layout.
	$id = '';

	//If CSS ID is set.
	if($display->css_id) {
		$id = "$display->css_id-";
	}
	$pane_id = reset(array_keys($panes));
	$id .= $display->content[$pane_id]->panel;

	// Add the Javascript to the page, and save the settings for this panel.
	_panels_tabs_add_js($id, $settings['filling_tabs']);

	$tabs = array();
	$tabs[$id] = array(
		'#type' => 'tabset',
		'#tabset_name' => $id,
	);
	$index = 0;
	foreach ($panes as $pane_id => $pane_content) {
		if (!empty($pane_content) && strlen($pane_content) > 300) {
			$tabs[$id][$pane_id] = array(
				'#type'    => 'tabpage',
				'#title'   => $display->content[$pane_id]->tab_title,
				'#content' => $pane_content,
				'#weight'  => $index,
				'#attributes' => array('class' => 'tab-' . str_replace(' ', '-',$display->content[$pane_id]->tab_title)),
			);
			$index++;
		}
	}

	// No content has been rendered
	if (empty($index)) {
		return;
	}

	// See if an optional title was added.
	if (!empty($settings['title'])) {
		$output .= theme('panels_tabs_title', $settings['title']);
	}
	$output .= tabs_render($tabs);
	
	return $output;
}
 
 /**
 * Format a query pager.
 *
 * Menu callbacks that display paged query results should call theme('pager') to
 * retrieve a pager control so that users can view other results.
 * Format a list of nearby pages with additional query results.
 *
 * @param $tags
 *   An array of labels for the controls in the pager.
 * @param $limit
 *   The number of query results to display per page.
 * @param $element
 *   An optional integer to distinguish between multiple pagers on one page.
 * @param $parameters
 *   An associative array of query string parameters to append to the pager links.
 * @param $quantity
 *   The number of pages in the list.
 * @return
 *   An HTML string that generates the query pager.
 *
 * @ingroup themeable
 */
function andrew_harper_pager($tags = array(), $limit = 10, $element = 0, $parameters = array(), $quantity = 9) {
	global $pager_page_array, $pager_total;

	// Calculate various markers within this pager piece:
	// Middle is used to "center" pages around the current page.
	$pager_middle = ceil($quantity / 2);

	// current is the page we are currently paged to
	$pager_current = $pager_page_array[$element] + 1;

	// first is the first page listed by this pager piece (re quantity)
	$pager_first = $pager_current - $pager_middle + 1;

	// last is the last page listed by this pager piece (re quantity)
	$pager_last = $pager_current + $quantity - $pager_middle;

	// max is the maximum page number
	$pager_max = $pager_total[$element];
	// End of marker calculations.

	// Prepare for generation loop.
	$i = $pager_first;
	if ($pager_last > $pager_max) {	
		// Adjust "center" if at end of query.
		$i = $i + ($pager_max - $pager_last);
		$pager_last = $pager_max;
	}
	if ($i <= 0) {	
		// Adjust "center" if at start of query.
		$pager_last = $pager_last + (1 - $i);
		$i = 1;
	}
	// End of generation loop preparation.

	$li_previous = theme('pager_previous', (isset($tags[1]) ? $tags[1] : t('‹‹ Previous')), $limit, $element, 1, $parameters);
	$li_next = theme('pager_next', (isset($tags[3]) ? $tags[3] : t('Next ››')), $limit, $element, 1, $parameters);
	if ($pager_total[$element] > 1) {
		if ($li_first) {
			$items[] = array(
				'class' => 'pager-first',
				'data' => $li_first,
			);
		}
		if ($li_previous) {
			$items[] = array(
				'class' => 'pager-previous',
				'data' => $li_previous,
			);
		}

		// When there is more than one page, create the pager list.
		if ($i != $pager_max) {
			if ($i > 1) {
				$items[] = array(
					'class' => 'pager-ellipsis',
					'data' => '&#8230;',
				);
			}
		
			// Now generate the actual pager piece.
			for (; $i <= $pager_last && $i <= $pager_max; $i++) {
				if ($i < $pager_current) {
					$items[] = array(
						'class' => 'pager-item',
						'data' => theme('pager_previous', $i, $limit, $element, ($pager_current - $i), $parameters),
					);
				}
				if ($i == $pager_current) {
					$items[] = array(
						'class' => 'pager-current',
						'data' => $i,
					);
				}
				if ($i > $pager_current) {
					$items[] = array(
						'class' => 'pager-item',
						'data' => theme('pager_next', $i, $limit, $element, ($i - $pager_current), $parameters),
					);
				}
			}
			if ($i < $pager_max) {
				$items[] = array(
					'class' => 'pager-ellipsis',
					'data' => '&#8230;',
				);
			}
		}
		
		// End generation.
		if ($li_next) {
			$items[] = array(
				'class' => 'pager-next',
				'data' => $li_next,
			);
		}
		if ($li_last) {
			$items[] = array(
				'class' => 'pager-last',
				'data' => $li_last,
			);
		}

		return theme('item_list', $items, NULL, 'ul', array('class' => 'pager'));
	}
}

/**
 * This is a copy of the zen_breadcrumb() function from the zen template.
 * The only difference is that it filters the blank elements from the array. For some reason, the forum
 * topic breadcrumbs have one blank element at the end.
 */
function andrew_harper_breadcrumb($breadcrumb) {	
	// Filter blank entries from breadcrumbs
	$new_bc = array();
	foreach($breadcrumb as $bc)
	{
		if(!empty($bc)){
			$new_bc[] = $bc;
		}
	}
	$breadcrumb = $new_bc;
	
	// Determine if we are to display the breadcrumb.
	$show_breadcrumb = theme_get_setting('zen_breadcrumb');
	if ($show_breadcrumb == 'yes' || $show_breadcrumb == 'admin' && arg(0) == 'admin') {	
		// Optionally get rid of the homepage link.
		$show_breadcrumb_home = theme_get_setting('zen_breadcrumb_home');
		if (!$show_breadcrumb_home) {
			array_shift($breadcrumb);
		}

		// Return the breadcrumb with separators.
		if (!empty($breadcrumb)) {
			$breadcrumb_separator = theme_get_setting('zen_breadcrumb_separator');
			$trailing_separator = $title = '';
			if (theme_get_setting('zen_breadcrumb_title')) {
				if ($title = drupal_get_title()) {
					$trailing_separator = $breadcrumb_separator;
				}
			}
			elseif (theme_get_setting('zen_breadcrumb_trailing')) {
				$trailing_separator = $breadcrumb_separator;
			}
			return '<div class="breadcrumb">' . implode($breadcrumb_separator, $breadcrumb) . "$trailing_separator$title</div>";
		}
	}

	// Otherwise, return an empty string.
	return '';
}

/**
 * Retrieve the display name for the last poster in the forum listing
 */
function andrew_harper_preprocess_forum_submitted(&$variables) {
	$arr = andrew_harper_fetch_comment_fullname($variables['topic']->uid);
	$variables['author'] = $arr['name'];        
}
