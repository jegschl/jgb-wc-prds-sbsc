<?php 
	$main_title = 'TUS CRISTALES'; //$args['main_title'];;
	$steps = $args['steps'];
?>

<div class="cristal-selection main-container">
	<div class="help-box hidden" data-hb-id="tipo-de-lente">
		<div class="close-buton">X</div>
		<div class="box-container">
			<img src="http://verwell.local/wp-content/uploads/2024/03/help-link-tipos_de_lentes-686x386-1.png">
		</div>
	</div>

	<div class="help-box hidden" data-hb-id="material-cristal">
		<div class="close-buton">X</div>
		<div class="box-container">
			<img src="http://verwell.local/wp-content/uploads/2024/03/help-link-materiales_de_crsitales-800x450-1.png">
		</div>
	</div>
	
	<div class="left-container">

		<div class="main-title"><?= $main_title ?></div>
		<div class="swiper">
			<!-- Additional required wrapper -->
			<div class="swiper-wrapper">
				<?php foreach( $steps as $step_index => $step ){ ?>
				<!-- Slides -->
				<div class="swiper-slide">
					<div class="step step-<?= $step_index ?>">
						<div class="title"></div>
						
						<div class="content">
							
						<!-- contenido -->
						<?php do_action('JGB/WPSBSC/render_fields', $step['fields'] ); ?>

						</div>

						<div class="nav-buttons">
							<?php if( $step_index > 0) { ?>
							<div class="forward">Volver</div>
							<?php } ?>
							<?php if( $step_index < count( $steps ) - 1 ){ ?>
							<div class="next">Siguiente</div>
							<?php } ?>
						</div>
					</div>
				</div>
				
				<?php } ?>
				
				
			</div>
			<!-- If we need pagination -->
			<div class="swiper-pagination"></div>

			<!-- If we need navigation buttons -->
			<!-- <div class="swiper-button-prev"></div>
			<div class="swiper-button-next"></div> -->

			<!-- If we need scrollbar -->
			<!-- <div class="swiper-scrollbar"></div> -->
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