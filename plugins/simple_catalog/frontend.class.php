<?php
include_once('catalog.class.php');

class simple_catalog_frontend
{
	public static $breadcrumbs = array();
	public static $itemsPerPage = 999;
	
	public function addBreadcrumb($link, $name, $current)
	{
		if ($current)
		{
			self::$breadcrumbs[] = $name;
		}
		else
		{
			self::$breadcrumbs[] = '<a href="'.$link.'">'.$name.'</a>';
		}
	}
	
	public static function getBreadcrumbs()
	{
		//return implode(' >> ',self::$breadcrumbs);
	}
	
#catalogs
	public static function getCatalog($url)
	{
		$templates = simple_catalog_get_templates();
		$tree = simple_catalog::getCatalogByUrl($url);
		if (!$tree) error404();
		
		//if url not in GET array, but we have correct tree - redirect to right url
		if ($_GET['catalog'] != $tree->config->url && $tree) redirect('/catalog/'.$tree->config->url);
		self::addBreadcrumb($tree->config->url,$tree->config->name, 1);
		$categories_list = self::createCategoriesList($templates, $tree);
		//create output
		$catalog_template = $templates['catalog_overview'];
		$output = str_replace('{name}',$tree->config->name,$catalog_template);
		$output = str_replace('{categories_list}',$categories_list,$output);
		return $output;
	}
#/catalogs
	
#categories	
	public static function createCategoriesList($templates, $tree)
	{
		$list_template = $templates['categories_list'];
		$link_template = $templates['category_link'];
		
		foreach ($tree->categories->category as $category)
		{
			$link = '';
			
			$href = $_SERVER['REQUEST_URI'].'/'.$category->config->url.'/';
			$link = str_replace('{href}',$href,$link_template);
			$link = str_replace('{name}',$category->config->name,$link);
			
			$links .= $link;
		}
		
		$output = str_replace('{categories_links}',$links,$list_template);
		return $output;
	}


	public static function getCategory($catalog_url, $category_url, $page)
	{
		if (!$page) $page = 1;
		$templates = simple_catalog_get_templates();
		$tree = simple_catalog::getCategoryByUrl($catalog_url, $category_url);
		if (!$tree) error404();
		#self::addBreadcrumb($tree->config->url,$tree->config->name, 1);
		
		$items_list = self::createItemsList($templates, $tree, $page);
		
		//create output
		$category_template = $templates['category_overview'];
		$output = str_replace('{name}',$tree->config->name,$category_template);
		$output = str_replace('{items_list}',$items_list,$output);
		return $output;
	}
#/categories

#items
	public static function createItemsList($templates, $tree, $page)
	{
		$list_template = $templates['items_list'];
		$link_template = $templates['item_link'];
	
		$count = $tree->items->item->count();
		#create item's array
		for ($count1 = 0; $count1 < $count; $count1++)
		{
			$item = $tree->items->item[$count1];
			$pagesArray[$count1]['name'] = $tree->items->item[$count1]->config->name;
			$pagesArray[$count1]['description'] = $tree->items->item[$count1]->config->description;
			$pagesArray[$count1]['description_short'] = $tree->items->item[$count1]->config->description_short;
			$pagesArray[$count1]['date'] = $tree->items->item[$count1]->config->date;
			$pagesArray[$count1]['url'] = $tree->items->item[$count1]->config->url;
			$pagesArray[$count1]['sort'] = $tree->items->item[$count1]->config->sort;
			$pagesArray[$count1]['photo_preview'] = $tree->items->item[$count1]->photos->photo[0]->url_preview;
			$pagesArray[$count1]['photo'] = $tree->items->item[$count1]->photos->photo[0]->url;
        }
		
		$pagesSorted = subval_sort($pagesArray,'sort');
		
		#for navigation
		$from = self::$itemsPerPage * $page - self::$itemsPerPage;
		$to = $from + self::$itemsPerPage - 1;
		if ($to > $tree->items->item->count()) $to = $tree->items->item->count();
		for (;$from < $to; $from++)
		{
			$item = $pagesSorted[$from];

			if ($tree->items->item[$from] != null)
			{
				$link = '';
				
				$href = '/catalog/'.$item['url'];
				$link = str_replace('{more_href}',$href,$link_template);
				$link = str_replace('{name}',$item['name'],$link);
				$link = str_replace('{description}',$item['description_short'],$link);
				$link = str_replace('{image_link}','/data/uploads/'.$item['photo_preview'],$link);
				if ($from + 1 != $to) $link .= $templates['items_separator'];
				$links .= $link;
			}
			else
			{
				break;
			}
		}
		
		#do navigation menu
		$pages_count = ceil($tree->items->item->count() / self::$itemsPerPage);
		$navigation = simple_catalog_get_navigation($pages_count, $page);
		$output = str_replace('{items_links}',$links,$list_template);
		$output = str_replace('{navigation}',$navigation,$output);
		return $output;
	}
	


