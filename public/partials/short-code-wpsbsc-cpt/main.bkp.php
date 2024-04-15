<?php 
	
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

		<div class="main-title">TUS CRISTALES</div>
		<div class="swiper">
			<!-- Additional required wrapper -->
			<div class="swiper-wrapper">
				<!-- Slides -->
				<div class="swiper-slide">
					<div class="step step-1">
						<div class="title"></div>
						
						<div class="content">
							<table class="variations field" cellspacing="0">
								<tbody>
									<tr>
									<td colspan="2" class="label">Línea de cristales</td>
									</tr>
									
									<tr>
										<td class="value">
                                            <div class="wrapper">
                                                <label for="premium-glass">Cristales Premium</label>
                                                <input type="radio" name="quality-glass" value="premium" id="premium-glass"/>
                                                <div class="select-buton outer">
                                                    <div class="select-buton">Escoger</div>
                                                </div>
                                            </div>
                                        </td>
									</tr>
                                    <tr>
                                        <td class="value">
                                            <div class="wrapper">
                                                <label for="generic-glass">Cristales genéricos</label>
                                                <input type="radio" name="quality-glass" value="generic" id="generic-glass"/>
                                                <div class="select-buton outer">
                                                    <div class="select-buton">Escoger</div>
                                                </div>
                                            </div>
                                        </td>
									</tr>

								</tbody>
							</table>

							<table class="variations field" cellspacing="0">
								<tbody>
									
									<tr>
										<td colspan="2" class="label">¿Tienes receta?</td>
									</tr>
									
									<tr>
										<td class="value">
											<div class="wrapper">
												<div class="label-wrapper">
													<img src="http://verwell.local/wp-content/uploads/2024/03/icon01-250x250-1.png">
													<label for="receta-si">Con receta</label>
												</div>
												<input type="radio" name="receta" value="si" id="receta-si"/>
												<div class="buton-group multiple" data-opts-sels="">
													<div class="option-buton outer">
														<div class="option-buton" data-option="ver-lejos">Ver de lejos</div>
													</div>
													<div class="option-buton outer">
														<div class="option-buton" data-option="ver-cerca">Ver de cerca</div>
													</div>
												</div>
											</div>
										</td>
									</tr>
									<tr>
										<td class="value">
											<div class="wrapper">
												<div class="label-wrapper">
													<img src="http://verwell.local/wp-content/uploads/2024/03/icon02-250x250-1.png">
													<label for="receta-no">Sin receta</label>
												</div>
												<input type="radio" name="receta" value="no" id="receta-no"/>
												<div class="select-buton outer">
													<div class="select-buton">Escoger</div>
												</div>
											</div>
										</td>
									</tr>
								</tbody>
							</table>
							
							<table class="variations field" cellspacing="0">
								<tbody>
									
									<tr>
										<td colspan="2" class="label">Tipo de lente</td>
									</tr>

									<tr>
										<td colspan="2" class="label-sub-title">
											<div class="help-link" data-show-hb-id="tipo-de-lente">Te ayudamos a elegir tu tipo de lentes</div>
										</td>
									</tr>
									
									<tr>
										<td class="value">
											<div class="wrapper">
												<div class="label-wrapper">
													
														<img src="http://verwell.local/wp-content/uploads/2023/12/glasses-006.png">
														<label for="tdl-monofocal">Monofocal</label>
												</div>
													
												<input type="radio" name="tipo-de-lente" value="monofocal" id="tdl-monofocal"/>
												<div class="item-content">
													<p class="short-desc">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
												</div>
												<div class="select-buton outer">
													<div class="select-buton">Escoger</div>
												</div>
												
											</div>
										</td>
									</tr>

									<tr>
										<td class="value">
											<div class="wrapper">
												<div class="label-wrapper">
													<img src="http://verwell.local/wp-content/uploads/2023/12/glasses-005.png">
													<label for="tdl-bifocal-tradicional">Bifocal</label>
												</div>
												<input type="radio" name="tipo-de-lente" value="bifocal-tradicional" id="tdl-bifocal-tradicional"/> 
												<div class="item-content">
													<p class="short-desc">Lorem ipsum dolor sit amet, consectetur adipisicing elit.</p>
												</div>
												<div class="select-buton outer">
													<div class="select-buton">Escoger</div>
												</div>
											</div>
										</td>
									</tr>

									<tr>
										<td class="value">
											<div class="wrapper">
												<div class="label-wrapper">
													<img src="http://verwell.local/wp-content/uploads/2023/12/glasses-004.png">
													<label for="tdl-multifocal-cvc">Multifocal campo visual corto</label>
												</div>
												<input type="radio" name="tipo-de-lente" value="multifocal-cvc" id="tdl-multifocal-cvc"/> 
												<div class="item-content">
													<p class="short-desc">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
												</div>
												<div class="select-buton outer">
													<div class="select-buton">Escoger</div>
												</div>
											</div>
										</td>
									</tr>

								</tbody>
							</table>
						</div>
						<div class="nav-buttons">
							<div class="next">Siguiente</div>
						</div>
					</div>
				</div>
				
				<div class="swiper-slide">
					<div class="step step-2">
						<div class="title"></div>
						
						<div class="content">
							<table class="variations field" cellspacing="0">
								<tbody>
									
									<tr>
										<td colspan="2" class="label">Material del cristal</td>
									</tr>

									<tr>
										<td colspan="2" class="label-sub-title">
											<div class="help-link" data-show-hb-id="material-cristal">Te ayudamos a elegir el material de tus cristales</div>
										</td>
									</tr>
									
									<tr>
										<td class="value">
											<div class="wrapper">
												<div class="label-wrapper">
													<img src="http://verwell.local/wp-content/uploads/2023/12/glasses-001.png">
													<label for="ml-organico-156">Organico 1.56</label>
												</div>
												<input type="radio" name="material-lente" value="organico-156" id="ml-organico-156"/>
												<div class="item-content">
													<p class="short-desc">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
												</div>
												<div class="select-buton outer">
													<div class="select-buton">Escoger</div>
												</div>
											</div>
										</td>
									</tr>

									<tr>
										<td class="value">
											<div class="wrapper">
												<div class="label-wrapper">
													<img src="http://verwell.local/wp-content/uploads/2023/12/glasses-002.png">
													<label for="ml-policarbonato-159">Policarbonato 1.59</label>
												</div>
												<input type="radio" name="material-lente" value="policarbonato-159" id="ml-policarbonato-159"/> 
												<div class="item-content">
													<p class="short-desc">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
												</div>
												<div class="select-buton outer">
													<div class="select-buton">Escoger</div>
												</div>
											</div>
										</td>
									</tr>

									<tr>
										<td class="value">
											<div class="wrapper">
												<div class="label-wrapper">
													<img src="http://verwell.local/wp-content/uploads/2023/12/glasses-003.png">
													<label for="ml-alto-indice-167">Alto indice 1.67</label>
												</div>
												<input type="radio" name="material-lente" value="alto-indice-167" id="ml-alto-indice-167"/>
												<div class="item-content">
													
													<p class="short-desc">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
												</div>
												<div class="select-buton outer">
													<div class="select-buton">Escoger</div>
												</div>
											</div>
										</td>
									</tr>

								</tbody>
							</table>
							
						</div>

						<div class="nav-buttons">
							<div class="forward">Volver</div>
							<div class="next">Siguiente</div>
						</div>
					</div>
				</div>

				<div class="swiper-slide">
					<div class="step step-3">
						<div class="title"></div>
						<div class="content">
							
							<table class="variations field" cellspacing="0">
								<tbody>
									
									<tr>
										<td colspan="2" class="label">Tratamiento del cristal</td>
									</tr>
									
									<tr>
										<td class="value">
											<div class="wrapper">
												<div class="label-wrapper">
													<img src="http://verwell.local/wp-content/uploads/2023/12/glasses-001.png">
													<label for="tc-antireflejo-tradicional">Antireflejo tradicional</label>
												</div>
												<input type="radio" name="tratamiento-cristal" value="antireflejo-tradicional" id="tc-antireflejo-tradicional"/> 
												<div class="item-content">
													
													<p class="short-desc">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
													<div class="option-aditional-imgs">
														<div class="img-itm">
															<img src="http://verwell.local/wp-content/uploads/2024/01/Logo-Crizal-Sapphire.png">
														</div>
														<div class="img-itm">
															<img src="http://verwell.local/wp-content/uploads/2024/01/flg-usa.png">
														</div>
													</div>
												</div>
												<div class="select-buton outer">
													<div class="select-buton">Escoger</div>
												</div>
											</div>
										</td>
									</tr>

									<tr>
										<td class="value">
											<div class="wrapper">
												<div class="label-wrapper">
													<img src="http://verwell.local/wp-content/uploads/2023/12/glasses-002.png">
													<label for="tc-antireflejo-filtro-azul">Antireflejo con filtro azul</label>
												</div>
												<input type="radio" name="tratamiento-cristal" value="antireflejo-filtro-azul" id="tc-antireflejo-filtro-azul"/> 
												<div class="item-content">
													
													<p class="short-desc">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
													<div class="option-aditional-imgs">
														<div class="img-itm">
															<img src="http://verwell.local/wp-content/uploads/2024/01/Logo-Crizal-Prevencia.png">
														</div>
														<div class="img-itm">
															<img src="http://verwell.local/wp-content/uploads/2024/01/flg-italy.png">
														</div>
													</div>
												</div>
												<div class="select-buton outer">
													<div class="select-buton">Escoger</div>
												</div>
											</div>
										</td>
									</tr>

									<tr>
										<td class="value">
											<div class="wrapper">
												<div class="label-wrapper">
													<img src="http://verwell.local/wp-content/uploads/2023/12/glasses-003.png">
													<label for="tc-fotocromatico+ar">Fotocromático AR</label>
												</div>
												<input type="radio" name="tratamiento-cristal" value="fotocromatico+ar" id="tc-fotocromatico+ar"/> 
												<div class="item-content">
													
													<p class="short-desc">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
													<div class="option-aditional-imgs">
														<div class="img-itm">
															<img src="http://verwell.local/wp-content/uploads/2024/01/Logo-Transitions-Xtractive.png">
														</div>
														<div class="img-itm">
															<img src="http://verwell.local/wp-content/uploads/2024/01/flg-germany.png">
														</div>
													</div>
												</div>
												<div class="select-buton outer">
													<div class="select-buton">Escoger</div>
												</div>
											</div>
										</td>
									</tr>

									<tr>
										<td class="value">
											<div class="wrapper">
												<div class="label-wrapper">
													<img src="http://verwell.local/wp-content/uploads/2023/12/glasses-003.png">
													<label for="tc-fotocromatico+ar+filtro-azul">Fotocromático AR + Filtro azul</label>
												</div>
												<input type="radio" name="tratamiento-cristal" value="fotocromatico+ar+filtro-azul" id="tc-fotocromatico+ar+filtro-azul"/> 
												<div class="item-content">
													
													<p class="short-desc">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
													<div class="option-aditional-imgs">
														<div class="img-itm">
															<img src="http://verwell.local/wp-content/uploads/2024/01/Logo-Transitions-Xtractive.png">
														</div>
														<div class="img-itm">
															<img src="http://verwell.local/wp-content/uploads/2024/01/flg-germany.png">
														</div>
													</div>
												</div>
												<div class="select-buton outer">
													<div class="select-buton">Escoger</div>
												</div>
											</div>
										</td>
									</tr>

									<tr>
										<td class="value">
											<div class="wrapper">
												<div class="label-wrapper">
													<img src="http://verwell.local/wp-content/uploads/2023/12/glasses-003.png">
													<label for="tc-tenido-para-sol">Teñidos para el sol</label>
												</div>
												<input type="radio" name="tratamiento-cristal" value="tenido-para-sol" id="tc-tenido-para-sol"/> 
												<div class="item-content">
													
													<p class="short-desc">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
													<div class="option-aditional-imgs">
														<div class="img-itm">
															<img src="http://verwell.local/wp-content/uploads/2024/01/Logo-Transitions-Xtractive.png">
														</div>
														<div class="img-itm">
															<img src="http://verwell.local/wp-content/uploads/2024/01/flg-germany.png">
														</div>
													</div>
												</div>
												<div class="select-buton outer">
													<div class="select-buton">Escoger</div>
												</div>
											</div>
										</td>
									</tr>

									<tr>
										<td class="value">
											<div class="wrapper">
												<div class="label-wrapper">
													<img src="http://verwell.local/wp-content/uploads/2023/12/glasses-003.png">
													<label for="tc-polarizados+ar-para-sol">Polarizados + AR para el sol</label>
												</div>
												<input type="radio" name="tratamiento-cristal" value="polarizados+ar-para-sol" id="tc-polarizados+ar-para-sol"/> 
												<div class="item-content">
													
													<p class="short-desc">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
													<div class="option-aditional-imgs">
														<div class="img-itm">
															<img src="http://verwell.local/wp-content/uploads/2024/01/Logo-Transitions-Xtractive.png">
														</div>
														<div class="img-itm">
															<img src="http://verwell.local/wp-content/uploads/2024/01/flg-germany.png">
														</div>
													</div>
												</div>
												<div class="select-buton outer">
													<div class="select-buton">Escoger</div>
												</div>
											</div>
										</td>
									</tr>

									<tr>
										<td class="value">
											<div class="wrapper">
												<div class="label-wrapper">
													<img src="http://verwell.local/wp-content/uploads/2023/12/glasses-003.png">
													<label for="tc-polarizados-espejados+ar-para-sol">Polarizados espejados + AR para el sol</label>
												</div>
												<input type="radio" name="tratamiento-cristal" value="polarizados-espejados+ar-para-sol" id="tc-polarizados-espejados+ar-para-sol"/> 
												<div class="item-content">
													
													<p class="short-desc">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
													<div class="option-aditional-imgs">
														<div class="img-itm">
															<img src="http://verwell.local/wp-content/uploads/2024/01/Logo-Transitions-Xtractive.png">
														</div>
														<div class="img-itm">
															<img src="http://verwell.local/wp-content/uploads/2024/01/flg-germany.png">
														</div>
													</div>
												</div>
												<div class="select-buton outer">
													<div class="select-buton">Escoger</div>
												</div>
											</div>
										</td>
									</tr>

								</tbody>
							</table>

						</div>
						<div class="nav-buttons">
							<div class="forward">Volver</div>
						</div>

					</div>
				</div>
				
				
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