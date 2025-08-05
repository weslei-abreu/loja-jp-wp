<?php

if (!function_exists('zota_tbay_category')) {
    function zota_tbay_category($post) {
        if (!is_object($post) || !isset($post->ID)) return;
        $post_format = get_post_format($post->ID);
        $header_class = $post_format ? '' : 'border-left';
        echo '<span class="category">';
        $categories = get_the_category($post->ID);
        $total = count($categories);
        foreach ($categories as $index => $category) {
            $comma = ($index < $total - 1) ? ', ' : '';
            echo '<a href="' . esc_url(get_category_link($category->term_id)) . '" class="categories-name"><i class="fa fa-bar-chart"></i>' . esc_html($category->name) . $comma . '</a>';
        }
        echo '</span>';
    }
}

if (!function_exists('zota_tbay_full_top_meta')) {
    function zota_tbay_full_top_meta($post) {
        if (!is_object($post) || !isset($post->ID)) return;
        $post_format = get_post_format($post->ID);
        $header_class = $post_format ? '' : 'border-left';
        echo '<header class="entry-header-top ' . esc_attr($header_class) . '">';
        if (!is_single()) {
            the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>');
        }
        $id = get_the_author_meta('ID', $post->post_author);
        echo '<span class="entry-profile"><span class="col"><span class="entry-author-link"><strong>' . esc_html__('By:', 'zota') . '</strong><span class="author vcard"><a class="url fn n" href="' . esc_url(get_author_posts_url($id)) . '" rel="author">' . get_the_author_meta('display_name', $post->post_author) . '</a></span></span><span class="entry-date"><strong>' . esc_html__('Posted: ', 'zota') . '</strong>' . esc_html(get_the_date('M jS, Y', $post->ID)) . '</span></span></span>';
        echo '<span class="entry-categories"><strong>' . esc_html__('In:', 'zota') . '</strong> ';
        $categories = get_the_category($post->ID);
        $total = count($categories);
        foreach ($categories as $index => $category) {
            $comma = ($index < $total - 1) ? ', ' : '';
            echo '<a href="' . esc_url(get_category_link($category->term_id)) . '" class="categories-name">' . esc_html($category->name) . $comma . '</a>';
        }
        echo '</span>';
        if (!is_search() && !post_password_required() && (comments_open() || get_comments_number())) {
            echo '<span class="entry-comments-link">';
            comments_popup_link('0', '1', '%');
            echo '</span>';
        }
        echo '</header>';
    }
}

if (!function_exists('zota_tbay_post_tags')) {
    function zota_tbay_post_tags() {
        $posttags = get_the_tags();
        if ($posttags) {
            echo '<div class="tagcloud"><span class="meta-title">' . esc_html__('Tags: ', 'zota') . '</span>';
            $total = count($posttags);
            foreach ($posttags as $index => $tag) {
                $comma = ($index < $total - 1) ? ' ' : '';
                echo '<a href="' . esc_url(get_tag_link($tag->term_id)) . '" class="tag-item">' . esc_html($tag->name) . $comma . '</a>';
            }
            echo '</div>';
        }
    }
    add_action('zota_tbay_post_tag_socials', 'zota_tbay_post_tags', 10);
}

if (!function_exists('zota_custom_share_code')) {
    function zota_custom_share_code($title, $link, $media) {
        $enable_share = zota_tbay_get_config('enable_code_share', true);
        if (!$enable_share || (!is_singular('post') && !is_singular('product'))) {
            return;
        }
        $socials = zota_tbay_get_config('sortable_sharing', []);
        if (empty($socials)) return;
        $socials_html = '';
        foreach ($socials as $key => $value) {
            $socials_html .= zota_get_social_html($key, $value, $title, $link, $media);
        }
        if ($socials_html) {
            $socials_html = apply_filters('zota_addons_share_link_socials', $socials_html);
            printf('<div class="zota-social-links">%s</div>', $socials_html);
        }
    }
}

if (!function_exists('zota_tbay_post_info_author')) {
    function zota_tbay_post_info_author()
    {
        $author_id = zota_tbay_get_id_author_post();

        if (defined('TBAY_ELEMENTOR_ACTIVED') && TBAY_ELEMENTOR_ACTIVED) {
            ?>
		<div class="author-info">
			<div class="avarta">
				<?php echo get_avatar($author_id, 90); ?>
			</div>
			<div class="author-meta">
				<div class="content">
					<h4 class="name"><?php echo get_the_author(); ?></h4>
					<p><?php the_author_meta('description', $author_id); ?></p>
				</div>
				<?php
                    printf(
                        '<a href="%1$s" title="%2$s" rel="author" class="author-link" >%3$s</a>',
                        esc_url(get_author_posts_url($author_id, get_the_author())),
                        /* translators: %s: Author's display name. */
                        esc_attr(get_the_author()),
                        esc_html__('All Author Posts', 'zota')
                    ); ?>
			</div>
		</div>
		<?php
        }
    }
    add_action('zota_tbay_post_bottom', 'zota_tbay_post_info_author', 20);
}

if (!function_exists('zota_tbay_post_share_box')) {
    function zota_tbay_post_share_box()
    {
        if (!zota_tbay_get_config('enable_code_share', false) || !zota_tbay_get_config('show_blog_social_share', false)) {
            return;
        }

        if (zota_tbay_get_config('select_share_type') == 'custom') {
            $image = get_the_post_thumbnail_url(get_the_ID(), 'full');
            zota_custom_share_code(get_the_title(), get_permalink(), $image);
        } else {
            ?>
  			 <div class="tbay-post-share">
              	<div class="addthis_inline_share_toolbox"></div>
            </div>
  			<?php
        }
    }
    add_action('zota_tbay_post_tag_socials', 'zota_tbay_post_share_box', 10);
}

