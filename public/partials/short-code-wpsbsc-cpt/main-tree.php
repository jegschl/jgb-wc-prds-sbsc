<?php 
	global $post;
	$product = wc_get_product( $post->ID );
	$main_title = 'TUS CRISTALES'; //$args['main_title'];;
	$steps = $args['steps'];
	$product_img_url = wp_get_attachment_image_url( $product->get_image_id(), 'full' );
?>

<div class="cristal-selection main-container">

	<?php do_action('jgb_wc_prds_sbsc_widget_render_before_fields_containers', $args); ?>
		
	<div class="left-container">

		<div class="main-title"><?= $main_title ?> x</div>
		<div class="swiper">
			<!-- Additional required wrapper -->
			<div class="swiper-wrapper">
							
				

			</div>
			<!-- If we need pagination -->
			<div class="swiper-pagination"></div>

			

		</div>

		<div class="nav-buttons">
							
			<div class="forward">Volver</div>

			<div class="next">Siguiente</div>
			
		</div>
	</div>

	<div class="right-container">
		<div class="header">
			<div class="title-1">TU SELECCIÓN</div>
			<div class="empty-1"></div>
			<div class="SKU"><?= $args['sku'] ?></div>
		</div>
		<div class="photo-container">
			<img src="<?= $product_img_url ?>"  class="spf">
		</div>
		<div class="primary-product-details">
			<div class="title-1">ARMAZÓN</div>
			<div class="empty-1"></div>
			<div class="brand">
				<img src="<?= site_url( "/wp-content/uploads/2023/09/logo-verwell.png" ) ?>" width="120px">
			</div>
		</div>
		<div class="selected-features-container">

		</div>

		<div class="scs-price">
			<div class="label">Precio</div>
			<div class="price-container"></div>
		</div>

		<div class="nav-buttons">
			<div class="add-crystal-to-cart">Comprar</div>
		</div>
	</div>
</div>