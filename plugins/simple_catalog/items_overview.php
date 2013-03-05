<?php
	
require_once(GSPLUGINPATH.'simple_catalog/catalog.class.php');

//before any action with tree we check any delete,add, change actions
simple_catalog::checkActions();

$catalog_id = (int)$_GET['catalog_id'];
$category_id = (int)$_GET['category_id'];

$tree = simple_catalog::getItems($catalog_id,$category_id);
$catalog = simple_catalog::getCatalog($catalog_id);
$category = simple_catalog::getCategory($category_id, $catalog_id);

echo '<a href="loadtab.php?id=simple_catalog&amp;item=simple_catalog_categories_overview&amp;catalog_id='.$_GET['catalog_id'].'">'.$catalog->config->name.'</a> -> '.$category->config->name.'<br /><br />';

$count = $tree->items->item->count();

#create item's array
for ($count1 = 0; $count1 < $count; $count1++)
{
	$item = $tree->items->item[$count1];
	$itemsArray[$count1]['id'] = $tree->items->item[$count1]->attributes()->id;
	$itemsArray[$count1]['name'] = $tree->items->item[$count1]->config->name;
	$itemsArray[$count1]['description'] = $tree->items->item[$count1]->config->description;
	$itemsArray[$count1]['description_short'] = $tree->items->item[$count1]->config->description_short;
	$itemsArray[$count1]['date'] = $tree->items->item[$count1]->config->date;
	$itemsArray[$count1]['url'] = $tree->items->item[$count1]->config->url;
	$itemsArray[$count1]['sort'] = $tree->items->item[$count1]->config->sort;
	$itemsArray[$count1]['photo_preview'] = $tree->items->item[$count1]->photos->photo[0]->url_preview;
	$itemsArray[$count1]['photo'] = $tree->items->item[$count1]->photos->photo[0]->url;
}

$itemsSorted = subval_sort($itemsArray,'sort');
?>
<table>
	<tr>
		<th>
			<?php i18n('simple_catalog/NAME'); ?>
		</th>
		<th><?php i18n('simple_catalog/DELETE'); ?></th>
	</tr>
	<?php
	//output all catalogs with links for edit and delete
	foreach ($itemsSorted as $item)
	{
		$del_path = $_SERVER['REQUEST_URI'].'&item_type=item&category_id='.$category_id.'&catalog_id='.(int)$_GET['catalog_id'].'&item_id='.$item['id'].'&action=delete';
		$edit_path = '?id=simple_catalog&item=simple_catalog_item_edit&catalog_id='.(int)$_GET['catalog_id'].'&category_id='.(int)$_GET['category_id'].'&item_id='.$item['id'];
		$name = $item['name'];
		echo '<tr><td><a href="'.$edit_path.'">'.$name.'</a></td><td>[<a href="'.$del_path.'">X</a>]</td></tr>';
	}
	?>
</table>