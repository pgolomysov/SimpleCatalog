<?php


global $i18n;

require_once(GSPLUGINPATH.'simple_catalog/catalog.class.php');

$action = $_GET['item'];

if ($action == 'simple_catalog_catalog_edit')
{
	$catalog = simple_catalog::getCatalog((int)$_GET['catalog_id'])->config;
}

#load config file
#if (!file_exists(GSDATAPAGESPATH.'gallery.xml')) copy(GSPLUGINPATH.'i18n_gallery/gallery.xml',GSDATAPAGESPATH.'gallery.xml');


$success = false;

//check needed folders and files
if (!simple_catalog::checkPrerequisites()) {
  $msg = i18n_r('simple_catalog/MISSING_DIR');
//if we save catalog
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
  if (!isset($msg)) {
  
	if ($action == 'simple_catalog_catalog_edit')
	{
		$result = simple_catalog::saveCatalog((int)$_GET['catalog_id'], $_POST['post-name'],$_POST['post-description'],$url);
	}
	
	if ($action == 'simple_catalog_create')
	{
		echo 123;
		$result = simple_catalog::createCatalog($_POST['post-name'],$_POST['post-description'],$url);
	}
	
    if ($result) {
      $msg = i18n_r('simple_catalog/SAVE_SUCCESS');
      $success = true;
      $gallery = return_i18n_gallery(@$_POST['post-name']); // reread
      $name = @$_POST['post-name'];
    } else {
      $msg = i18n_r('simple_catalog/SAVE_FAILURE');
    }
  }
}

/*
$settings = i18n_gallery_settings();
$w = intval(@$settings['adminthumbwidth']) > 0 ? intval($settings['adminthumbwidth']) : I18N_GALLERY_DEFAULT_THUMB_WIDTH;
$h = intval(@$settings['adminthumbheight']) > 0 ? intval($settings['adminthumbheight']) : I18N_GALLERY_DEFAULT_THUMB_HEIGHT;
$viewlink = find_url('gallery',null);
$viewlink .= (strpos($viewlink,'?') === false ? '?' : '&amp;') . 'name=' . $name;
$plugins = i18n_gallery_plugins();
$plugins = subval_sort($plugins,'name');
// default gallery type
if (!@$gallery['type']) $gallery['type'] = @$settings['type'] ? $settings['type'] : I18N_GALLERY_DEFAULT_TYPE;
*/



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




<h3 class="floated" style="float:left"><?php i18n('simple_catalog/NEW_CATALOG'); ?></h3>

<form method="post">
	<input type="text" class="text" id="post-name" name="post-name" value="<?php echo $catalog->name[0]; ?>"/><br />
	<?php i18n('simple_catalog/DESCRIPTION')?>:
	<textarea class="" cols=1 rows=1 id="post-content" name="post-description" style=""><?php echo $catalog->description[0]; ?></textarea><br />
	URL<br />
	<input type="text" class="text" id="post-url" name="post-url"  value="<?php echo $catalog->url[0]; ?>"/><br /><br />
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

<?php
	# CKEditor setup functions
	ckeditor_add_page_link();
	exec_action('html-editor-init'); 
?>


