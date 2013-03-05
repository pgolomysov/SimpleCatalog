<?php
include_once("imageresize.class.php");

class simple_catalog
{
	private static $settings = null;
	
	#size for open in fancybox image
	private static $imageWidth = 1000;
	private static $imageHeight = 1000;
	
	#size for preview of image
	private static $imagePreviewWidth = 220;
	private static $imagePreviewHeight = 165;
  
	//get settings from config file
    public static function getSettings($reload=false) {
		if (self::$settings != null && !$reload) return self::$settings;
		self::$settings = array();
		if (file_exists(GSDATAOTHERPATH.'simple_catalog.xml')) {
		  $data = getXML(GSDATAOTHERPATH.'simple_catalog.xml');
		  if ($data) {
			foreach ($data as $key => $value) self::$settings[$key] = (string) $value;
		  }
		}
		return self::$settings;
	}
	
	//must check folders for existing
	public static function checkPrerequisites()
	{
		//there must be some code...
		return true;
	}
	
	//check for any delete change add actions
	public static function checkActions()
	{
		//if we have any "item_type" in GET
		if ($_GET['item_type'])
		{
			if ($_GET['action'] == 'delete')
			{
				switch($_GET['item_type'])
				{
					case 'catalog': self::deleteCatalog($_GET['catalog_id']); break;
					case 'category': self::deleteCategory($_GET['category_id'], $_GET['catalog_id']); break;
					case 'item': self::deleteItem($_GET['item_id'], $_GET['catalog_id'], $_GET['category_id']); break;
				}
			}
		}
	}
  
	public static function getTree()
	{
		//get tree file
		$path = GSDATAOTHERPATH.SIMPLE_CATALOG_DIR.'tree.xml';
		$tree = getXML($path);
		return $tree;
	}
	