if (!function_exists('zota_tbay_post_format_link_helper')) {
    function zota_tbay_post_format_link_helper($content = null, $title = null, $post = null)
    {
        if (!$content) {
            $post = get_post($post);
            $title = $post->post_title;
            $content = $post->post_content;
        }
        $link = zota_tbay_get_first_url_from_string($content);
        if (!empty($link)) {
            $title = '<a href="'.esc_url($link).'" rel="bookmark">'.$title.'</a>';
            $content = str_replace($link, '', $content);
        } else {
            $pattern = '/^\<a[^>](.*?)>(.*?)<\/a>/i';
            preg_match($pattern, $content, $link);
            if (!empty($link[0]) && !empty($link[2])) {
                $title = $link[0];
                $content = str_replace($link[0], '', $content);
            } elseif (!empty($link[0]) && !empty($link[1])) {
                $atts = shortcode_parse_atts($link[1]);
                $target = (!empty($atts['target'])) ? $atts['target'] : '_self';
                $title = (!empty($atts['title'])) ? $atts['title'] : $title;
                $title = '<a href="'.esc_url($atts['href']).'" rel="bookmark" target="'.$target.'">'.$title.'</a>';
                $content = str_replace($link[0], '', $content);
            } else {
                $title = '<a href="'.esc_url(get_permalink()).'" rel="bookmark">'.$title.'</a>';
            }
        }
        $out['title'] = '<h2 class="entry-title">'.$title.'</h2>';
        $out['content'] = $content;

        return $out;
    }
}

if (!function_exists('zota_tbay_breadcrumbs')) {
    function zota_tbay_breadcrumbs()
    {
        $delimiter = ' / ';
        $home = esc_html__('Home', 'zota');
        $before = '<li class="active">';
        $after = '</li>';
        $title = '';
        if (!is_front_page() || is_paged()) {
            echo '<ol class="breadcrumb">';

            global $post;
            $homeLink = esc_url(home_url());
            echo '<li><a href="'.esc_url($homeLink).'" class="active">'.esc_html($home).'</a> '.esc_html($delimiter).'</li> ';

            if (is_category()) {
                global $wp_query;
                $cat_obj = $wp_query->get_queried_object();
                $thisCat = $cat_obj->term_id;
                $thisCat = get_category($thisCat);
                $parentCat = get_category($thisCat->parent);
                if ($thisCat->parent != 0) {
                    echo get_category_parents($parentCat, true, ' '.$delimiter.' ');
                }
                echo trim($before).esc_html__('blog', 'zota').$after;
            } elseif (is_day()) {
                echo '<li><a href="'.esc_url(get_year_link(get_the_time('Y'))).'">'.get_the_time('Y').'</a></li> '.esc_html($delimiter).' ';
                echo '<li><a href="'.esc_url(get_month_link(get_the_time('Y'), get_the_time('m'))).'">'.get_the_time('F').'</a></li> '.esc_html($delimiter).' ';
                echo trim($before).get_the_time('d').$after;
            } elseif (is_month()) {
                echo '<li><a href="'.esc_url(get_year_link(get_the_time('Y'))).'">'.get_the_time('Y').'</a></li> '.esc_html($delimiter).' ';
                echo trim($before).get_the_time('F').$after;
            } elseif (is_year()) {
                echo trim($before).get_the_time('Y').$after;
            } elseif (is_single() && !is_attachment()) {
                if (get_post_type() != 'post') {
                    $delimiter = '';
                    $post_type = get_post_type_object(get_post_type());
                    $slug = $post_type->rewrite;
                    echo '<li><a href="'.esc_url($homeLink).'/'.$slug['slug'].'/">'.esc_html($post_type->labels->singular_name).'</a></li> '.esc_html($delimiter).' ';
                } else {
                    $delimiter = '';
                    $cat = get_the_category();
                    if (!empty($cat[0])) {
                        echo '<li>'.get_category_parents($cat[0]->term_id, true, ' '.$delimiter.' ').'</li>';
                    }
                }
            } elseif (!is_single() && !is_page() && get_post_type() != 'post' && !is_404()) {
                $post_type = get_post_type_object(get_post_type());
                if (is_object($post_type)) {
                    echo trim($before).esc_html($post_type->labels->singular_name).$after;
                }
            } elseif (is_attachment()) {
                $parent = get_post($post->post_parent);
                $cat = get_the_category($parent->ID);
                if (isset($cat) && !empty($cat)) {
                    $cat = $cat[0];
                    echo get_category_parents($cat, true, ' '.$delimiter.' ');
                }
                echo '<li><a href="'.esc_url(get_permalink($parent->ID)).'">'.esc_html($parent->post_title).'</a></li> '.esc_html($delimiter).' ';
                echo trim($before).get_the_title().$after;
            } elseif (is_page() && !$post->post_parent) {
                echo trim($before).esc_html__('Page', 'zota').$after;
            } elseif (is_page() && $post->post_parent) {
                $parent_id = $post->post_parent;
                $breadcrumbs = [];
                while ($parent_id) {
                    $page = get_post($parent_id);
                    $breadcrumbs[] = '<li><a href="'.esc_url(get_permalink($page->ID)).'">'.get_the_title($page->ID).'</a></li>';
                    $parent_id = $page->post_parent;
                }
                $breadcrumbs = array_reverse($breadcrumbs);
                foreach ($breadcrumbs as $crumb) {
                    echo trim($crumb).' '.$delimiter.' ';
                }
                echo trim($before).esc_html__('Page', 'zota').$after;
            } elseif (is_search()) {
                echo trim($before).esc_html__('Search', 'zota').$after;
            } elseif (is_tag()) {
                echo trim($before).esc_html__('Tags', 'zota').$after;
            } elseif (is_author()) {
                global $author;
                $userdata = get_userdata($author);
                echo trim($before).esc_html__('Author', 'zota').$after;
            } elseif (is_404()) {
                echo trim($before).esc_html__('Error 404', 'zota').$after;
            } elseif (is_home()) {
                $page_for_post_title = get_the_title(get_option('page_for_posts', true));
                echo trim($before).$page_for_post_title.$after;
            }

            echo '</ol>';
        }
    }
}

