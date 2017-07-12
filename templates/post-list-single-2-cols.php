<?php
	$post = isset($params["post"]) ? $params["post"] : null;
	$show_meta = isset($params["show_meta"]) ? $params["show_meta"] : true;
	$meta_fields = isset($params["meta_fields"]) ? $params["meta_fields"] : array("date");
	$max_num_topics = isset($params["max_num_topics"]) ? $params["max_num_topics"] : null;
	$max_num_tags = isset($params["max_num_tags"]) ? $params["max_num_tags"] : null;
	$show_thumbnail = isset($params["show_thumbnail"]) ? $params["show_thumbnail"] : true;
	$show_excerpt = isset($params["show_excerpt"]) ? $params["show_excerpt"] : false;
	$show_source_meta = isset($params["show_source_meta"]) ? $params["show_source_meta"] : false;
	$show_solr_meta = isset($params["show_solr_meta"]) ? $params["show_solr_meta"] : false;
	$highlight_words_query = isset($params["highlight_words_query"]) ? $params["highlight_words_query"] : null;
	$solr_search_result = isset($params["solr_search_result"]) ? $params["solr_search_result"] : null;
	$show_post_type = isset($params["show_post_type"]) ? $params["show_post_type"] : false;
	$show_summary_translated_by_odc_team = isset($params["show_summary_translated_by_odc_team"]) ? $params["show_summary_translated_by_odc_team"] : false;
	$header_tag = isset($params["header_tag"]) ? $params["header_tag"] : false;
	$order = isset($params["order"]) ? $params["order"] : "metadata_created";
	$extra_classes = isset($params["extra_classes"]) ? $params["extra_classes"] : null;
?>

<div class="eight columns<?php if (isset($extra_classes)): echo " ". $extra_classes; endif; ?>">
	<div class="post-list-item single_result_container">
		<?php
			$localized_title = apply_filters('translate_text', $post->post_title, odm_language_manager()->get_current_language());
			$localized_title = !empty($localized_title) ? $localized_title : strip_shortcodes($post->post_title); ?>
		<?php if ($header_tag): ?>
      <?php
        $link = isset($post->dataset_link) ? $post->dataset_link : get_permalink($post->ID); ?>
			<h5>
				<a class="item-title" href="<?php echo $link; ?>" title="<?php echo $localized_title; ?>">
					<?php
						if ($show_post_type):
							$post_type_name = get_post_type($post->ID); ?>
							<i class="<?php echo get_post_type_icon_class($post_type_name); ?>"></i>
						<?php
						endif; ?>
					<?php echo $localized_title; ?>
				</a>
			</h5>
		<?php else: ?>
			<h5>
				<a class="item-title" href="<?php echo get_permalink($post->ID); ?>" title="<?php echo $localized_title; ?>">
				<?php
					if ($show_post_type):
						$post_type_name = get_post_type($post->ID); ?>
						<i class="<?php echo get_post_type_icon_class($post_type_name); ?>"></i>
					<?php
					endif; ?>

					<?php echo $localized_title; ?>
				</a>
			</h5>
		<?php endif; ?>

		<?php
			if ($show_meta):
				echo_post_meta($post,$meta_fields,$order,$max_num_topics,$max_num_tags);
			endif; ?>

		<section class="content item-content section-content">
			<?php
			if ($show_thumbnail):
				$thumb_src = odm_get_thumbnail($post->ID, false, array( 80, 'auto'));
				if (isset($thumb_src)):
					echo $thumb_src;
				else:
					echo_documents_cover($post->ID);
				endif;
			endif;
			?>
			<?php
				if ($show_excerpt || $show_source_meta): ?>
				<?php
					if ($show_excerpt): ?>
					<div class="post-excerpt">
						<?php
							$excerpt = odm_excerpt($post);
							if (isset($highlight_words_query) && function_exists('wp_odm_solr_highlight_search_words')):
								$excerpt = wp_odm_solr_highlight_search_words($highlight_words_query,$excerpt);
							endif;
							echo $excerpt;?>
					</div>
					<?php
					endif;
					if ($show_source_meta):
						odm_echo_extras();
					endif;

					echo_downloaded_documents();
					
				endif;
				?>
		</section>

		<?php
			if ($show_solr_meta && isset($solr_search_result)):
				odm_echo_solr_meta($solr_search_result);
			endif; ?>

	</div>
</div>
