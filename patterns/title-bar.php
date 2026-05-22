<?php
/**
 * Title Bar Block Pattern
 *
 * @package ChoctawNation
 */

ob_start();
?>
<!-- wp:cover {"url":"https://choctawlanding.com/wp-content/uploads/2026/05/light-gray-map-background.webp","id":2110,"dimRatio":0,"isUserOverlayColor":true,"isDark":false,"sizeSlug":"full","align":"wide","className":"title-bar","style":{"spacing":{"padding":{"top":"var:preset|spacing|xl","bottom":"var:preset|spacing|xl"}},"dimensions":{"aspectRatio":"auto"}},"layout":{"type":"constrained","contentSize":"1076px"}} -->
<div class="wp-block-cover alignwide is-light title-bar" style="padding-top: var( --wp--preset--spacing--xl );padding-bottom: var( --wp--preset--spacing--xl );">
	<img class="wp-block-cover__image-background wp-image-2110 size-full" alt="" src="https://landing.local/wp-content/uploads/2026/05/light-gray-map-background.webp"
		data-object-fit="cover" />
	<span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span>
	<div class="wp-block-cover__inner-container">
		<!-- wp:group {"style":{"spacing":{"blockGap":"0","padding":{"top":"var:preset|spacing|base","bottom":"var:preset|spacing|base"}}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
		<div class="wp-block-group" style="padding-top: var( --wp--preset--spacing--base );padding-bottom: var( --wp--preset--spacing--base );flex-direction: column;">
			<!-- wp:post-title {"level":1,"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}}},"textColor":"primary","fontSize":"xxl"} /-->

			<!-- wp:paragraph {"style":{"layout":{"selfStretch":"fit","flexSize":null}},"fontSize":"sm"} -->
			<p class="has-sm-font-size">
				A nice description goes here. It can be a couple sentences long, but should be concise and to the point. It should give users a good idea of what this page is about and what
				they can expect to find on it.
			</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
</div>
<!-- /wp:cover -->
<?php
return ob_get_clean();