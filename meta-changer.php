<?php
/**
 * @package Meta_Changer
 * @version 1.0
 */
/*
Plugin Name: Meta Changer
Plugin URI: http://wordpress.org/plugins/meta-changer/
Description: This is a plugin that allows you to change the meta description of any page you want on your site by simply specifying the page name.
Author: Tapha Ngum
Version: 1.0
Author URI: http://taphangum.com/
*/

//set a page name that you want to change, with a meta description under it. We store the pagename and corresponding meta description under it. Can 'add new'.

//On page load, check the page, and if that page name matches a page name stored in the metachanger table then check of there is a meta description, if so, change the content to what is saved otherwise add the whole meta description html.

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

global $meta_description_key;

$meta_description_key = 'meta_description';

if(is_plugin_active('wordpress-seo/wp-seo.php')) {
	$meta_description_key = '_yoast_wpseo_metadesc';
}

function meta_changer_create_menu() {

	//create new top-level menu
	add_menu_page('Meta Changer Plugin Settings', 'Meta Changer Settings', 'administrator', __FILE__, 'meta_changer_settings_page');

	//call register settings function
	add_action( 'admin_init', 'register_mysettings' );
}

// create custom plugin settings menu
add_action('admin_menu', 'meta_changer_create_menu');

function register_mysettings() {
	//register our settings
	register_setting( 'mcp-settings-group', 'Page Title' );
	register_setting( 'mcp-settings-group', 'Meta Description' );
}

function get_select_pages(){

	global $wpdb;

	// Custom query to get posts *and* pages
	$results = $wpdb->get_results(
		"
		SELECT ID, post_title, post_status
		FROM $wpdb->posts
		WHERE post_type = 'post' OR post_type = 'page'
		AND post_status = 'publish' OR post_status = 'draft' OR post_status = 'pending'
		"
	);

	return $results;
}

function meta_changer_settings_page() {
?>
<div class="wrap">
<h2>Meta Changer</h2>

<form id="mcp_form_submit" action="<?php echo admin_url(); ?>/admin-ajax.php">
    <?php 
    // Get the old editable settings fields from the database and add them here.
    //settings_fields( 'mcp-settings-group' );
     ?>
    <?php $spages = get_select_pages(); ?>
    <?php //do_settings_sections( 'mcp-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Page Title</th>
        <td>
        <select id="select_dropdown" name="page_title">
        <?php foreach ($spages as $spage) {?>
        <?php //check to see if it hasnt already been edited. make function to check ?>
		  <option value="<?php echo $spage->ID; ?>"><?php echo $spage->post_title; ?></option>
		<?php } ?>  
		</select>
        </td>
        </tr>
         
        <tr valign="top">
        <th scope="row">Meta Description</th></td>
        <td><textarea id='metadesc' placeholder="(Max 160 Chars.)" rows="4" cols="50" name="meta_description" value=""></textarea>
        </tr>
    </table>
    
    <?php submit_button(); ?>

</form>

<p id="meta_description_success" style="display:none">Meta description updated successfully.</p>
</div>
<?php }

//Add Javascript
function mcp_scripts_method() {
	wp_enqueue_script(
		'mcp-custom',
		plugins_url().'/meta-changer/js/mcp-custom.js',
		array( 'jquery' )
	);
}

add_action( 'admin_enqueue_scripts', 'mcp_scripts_method' );

add_action( 'wp_ajax_check_for_meta_desc', 'check_for_meta_desc' );

function check_for_meta_desc() {

	global $meta_description_key;

	$mcp_page_id = intval( $_POST['page_id'] );

	//Check if metadescription exists, if it does, return it, if not, return false.
	$url = get_permalink( $mcp_page_id );

	$meta = get_post_meta($mcp_page_id);

	if(isset($meta[$meta_description_key][0])) {
		echo $meta[$meta_description_key][0];
	} else {
		return false;
	}

	die();
}

add_action('wp_ajax_mcp_insert_custom_table', 'mcp_insert_custom_table');
add_action('wp_ajax_nopriv_mcp_insert_custom_table', 'mcp_insert_custom_table');

function mcp_insert_custom_table() {
	$post_id = $_POST['post_id'];
	$desc = $_POST['desc_text'];
	$res = mcp_insert_custom_table_do($post_id, $desc);
}

function mcp_insert_custom_table_do($post_id, $desc)
{

	global $meta_description_key;

    $post_meta = get_post_meta($post_id);

    if(isset($post_meta[$meta_description_key][0])) {

    	// The post already has a meta description, so we need to update it
    	$result = update_post_meta($post_id, $meta_description_key, $desc);

    } else {

    	// The post doesn't have a meta description, so we need to add it
    	$result = add_post_meta($post_id, $meta_description_key, $desc);

    }

    echo $result;

}

add_action('wp_head','hook_new_meta');

function hook_new_meta()
{

	global $meta_description_key;

	global $post;
	$page_id = $post->ID;

	$meta = get_post_meta($page_id);

	if(isset($meta[$meta_description_key][0])) {
		echo '<meta name="description" content="' . $meta[$meta_description_key][0] . '">
		';
	}

}

?>