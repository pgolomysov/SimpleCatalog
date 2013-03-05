<?php


global $i18n;

require_once(GSPLUGINPATH.'simple_catalog/catalog.class.php');

#load config file
#if (!file_exists(GSDATAPAGESPATH.'gallery.xml')) copy(GSPLUGINPATH.'i18n_gallery/gallery.xml',GSDATAPAGESPATH.'gallery.xml');

//if we delete image
if ($_GET['delete_image'])
{
	if (simple_catalog::deleteImage($_GET['delete_image'])) Header('Location: '.$_SERVER['HTTP_REFERER']);
}

//if we mobe image
if ($_GET['move_image'])
{
	if (simple_catalog::moveImage($_GET['move_image'],$_GET['image_position'])) Header('Location: '.$_SERVER['HTTP_REFERER']);
}


$action = $_GET['item'];

if ($action == 'simple_catalog_item_edit')
{
	$item = simple_catalog::getItem((int)$_GET['item_id'],(int)$_GET['catalog_id'], (int)$_GET['category_id']);
}

$success = false;

//check needed folders and files
if (!simple_catalog::checkPrerequisites()) {
  $msg = i18n_r('simple_catalog/MISSING_DIR');
//if we save
} else if (isset($_POST['save'])) {
	//check input params for correct
	if ($_POST['post-name'] == '') {
		$msg = i18n_r('simple_catalog/ERR_INVALID_NAME');
	}
  
	
	#check url
	if ($_POST['post-url'] != '')
	{
		#replace letters to english in url
		if (isset($i18n['TRANSLITERATION']) && is_array($translit=$i18n['TRANSLITERATION']) && count($translit>0)) {
			$url = str_replace(array_keys($translit),array_values($translit),$_POST['post-url']);
		}
	}
	else
	{
		#replace letters to english in url
		if (isset($i18n['TRANSLITERATION']) && is_array($translit=$i18n['TRANSLITERATION']) && count($translit>0)) {
			$url = str_replace(array_keys($translit),array_values($translit),$_POST['post-name']);
		}
	}
	
	#if no error
	if (!isset($msg))
	{
		if ($action == 'simple_catalog_item_edit')
		{
			$result = simple_catalog::saveItem((int)$_GET['item_id'], (int)$_GET['catalog_id'], (int)$_GET['category_id'], $_POST['post-name'], $_POST['post-description'], $_POST['post-short-description'], $url, $_POST['post-sort']);
			$redirect = '<script type="text/javascript">
				location.replace("/admin/loadtab.php?id=simple_catalog&item=simple_catalog_items_overview&catalog_id='.(int)$_GET['catalog_id'].'&category_id='.$_GET['category_id'].'");
				</script>';
		}
		
		if ($action == 'simple_catalog_add_item')
		{
			$result = simple_catalog::createItem($_POST['post-name'],$_POST['post-description'],$_POST['post-short-description'],$url,(int)$_GET['catalog_id'],(int)$_GET['category_id'],(int)$_POST['post-sort']);
			$redirect = '<script type="text/javascript">
				location.replace("/admin/loadtab.php?id=simple_catalog&item=simple_catalog_items_overview&catalog_id='.$_GET['catalog_id'].'&category_id='.$_GET['category_id'].'");
				</script>';
		}

		if ($result)
		{
			$msg = i18n_r('simple_catalog/SAVE_SUCCESS');
			$success = true;
			
			//if need, use redirect
			if ($redirect) 
			{
				echo $redirect;
				die();
			}
			
			//reload data
			$item = simple_catalog::getItem((int)$_GET['item_id'],(int)$_GET['catalog_id'], (int)$_GET['category_id']);
		} else {
			$msg = i18n_r('simple_catalog/SAVE_FAILURE');
		}
	}
}

// Variable settings
$userid = login_cookie_check();


// Page variables reset
$theme_templates = ''; 
$parents_list = ''; 
$keytags = '';
$parent = '';
$template = '';
$menuStatus = ''; 
$private = ''; 
$menu = ''; 
$content = '';
$author = '';
$title = '';
$url = '';
$metak = '';
$metad = '';

?>




<h3 class="floated" style="float:left"><?php i18n('simple_catalog/NEW_ELEMENT'); ?></h3>
<div style="clear: both;"></div>
<form method="post" enctype="multipart/form-data">
	<br />
	<?php i18n('simple_catalog/NAME')?><br />
	<input type="text" class="text" id="post-name" name="post-name" value="<?php echo htmlspecialchars($item->config->name[0]); ?>"/><br /><br />
	<?php i18n('simple_catalog/SHORT_DESCRIPTION')?>:
	<textarea class="" cols=1 rows=1 id="post-short-content" name="post-short-description"><?php echo $item->config->description_short; ?></textarea><br />
	<?php i18n('simple_catalog/DESCRIPTION')?>:
	<textarea class="editbox" cols=1 rows=1 id="post-content" name="post-description"><?php echo $item->config->description; ?></textarea><br />
	<?php i18n('simple_catalog/URL')?>:<br />
	<input type="text" class="text" id="post-url" name="post-url" value="<?php echo $item->config->url; ?>"/><br /><br />
	<?php i18n('simple_catalog/SORT')?>:<br />
	<input style="width: 20px;" type="text" class="text" id="post-name" name="post-sort" value="<?php echo htmlspecialchars($item->config->sort); ?>"/><br /><br />
	<div id="images">
		<table>
	<?php
		$count = 1;
		foreach ($item->photos->photo as $photo)
		{
			echo '
			<tr>
				<td>
					<img src="/data/uploads/'.$photo->url_preview.'"/>
				</td>
			';
			
			if ($count != 1) 
			{
				echo '<td><a href="'.$_SERVER['REQUEST_URI'].'&move_image='.$photo->url.'&image_position=1">[сделать главной]</a></td>';
			}
			else
			{
				echo '<td></td>';
			}
			
			echo '<td>
					<a href="'.$_SERVER['REQUEST_URI'].'&delete_image='.$photo->url.'">[X]</a>
				</td>
			</tr>';
			$count++;
		}
	?>
		</table>
	</div>
	<div style="clear: both;"></div>
	<br />
	Добавить изображение:<br />
	
		<input type="file" name="image" /><br /><br />
		<input type="submit" name="save" value="<?php i18n('simple_catalog/SAVE'); ?>" class="submit"/>

