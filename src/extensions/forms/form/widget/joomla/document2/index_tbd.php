<?php
define('DIRECTORY_SEPARATOR', DIRECTORY_SEPARATOR);
$jpath = str_replace('plugins'.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'one'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'form'.DIRECTORY_SEPARATOR.'widget'.DIRECTORY_SEPARATOR.'joomla'.DIRECTORY_SEPARATOR.'document2', '', dirname(__FILE__));
require_once($jpath.'plugins'.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'one'.DIRECTORY_SEPARATOR.'vendor'. DIRECTORY_SEPARATOR.'joomla-standalone.php');
JoomlaInitiator::initiate($jpath, false);
$mainframe = JoomlaInitiator::getMainframe();

$uribase = str_replace('plugins/system/one/lib/form/widget/joomla/document2', '', JURI::base());

require_once(dirname(__FILE__) . '/class.document2.php');
$fromPath = base64_decode($_GET['path']);
if(preg_match('{^' . addslashes(JPATH_BASE) . '}i', $fromPath) == 0)
	$fromPath = JPATH_BASE;

$docWidget = new One_Document2_Widget(JPATH_BASE, $fromPath, $_GET['widget']);
$fnf = $docWidget->getFiles();
$parent = $docWidget->getParent();
?>
<html>
<head>
<title>OneDocument Selector</title>
<style type="">
	h3
	{
		width: 550px;
		height: 15px;
		color: #555;
		line-height: 1;
		font-size: 13px;
	}
	fieldset
	{
		border:1px solid #CCCCCC;
		margin-bottom:10px;
		padding:3px;
		text-align:left;
		display: block;
		clear: both;
		margin-left: 0px;
		width: 550px;
	}

	div.list
	{
		margin-left: 0px;
		border:1px solid #CCCCCC;
		margin-bottom:10px;
		text-align:left;
		display: block;
		clear: both;
		float: left;
		width: 550px;
		height: 300px;
		overflow: auto;
		padding: 3px;
	}

	.item {
		border:1px solid #CCCCCC;
		float:left;
		margin:3px;
		position: relative;
	}

	.item a {
		color:#0B55C4;
		display:block;
		height:90px;
		width:55px;
		line-height:90px;
		overflow:hidden;
		text-align:center;
		text-decoration:none;
		vertical-align:middle;
		width:80px;
		font-size: 12px;
	}

	.item img
	{
		margin: auto;
		display: inline;
		border: none;
	}

	.item span
	{
		background-color:#EEEEEE;
		bottom:0;
		clear:both;
		display:block;
		left:0;
		line-height:100%;
		overflow:hidden;
		padding:2px 0;
		position:absolute;
		width:100%;
	}

	input
	{
		border:1px solid #CCCCCC;
		width: 400px;
		height: 20px;
		font-size: 12px;
		color: #555;
	}

	button {
		background-color:white;
		border:1px solid #CCCCCC;
		color:#0B55C4;
		font-weight:bold;
		padding:3px;
		font:11px Tahoma,Verdana,sans-serif;
		height: 20px;
		vertical-align: center;
	}
</style>
</head>
<body>
<h3><input type="text" readonly="readonly" value="<?php echo $docWidget->getCurrent(); ?>" /></h3>
<div class="list">
	<div class="item">
		<a class="folder" href="<?php echo $_SERVER[ 'PHP_SELF' ] . '?path=' . base64_encode( $parent ) .'&widget=' . $docWidget->getWidget(); ?>">
			<img alt=".." src="<?php echo $uribase; ?>media/media/images/folderup_32.png" style="margin-top: 25px;">
			<span>..</span>
		</a>
	</div>
	<?php
	foreach( $fnf[ 'folders' ] as $folder )
	{
		?>
	<div class="item">
		<a class="folder" href="<?php echo $_SERVER[ 'PHP_SELF' ] . '?path=' . base64_encode( $folder->link ) .'&widget=' . $docWidget->getWidget(); ?>">
			<img width="80" height="80" alt="<?php echo $folder->name; ?>" src="<?php echo $uribase; ?>media/media/images/folder.gif">
			<span><?php echo $folder->name; ?></span>
		</a>
	</div>
		<?php
	}
	foreach( $fnf[ 'files' ] as $file )
	{
		?>
	<div class="item">
		<a class="file" href="#" onclick="document.getElementById( 'fillIn' ).value='/<?php echo str_replace( array( JPATH_BASE, DIRECTORY_SEPARATOR ), array( '', '/' ), $file->link ); ?>'; return false;">
			<img alt="<?php echo $file->name; ?>" src="<?php echo $uribase; ?>media/media/images/con_info.png" style="margin-top: 30px;">
			<span><?php echo $file->name; ?></span>
		</a>
	</div>
		<?php
	}
?>
</div>
<fieldset>
	<input type="text" id="fillIn" name="fillIn" value="" readonly="readonly" />
	<button type="button" onclick="window.parent.document.getElementById( '<?php echo $docWidget->getWidget(); ?>' ).value = document.getElementById( 'fillIn' ).value; window.parent.SqueezeBox.close();">Insert</button>
</fieldset>
</body>
</html>