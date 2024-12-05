<?php

if (!defined('ABSPATH')) exit; // just in case


function kpg_no_rc_img_control_2()  {

	if(!current_user_can('manage_options')) {
		die('Access Denied');
	}
	$options=kpg_nrci_get_options();
	extract($options);
	// check for update submit
	if (array_key_exists('kpg_nrci_update',$_POST)&&wp_verify_nonce($_POST['kpg_nrci_update'],'kpg_nrci_update')) { 
		// need to update replace
		if (array_key_exists('kpg_drag',$_POST)) {
			$drag=stripslashes($_POST['kpg_drag']);
		} else {
			$drag='N';
		}
		if (array_key_exists('kpg_touch',$_POST)) {
			$touch=stripslashes($_POST['kpg_touch']);
		} else {
			$touch='N';
		}
		if (array_key_exists('kpg_gesture',$_POST)) {
			$gesture=stripslashes($_POST['kpg_gesture']);
		} else {
			$gesture='N';
		}
		if (array_key_exists('kpg_allowforlogged',$_POST)) {
			$allowforlogged=stripslashes($_POST['kpg_allowforlogged']);
		} else {
			$allowforlogged='N';
		}
		if (array_key_exists('kpg_ios',$_POST)) {
			$ios=stripslashes($_POST['kpg_ios']);
		} else {
			$ios='N';
		}
		if (array_key_exists('kpg_admin',$_POST)) {
			$admin=stripslashes($_POST['kpg_admin']);
		} else {
			$admin='N';
		}
		// update options
		$options['drag']=$drag;
		$options['touch']=$touch;
		$options['gesture']=$gesture;
		$options['allowforlogged']=$allowforlogged;
		$options['ios']=$ios;
		$options['admin']=$admin;
		update_option('kpg_no_right_click_image',$options);

	}
	$nonce=wp_create_nonce('kpg_nrci_update');
	
	?>

	<div class="wrap" style="position:relative;">
	<h2>No Right Click Images Plugin</h2>
	<h4>The No Right Click Images Plugin is installed and working correctly.</h4>
	<div style="float:left;width:calc(100% - 245px);">
	<p>&nbsp;</p>
	<form method="post" action="">
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="kpg_nrci_update" value="<?php echo esc_attr($nonce);?>" />
	<fieldset style="border:thin solid black;padding:6px;width:100%;">
	<legend> <span style="font-weight:bold;font-size:1.5em">Options</span> </legend>
	Allow Right Click for Logged Users:
	<input type="checkbox" <?php if ($allowforlogged=='Y') {echo 'checked="true"';} ?> value="Y" name="kpg_allowforlogged" />
	You may wish to allow logged in users to copy images. You can do this by checking this box. <br />
	Disable Dragging of images:
	<input type="checkbox" <?php if ($drag=='Y') {echo 'checked="true"';} ?> value="Y" name="kpg_drag" />
	This will prevent images from being dragged to the desktop or image software <br />
	Disable Touch events:
	<input type="checkbox" <?php if ($touch=='Y') {echo 'checked="true"';} ?> value="Y" name="kpg_touch" />
	Prevents touch events on images, but if images are used as links or buttons this may cause problems. <br />
	Disable Gesture events:
	<input type="checkbox" <?php if ($gesture=='Y') {echo 'checked="true"';} ?> value="Y" name="kpg_gesture" />
	Prevents some gestures. If you site uses image gestures for images this may cause problems. <br />
	Disable context menu on Apple devices:
	<input type="checkbox" <?php if ($ios=='Y') {echo 'checked="true"';} ?> value="Y" name="kpg_ios" />
	Adds a style to images on Apple IOS devices to prevent the context menu<br />
	Admin can always right click images:
	<input type="checkbox" <?php if ($admin=='Y') {echo 'checked="true"';} ?> value="Y" name="kpg_admin" />
	Admins can always right click images even if logged in users cannot.<br />
	</fieldset>
	<br>
	<input type="submit" name="Submit" class="button-primary" value="<?php echo("Save Changes"); ?>" />
	</form>
	<p>This plugin installs a little javascript in your pages. When your page finishes loading, the javascript sets properties the images to supress the context menu. This prevents casual users from using the copy function to grab an image.</p>
	<p>There are many ways to bypass this plugin and it is impossible to prevent a determined and resourceful user from stealing images, but this plugin will prevent casual users from glomming your images.</p>
	<p>The context menu is disabled on images and simple elements with background images, but will not work in some cases depending on which element receives the mouse click.</p>
	<p>If you have uploaded your images to WordPress so that the images from the gallery can be opened in their own window, then this plugin will not work on the clicked image.</p>
	<br>
	<fieldset style="border:thin solid black;padding:6px;width:100%;">
	<legend> <span style="font-weight:bold;font-size:1.5em">Support the Programmer</span> </legend>
	
	<p>If you wish to support my programming, anything you can do would be appreciated.</p>
<p>There are two ways to do this.</p>
<p>First, you can go to the the plugin pages on WordPress.org and click on a few stars for this plugin rating, and check off the “it works” information. You might, if so moved, say a few nice words under reviews.</p>
<p>Second, If you feel that you’d like to encourage me, please buy one of my books and give it a good review. I worked hard on these books, and they are worth reading.
<a href="https://amzn.to/42BjwXv" target="_new">My Author Page at Amazon</a> (I would prefer this. I like knowing people might be reading my books.) Try "Murder in Luna City", a financial thriller that takes place on the Moon.

	</fieldset>
	</div>
	</div>
	<?php } 


// end of module

?>