if (!function_exists('zota_tbay_get_title_bottom')) {
    function zota_tbay_get_title_bottom()
    {
        global $post;

        $title_bottom = '';

        if (is_category()) {
            $title_bottom = '<h1 class="page-title">'.single_cat_title('', false).'</h1>';
        } elseif (is_tag()) {
            $title_bottom = '<h1 class="page-title">'.get_the_archive_title().'</h1>';
        } elseif (is_archive()) {
            $title_bottom = '<h1 class="page-title">'.get_the_archive_title().'</h1>';
        } elseif (is_search()) {
            $title_bottom = '<h1 class="page-title">'.esc_html__('Search results for "', 'zota').get_search_query().'"</h1>';
        } elseif (is_author()) {
            global $author;
            $userdata = get_userdata($author);
            $title_bottom = '<h1 class="page-title">'.esc_html__('Articles posted by "', 'zota').esc_html($userdata->display_name).'"</h1>';
        } elseif (!is_single() && !is_page() && get_post_type() != 'post' && !is_404()) {
            $post_type = get_post_type_object(get_post_type());
            if (is_object($post_type)) {
                $title_bottom = '<h1 class="page-title">'.$post_type->labels->singular_name.'</h1>';
            }
        } elseif ((is_page() && $post->post_parent) || (is_page() && !$post->post_parent) || is_attachment()) {
            $title_bottom = '<h1 class="page-title">'.get_the_title().'</h1>';
        } elseif (is_home()) {
            $page_for_post_title = get_the_title(get_option('page_for_posts', true));
            $title_bottom = '<h1 class="page-title">'.$page_for_post_title.'</h1>';
        }

        return $title_bottom;
    }
}

if (!function_exists('zota_tbay_render_breadcrumbs')) {
    function zota_tbay_render_breadcrumbs()
    {
        if (zota_checkout_optimized()) {
            return;
        }

        global $post;
        $show = true;
        $img = '';
        $style = [];

        $sidebar_configs = zota_tbay_get_blog_layout_configs();

        $breadcrumbs_layout = zota_tbay_get_config('blog_breadcrumb_layout', 'color');

        if (is_front_page()) {
            return;
        }

        if (isset($post->ID) && !empty(get_post_meta($post->ID, 'tbay_page_breadcrumbs_layout', 'color'))) {
            $breadcrumbs_layout = get_post_meta($post->ID, 'tbay_page_breadcrumbs_layout', 'color');
        }

        if (isset($_GET['breadcrumbs_layout'])) {
            $breadcrumbs_layout = $_GET['breadcrumbs_layout'];
        }

        if (is_singular('post')) {
            $breadcrumbs_layout = 'text';
        }

        $title_bottom = zota_tbay_get_title_bottom();

        switch ($breadcrumbs_layout) {
            case 'image':
                $breadcrumbs_class = ' breadcrumbs-image';
                break;
            case 'color':
                $breadcrumbs_class = ' breadcrumbs-color';
                break;
            case 'text':
                $breadcrumbs_class = ' breadcrumbs-text';
                break;
            default:
                $breadcrumbs_class = ' breadcrumbs-image';
        }

        if (isset($sidebar_configs['breadscrumb_class'])) {
            $breadcrumbs_class .= ' '.$sidebar_configs['breadscrumb_class'];
        }
        if (is_page() && is_object($post)) {
            $show = get_post_meta($post->ID, 'tbay_page_show_breadcrumb', true);

            if (isset($show) && $show != 'yes') {
                if (!zota_tbay_is_home_page() && !empty($title_bottom)) {
                    echo '<div class="title-not-breadcrumbs"><div class="container">'.trim($title_bottom).'</div></div>';
                }

                return '';
            }

            $bgimage = get_post_meta($post->ID, 'tbay_page_breadcrumb_image', true);
            $bgcolor = get_post_meta($post->ID, 'tbay_page_breadcrumb_color', true);
            $style = [];
            if ($bgcolor && $breadcrumbs_layout !== 'image' && $breadcrumbs_layout !== 'text') {
                $style[] = 'background-color:'.$bgcolor;
            }
            if ($bgimage && $breadcrumbs_layout !== 'color' && $breadcrumbs_layout !== 'text') {
                $img = ' <img src="'.esc_url($bgimage).'">  ';
            }
        } elseif (is_singular('post') || is_category() || is_home() || is_tag() || is_author() || is_day() || is_month() || is_year() || is_search()) {
            $show = zota_tbay_get_config('show_blog_breadcrumb', false);
            if (!$show) {
                if (!zota_tbay_is_home_page() && !empty($title_bottom)) {
                    echo '<div class="title-not-breadcrumbs"><div class="container">'.trim($title_bottom).'</div></div>';
                }

                return '';
            }
            $breadcrumb_img = zota_tbay_get_config('blog_breadcrumb_image');

            $breadcrumb_color = zota_tbay_get_config('blog_breadcrumb_color');

            $style = [];
            if ($breadcrumb_color && $breadcrumbs_layout !== 'image' && $breadcrumbs_layout !== 'text') {
                $style[] = 'background-color:'.$breadcrumb_color;
            }
            if (isset($breadcrumb_img['url']) && !empty($breadcrumb_img['url']) && $breadcrumbs_layout !== 'color' && $breadcrumbs_layout !== 'text') {
                $img = ' <img src="'.$breadcrumb_img['url'].'" alt="'.esc_attr__('breadcrumb', 'zota').'">  ';
            }
        }

        $nav = '';

        if ($breadcrumbs_layout !== 'image') {
            if (!zota_tbay_is_home_page() && zota_tbay_get_config('enable_previous_page_post', true)) {
                $nav .= '<a href="javascript:history.back()" class="zota-back-btn"><i class="tb-icon tb-icon-angle-left"></i><span class="text">'.esc_html__('Previous page', 'zota').'</span></a>';
                $breadcrumbs_class .= ' active-nav-right';
            }
            if (!zota_tbay_is_home_page() && isset($post->ID) && !empty(get_the_title($post->ID) && is_page())) {
                $title_bottom = '<h1 class="page-title">'.get_the_title($post->ID).'</h1>';
                $breadcrumbs_class .= ' show-title';
            }
            if (is_category() || is_author()) {
                $breadcrumbs_class .= ' show-title';
            }
            if (is_archive()) {
                $breadcrumbs_class .= ' blog';
            }
        }

        if (class_exists('WooCommerce') && (is_edit_account_page() || is_add_payment_method_page() || is_lost_password_page() || is_account_page() || is_view_order_page())) {
            $breadcrumbs_class = trim(str_replace('show-title', '', $breadcrumbs_class));
        }
        $estyle = !empty($style) ? ' style="'.implode(';', $style).'"' : '';

        echo '<section id="tbay-breadcrumb" '.trim($estyle).' class="tbay-breadcrumb '.esc_attr($breadcrumbs_class).'">'.trim($img).'<div class="container"><div class="breadscrumb-inner" >';
        zota_tbay_breadcrumbs();
        echo trim($nav).'</div></div></section>';

        if ($breadcrumbs_layout !== 'image' && !zota_tbay_is_home_page() && !empty($title_bottom)) {
            echo '<div class="title-not-breadcrumbs"><div class="container">'.trim($title_bottom).'</div></div>';
        }
    }
}

