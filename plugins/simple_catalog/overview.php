<?php
	
require_once(GSPLUGINPATH.'simple_catalog/catalog.class.php');

//before any action with tree we check any delete,add, change actions
simple_catalog::checkActions();

$tree = simple_catalog::getCatalogs();
?>
<table>
	<tr>
		<th>
			<?php i18n('simple_catalog/CATALOG_NAME'); ?>
		</th>
		<th>
			<?php i18n('simple_catalog/CATEGORIES_COUNT'); ?>
		</th>
		<th><?php i18n('simple_catalog/DELETE'); ?></th>
		<th>&nbsp;</th>
	</tr>
	<?php
	//output all catalogs with links for edit and delete
	foreach ($tree->catalog as $catalog)
	{
		$del_path = $_SERVER['REQUEST_URI'].'&item_type=catalog&catalog_id='.$catalog->attributes()->id.'&action=delete';
		$overview_path = '?id=simple_catalog&item=simple_catalog_categories_overview&catalog_id='.$catalog->attributes()->id;
		$edit_path = 'loadtab.php?id=simple_catalog&item=simple_catalog_catalog_edit&catalog_id='.$catalog->attributes()->id;
		
		echo '<tr>
			<td><a href="'.$overview_path.'">'.$catalog->config->name.'</a></td>
			<td>'.sizeof($catalog->config->name).'</td><td>[<a href="'.$del_path.'">X</a>]</td>
			<td><a href="'.$edit_path.'">'.i18n_r('simple_catalog/EDIT').'</a></td>
			</tr>';
	}
	?>
</table>