	public static function saveTree($tree)
	{
		if ($tree)
		{
			if (is_string($tree)) 
			{
				$result = file_put_contents(GSDATAOTHERPATH.SIMPLE_CATALOG_DIR.'tree.xml', $tree);
			}
			else
			{
				$result = file_put_contents(GSDATAOTHERPATH.SIMPLE_CATALOG_DIR.'tree.xml', $tree->asXML());
			}
			
			if ($result)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	
##### Catalogs actions #####
	
	//get catalogs list
	public static function getCatalogs()
	{
		//catalogs is the first element of the tree - return all tree
		return self::getTree();
	}
	
	//get one catalog
	public static function getCatalog($catalog_id)
	{
		$tree = self::getTree();

		foreach ($tree->catalog as $catalog)
		{
			if ($catalog->attributes()->id == $catalog_id)
			{
				return $catalog;
			}

		}
	}
	
	public static function getCatalogByUrl($url)
	{
		$tree = self::getTree();

		foreach ($tree->catalog as $catalog)
		{
			if ($catalog->config->url == $url)
			{
				return $catalog;
			}

		}
	}
	
	public static function saveCatalog($catalog_id,$name,$description,$url)
	{
		$tree = self::getTree();
		//find all catalogs
		
		foreach ($tree as $catalog)
		{
			if ($catalog->attributes()->id == $catalog_id)
			{
				break;
			}
		}
		$catalog->config->name = $name;
		$catalog->config->description = $description;
		$catalog->config->url = $url;

		//put new tree into file
		if (self::saveTree($tree))
		{		
			echo '<script type="text/javascript">
				location.replace("/admin/loadtab.php?id=simple_catalog&item=simple_catalog_overview");
				</script>';
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public static function deleteCatalog($id)
	{
		//get tree file
		$tree = self::getTree();
		//find needed catalog
		$count = 0;
		foreach ($tree->catalog as $catalog)
		{
			//if found catalog with needed id
			if ($id == $catalog->attributes()->id)
			{
				//backup old tree
				copy(GSDATAOTHERPATH.SIMPLE_CATALOG_DIR.'tree.xml', GSBACKUPSPATH.SIMPLE_CATALOG_DIR.'tree_'.time().'.xml');
				//delete from tree
				unset($tree->catalog[$count]);
				break;
			}
			$count++;
		}
		//put new tree into file
		if (self::saveTree($tree))
		{		
			echo '<script type="text/javascript">
				location.replace("/admin/loadtab.php?id=simple_catalog&item=simple_catalog_overview");
				</script>';
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public static function createCatalog($name,$description,$url) {
		$xml = self::getTree();
		//find all catalogs
		$children = $xml->count();
		//calculate id
		if ($children != 0)
		{
			$id = $xml->catalog[$children-1]->attributes()->id;
			$id += 1;
		}
		else
		{
			$id = 1;
		}

		$catalog = $xml->addChild('catalog');
		$catalog->addAttribute('id',$id);
		$config = $catalog->addChild('config');
		$config->addChild('name')->addCdata($name);
		$config->addChild('description')->addCdata($description);
		$config->addChild('date',date('d.m.Y'));
		$config->addChild('url')->addCdata($url);

		//put new tree into file
		if (self::saveTree($xml))
		{		
			echo '<script type="text/javascript">
				location.replace("/admin/loadtab.php?id=simple_catalog&item=simple_catalog_overview");
				</script>';
			return true;
		}
		else
		{
			return false;
		}
	}
##### /Catalogs actions #####
	
##### Categories actions #####
	//get categories for choosen catalog
	public static function getCategories($catalog_id)
	{
		$catalog_id = (int)$catalog_id;
		$tree = self::getTree();
		foreach ($tree as $catalog)
		{
			if ($catalog->attributes()->id == $catalog_id)
			{
				return $catalog;
			}
		}
	}
	
	//get one category
	public static function getCategory($category_id, $catalog_id)
	{
		$tree = self::getTree();

		foreach ($tree->catalog as $catalog)
		{
			if ($catalog->attributes()->id == $catalog_id)
			{
				foreach ($catalog->categories->category as $category)
				{
					if ($category->attributes()->id == $category_id)
					{
						return $category;
					}

				}
			}

		}
	}
	
	public static function getCategoryByUrl($catalog_url, $category_url)
	{
		$tree = self::getTree();

		foreach ($tree->catalog as $catalog)
		{
			if ($catalog->config->url == $catalog_url)
			{
				#simple_catalog_frontend::addBreadcrumb('/catalog/'.$catalog->config->url.'/',$catalog->config->name);
				simple_catalog_frontend::addBreadcrumb('/catalog/','Каталог', 1);
				foreach ($catalog->categories->category as $category)
				{
					if ($category->config->url == $category_url)
					{
						return $category;
					}

				}
			}

		}
	}
	
	//save category
	public static function createCategory($name,$description,$url,$catalog_id) 
	{
		$xml = self::getTree();
		
		foreach ($xml as $catalog)
		{
			//find catalog by id
			if ($catalog->attributes()->id == $catalog_id)
			{
				//find all categories
				if ($catalog->categories->count() == 0) {
					$catalog->addChild('categories');
				}
				$children = $catalog->categories->category->count();
				//calculate id
				if ($children != 0)
				{
					$id = $catalog->categories->category[$children-1]->attributes()->id;
					$id += 1;
				}
				else
				{
					$id = 1;
				}
				break;
			}
		}

		$category = $catalog->categories->addChild('category');
		$category->addAttribute('id',$id);
		$config = $category->addChild('config');
		$config->addChild('name')->addCdata($name);
		$config->addChild('description')->addCdata($description);
		$config->addChild('date',date('d.m.Y'));
		$config->addChild('url')->addCdata($url);

		//put new tree into file
		if (self::saveTree($xml))
		{		
			echo '<script type="text/javascript">
			location.replace("/admin/loadtab.php?id=simple_catalog&item=simple_catalog_categories_overview&catalog_id='.$_GET['catalog_id'].'");
				</script>';
			return true;
		}
		else
		{
			return false;
		}
	}
	
	
	public static function saveCategory($category_id, $catalog_id, $name,$description,$url,$catalog_id)
	{
		$tree = self::getTree();
		//find all catalogs
		foreach ($tree as $catalog)
		{
			if ($catalog->attributes()->id == $catalog_id)
			{
				foreach ($catalog->categories->category as $category)
				{
					if ($category->attributes()->id == $category_id)
					{
						break(2);
					}
				}
			}
		}
		
		$category->config->name = $name;
		$category->config->description = $description;
		$category->config->url = $url;

		//put new tree into file
		if (self::saveTree($tree))
		{		
			echo '<script type="text/javascript">
				location.replace("/admin/loadtab.php?id=simple_catalog&item=simple_catalog_categories_overview&catalog_id='.$catalog_id.'");
				</script>';
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public static function deleteCategory($category_id, $catalog_id)
	{
		//get tree file
		$tree = self::getTree();
		//find needed category
		$count = 0;
		foreach ($tree->catalog as $catalog)
		{
			foreach ($catalog->categories->category as $category)
			{
				//if found catalog with needed id
				if ($category->attributes()->id == $category_id)
				{
					//backup old tree
					copy(GSDATAOTHERPATH.SIMPLE_CATALOG_DIR.'tree.xml', GSBACKUPSPATH.SIMPLE_CATALOG_DIR.'tree_'.time().'.xml');
					//delete from tree
					unset($catalog->categories->category[$count]);
					break;
				}
				$count++;
			}
		}

		//put new tree into file
		if (self::saveTree($tree))
		{		
			echo '<script type="text/javascript">
			location.replace("/admin/loadtab.php?id=simple_catalog&item=simple_catalog_categories_overview&catalog_id='.$_GET['catalog_id'].'");
				</script>';
			return true;
		}
		else
		{
			return false;
		}
	}
##### /Categories actions #####
	
##### Items actions #####

	static public function getItems($catalog_id, $category_id)
	{
		$tree = self::getTree();
		foreach ($tree->catalog as $catalog)
		{
			//find catalog by id
			if ($catalog->attributes()->id == $catalog_id)
			{
				//find category by id
				foreach ($catalog->categories->category as $category)
				{
					if ($category->attributes()->id == $category_id)
					{
						return $category;
					}
				}
			}
		}
	}
	
	//get one item
	public static function getItem($item_id, $catalog_id, $category_id)
	{
		$tree = self::getTree();

		foreach ($tree->catalog as $catalog)
		{
			if ($catalog->attributes()->id == $catalog_id)
			{
				foreach ($catalog->categories->category as $category)
				{
					if ($category->attributes()->id == $category_id)
					{
						foreach ($category->items->item as $item)
						{
							if ($item->attributes()->id == $item_id)
							{
								return $item;
							}

						}
					}

				}
			}

		}
	}
	
	//get one item
	public static function getItemByUrl($catalog_url, $category_url, $item_url)
	{
		$tree = self::getTree();

		foreach ($tree->catalog as $catalog)
		{
			if ($catalog->config->url == $catalog_url)
			{
				#simple_catalog_frontend::addBreadcrumb('/catalog/'.$catalog->config->url.'/',$catalog->config->name);
				foreach ($catalog->categories->category as $category)
				{
					if ($category->config->url == $category_url)
					{
						#simple_catalog_frontend::addBreadcrumb('/catalog/'.$catalog->config->url.'/'.$category->config->url.'/',$category->config->name);
						simple_catalog_frontend::addBreadcrumb('/catalog/','Каталог');
						foreach ($category->items->item as $item)
						{
							if ($item->config->url == $item_url)
							{
								return $item;
							}

						}
					}

				}
			}

		}
	}
	
	static public function createItem($name, $description, $description_short, $url, $catalog_id, $category_id, $sort)
	{	
		$xml = self::getTree();

		foreach ($xml as $catalog)
		{
			//find catalog by id
			if ($catalog->attributes()->id == $catalog_id)
			{
				//find category by id
				foreach ($catalog->categories->category as $category)
				{
					if ($category->attributes()->id == $category_id)
					{
						break(2);
					}
				}
			}
		}
		
		//find all items
		if ($category->items->count() == 0) {
			$category->addChild('items');
		}
		
		$children = $category->items->item->count();

		//calculate id
		if ($children != 0)
		{
			$id = $category->items->item[$children-1]->attributes()->id;
			$id += 1;
		}
		else
		{
			$id = 1;
		}

		//work with images
		if ($_FILES['image']['name'])
		{
			if ($_FILES['image']['size'] > 1024*1024*5 || !strstr($_FILES['image']['type'], 'image'))
			{
				$err = 'Размер файла более 5Мб, или файл не является изображением';
			}
			else
			{
				$img_name = $_FILES["image"]["name"];
				#replace letters to english in url
				if (isset($i18n['TRANSLITERATION']) && is_array($translit=$i18n['TRANSLITERATION']) && count($translit>0)) {
					$img_name = time().'_'.str_replace(array_keys($translit),array_values($translit),$_FILES["image"]["name"]);
				}
				$image_url =  "../data/uploads/".$img_name;
				move_uploaded_file($_FILES["image"]["tmp_name"], $image_url);
						
				#recize original photo
				self::resizeImage(self::$imageWidth, self::$imageHeight, $image_url, "../data/uploads/", $img_name);

				#do preview for image
				self::resizeImage(self::$imagePreviewWidth, self::$imagePreviewHeight, $image_url, '../data/uploads/', 'prev_'.$img_name);
			}
		}
		

		$item = $category->items->addChild('item');
		$item->addAttribute('id',$id);
		$config = $item->addChild('config');
		$config->addChild('name')->addCdata($name);
		$config->addChild('description')->addCdata($description);
		$config->addChild('description_short')->addCdata($description_short);
		$config->addChild('date',date('d.m.Y'));
		$config->addChild('url')->addCdata($url);
		$config->addChild('sort')->addCdata($sort);
				
		if ($image_url)
		{
			if ($item->photos->count() == 0) $item->addChild('photos');
			$photo = $item->photos->addChild('photo');
			$photo->url = $img_name;
			$photo->url_preview = 'prev_'.$img_name;	
		}

		//put new tree into file
		if (self::saveTree($xml))
		{		
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public static function saveItem($item_id, $catalog_id, $category_id, $name, $description, $description_short, $url, $sort)
	{
		global $i18n;
		
		$tree = self::getTree();
		//find all catalogs
		foreach ($tree as $catalog)
		{
			if ($catalog->attributes()->id == $catalog_id)
			{
				foreach ($catalog->categories->category as $category)
				{
					if ($category->attributes()->id == $category_id)
					{
						foreach ($category->items->item as $item)
							{
								if ($item->attributes()->id == $item_id)
								{
									break(3);
								}
							}
					}
				}
			}
		}
		
		if ($_FILES['image']['name'])
		{
			if ($_FILES['image']['size'] > 1024*1024*2 || !strstr($_FILES['image']['type'], 'image'))
			{
				$err = 'Размер файла более 2Мб, или файл не является изображением';
			}
			else
			{
				$img_type = preg_replace('/.+\./','',$_FILES["image"]["name"]);
				#replace letters to english in url
				if (isset($i18n['TRANSLITERATION']) && is_array($translit=$i18n['TRANSLITERATION']) && count($translit>0)) {
					$img_name = time().'.'.$img_type;
				}
				$image_url =  "../data/uploads/".$img_name;
				move_uploaded_file($_FILES["image"]["tmp_name"], $image_url);
				
				
				#recize original photo
				self::resizeImage(self::$imageWidth, self::$imageHeight, $image_url, "../data/uploads/", $img_name);

				#do preview for image
				self::resizeImage(self::$imagePreviewWidth, self::$imagePreviewHeight, $image_url, '../data/uploads/', 'prev_'.$img_name);
			}
		}
		
		$item->config->name = $name;
		$item->config->description = $description;
		$item->config->description_short = $description_short;
		$item->config->url = $url;
		$photo->url_preview = 'prev_'.$img_name;	
		$old_sort = strval($item->config->sort);
		$item->config->sort = $sort;
		
		if ($image_url)
		{
			if ($item->photos->count() == 0) $item->addChild('photos');
			$photo = $item->photos->addChild('photo');
			$photo->url = $img_name;
			$photo->url_preview = 'prev_'.$img_name;
		}

		//put new tree into file
		if (self::saveTree($tree))
		{	
			#if ($old_sort != $sort) self::sortCategory();
			//echo '<script type="text/javascript">
			//	location.replace("/admin/loadtab.php?id=simple_catalog&item=simple_catalog_items_overview&catalog_id='.$catalog_id.'&category_id='.$category_id.'");
			//	</script>';
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public static function deleteItem($item_id, $catalog_id, $category_id)
	{
		//get tree file
		$tree = self::getTree();
		//find needed catalog
		$count = 0;
		foreach ($tree->catalog as $catalog)
		{
			foreach ($catalog->categories->category as $category)
			{
				if ($category->attributes()->id == $category_id)
				{
					foreach ($category->items->item as $item)
					{				
						if ($item->attributes()->id == $item_id)
						{
							//backup old tree
							copy(GSDATAOTHERPATH.SIMPLE_CATALOG_DIR.'tree.xml', GSBACKUPSPATH.SIMPLE_CATALOG_DIR.'tree_'.time().'.xml');
							//delete from tree
							unset($category->items->item[$count]);
							break(3);
						}
						$count++;
					}
				}	
			}
		}

		//put new tree into file
		if (self::saveTree($tree))
		{		
			echo '<script type="text/javascript">
				location.replace("/admin/loadtab.php?id=simple_catalog&item=simple_catalog_items_overview&catalog_id='.$catalog_id.'&category_id='.$category_id.'");
				</script>';
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public static function deleteImage($url)
	{
		$tree = self::getTree()->asXML();
		$pattern = '/<photo><url>'.$url.'<\/url><url_preview>prev_'.$url.'<\/url_preview><\/photo>/Us';
		$tree = preg_replace($pattern ,'',$tree);
		if (self::saveTree($tree)) return true;
	}
	
	private function resizeImage($width, $height, $image_path, $save_path, $image_name)
	{
		$img = new img($image_path);
		// Уменьшит фото пропорционально, сохранив в директорию img c названием newFile
		$infoResize = $img->resize($width, $height,$save_path,$image_name);
	}
	
	
	public static function moveImage($url, $position)
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
				//simple_catalog_frontend::addBreadcrumb('/catalog/'.$catalog->config->url.'/',$catalog->config->name);
				foreach ($catalog->categories->category as $category)
				{
					if ($category->attributes()->id == $category_id)
					{
						//simple_catalog_frontend::addBreadcrumb('/catalog/'.$catalog->config->url.'/'.$category->config->url.'/',$category->config->name);
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
		$save_url_preview = (string)$item->photos->photo[$position]->url_preview;
		
		$item->photos->photo[$position]->url = (string)$item->photos->photo[$count]->url;
		$item->photos->photo[$position]->url_preview = (string)$item->photos->photo[$count]->url_preview;
		
		$item->photos->photo[$count]->url = $save_url;
		$item->photos->photo[$count]->url_preview = $save_url_preview;
		
		

		if (self::saveTree($tree)) 
		{
			return true;
		}
		else
		{
			return false;
		}
	}

##### /Items actions ##### 
}


?>