if (!function_exists('zota_tbay_paging_nav')) {
    function zota_tbay_paging_nav()
    {
        global $wp_query, $wp_rewrite;

        if ($wp_query->max_num_pages < 2) {
            return;
        }

        $paged = get_query_var('paged') ? intval(get_query_var('paged')) : 1;
        $pagenum_link = html_entity_decode(get_pagenum_link());
        $query_args = [];
        $url_parts = explode('?', $pagenum_link);

        if (isset($url_parts[1])) {
            wp_parse_str($url_parts[1], $query_args);
        }

        $pagenum_link = remove_query_arg(array_keys($query_args), $pagenum_link);
        $pagenum_link = trailingslashit($pagenum_link).'%_%';

        $format = $wp_rewrite->using_index_permalinks() && !strpos($pagenum_link, 'index.php') ? 'index.php/' : '';
        $format .= $wp_rewrite->using_permalinks() ? user_trailingslashit($wp_rewrite->pagination_base.'/%#%', 'paged') : '?paged=%#%';

        // Set up paginated links.
        $links = paginate_links([
            'base' => $pagenum_link,
            'format' => $format,
            'total' => $wp_query->max_num_pages,
            'current' => $paged,
            'mid_size' => 1,
            'add_args' => array_map('urlencode', $query_args),
            'prev_text' => '<i class="tb-icon tb-icon-angle-left"></i>',
            'next_text' => '<i class="tb-icon tb-icon-angle-right"></i>',
        ]);

        if ($links) :

        ?>
		<nav class="navigation paging-navigation">
			<h5 class="screen-reader-text hidden"><?php esc_html_e('Posts navigation', 'zota'); ?></h5>
			<div class="tbay-pagination">
				<?php echo trim($links); ?>
			</div><!-- .pagination -->
		</nav><!-- .navigation -->
		<?php
        endif;
    }
}

if (!function_exists('zota_tbay_post_nav')) {
    function zota_tbay_post_nav()
    {
        // Don't print empty markup if there's nowhere to navigate.
        $previous = (is_attachment()) ? get_post(get_post()->post_parent) : get_adjacent_post(false, '', true);
        $next = get_adjacent_post(false, '', false);

        if (!$next && !$previous) {
            return;
        }
        $prevPost = get_previous_post();
        $nextPost = get_next_post();
        if (is_object($prevPost)) {
            $prevthumbnail = get_the_post_thumbnail($prevPost->ID, 'zota_avatar_post_carousel');
        }
        if (is_object($nextPost)) {
            $nextthumbnail = get_the_post_thumbnail($nextPost->ID, 'zota_avatar_post_carousel');
        } ?>
		<nav class="navigation post-navigation">
			<h3 class="screen-reader-text"><?php esc_html_e('Post navigation', 'zota'); ?></h3>
			<div class="nav-links clearfix">
				<?php
                if (is_attachment()) :
                    previous_post_link('%link', '<div class="col-lg-6"><span class="meta-nav">'.esc_html__('Published In', 'zota').'</span></div>'); else :
                    if (isset($prevthumbnail)) {
                        previous_post_link('%link', '<div class="media">'.$prevthumbnail.'<div class="wrapper-title-meta media-body">'.'<span class="meta-nav nav-previous">'.esc_html__('Previous', 'zota').'</span><span class="post-title">%title</span></div></div>');
                    }
        if (isset($nextthumbnail)) {
            next_post_link('%link', '<div class="media">'.$nextthumbnail.'<div class="wrapper-title-meta media-body">'.'<span class="meta-nav nav-next">'.esc_html__('Next', 'zota').'</span><span></span><span class="post-title">%title</span></div></div>');
        }
        endif; ?>
			</div><!-- .nav-links -->
		</nav><!-- .navigation -->
		<?php
    }
}

