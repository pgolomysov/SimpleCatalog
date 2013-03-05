<?php
/*
Plugin Name: Simple Catalog
Description: Simple Catalog
Version: 0.1
Author: Paul Golomysov
Author URI: 
*/

# get correct id for plugin
$thisfile=basename(__FILE__, ".php");

# some defines
define('SIMPLE_CATALOG_DIR', 'simple_catalog/'); 
#define('I18N_GALLERY_DEFAULT_TYPE', 'prettyphoto');
#define('I18N_GALLERY_DEFAULT_THUMB_WIDTH', 160);
#define('I18N_GALLERY_DEFAULT_THUMB_HEIGHT', 120);

# register plugin
register_plugin(
	$thisfile, //Plugin id
	'Simple Catalog', 	//Plugin name
	'0.1', 		//Plugin version
	'Paul Golomysov',  //Plugin author
	'', //author website
	'Create catalog', //Plugin description
	'theme', //page type - on which admin tab to display
	'simple_catalog_show'  //main function (administration)
);

# include language
i18n_merge('simple_catalog') || i18n_merge('simple_catalog','ru_RU');

# activate filter
add_action('nav-tab','simple_catalog_tab');
add_action('simple_catalog-sidebar', 'simple_catalog_sidebar');

include_once('simple_catalog/frontend.class.php');

# functions
function simple_catalog_tab()
{
	echo '<li><a href="loadtab.php?id=simple_catalog&amp;item=simple_catalog_overview" '.(@$_GET['id'] == 'simple_catalog' ? 'class="current"' : '').'>';
	echo i18n('simple_catalog/TAB');
	echo '</a></li>';
}

function simple_catalog_sidebar() {

	#catalog_actions
	$f = 'simple_catalog_overview';
	echo '<li><a href="loadtab.php?id=simple_catalog&amp;item='.$f.'" '.(@$_GET['item'] == $f ? 'class="current"' : '').' >'.i18n_r('simple_catalog/CATALOGS_LIST').'</a></li>';
	
	//if we dont have catalog and item id - we in catalog_actions, print it
	if (!$_GET['catalog_id'] && !$_GET['item_id'])
	{
		$f = 'simple_catalog_create';
		echo '<li><a href="loadtab.php?id=simple_catalog&amp;item='.$f.'" '.(@$_GET['item'] == $f ? 'class="current"' : '').' >'.i18n_r('simple_catalog/ADD_CATALOG').'</a></li>';

		$f = 'simple_catalog_configure';
		echo '<li><a href="loadtab.php?id=simple_catalog&amp;item='.$f.'" '.(@$_GET['item'] == $f ? 'class="current"' : '').' >'.i18n_r('simple_catalog/SETTINGS').'</a></li>';
	}
	
	#categories actions
	$f = 'simple_catalog_categories_overview';
	if (@$_GET['item'] == $f) {
		echo '<li><a href="loadtab.php?id=simple_catalog&amp;item='.$f.'&amp;catalog_id='.$_GET['catalog_id'].'" class="current">'.i18n_r('simple_catalog/CATEGORIES_LIST').'</a></li>';
		echo '<li><a href="loadtab.php?id=simple_catalog&amp;item=simple_catalog_add_category&amp;catalog_id='.$_GET['catalog_id'].'">'.i18n_r('simple_catalog/ADD_CATEGORY').'</a></li>';
	}

	$f = 'simple_catalog_add_category';
	if (@$_GET['item'] == $f) {
		echo '<li><a href="loadtab.php?id=simple_catalog&amp;item=simple_catalog_categories_overview&amp;catalog_id='.(int)$_GET['catalog_id'].'">'.i18n_r('simple_catalog/CATEGORIES_LIST').'</a></li>';
		echo '<li><a href="loadtab.php?id=simple_catalog&amp;item='.$f.'&amp;catalog_id='.$_GET['catalog_id'].'" class="current">'.i18n_r('simple_catalog/ADD_CATEGORY').'</a></li>';
	}
	
	$f = 'simple_catalog_items_overview';
	if (@$_GET['item'] == $f) {
		echo '<li><a href="loadtab.php?id=simple_catalog&amp;item=simple_catalog_categories_overview&amp;catalog_id='.$_GET['catalog_id'].'">'.i18n_r('simple_catalog/CATEGORIES_LIST').'</a></li>';
		echo '<li><a href="loadtab.php?id=simple_catalog&amp;item=simple_catalog_categories_overview&amp;catalog_id='.$_GET['catalog_id'].'" class="current">'.i18n_r('simple_catalog/ELEMENTS_LIST').'</a></li>';
		echo '<li><a href="loadtab.php?id=simple_catalog&amp;item=simple_catalog_add_item&amp;catalog_id='.(int)$_GET['catalog_id'].'&amp;category_id='.(int)$_GET['category_id'].'">'.i18n_r('simple_catalog/ADD_ELEMENT').'</a></li>';
	}
	
	#items actions
	$f = 'simple_catalog_add_item';
	if (@$_GET['item'] == $f) {
		echo '<li><a href="loadtab.php?id=simple_catalog&amp;item=simple_catalog_categories_overview&amp;catalog_id='.$_GET['catalog_id'].'">'.i18n_r('simple_catalog/CATEGORIES_LIST').'</a></li>';
		echo '<li><a href="loadtab.php?id=simple_catalog&amp;item=simple_catalog_items_overview&amp;catalog_id='.$_GET['catalog_id'].'&amp;category_id='.$_GET['category_id'].'">'.i18n_r('simple_catalog/ELEMENTS_LIST').'</a></li>';
		echo '<li><a href="loadtab.php?id=simple_catalog&amp;item=simple_catalog_add_item&amp;catalog_id='.(int)$_GET['catalog_id'].'&amp;category_id='.(int)$_GET['category_id'].'" class="current">'.i18n_r('simple_catalog/ADD_ELEMENT').'</a></li>';
	}
	
	$f = 'simple_catalog_item_edit';
	if (@$_GET['item'] == $f) {
		echo '<li><a href="loadtab.php?id=simple_catalog&amp;item=simple_catalog_categories_overview&amp;catalog_id='.$_GET['catalog_id'].'">'.i18n_r('simple_catalog/CATEGORIES_LIST').'</a></li>';
		echo '<li><a href="loadtab.php?id=simple_catalog&amp;item=simple_catalog_items_overview&amp;catalog_id='.$_GET['catalog_id'].'&amp;category_id='.$_GET['category_id'].'">'.i18n_r('simple_catalog/ELEMENTS_LIST').'</a></li>';
		echo '<li><a href="loadtab.php?id=simple_catalog&amp;item=simple_catalog_add_item&amp;catalog_id='.(int)$_GET['catalog_id'].'&amp;category_id='.(int)$_GET['category_id'].'" class="current">'.i18n_r('simple_catalog/EDIT_ELEMENT').'</a></li>';
	}
}

