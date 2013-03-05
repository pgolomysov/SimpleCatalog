<?php
	
require_once(GSPLUGINPATH.'simple_catalog/catalog.class.php');

//before any action with tree we check any delete,add, change actions
simple_catalog::checkActions();

$catalog_id = (int)$_GET['catalog_id'];

$tree = simple_catalog::getCategories($catalog_id);
$catalog = simple_catalog::getCatalog($catalog_id);

echo $catalog->config->name.'<br /><br />';
?>

<table>
	<tr>
		<th>
			<?php i18n('simple_catalog/CATEGORY_NAME'); ?>
		</th>
		<th>
			<?php i18n('simple_catalog/ITEMS_COUNT'); ?>
		</th>
		<th><?php i18n('simple_catalog/DELETE'); ?></th>
		<th>&nbsp;</th>
	</tr>
	<?php
	//output all catalogs with links for edit and delete
	foreach ($tree->categories->category as $category)
	{
		$category_id = $category->attributes()->id;
		$del_path = $_SERVER['REQUEST_URI'].'&item_type=category&category_id='.$category_id.'&catalog_id='.(int)$_GET['catalog_id'].'&action=delete';
		$overview_path = '?id=simple_catalog&item=simple_catalog_items_overview&catalog_id='.(int)$_GET['catalog_id'].'&category_id='.$category_id;
		$edit_path = 'loadtab.php?id=simple_catalog&item=simple_catalog_category_edit&category_id='.$category_id.'&catalog_id='.(int)$_GET['catalog_id'];
		
		echo '<tr>
			<td><a href="'.$overview_path.'">'.$category->config->name.'</a></td>
			<td>'.sizeof($category->items->item).'</td>
			<td>[<a href="'.$del_path.'">X</a>]</td>
			<td><a href="'.$edit_path.'">'.i18n_r('simple_catalog/EDIT').'</a></td>
			</tr>';
	}
	?>
</table>