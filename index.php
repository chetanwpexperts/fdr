<?php
/**
* Plugin Name: Custom Folder Structure
* Plugin URI: #
* Description: Test.
* Version: 0.1
* Author: your-name
* Author URI: #
**/


include_once dirname( __FILE__ ).'/include/install.php';
register_activation_hook( __FILE__, array( 'Installation', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Installation', 'deactivate' ) );

function my_plugin_scripts() {
	wp_enqueue_script('js_bootstrap', plugin_dir_url( __DIR__ ).'fdr/bootstrap/dist/js/bootstrap.min.js',false,'3.3.7',false);
	wp_enqueue_style('css_bootstrap', plugin_dir_url( __DIR__ ).'fdr/bootstrap/dist/css/bootstrap.min.css',true,'3.3.7','all');
}
add_action( 'wp_enqueue_scripts', 'my_plugin_scripts' );

function createNewFolder()
{
	ob_start();
	include "html.php";
	$html = ob_get_clean();
	return $html;
}

add_shortcode("folders", 'createNewFolder');

function getCategoryTreeIDs($catID) 
{
	global $wpdb;
	$q = "SELECT * FROM wp_folders WHERE parent = '".$catID."'";
	$row = $wpdb->get_row( $q );
	$qparent = "SELECT * FROM wp_folders WHERE id = '".$row->parent."'";
	$rowparent = $wpdb->get_row( $qparent );
	$path = array();
	$path['root'] = $rowparent->name;
	if ($row->parent != 0) {
		$path['child'] = ucfirst($row->name);
		$path = array_merge(getCategoryTreeIDs($row->parent), $path);
	}
	
	return $path;
}

function createPath($id, $category_tbl, $except = null) 
{
	$link = "<li class='breadcrumb-item'><a href='javascript:void(0);' class='subfold' data-folderid='0'>Dashboard</a></li>";
	global $wpdb;	
    $s = "SELECT * FROM wp_folders WHERE parent = $id";
	$row = $wpdb->get_row( $s );
	$qparent = "SELECT * FROM wp_folders WHERE id = '".$row->parent."'";
	$rowparent = $wpdb->get_row( $qparent );
    if($rowparent->parent == 0) 
	{
        $name = $row->name;  

        $link .=" <li class='breadcrumb-item'><a href='javascript:void(0);' class='subfold' data-folderid='".$rowparent->id."'>".$rowparent->name."</a></li>";
    }else{

		$inqparent = "SELECT * FROM wp_folders WHERE id = '".$rowparent->parent."'";
		$inrowparent = $wpdb->get_row( $inqparent );
		$name = $row->name;  
        $link .=" <li class='breadcrumb-item'><a href='javascript:void(0);' class='subfold' data-folderid='".$inrowparent->id."'>".$inrowparent->name."</a> </li> <li class='breadcrumb-item'> <a href='javascript:void(0);' class='subfold' data-folderid='".$rowparent->id."'>".$rowparent->name."</a> </li>";
	}
	return $link;
}

function showCatBreadCrumb($catID) 
{
	echo '<nav aria-label="breadcrumb"> <ol class="breadcrumb">' . createPath($catID, "wp_folders", $except = null) . '</ol></nav>';
}

add_action("wp_ajax_newfolder", "addnewfolder");
add_action("wp_ajax_nopriv_newfolder", "addnewfolder");

function addnewfolder() 
{
	$uid = "";
	global $wpdb;
	$table = $wpdb->prefix.'folders';
	if ( is_user_logged_in() ) 
	{
		$uid = get_current_user_id();
	} else {
		$uid = 0;
	}
	$data = array('uid' => $uid , 'number' => $_POST['number'], 'name' => $_POST['name'], 'parent' => $_POST['parent']);
	$format = array('%d','%s','%s','%s');
	$wpdb->insert($table,$data,$format);
	$my_id = $wpdb->insert_id;
	if($my_id != "" || $my_id != 0)
	{
		$result['type'] = "success";
		$result['id'] = $my_id;
	}else{
		$result['type'] = "error";
		$result['id'] = 0;
	}
	$result = json_encode($result);
    echo $result;
	die();
}

function getallfolders()
{
	global $wpdb;
	$table = $wpdb->prefix.'folders';
	$results = $wpdb->get_results( "SELECT * FROM $table", 'ARRAY_A'); // Query to fetch data from database table and storing in
	
	$folders = "<label>Select Parent Folder</label> <select name='parent' id='isparent'><option value='0'>Select</option>";
		foreach($results as $row)
		{
			$folders .= "<option value='".$row['id']."'>".$row['name']."</option>";
		}
	$folders .= "</select>";
	
	echo $folders;
}

add_shortcode("folderoptions", 'getallfolders');

function getFolders()
{
	$uid = "";
	if ( is_user_logged_in() ) 
	{
		$user = wp_get_current_user();
		$uid = get_current_user_id();
	} else {
		$user = array();
		$uid = 0;
	}
	$user = "";
	global $wpdb;
	$table = $wpdb->prefix.'folders';
	$results = $wpdb->get_results( "SELECT * FROM $table WHERE uid = '".$uid."' AND parent = 0", 'ARRAY_A'); // Query to fetch data from database table and storing in
	
	$folders = "<div id='mainfolders' class='row'>";
		if($uid == 0)
		{
			$folders .= "<div class='col-md-12'>No folders found for this user</div>";
		}else{
			$row_count = 0;
			
			foreach($results as $row)
			{
				$row_count++;
				
				$folders .= "<a href='javascript:void(0);' class='subfold' data-folderid='".$row['id']."'><div class='col-md-3 text-center'><span class='title'>".$row['name']." </span><img src='".plugin_dir_url( __DIR__ )."fdr/foldericon.jpg' style='width: 85%;'></div></a>";
				if ($row_count == 4)
				{
					$folders .= "<p> &nbsp;</p>";
				}
			}
		}
	$folders .= "</div>";
	$folders .= "<div id='subfolderslist'></div>";
	
	echo $folders;
}

add_shortcode("folderlist", 'getFolders');

function getSubFolders()
{
	
	$parent_folder_id = $_POST['folder_id'];
	$uid = "";
	if ( is_user_logged_in() ) {
		$user = wp_get_current_user();
		$uid = get_current_user_id();
	} else {
		$user = array();
		$uid = 0;
	}
	$user = "";
	global $wpdb;
	$table = $wpdb->prefix.'folders';
	
	$results = $wpdb->get_results( "SELECT * FROM $table WHERE uid = '".$uid."' AND parent = '".$parent_folder_id."'", 'ARRAY_A'); // Query to fetch data from database table and storing in
	if($parent_folder_id == 0)
	{
		$results = $wpdb->get_results( "SELECT * FROM $table WHERE uid = '".$uid."' AND parent = 0", 'ARRAY_A'); // Query to fetch data from database table and storing in
	}
	$folders = showCatBreadCrumb($parent_folder_id);
	$folders .= "<div id='subfolders' class='row'>";
		if($uid == 0)
		{
			$folders .= "<li>No folders found for this user</li>";
		}else{
			$row_count = 0;
			foreach($results as $row)
			{
				$row_count++;
				$folders .= "<a href='javascript:void(0);' class='subfold' data-folderid='".$row['id']."'><div class='col-md-3 text-center'><span class='title'>".$row['name']." </span><img src='".plugin_dir_url( __DIR__ )."fdr/foldericon.jpg' style='width: 85%;'></div></a>";
				if ($row_count == 4)
				{
					$folders .= "<p> &nbsp;</p>";
				}
			}
			
		}
	$folders .= "</div>";
	echo $folders;
	
	die;
}

add_shortcode("subfolderlist", 'getSubFolders');

add_action("wp_ajax_getsubfolder", "getSubFolders");
add_action("wp_ajax_nopriv_getsubfolder", "getSubFolders");