if (!function_exists('zota_tbay_pagination')) {
    function zota_tbay_pagination($per_page, $total, $max_num_pages = '')
    {
        global $wp_query, $wp_rewrite; ?>
        <div class="tbay-pagination">
        	<?php
            $prev = esc_html__('Previous', 'zota');
        $next = esc_html__('Next', 'zota');
        $pages = $max_num_pages;
        $args = ['class' => 'pull-left'];

        $wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1;
        if (empty($pages)) {
            global $wp_query;
            $pages = $wp_query->max_num_pages;
            if (!$pages) {
                $pages = 1;
            }
        }
        $pagination = [
                'base' => @add_query_arg('paged', '%#%'),
                'format' => '',
                'total' => $pages,
                'current' => $current,
                'prev_text' => $prev,
                'next_text' => $next,
                'type' => 'array',
            ];

        if ($wp_rewrite->using_permalinks()) {
            $pagination['base'] = user_trailingslashit(trailingslashit(remove_query_arg('s', get_pagenum_link(1))).'page/%#%/', 'paged');
        }

        if (isset($_GET['s'])) {
            $cq = $_GET['s'];
            $sq = str_replace(' ', '+', $cq);
        }

        if (!empty($wp_query->query_vars['s'])) {
            $pagination['add_args'] = ['s' => $sq];
        }
        $paginations = paginate_links($pagination);
        if (!empty($paginations)) {
            echo '<ul class="pagination '.esc_attr($args['class']).'">';
            foreach ($paginations as $key => $pg) {
                echo '<li>'.esc_html($pg).'</li>';
            }
            echo '</ul>';
        } ?>
            
        </div>
    <?php
    }
}

if (!function_exists('zota_tbay_get_post_galleries')) {
    function zota_tbay_get_post_galleries($size = 'full')
    {
        $ids = get_post_meta(get_the_ID(), 'tbay_post_gallery_files');

        $output = [];

        if (!empty($ids)) {
            $id = $ids[0];

            if (is_array($id) || is_object($id)) {
                foreach ($id as $id_img => $link_img) {
                    $image = wp_get_attachment_image_src($id_img, $size);
                    $output[] = $image[0];
                }
            }
        }

        return $output;
    }
}

if (!function_exists('zota_tbay_comment_form')) {
    function zota_tbay_comment_form($arg, $class = 'btn-primary btn-outline ')
    {
        global $post;
        if ('open' == $post->comment_status) {
            ob_start();
            comment_form($arg);
            $form = ob_get_clean(); ?>
	      	<div class="commentform reset-button-default">
		    	<?php
                  echo str_replace('id="submit"', 'id="submit"', $form); ?>
	      	</div>
	      	<?php
        }
    }
}

if (!function_exists('zota_get_elementor_css_print_method')) {
    function zota_get_elementor_css_print_method() {
        static $method = null;
        if (null === $method) {
            $method = ('internal' === get_option('elementor_css_print_method', 'external'));
        }

        return $method;
    }
}

if (!function_exists('zota_tbay_display_header_builder')) {
    function zota_tbay_display_header_builder()
    {
        echo zota_get_display_header_builder();
    }
}

if (!function_exists('zota_get_header_id')) {
    function zota_get_header_id() {
        $header = apply_filters('zota_tbay_get_header_layout', 'default');
        $cache_key = 'zota_header_id_' . sanitize_key($header);
        $header_id = get_transient($cache_key);

        if (empty($header_id) || $header_id === false) {
            $query_args = [
                'name' => $header,
                'post_type' => 'tbay_header',
                'post_status' => 'publish',
                'numberposts' => 1,
                'no_found_rows' => true,
                'fields'         => 'ids',
            ];
    
            // Execute the query.
            $query = new WP_Query( $query_args );
    
            // Get the header ID or set to empty string if not found.
            $header_id = ! empty( $query->posts ) ? $query->posts[0] : '';

            set_transient($cache_key, $header_id, DAY_IN_SECONDS);
        }
        return $header_id;
    }
}

if (!function_exists('zota_get_display_header_builder')) {
    function zota_get_display_header_builder()
    {
        $id = zota_get_header_id();

        return  zota_get_html_custom_post($id);
    }
}

if (!function_exists('zota_get_footer_id')) {
    function zota_get_footer_id()
    {
        $footer = apply_filters('zota_tbay_get_footer_layout', 'footer_default');

        $args = [
            'name' => $footer,
            'post_type' => 'tbay_footer',
            'post_status' => 'publish',
            'numberposts' => 1,
        ];

        $posts = get_posts($args);

        return  (!empty($posts[0]->ID)) ? $posts[0]->ID : '';
    }
}

if (!function_exists('zota_get_display_footer_builder')) {
    function zota_get_display_footer_builder()
    {
        $id = zota_get_footer_id();

        return  zota_get_html_custom_post($id);
    }
}