	public static function getItem($catalog_url,$category_url,$item_url)
	{
		$templates = simple_catalog_get_templates();
		$tree = simple_catalog::getItemByUrl($catalog_url, $category_url, $item_url);
		if (!$tree) error404();
		#self::addBreadcrumb($tree->config->url,$tree->config->name, 1);
		$output = self::createItemPage($templates, $tree);

		return $output;
	}
	
	public static function createImagesList($templates, $tree)
	{
		$list_template = $templates['images_list'];
		$image_template = $templates['item_image'];
		
		foreach ($tree->photos->photo as $photo)
		{
			$image = '';
			
			$url = '/data/uploads/'.$photo->url;
			$url_preview = '/data/uploads/'.$photo->url_preview;
			
			$image = str_replace('{url}',$url,$image_template);
			$image = str_replace('{url_preview}',$url_preview,$image);

			$images .= $image;
		}
		
		$output = str_replace('{images_list}',$images,$list_template);
		return $output;
	}
	
	public static function createItemPage($templates, $tree)
	{
		$templates = simple_catalog_get_templates();
		
		$images_list = self::createImagesList($templates, $tree);
		$item_overview = $templates['item_overview'];
		
		$href = $_SERVER['REQUEST_URI'].$tree->config->url;
		$output = str_replace('{name}',$tree->config->name,$item_overview);
		$output = str_replace('{description}',$tree->config->description,$output);
		$output = str_replace('{images_list}',$images_list,$output);

		return $output;
	}
	
	public static function moveItem($position)
	{
		$catalog_id = (int)$_GET['catalog_id'];
		$category_id = (int)$_GET['category_id'];
		$item_id = (int)$_GET['item_id'];
		
		$tree = self::getTree();
		
		$count = 0;
		foreach ($tree->catalog as $catalog)
		{
			if ($catalog->attributes()->id == $catalog_id)
			{
				simple_catalog_frontend::addBreadcrumb('/catalog/'.$catalog->config->url.'/',$catalog->config->name);
				foreach ($catalog->categories->category as $category)
				{
					if ($category->attributes()->id == $category_id)
					{
						simple_catalog_frontend::addBreadcrumb('/catalog/'.$catalog->config->url.'/'.$category->config->url.'/',$category->config->name);
						foreach ($category->items->item as $item)
						{
							if ($item->attributes()->id == $item_id)
							{
								foreach ($item->photos->photo as $photo)
								{
									if ($photo->url == $url)
									{
										break(4);
									}
									$count++;
								}
							}
						}
					}

				}
			}
		}
		
		$position -= 1;
		$save_url = (string)$item->photos->photo[$position]->url;
		$item->photos->photo[$position]->url = (string)$item->photos->photo[$count]->url;
		$item->photos->photo[$count]->url = $save_url;
		
		

		if (self::saveTree($tree)) 
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
#/items
}


?>