</form>

<?php 
	if (defined('GSEDITORHEIGHT')) { $EDHEIGHT = GSEDITORHEIGHT .'px'; } else {	$EDHEIGHT = '500px'; }
	if (defined('GSEDITORLANG')) { $EDLANG = GSEDITORLANG; } else {	$EDLANG = i18n_r('CKEDITOR_LANG'); }
	if (defined('GSEDITORTOOL')) { $EDTOOL = GSEDITORTOOL; } else {	$EDTOOL = 'basic'; }
	if (defined('GSEDITOROPTIONS') && trim(GSEDITOROPTIONS)!="") { $EDOPTIONS = ", ".GSEDITOROPTIONS; } else {	$EDOPTIONS = ''; }
	
	if ($EDTOOL == 'advanced') {
		$toolbar = "
				['Bold', 'Italic', 'Underline', 'NumberedList', 'BulletedList', 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock', 'Table', 'TextColor', 'BGColor', 'Link', 'Unlink', 'Image', 'RemoveFormat', 'Source'],
	  '/',
	  ['Styles','Format','Font','FontSize']
  ";
	} elseif ($EDTOOL == 'basic') {
		$toolbar = "['Bold', 'Italic', 'Underline', 'NumberedList', 'BulletedList', 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock', 'Link', 'Unlink', 'Image', 'RemoveFormat', 'Source']";
	} else {
		$toolbar = GSEDITORTOOL;
	}
?>


<script type="text/javascript" src="template/js/ckeditor/ckeditor.js"></script>

<script type="text/javascript">

var editor = CKEDITOR.replace( 'post-description', {
skin : 'getsimple',
forcePasteAsPlainText : true,
language : '<?php echo $EDLANG; ?>',
defaultLanguage : 'ru',
<?php if (file_exists(GSTHEMESPATH .$TEMPLATE."/editor.css")) { 
	$fullpath = suggest_site_path();
?>
contentsCss: '<?php echo $fullpath; ?>theme/<?php echo $TEMPLATE; ?>/editor.css',
<?php } ?>
entities : false,
uiColor : '#FFFFFF',
height: '<?php echo $EDHEIGHT; ?>',
baseHref : '<?php echo $SITEURL; ?>',
toolbar : 
[
<?php echo $toolbar; ?>
]
<?php echo $EDOPTIONS; ?>,
		tabSpaces:10,
filebrowserBrowseUrl : 'filebrowser.php?type=all',
		filebrowserImageBrowseUrl : 'filebrowser.php?type=images',
filebrowserWindowWidth : '730',
filebrowserWindowHeight : '500'
});
CKEDITOR.instances["post-description"].on("instanceReady", InstanceReadyEvent);
	var yourText = $('#post-description').val();
	function InstanceReadyEvent() {
	  this.document.on("keyup", function () {
			warnme = true;
		  yourText = CKEDITOR.instances["post-description"].getData();
		  $('#cancel-updates').show();
	  });
	}

</script>


<script type="text/javascript">

var editor = CKEDITOR.replace( 'post-short-content', {
skin : 'getsimple',
forcePasteAsPlainText : true,
language : '<?php echo $EDLANG; ?>',
defaultLanguage : 'ru',
<?php if (file_exists(GSTHEMESPATH .$TEMPLATE."/editor.css")) { 
	$fullpath = suggest_site_path();
?>
contentsCss: '<?php echo $fullpath; ?>theme/<?php echo $TEMPLATE; ?>/editor.css',
<?php } ?>
entities : false,
uiColor : '#FFFFFF',
height: '150px',
baseHref : '<?php echo $SITEURL; ?>',
toolbar : 
[
<?php echo $toolbar; ?>
]
<?php echo $EDOPTIONS; ?>,
		tabSpaces:10,
filebrowserBrowseUrl : 'filebrowser.php?type=all',
		filebrowserImageBrowseUrl : 'filebrowser.php?type=images',
filebrowserWindowWidth : '730',
filebrowserWindowHeight : '500'
});
CKEDITOR.instances["post-short-content"].on("instanceReady", InstanceReadyEvent);
	var yourText = $('#post-short-content').val();
	function InstanceReadyEvent() {
	  this.document.on("keyup", function () {
			warnme = true;
		  yourText = CKEDITOR.instances["post-short-content"].getData();
		  $('#cancel-updates').show();
	  });
	}

</script>

<?php
	# CKEditor setup functions
	ckeditor_add_page_link();
	exec_action('html-editor-init'); 
?>