if (!function_exists('zota_get_html_custom_post')) {
    /**
     * Retrieve HTML content for a custom post by ID
     * @param int $id Post ID
     * @return string|null HTML content or null if invalid
     */
    function zota_get_html_custom_post($id) {
        if (empty($id) || !is_numeric($id)) {
            return null;
        }

        $id = (int) $id;
        $cache_key = 'zota_html_post_' . $id;
        $content = get_transient($cache_key);

        $post = get_post($id);
        if (!$post || !($post instanceof WP_Post)) {
            set_transient($cache_key, '', HOUR_IN_SECONDS);
            return null;
        }

        if (zota_elementor_is_activated()) {
            $elementor = Elementor\Plugin::instance();
            if ($elementor->documents->get($id)->is_built_with_elementor()) {
                $content = $elementor->frontend->get_builder_content_for_display($id, zota_get_elementor_css_print_method());
            } else {
                $content = do_shortcode($post->post_content);
            }
        } else {
            $content = do_shortcode($post->post_content);
        }

        set_transient($cache_key, $content, DAY_IN_SECONDS);

        return $content ?: null;
    }
}

if (!function_exists('zota_tbay_display_footer_builder')) {
    function zota_tbay_display_footer_builder()
    {
        echo zota_get_display_footer_builder();
    }
}

if (!function_exists('zota_tbay_get_random_blog_cat')) {
    function zota_tbay_get_random_blog_cat()
    {
        $post_category = '';
        $categories = get_the_category();

        $number = rand(0, count($categories) - 1);

        if ($categories) {
            $post_category .= '<a href="'.esc_url(get_category_link($categories[$number]->term_id)).'" title="'.esc_attr(sprintf(esc_html__('View all posts in %s', 'zota'), $categories[$number]->name)).'">'.$categories[$number]->cat_name.'</a>';
        }

        echo trim($post_category);
    }
}

if (!function_exists('zota_tbay_get_id_author_post')) {
    function zota_tbay_get_id_author_post()
    {
        global $post;

        $author_id = $post->post_author;

        if (isset($author_id)) {
            return $author_id;
        }
    }
}