#HOOKS
//filter hook that displays catalogs
add_filter('content','simple_category_display'); 

# ===== BACKEND PAGES =====

function error404()
{
	Header('Location: /404');
}

function simple_category_display($content)
{
	$content = preg_replace_callback('/\(% simple_catalog catalog=(.+),category=(.+) %\)/Us','simple_category_frontend',$content);
	return $content;
}

function simple_category_frontend($matches)
{
	$_GET['catalog'] = $matches[1];
	$_GET['category'] = $matches[2];
	$type = explode('=', $matches[1]);
	//if ($type[0] == 'catalog')
	//{
		simple_catalog_frontend::addBreadcrumb('/','Главная');
		if ($_GET['catalog'] && !$_GET['category'])
		{
			$content = simple_catalog_frontend::getCatalog($type[1]);
		}
		elseif ($_GET['category'] && !$_GET['item'])
		{
			$content = simple_catalog_frontend::getCategory($_GET['catalog'],$_GET['category'], $_GET['page']);
		}
		elseif  ($_GET['item'])
		{
			$content = simple_catalog_frontend::getItem($_GET['catalog'],$_GET['category'],$_GET['item']);
		}
	//}
	echo simple_catalog_frontend::getBreadcrumbs();
	return $content;
}

function simple_catalog_overview()
{
	include(GSPLUGINPATH.'simple_catalog/overview.php');
}

function simple_catalog_categories_overview() 
{
	include(GSPLUGINPATH.'simple_catalog/categories_overview.php');
}

function simple_catalog_items_overview()
{
	include(GSPLUGINPATH.'simple_catalog/items_overview.php');
}

function simple_catalog_item_edit()
{
	include(GSPLUGINPATH.'simple_catalog/item_edit.php');
}

function simple_catalog_create() 
{
	include(GSPLUGINPATH.'simple_catalog/edit.php');
}

function simple_catalog_catalog_edit()
{
	include(GSPLUGINPATH.'simple_catalog/edit.php');	
}

function simple_catalog_category_edit()
{
	include(GSPLUGINPATH.'simple_catalog/category_edit.php');	
}

function simple_catalog_configure()
{
	include(GSPLUGINPATH.'simple_catalog/configure.php');
}

function simple_catalog_add_category()
{
	include(GSPLUGINPATH.'simple_catalog/category_edit.php');
}

function simple_catalog_add_item()
{
	include(GSPLUGINPATH.'simple_catalog/item_edit.php');
}

?>