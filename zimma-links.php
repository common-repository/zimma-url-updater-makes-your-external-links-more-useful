<?php

/*
Plugin Name: Zim.ma Links
Plugin URI: http://zim.ma/wordpress-plugin
Description: Update your links to run through the zim.ma service. Zim.ma is a free service that makes your links more useful.
Author: Zimma Web Development
Version: 0.01
License: GPL
Author URI: http://www.zimma.co.uk
Min WP Version: 1.0
Max WP Version: 2.9.2
*/

function upadte_links()
{
	$zimma_registration_id = get_option('zimma_registration_id');
	$zimma_exclude_urls = array_map('trim', preg_split('/,/', get_option('zimma_exclude_urls')));
	$zimma_exclude_urls[] = $_SERVER["SERVER_NAME"];
	if($zimma_registration_id*1 == 0) $zimma_registration_id = 1;
	?>
	<script type="text/javascript">
		function upadteLinks()
		{
			for (var i=0;i < document.links.length;i++)
			{
				var link_url = document.links[i].href;
				if(link_url.substring(0,4).toLowerCase() == "http" && link_url.substring(7,6).toLowerCase() != "zim.ma")
				{
					var change_this_url = true;
					<?php
					//check it's allowed
					foreach($zimma_exclude_urls as $zimma_exclude_url)
					{
						?>
						if(change_this_url && link_url.toLowerCase().indexOf("<?php echo strtolower($zimma_exclude_url); ?>") > 0)
						{
							change_this_url = false;
						}
						<?php
					}
					?>
					if(change_this_url)
					{
						//modify the link
						document.links[i].href = 'http://zim.ma/u/<?php echo $zimma_registration_id; ?>/' + link_url.substring(7);
					}
				}
			}
		}
		window.onload=upadteLinks;
	</script>
	<?php
}

add_action ('wp_head', 'upadte_links');

add_action('admin_menu', 'zimma_links_create_menu');

function zimma_links_create_menu() {

	//create new top-level menu
	add_menu_page('Zim.ma Plugin Settings', 'Zim.ma Settings', 'administrator', __FILE__, 'zimma_settings_page');

	//call register settings function
	add_action( 'admin_init', 'zimma_register_mysettings' );
}

function zimma_register_mysettings() {
	//register our settings
	if(function_exists('register_setting'))
	{
		register_setting('zimma-settings-group', 'zimma_registration_id');
		register_setting('zimma-settings-group', 'zimma_exclude_urls');
	}
}

function zimma_settings_page() {
?>
<div class="wrap">
<h2>Zim.ma Plugin</h2>

<form method="post" action="options.php">
	<?php if(!function_exists('settings_fields')) wp_nonce_field('update-options'); ?>
    <?php if(function_exists('settings_fields')) settings_fields('zimma-settings-group'); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Zim.ma Registration ID</th>
        <td><input type="text" name="zimma_registration_id" value="<?php echo get_option('zimma_registration_id'); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Exclude URLs</th>
        <td><input type="text" name="zimma_exclude_urls" value="<?php echo get_option('zimma_exclude_urls'); ?>" />
		<p><em>Comma separate any urls you wish to exclude. e.g. google.com, amazon.com. Any relative links will be ignored as standard.</em></p></td>
        </tr>
    </table>

    <p class="submit">
		<?php
		if(!function_exists('settings_fields'))
		{
			?>
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="zimma_registration_id, zimma_exclude_urls" />
			<?php
		}
		?>
	    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>
<?php }
?>