if (!function_exists('zota_active_mobile_footer_icon')) {
    function zota_active_mobile_footer_icon()
    {
        $active = zota_tbay_get_config('mobile_footer_icon', true);

        if ($active) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('zota_body_class_mobile_footer')) {
    function zota_body_class_mobile_footer($classes)
    {
        $mobile_footer = zota_tbay_get_config('mobile_footer', true);

        if (isset($mobile_footer) && !$mobile_footer) {
            $classes[] = 'mobile-hidden-footer';
        }

        if (!zota_active_mobile_footer_icon()) {
            $classes[] = 'mobile-hidden-footer-icon';
        }

        return $classes;
    }
    add_filter('body_class', 'zota_body_class_mobile_footer', 99);
}

//Add div wrapper author and name in comment form
if (!function_exists('zota_tbay_comment_form_fields_open')) {
    function zota_tbay_comment_form_fields_open()
    {
        echo '<div class="comment-form-fields-wrapper">';
    }
}
if (!function_exists('zota_tbay_comment_form_fields_close')) {
    function zota_tbay_comment_form_fields_close()
    {
        echo '</div>';
    }
}
add_action('comment_form_before_fields', 'zota_tbay_comment_form_fields_open');
add_action('comment_form_after_fields', 'zota_tbay_comment_form_fields_close');

if (!function_exists('zota_the_post_category_full')) {
    function zota_the_post_category_full($has_separator = true)
    {
        $post_category = '';
        $categories = get_the_category();
        $separator = ($has_separator) ? '' : '';
        $output = '';
        if ($categories) {
            foreach ($categories as $category) {
                $output .= '<a href="'.esc_url(get_category_link($category->term_id)).'" title="'.esc_attr(sprintf(esc_html__('View all posts in %s', 'zota'), $category->name)).'">'.$category->cat_name.'</a>'.$separator;
            }
            $post_category = trim($output, $separator);
        }

        echo trim($post_category);
    }
}

//Check active WPML
if (!function_exists('zota_tbay_wpml')) {
    function zota_tbay_wpml()
    {
        if (is_active_sidebar('wpml-sidebar')) {
            dynamic_sidebar('wpml-sidebar');
        }
    }

    add_action('zota_tbay_header_custom_language', 'zota_tbay_wpml', 10);
}

//Config Layout Blog
if (!function_exists('zota_tbay_get_blog_layout_configs')) {
    function zota_tbay_get_blog_layout_configs()
    {
        if (!is_singular('post')) {
            $page = 'blog_archive_sidebar';
        } else {
            $page = 'blog_single_sidebar';
        }

        $sidebar = zota_tbay_get_config($page);

        if (!is_singular('post')) {
            $blog_archive_layout = (isset($_GET['blog_archive_layout'])) ? $_GET['blog_archive_layout'] : zota_tbay_get_config('blog_archive_layout', 'main-right');

            if (isset($blog_archive_layout)) {
                switch ($blog_archive_layout) {
                    case 'left-main':
                        $configs['sidebar'] = ['id' => $sidebar, 'class' => 'col-12 col-xl-3'];
                        $configs['main'] = ['class' => 'col-xl-9'];
                        break;
                    case 'main-right':
                        $configs['sidebar'] = ['id' => $sidebar,  'class' => 'col-12 col-xl-3'];
                        $configs['main'] = ['class' => 'col-xl-9'];
                        break;
                    case 'main':
                        $configs['main'] = ['class' => ''];
                        break;
                    default:
                        $configs['main'] = ['class' => ''];
                        break;
                   }

                if (($blog_archive_layout === 'left-main' || $blog_archive_layout === 'main-right') && (empty($configs['sidebar']['id']) || !is_active_sidebar($configs['sidebar']['id']))) {
                    $configs['main'] = ['class' => ''];
                }
            }
        } else {
            $blog_single_layout = (isset($_GET['blog_single_layout'])) ? $_GET['blog_single_layout'] : zota_tbay_get_config('blog_single_layout', 'left-main');

            if (isset($blog_single_layout)) {
                switch ($blog_single_layout) {
                        case 'left-main':
                            $configs['sidebar'] = ['id' => $sidebar, 'class' => 'col-12 col-xl-3'];
                            $configs['main'] = ['class' => 'col-xl-9'];
                            break;
                        case 'main-right':
                            $configs['sidebar'] = ['id' => $sidebar,  'class' => 'col-12 col-xl-3'];
                            $configs['main'] = ['class' => 'col-xl-9'];
                            break;
                        case 'main':
                            $configs['main'] = ['class' => 'single-full'];
                            break;
                        default:
                            $configs['main'] = ['class' => 'single-full'];
                            break;
                     }

                if (($blog_single_layout === 'left-main' || $blog_single_layout === 'main-right') && (empty($configs['sidebar']['id']) || !is_active_sidebar($configs['sidebar']['id']))) {
                    $configs['main'] = ['class' => ''];
                }
            }
        }

        return $configs;
    }
}

if (!function_exists('zota_tbay_add_bg_close_canvas_menu')) {
    function zota_tbay_add_bg_close_canvas_menu()
    {
        $sidebar_id = 'canvas-menu';
        if (!is_active_sidebar($sidebar_id)) {
            return;
        } ?>
			<div class="bg-close-canvas-menu"></div>
 			<div class="sidebar-content-wrapper">

				<div class="sidebar-header">
					<a href="javascript:void(0);" class="close-canvas-menu"><?php esc_html_e('Close', 'zota'); ?><i class="tb-icon tb-icon-close-01"></i></a>
				</div>

				<div class="sidebar-content">
					<?php dynamic_sidebar($sidebar_id); ?>
				</div>

			</div>
		<?php
    }
    add_action('wp_footer', 'zota_tbay_add_bg_close_canvas_menu');
}

if (!function_exists('zota_get_social_html')) {
    function zota_get_social_html($key, $value, $title, $link, $media)
    {
        if (!$value) {
            return;
        }

        switch ($key) {
            case 'facebook':
                $output = sprintf(
                    '<a class="share-facebook zota-facebook" title="%s" href="http://www.facebook.com/sharer.php?u=%s&t=%s" target="_blank"><i class="fab fa-facebook-f"></i></a>',
                    esc_attr($title),
                    urlencode($link),
                    urlencode($title)
                );
                break;
            case 'twitter':
                $output = sprintf(
                    '<a class="share-twitter zota-twitter" href="http://x.com/share?text=%s&url=%s" title="%s" target="_blank"><i class="fab fa-twitter"></i></a>',
                    esc_attr($title),
                    urlencode($link),
                    urlencode($title)
                );
                break;
            case 'linkedin':
                $output = sprintf(
                    '<a class="share-linkedin zota-linkedin" href="http://www.linkedin.com/shareArticle?url=%s&title=%s" title="%s" target="_blank"><i class="fab fa-linkedin-in"></i></a>',
                    urlencode($link),
                    esc_attr($title),
                    urlencode($title)
                );
                break;

            case 'pinterest':
                $output = sprintf(
                    '<a class="share-pinterest zota-pinterest" href="http://pinterest.com/pin/create/button?media=%s&url=%s&description=%s" title="%s" target="_blank"><i class="fab fa-pinterest"></i></a>',
                    urlencode($media),
                    urlencode($link),
                    esc_attr($title),
                    urlencode($title)
                );
                break;

            case 'whatsapp':
                $output = sprintf(
                    '<a class="share-whatsapp zota-whatsapp" href="https://api.whatsapp.com/send?text=%s" title="%s" target="_blank"><i class="fab fa-whatsapp"></i></a>',
                    urlencode($link),
                    esc_attr($title)
                );
                break;

            case 'email':
                $output = sprintf(
                    '<a class="share-email zota-email" href="mailto:?subject=%s&body=%s" title="%s" target="_blank"><i class="far fa-envelope"></i></a>',
                    esc_html($title),
                    urlencode($link),
                    esc_attr($title)
                );
                break;

            default:
                // code...
                break;
        }

        return $output;
    }
}

if (!function_exists('zota_tbay_nav_description')) {
    /**
     * Display descriptions in main navigation.
     *
     * @since Zota 1.0
     *
     * @param string  $item_output The menu item output.
     * @param WP_Post $item        Menu item object.
     * @param int     $depth       Depth of the menu.
     * @param array   $args        wp_nav_menu() arguments.
     *
     * @return string Menu item with possible description.
     */
    function zota_tbay_nav_description($item_output, $item, $depth, $args)
    {
        if ('primary' == $args->theme_location && $item->description) {
            $item_output = str_replace($args->link_after.'</a>', '<div class="menu-item-description">'.$item->description.'</div>'.$args->link_after.'</a>', $item_output);
        }

        return $item_output;
    }
    add_filter('walker_nav_menu_start_el', 'zota_tbay_nav_description', 10, 4);
}

if (!function_exists('zota_add_class_wrapper_container')) {
    function zota_add_class_wrapper_container($class_ar)
    {
        $class_ar = explode(', ', $class_ar);

        $class = join(' ', $class_ar);

        return $class;
    }
    add_filter('zota_class_wrapper_container', 'zota_add_class_wrapper_container', 10, 1);
}

if (!function_exists('zota_tbay_woocs_redraw_cart')) {
    function zota_tbay_woocs_redraw_cart()
    {
        return 0;
    }
    add_filter('woocs_redraw_cart', 'zota_tbay_woocs_redraw_cart', 10, 1);
}

if (!function_exists('zota_load_html_dropdowns_action')) {
    /**
     * Improved function to load HTML blocks via AJAX for dropdowns
     */
    function zota_load_html_dropdowns_action() {
        // Default response
        $response = [
            'status'  => 'error',
            'message' => 'Unable to load HTML blocks via AJAX',
            'data'    => [],
        ];

        // Check if the request is valid
        if (!isset($_POST['ids']) || !is_array($_POST['ids']) || empty($_POST['ids'])) {
            $response['message'] = 'No valid IDs provided';
            wp_send_json($response);
        }


        // Sanitize and prepare IDs
        $ids = array_map('intval', zota_clean($_POST['ids'])); // Ensure all IDs are integers
        $ids = array_filter($ids); // Remove empty or invalid IDs

        if (empty($ids)) {
            $response['message'] = 'No valid IDs after sanitization';
            wp_send_json($response);
        }

        // Query posts in one go
        $args = [
            'post_type'      => 'tbay_megamenu', // Adjust post type if specific (e.g., 'tbay_header', 'tbay_footer')
            'post__in'       => $ids,
            'post_status'    => 'publish',
            'posts_per_page' => -1, // Get all requested IDs
            'no_found_rows'  => true, // Skip pagination for performance
        ];

        $query = new WP_Query($args);
        $loaded = 0;

        foreach ($ids as $id) {
            $id = (int) $id;
            $content = zota_get_html_custom_post($id);
            if (!$content) {
                continue;
            }

            $response['status'] = 'success';
            $response['message'] = 'At least one HTML block loaded';
            $response['data'][$id] = $content;
        }

        // Update response based on results
        if ($loaded > 0) {
            $response['status']  = 'success';
            $response['message'] = sprintf('Loaded %d HTML block(s)', $loaded);
        } else {
            $response['message'] = 'No HTML blocks found for the provided IDs';
        }

        wp_send_json($response);
    }
    add_action('wp_ajax_zota_load_html_dropdowns', 'zota_load_html_dropdowns_action');
    add_action('wp_ajax_nopriv_zota_load_html_dropdowns', 'zota_load_html_dropdowns_action');
}

if (!function_exists('zota_load_html_click_action')) {
    function zota_load_html_click_action()
    {
        $response = [
            'status' => 'error',
            'message' => 'Can\'t load HTML blocks with AJAX',
            'data' => [],
        ];

        if (!empty($_POST['slug'])) {
            $slug = zota_clean($_POST['slug']);
            $type_menu = zota_clean($_POST['type_menu']);
            $layout = zota_clean($_POST['layout']);

            $args = [
                'echo' => false,
                'menu' => $slug,
                'container_class' => 'collapse navbar-collapse',
                'menu_id' => 'menu-'.$slug,
                'walker' => new Zota_Tbay_Nav_Menu(),
                'fallback_cb' => '__return_empty_string',
                'container' => '',
            ];

            $args['menu_class'] = zota_nav_menu_get_menu_class($layout);

            $content = wp_nav_menu($args);

            $response['status'] = 'success';
            $response['message'] = 'At least one HTML Menu Canvas loaded';
            $response['data'] = $content;
        }

        echo json_encode($response);

        die();
    }
    add_action('wp_ajax_zota_load_html_click', 'zota_load_html_click_action');
    add_action('wp_ajax_nopriv_zota_load_html_click', 'zota_load_html_click_action');
}

if (!function_exists('zota_get_elementor_post_scripts')) {
    function zota_get_elementor_post_scripts()
    {
        if (!zota_elementor_is_activated()) {
            return;
        }

        if (class_exists('\Elementor\Plugin')) {
            $elementor = \Elementor\Plugin::instance();
            $elementor->frontend->enqueue_styles();
        }

        if (class_exists('\Elementor\Core\Files\CSS\Post')) {
            $css_file = new \Elementor\Core\Files\CSS\Post(zota_get_header_id());
        } elseif (class_exists('\Elementor\Post_CSS_File')) {
            $css_file = new \Elementor\Post_CSS_File(zota_get_header_id());
        }

        $css_file->enqueue();

        if (class_exists('\Elementor\Core\Files\CSS\Post')) {
            $css_file = new \Elementor\Core\Files\CSS\Post(zota_get_footer_id());
        } elseif (class_exists('\Elementor\Post_CSS_File')) {
            $css_file = new \Elementor\Post_CSS_File(zota_get_footer_id());
        }

        $css_file->enqueue();
    }
}

if (!function_exists('zota_tbay_back_to_top')) {
    function zota_tbay_back_to_top()
    {
        if (zota_tbay_get_config('back_to_top')) { ?>
			<div class="tbay-to-top">
				<a href="javascript:void(0);" id="back-to-top">
					<i class="tb-icon tb-icon-angle-up"></i>
				</a>
			</div>
		<?php
        } ?>
	
		<?php
        if (zota_tbay_get_config('mobile_back_to_top')) { ?>
			<div class="tbay-to-top-mobile tbay-to-top">
	
				<div class="more-to-top">
				
					<a href="javascript:void(0);" id="back-to-top-mobile">
						<i class="tb-icon tb-icon-angle-up"></i>
					</a>
					
				</div>
			</div>
			
			
		<?php
        }
    }
    add_action('elementor/theme/after_do_footer', 'zota_tbay_back_to_top', 10);
    add_action('zota_after_do_footer', 'zota_tbay_back_to_top', 10);
}
