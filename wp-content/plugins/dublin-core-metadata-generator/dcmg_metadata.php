<?php
/**
 * Here we get the Dublin Core and Open Graph metadata
 **
 */
function dcmg_function_doctype_opengraph($output)
{
	$options = get_option('dublincore_metadata_generator_settings');
	$opengraph_html = $options['dublincore_metadata_generator_html_opengraph'];
	if (!empty($opengraph_html))
	{
		return $output . ' xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://developers.facebook.com/schema/"';
	}
	else
	{
		return $output;
	}
}
add_filter('language_attributes', 'dcmg_function_doctype_opengraph');
function add_extra_dcmg_tags()
{
	$options = get_option('dublincore_metadata_generator_settings');
	$description = $options['dublincore_metadata_generator_description'];
	$cleanmetas = $options['dublincore_metadata_generator_cleanmetas'];
	// clean useless generator metas
	if (!empty($cleanmetas)):
		remove_action('wp_head', 'rsd_link');
		remove_action('wp_head', 'wlwmanifest_link');
		remove_action('wp_head', 'wp_generator');
		remove_action('wp_head', 'wp_shortlink_wp_head');
		if (class_exists('Vc_Manager'))
		{
			remove_action('wp_head', array(
				visual_composer() ,
				'addMetaData'
			));
			add_filter('addMetaData', 'addMetaData2');
			function addMetaData2()
			{
				return false;
			}
		}
		function remove_revslider_meta_tag()
		{
			return '';
		}
		add_filter('revslider_meta_generator', 'remove_revslider_meta_tag');
		function remove_layerslider_meta_tag()
		{
			return '';
		}
		add_filter('ls_meta_generator', 'remove_layerslider_meta_tag');
		function remove_woocommerce_version()
		{
			return true;
		}
		add_filter('wf_disable_generator_tags', 'remove_woocommerce_version');
		global $sitepress;
		remove_action('wp_head', array(
			$sitepress,
			'meta_generator_tag'
		));
?>
<meta name="Distribution" content="global" />
<meta name="language" content="<?php
		$language = get_bloginfo('language');
		if (($language == 'en-US') || ($language == 'en-GB'))
		{
			$thelanguage = 'English';
		}
		if ($language == 'fr-FR')
		{
			$thelanguage = 'French';
		}
		if ($language == 'de-DE')
		{
			$thelanguage = 'German';
		}
		if ($language == 'es-ES')
		{
			$thelanguage = 'Spanish';
		}
		if ($language == 'ro-RO')
		{
			$thelanguage = 'Romanian';
		}
		echo $thelanguage; ?>" />
<meta name="rating" content="General" />
<meta name="Robots" content="index, all" />
<meta name="Robots" content="index, follow" />
<meta name="revisit-after" content="1 days" />
<link rel="author" href="<?php echo get_site_url(); ?>" title="<?php echo get_bloginfo('name'); ?>" />
<meta http-equiv="ImageToolbar" content="No" />
<meta name="MSSmartTagsPreventParsing" content="True" />
<?php
	endif;
	$keywords = $options['dublincore_metadata_generator_keywords'];
	if (!empty($description)): ?>
<meta name="description" content="<?php echo $description; ?>" />
	<?php
	endif; ?>
<?php if (is_single())
	{
		global $post;
		$tags = get_the_terms($post, 'post_tag'); // UPDATED
		$keywordsfromtags = $tags;
	}
	else
	{
		$titlestring = strip_tags(get_the_title());
		$keywordsfromtitle = preg_replace("/[^\w\ _]+/", '', $titlestring); // strip all punctuation characters, news lines, etc.
		$keywordsfromtitle = preg_split("/\s+/", $keywordsfromtitle); // split by left over spaces
		$keywordsfromtitles = ''; // UPDATED
		foreach ($keywordsfromtitle as $keywordfromtitle)
		{
			$keywordsfromtitles .= $keywordfromtitle;
			$keywordsfromtitles .= ', ';
		}
	}
	if (class_exists('WooCommerce'))
	{
		if (is_woocommerce())
		{
			global $product;
			$terms = get_the_terms($values['product_id'], 'product_tag');
			if (!empty($terms))
			{
				foreach ($terms as $term)
				{
					$keywordsfromproducttags .= $term->name;
					$keywordsfromproducttags .= ', ';
				}
			}
			$categories = get_the_terms($values['product_id'], 'product_cat');
			if (!empty($categories))
			{
				foreach ($categories as $category)
				{
					$keywordsfromproductcats .= $category->name;
					$keywordsfromproductcats .= ', ';
				}

			}
		}
	}
	// preparing keywords
	if (empty($thekeywords)) // UPDATED
	$thekeywords = ''; // UPDATED
	if (!empty($keywordsfromproducttags))
	{
		$thekeywords .= $keywordsfromproducttags;
	}
	if (!empty($keywordsfromproductcats))
	{
		$thekeywords .= $keywordsfromproductcats;
	}
	if (!empty($keywordsfromtags))
	{
		foreach ($keywordsfromtags as $tag) // UPDATED
		$thekeywords .= $tag->name . ' '; // UPDATED
		
	}
	if (!empty($keywordsfromtitles))
	{
		$thekeywords .= $keywordsfromtitles;
	}
	if (!empty($keywords))
	{
		$thekeywords .= $keywords;
	}
	if (!empty($thekeywords))
	{
		// decided initially to completely remove the generation of keywords meta, but if you really want to have it, just uncomment the next line
		//		echo '<meta name="keywords" content="' . $thekeywords . '" />' . "\xA";
		
	}
}
add_action('wp_head', 'add_extra_dcmg_tags', 5);
function add_extra_dcmg_metadata()
{
	global $post;
	$options = get_option('dublincore_metadata_generator_settings');
	$opengraph = $options['dublincore_metadata_generator_opengraph'];
	$dublincore = $options['dublincore_metadata_generator_dublincore'];
	$cardtype = is_array($options) && array_key_exists('dublincore_metadata_generator_image_format', $options) ? $options['dublincore_metadata_generator_image_format'] : 0; // UPDATED
	$fbadm = $options['dublincore_metadata_generator_fb_admins'];
	$defaultimg = $options['dublincore_metadata_generator_default_image'];
	$site_lang = get_bloginfo('language');
	if (has_category()):
		$category = get_the_category();
		$cat = $category[0]->cat_name;
	endif;
	if ($cardtype == 1):
		$type = 'summary';
	else:
		$type = 'summary_large_image';
	endif;
	// Metadata for single page
	
	if (is_single() && !is_404() && !is_home()):
		if (has_excerpt()):
			$abstract = wp_strip_all_tags(get_the_excerpt() , true);
		else:
			$abstract = strip_tags($post->post_content);
			$abstract_more = '';
			if (strlen($abstract) > 155):
				$abstract = substr($abstract, 0, 155);
				$abstract_more = ' ...';
			endif;
			$abstract = str_replace('"', '', $abstract);
			$abstract = str_replace("'", '', $abstract);
			$abstractwords = preg_split('/[\n\r\t ]+/', $abstract, -1, PREG_SPLIT_NO_EMPTY);
			array_pop($abstractwords);
			$abstract = implode(' ', $abstractwords) . $abstract_more;
		endif;
		$fname = get_the_author_meta('first_name');
		$lname = get_the_author_meta('last_name');
		$author = trim("$fname $lname");
		if (get_the_post_thumbnail($post->ID, 'thumbnail')):
			$thumbnail_id = get_post_thumbnail_id($post->ID);
			$thumbnail_object = get_post($thumbnail_id);
			$image = $thumbnail_object->guid;
		else:
			$image = $defaultimg;
		endif;
	endif;
	if (!is_single() && !empty($defaultimg)):
		$image = $defaultimg;
	endif;
	// Open Graph Metadata
	
	if (!empty($opengraph)):
		if (is_single()): ?>
<meta property="og:title" content="<?php the_title(); ?>" />
	<?php
		endif;
		if (!empty($abstract) && is_single()): ?>
<meta property="og:description" content="<?php echo $abstract; ?>" />
	<?php
		endif; ?>
<meta property="og:type" content="article" />
	<?php if (is_single()): ?>
<meta property="og:url" content="<?php the_permalink(); ?>" />
	<?php
		endif;
		if (!empty($image))
		{ ?>
<meta property="og:image" content="<?php echo $image; ?>" />
	<?php
		}
		elseif (has_post_thumbnail())
		{ ?>
<meta property="og:image" content="' . wp_get_attachment_url( get_post_thumbnail_id() ) . '" />
	<?php
		} ?>
<meta property="og:site_name" content="<?php echo get_bloginfo('name'); ?>" />
<meta property="og:determiner" content="auto" />
<meta property="og:locale" content="<?php echo $site_lang; ?>" />
	<?php if (!empty($author)): ?>
<meta property="og:profile" content="<?php echo $author; ?>" />
	<?php
		endif;
		if (!empty($fname)): ?>
<meta property="profile:first_name" content="<?php echo $fname; ?>" />
	<?php
		endif;
		if (!empty($lname)): ?>
<meta property="profile:last_name" content="<?php echo lname; ?>" />
	<?php
		endif; ?>
<meta property="og:website" content="<?php echo get_site_url(); ?>" />
	<?php if (!empty($fbadm)): ?>
<meta property="fb:admins" content="<?php echo $fbadm; ?>"/>
	<?php
		endif;
	endif;
	// Dublin Core
	
	if (!empty($dublincore)): ?>
<?php if ((is_single()) || (is_page())): ?>
<meta name="DC.Title" content="<?php
			// get correct SEO title
			function customyoastseotitle2()
			{
				return get_post_meta(get_the_ID() , '_yoast_wpseo_title', true) ? : get_the_title();
			}
			$yoasttitle = customyoastseotitle2();
			if (($yoasttitle != "") && ($yoasttitle != "%%title%%"))
			{
				$metatitle = $yoasttitle;
			}
			else
			{
				$metatitle = the_title();
			}
			echo $metatitle; ?>" />
<?php
		endif; ?>
<meta name="DC.Publisher" content="<?php echo get_bloginfo('name'); ?>" />
<meta name="DC.Language" scheme="UTF-8" content="<?php echo $site_lang; ?>" />
<?php if (!empty($author)): ?>
<meta name="DC.Creator" content="<?php the_author(); ?>" />
<?php
		endif; ?>
<?php if (empty($author)): ?>
<meta name="DC.Creator" content="<?php echo get_bloginfo('name'); ?>" />
<?php
		endif; ?>
<?php if (!empty($abstract) && is_single()): ?>
<meta name="DC.Description" content="<?php echo $abstract; ?>" />
<?php
		endif; ?>
<meta name="DC.Type" scheme="DCMIType" content="Text" />
<meta name="DC.Format" scheme="IMT" content="text/html" />
<meta name="DC.Format.MIME" content="text/html" />
<meta name="DC.Format.SysReq" content="Internet browser" />
<meta name="DC.Source" content="<?php echo network_site_url('/'); ?>">
<meta name="DC.Coverage" content="World">
<?php if (is_single()): ?>
<meta name="DC.Identifier" content="<?php the_permalink(); ?>" />
<meta name="DC.Date" content="<?php the_time('Y-m-d'); ?>" />
<?php
		else: ?>
<meta name="DC.Identifier" content="<?php echo get_site_url(); ?>" />
<?php
		endif;
		if (has_category()): ?>
<meta name="DC.Subject" content="<?php echo $cat; ?>" />
<?php
		endif; ?>
<?php if (!empty($options['dublincore_metadata_generator_keywords']) || !empty($thekeywords)): ?>
<meta name="DC.Subject.Keyword" content="<?php
			if (!empty($thekeywords))
			{
				echo $thekeywords . ', ';
			}
			if (!empty($options['dublincore_metadata_generator_keywords']))
			{
				echo $options['dublincore_metadata_generator_keywords'];
			}
?>" /> 
<?php
		endif;
	endif;
}
add_action('wp_head', 'add_extra_dcmg_metadata', 5);

// Here be dragons

// Disable Yoast source code comments

$options = get_option('dublincore_metadata_generator_settings');
$cleanyoast = is_array($options) && array_key_exists('dublincore_metadata_generator_cleanyoast', $options) ? $options['dublincore_metadata_generator_cleanyoast'] : 0; // UPDATED
if (!empty($cleanyoast)) {
	add_filter( 'wpseo_debug_markers', '__return_false' );
}
?>