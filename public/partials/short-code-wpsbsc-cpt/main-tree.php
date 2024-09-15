<?php 
	$main_title = 'TUS CRISTALES'; //$args['main_title'];;
	$steps = $args['steps'];
?>

<div class="cristal-selection main-container">

	<?php do_action('jgb_wc_prds_sbsc_widget_render_before_fields_containers'); ?>
		
	<div class="left-container">

		<div class="main-title"><?= $main_title ?></div>
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
			<img src="http://verwell.local/wp-content/uploads/2021/03/E6190002.jpg" class="spf">
		</div>
		<div class="primary-product-details">
			<div class="title-1">Armazón</div>
			<div class="empty-1"></div>
			<div class="brand">
				<img src="http://verwell.local/wp-content/uploads/2023/09/logo-verwell.png" width="120px">
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