<?php

if( !function_exists('add_action') )
	die("access denied.");
	
function powerpress_admin_taxonomyfeeds_columns($data=array())
{
	$data['name'] = __('Term Name', 'powerpress');
	$data['taxonomy'] = __('Taxonomy', 'powerpress');
	$data['feed-slug'] = __('Slug', 'powerpress');
	$data['url'] = __('Feed URL', 'powerpress');
	return $data;
}

add_filter('manage_powerpressadmin_taxonomyfeeds_columns', 'powerpress_admin_taxonomyfeeds_columns');

function powerpress_admin_taxonomyfeeds()
{
	$General = powerpress_get_settings('powerpress_general');

?>
<h2><?php echo __('Taxonomy Podcasting', 'powerpress'); ?></h2>
<p>
	<?php echo __('Taxonomy Podcasting adds custom podcast settings to specific taxonomies feeds.', 'powerpress'); ?>
</p>
<style type="text/css">
.column-url {
	width: 40%;
}
.column-name {
	width: 30%;
}
.column-feed-slug {
	width: 15%;
}
.column-episode-count {
	width: 15%;
}
.category-list {
	width: 100%;
}
</style>
<div id="col-container">

<div id="col-right">
<table class="widefat fixed" cellspacing="0">
	<thead>
	<tr>
<?php 
	if( function_exists('print_column_headers') )
	{
		print_column_headers('powerpressadmin_taxonomyfeeds');
	}
	else
	{
	?>
	<th scope="col" id="name" class="manage-column column-name"><?php echo __('Term Name', 'powerpress'); ?></th>
	<th scope="col" id="feed-slug" class="manage-column column-feed-slug"><?php echo __('Slug', 'powerpress'); ?></th>
	<th scope="col" id="url" class="manage-column column-url"><?php echo __('Feed URL', 'powerpress'); ?></th>
	<?php
	}
?>
	</tr>
	</thead>

	<tfoot>
	<tr>
<?php
		print_column_headers('powerpressadmin_taxonomyfeeds', false);
?>
	</tr>
	</tfoot>
	<tbody>
<?php
	
	$PowerPressTaxonomies = get_option('powerpress_taxonomy_podcasting');
	if( empty($PowerPressTaxonomies) )
		$PowerPressTaxonomies = array();
	

	$count = 0;
	while( list($tt_id, $null) = each($PowerPressTaxonomies) )
	{
		$taxonomy_type = '';
		$term_ID = '';
		
		global $wpdb;
		$term_info = $wpdb->get_results("SELECT term_id, taxonomy FROM $wpdb->term_taxonomy WHERE term_taxonomy_id = $tt_id",  ARRAY_A);
		if( !empty( $term_info[0]['term_id']) ) {
			$term_ID = $term_info[0]['term_id'];
			$taxonomy_type = $term_info[0]['taxonomy'];
		}
		else
		{
			continue; // we didn't find this taxonomy relationship
		}
	 //	var_dump($term_info);
		
		//$category = get_category_to_edit($cat_ID);
		$term_object = get_term( $term_ID,$taxonomy_type, OBJECT, 'edit');
		
		
		$columns = powerpress_admin_taxonomyfeeds_columns();
		$hidden = array();

		if( $count % 2 == 0 )
			echo '<tr valign="middle" class="alternate">';
		else
			echo '<tr valign="middle">';
			
		$edit_link = admin_url('admin.php?page=powerpress/powerpressadmin_taxonomyfeeds.php&amp;action=powerpress-edittaxonomyfeed&amp;term='. $term_ID .'&taxonomy='.$taxonomy_type) ;
		
		$feed_title = $term_object->name;
		$url = get_term_feed_link($term_ID, $taxonomy_type, 'rss2');
		$short_url = str_replace('http://', '', $url);
		$short_url = str_replace('www.', '', $short_url);
		if (strlen($short_url) > 35)
			$short_url = substr($short_url, 0, 32).'...';

		foreach($columns as $column_name=>$column_display_name) {
			$class = "class=\"column-$column_name\"";
			
			
			
			//$short_url = '';
			
			switch($column_name) {
				case 'feed-slug': {
					
					echo "<td $class>{$term_object->slug}";
					echo "</td>";
					
				}; break;
				case 'name': {

					echo '<td '.$class.'><strong><a class="row-title" href="'.$edit_link.'" title="' . esc_attr(sprintf(__('Edit "%s"', 'powerpress'), $feed_title)) . '">'.$feed_title.'</a></strong><br />';
					$actions = array();
					$actions['edit'] = '<a href="' . $edit_link . '">' . __('Edit', 'powerpress') . '</a>';
					$actions['remove'] = "<a class='submitdelete' href='". admin_url() . wp_nonce_url("admin.php?page=powerpress/powerpressadmin_taxonomyfeeds.php&amp;action=powerpress-delete-taxonomy-feed&amp;ttid=$tt_id", 'powerpress-delete-taxonomy-feed-' . $tt_id) . "' onclick=\"if ( confirm('" . esc_js(sprintf( __("You are about to remove podcast settings for taxonomy '%s'\n  'Cancel' to stop, 'OK' to delete.", 'powerpress'), $feed_title )) . "') ) { return true;}return false;\">" . __('Remove', 'powerpress') . "</a>";
					$action_count = count($actions);
					$i = 0;
					echo '<div class="row-actions">';
					foreach ( $actions as $action => $linkaction ) {
						++$i;
						( $i == $action_count ) ? $sep = '' : $sep = ' | ';
						echo '<span class="'.$action.'">'.$linkaction.$sep .'</span>';
					}
					echo '</div>';
					echo '</td>';
					
				};	break;
					
				case 'url': {
				
					echo "<td $class><a href='$url' title='". esc_attr(sprintf(__('Visit %s', 'powerpress'), $feed_title))."' target=\"_blank\">$short_url</a>";
						echo '<div class="row-actions">';
							echo '<span class="'.$action .'"><a href="http://www.feedvalidator.org/check.cgi?url='. urlencode( str_replace('&amp;', '&', $url) ) .'" target="_blank">' . __('Validate Feed', 'powerpress') . '</a></span>';
						echo '</div>';
					echo "</td>";
					
				};	break;
					
				case 'episode-count': {
				
					echo "<td $class>$episode_total";
					echo "</td>";
					
				}; break;
				case 'taxonomy': {
					echo "<td $class>$taxonomy_type";
					echo "</td>";
				}; break;
				default: {
				
				};	break;
			}
		}
		echo "\n    </tr>\n";
		$count++;
	}
?>
	</tbody>
</table>
</div> <!-- col-right -->

<div id="col-left">
<div class="col-wrap">
<div class="form-wrap">

<pre>
<?php
$taxonomies=get_taxonomies('','names'); 
foreach ($taxonomies as $taxonomy ) {
  echo '<p>'. $taxonomy. '</p>';
}
?>
</pre>

<?php

	if( !empty( $_GET['taxonomy']) && $_GET['taxonomy'] != '' )
	{
		$taxonomy = $_GET['taxonomy'];
?>
<h3><?php echo __('Add Podcast Settings to existing Category Feed', 'powerpress'); ?></h3>
<input type="hidden" name="action" value="powerpress-addtaxonomyfeed" />
<input type="hidden" name="taxonomy" value="<?php echo htmlspecialchars($taxonomy); ?>" />
<?php
	//wp_original_referer_field(true, 'previous'); 
	wp_nonce_field('powerpress-add-taxonomy-feed');
?>

<?php 

	
?>

<div class="form-field form-required">
	<label for="feed_name"><?php echo __('Taxonomy Term', 'powerpress') ?></label>
<?php
	
	wp_dropdown_categories(  array('class'=>'category-list', 'show_option_none'=>__('Select Term', 'powerpress'), 'orderby'=>'name', 'hide_empty'=>0, 'hierarchical'=>1, 'name'=>'term', 'id'=>'term_id', 'taxonomy'=>$taxonomy ) );
?>
	
    
</div>

<p class="submit"><input type="submit" class="button" name="submit" value="<?php echo __('Add Podcast Settings to Term', 'powerpress'); ?>" /></p>
<?php
	}
?>

</div>
</div>

</div> <!-- col-left -->

</div> <!-- col-container -->

<?php
	}
?>