<?php
/**
 * Variable product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/variable.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 6.1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $product;
$attribute_keys = array_keys( $attributes );
do_action( 'woocommerce_before_add_to_cart_form' ); ?>
<form class="variations_form cart" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo htmlspecialchars( json_encode( $available_variations ) ) ?>">
	<?php do_action( 'woocommerce_before_variations_form' ); ?>
	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock"><?php _e( 'This product is currently out of stock and unavailable.', 'punio' ); ?></p>
	<?php else : ?>


		<div class="swiper">
			<!-- Additional required wrapper -->
			<div class="swiper-wrapper">
				<!-- Slides -->
				<div class="swiper-slide">
					<div class="step step-1">
						<div class="title">Paso 1</div>
						<div class="content">
							<table class="variations" cellspacing="0">
								<tbody>
									<?php 
										$attribute_name = 'Receta';
										$options = $attributes[$attribute_name];
									?>
									<tr>
										<td class="label"><label for="<?php echo esc_attr(sanitize_title( $attribute_name )); ?>"><?php echo wc_attribute_label( $attribute_name ); ?></label></td>
										<td class="value">
											<?php
												$selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) ) : $product->get_variation_default_attribute( $attribute_name );
												wc_dropdown_variation_attribute_options( array( 'options' => $options, 'attribute' => $attribute_name, 'product' => $product, 'selected' => $selected ) );
												echo end( $attribute_keys ) === $attribute_name ? apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__( 'Clear', 'punio' ) . '</a>' ) : '';
											?>
										</td>
									</tr>
									<?php 
										$attribute_name = 'Tipo de lente';
										$options = $attributes[$attribute_name];
									?>
									<tr>
										<td class="label"><label for="<?php echo esc_attr(sanitize_title( $attribute_name )); ?>"><?php echo wc_attribute_label( $attribute_name ); ?></label></td>
										<td class="value">
											<?php
												$selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) ) : $product->get_variation_default_attribute( $attribute_name );
												wc_dropdown_variation_attribute_options( array( 'options' => $options, 'attribute' => $attribute_name, 'product' => $product, 'selected' => $selected ) );
												echo end( $attribute_keys ) === $attribute_name ? apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__( 'Clear', 'punio' ) . '</a>' ) : '';
											?>
										</td>
									</tr>
								</tbody>
							</table>
							
						</div>
					</div>
				</div>
				
				<div class="swiper-slide">
					<div class="step step-2">
						<div class="title">Paso 2</div>
						<div class="content">
							<table class="variations" cellspacing="0">
								<tbody>
									<?php 
										$attribute_name = 'Material del cristal';
										$options = $attributes[$attribute_name];
									?>
									<tr>
										<td class="label"><label for="<?php echo esc_attr(sanitize_title( $attribute_name )); ?>"><?php echo wc_attribute_label( $attribute_name ); ?></label></td>
										<td class="value">
											<?php
												$selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) ) : $product->get_variation_default_attribute( $attribute_name );
												wc_dropdown_variation_attribute_options( array( 'options' => $options, 'attribute' => $attribute_name, 'product' => $product, 'selected' => $selected ) );
												echo end( $attribute_keys ) === $attribute_name ? apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__( 'Clear', 'punio' ) . '</a>' ) : '';
											?>
										</td>
									</tr>
								</tbody>
							</table>
							
						</div>
					</div>
				</div>
				
				<div class="swiper-slide">
					<div class="step step-3">
						<div class="title">Paso 3</div>
						<div class="content">
							<table class="variations" cellspacing="0">
								<tbody>
									<?php 
										$attribute_name = "Tratamiento del cristal";
										$options = $attributes[$attribute_name];
									?>
									<tr>
										<td class="label"><label for="<?php echo esc_attr(sanitize_title( $attribute_name )); ?>"><?php echo wc_attribute_label( $attribute_name ); ?></label></td>
										<td class="value">
											<?php
												$selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) ) : $product->get_variation_default_attribute( $attribute_name );
												wc_dropdown_variation_attribute_options( array( 'options' => $options, 'attribute' => $attribute_name, 'product' => $product, 'selected' => $selected ) );
												echo end( $attribute_keys ) === $attribute_name ? apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__( 'Clear', 'punio' ) . '</a>' ) : '';
											?>
										</td>
									</tr>
								</tbody>
							</table>
							
						</div>
					</div>
				</div>

				<div class="swiper-slide">
					<div class="step step-4">
						<div class="title">Paso 4</div>
						<div class="content">
							<table class="variations" cellspacing="0">
								<tbody>
									<?php 
										$attribute_name = "Marca";
										$options = $attributes[$attribute_name];
									?>
									<tr>
										<td class="label"><label for="<?php echo esc_attr(sanitize_title( $attribute_name )); ?>"><?php echo wc_attribute_label( $attribute_name ); ?></label></td>
										<td class="value">
											<?php
												$selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) ) : $product->get_variation_default_attribute( $attribute_name );
												wc_dropdown_variation_attribute_options( array( 'options' => $options, 'attribute' => $attribute_name, 'product' => $product, 'selected' => $selected ) );
												echo end( $attribute_keys ) === $attribute_name ? apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__( 'Clear', 'punio' ) . '</a>' ) : '';
											?>
										</td>
									</tr>
								</tbody>
							</table>
							
						</div>
					</div>
				</div>
				
				...
			</div>
			<!-- If we need pagination -->
			<div class="swiper-pagination"></div>

			<!-- If we need navigation buttons -->
			<div class="swiper-button-prev"></div>
			<div class="swiper-button-next"></div>

			<!-- If we need scrollbar -->
			<div class="swiper-scrollbar"></div>
		</div>

		<link
		rel="stylesheet"
		href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"
		/>

		<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

		<style>
			.swiper-slide .step{
				padding: 40px 50px;
			}
		</style>
		
		<script>
			const swiper = new Swiper('.swiper', {
			// Optional parameters
			direction: 'horizontal',
			loop: false,

			// If we need pagination
			pagination: {
				el: '.swiper-pagination',
			},

			// Navigation arrows
			navigation: {
				nextEl: '.swiper-button-next',
				prevEl: '.swiper-button-prev',
			},

			// And if we need scrollbar
			scrollbar: {
				el: '.swiper-scrollbar',
			},

			autoHeight: false
			});

			jQuery( ".single_variation_wrap" ).on( "show_variation", function ( event, variation ) {
				jQuery('.reset_variations').click(function(){
					const dn = 0
					console.log('Cambiando a diapositiva ' + dn);
					//jQuery('.reset_variations').off('click');
				});
			});

			//jQuery('.reset_variations').click(function(){
			jQuery('.variations_form').on('hide_variation',function(){
			//jQuery.wc_variation_form().on('reset_data',function(){
				const dn = 0
				//console.log('Cambiando a diapositiva ' + dn);
				//swiper.slideTo(dn);

			});

		</script>

		

		<!-- <table class="variations" cellspacing="0">
			<tbody>
				<?php foreach ( $attributes as $attribute_name => $options ) : ?>
					<tr>
						<td class="label"><label for="<?php echo esc_attr(sanitize_title( $attribute_name )); ?>"><?php echo wc_attribute_label( $attribute_name ); ?></label></td>
						<td class="value">
							<?php
								$selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) ) : $product->get_variation_default_attribute( $attribute_name );
								wc_dropdown_variation_attribute_options( array( 'options' => $options, 'attribute' => $attribute_name, 'product' => $product, 'selected' => $selected ) );
								echo end( $attribute_keys ) === $attribute_name ? apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__( 'Clear', 'punio' ) . '</a>' ) : '';
							?>
						</td>
					</tr>
		        <?php endforeach;?>
			</tbody>
		</table> -->
		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
		<div class="single_variation_wrap">
			<?php
				/**
				 * woocommerce_before_single_variation Hook.
				 */
				do_action( 'woocommerce_before_single_variation' );
				/**
				 * woocommerce_single_variation hook. Used to output the cart button and placeholder for variation data.
				 * @since 2.4.0
				 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
				 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
				 */
				do_action( 'woocommerce_single_variation' );
				/**
				 * woocommerce_after_single_variation Hook.
				 */
				do_action( 'woocommerce_after_single_variation' );
			?>
			<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
		</div>
	<?php endif; ?>
	<?php do_action( 'woocommerce_after_variations_form' ); ?>
</form>
<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>