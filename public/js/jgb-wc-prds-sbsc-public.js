let swiper;
let selector = '#pum-22562';
let selectedFeatures = [
	{
		'field':'receta',
		'label':'Receta',
		'vlSltr':"input[name='receta']",
		'value':null
	},
	{
		'field':'tipo-de-lente',
		'label':'Tipo de lente',
		'vlSltr':"input[name='tipo-de-lente']",
		'value':null
	},
	{
		'field':'material-lente',
		'label':'Material del lente',
		'vlSltr':"input[name='material-lente']",
		'value':null
	},
	{
		'field':'tratamiento-cristal',
		'label':'Tratamiento del cristal',
		'vlSltr':"input[name='tratamiento-cristal']",
		'value':null
	}
];
const preAdditionalFieldsStepIndex = 2;
const cartFormSltr = "form.cart";
const crystalsPrices = [
	{
		'receta':'si',
		'tipo-de-lente':'monofocal',
		'material-lente':'organico-156',
		'tratamiento-cristal':'antireflejo-tradicional',
		'price': 25000
	},
	{
		'receta':'si',
		'tipo-de-lente':'monofocal',
		'material-lente':'organico-156',
		'tratamiento-cristal':'antireflejo-filtro-azul',
		'price': 40000
	},
	{
		'receta':'si',
		'tipo-de-lente':'monofocal',
		'material-lente':'organico-156',
		'tratamiento-cristal':'fotocromatico+ar',
		'price': 45000,
		'aditional':[
			{
				'fldId':'color-fotocromatico',
				'fldFormName':'color-fotocromatico',
				'label':'Color fotocromático',
				'inputType': 'select-color',
				'options':[
					{
						'color-code':'#6F4E37',
						'label':'Café',
						'value':'cafe'
					},
					{
						'color-code':'#808080',
						'label':'Gris',
						'value':'gris'
					}
				]
			}
		]
	},
	{
		'receta':'si',
		'tipo-de-lente':'monofocal',
		'material-lente':'organico-156',
		'tratamiento-cristal':'fotocromatico+ar+filtro-azul',
		'price': 65000
	},
	{
		'receta':'si',
		'tipo-de-lente':'monofocal',
		'material-lente':'organico-156',
		'tratamiento-cristal':'tenido-para-sol',
		'price': 30000
	},
	{
		'receta':'si',
		'tipo-de-lente':'monofocal',
		'material-lente':'organico-156',
		'tratamiento-cristal':'polarizados+ar-para-sol',
		'price': 80000
	},

	{
		'receta':'si',
		'tipo-de-lente':'monofocal',
		'material-lente':'organico-156',
		'tratamiento-cristal':'polarizados-espejados+ar-para-sol',
		'price': 100000
	},


	{
		'receta':'si',
		'tipo-de-lente':'monofocal',
		'material-lente':'policarbonato-159',
		'tratamiento-cristal':'antireflejo-tradicional',
		'price': 45000
	},
	{
		'receta':'si',
		'tipo-de-lente':'monofocal',
		'material-lente':'policarbonato-159',
		'tratamiento-cristal':'antireflejo-filtro-azul',
		'price': 65000
	},
	{
		'receta':'si',
		'tipo-de-lente':'monofocal',
		'material-lente':'policarbonato-159',
		'tratamiento-cristal':'fotocromatico+ar',
		'price': 80000
	},

	{
		'receta':'si',
		'tipo-de-lente':'monofocal',
		'material-lente':'policarbonato-159',
		'tratamiento-cristal':'fotocromatico+ar+filtro-azul',
		'price': 145000
	},
	{
		'receta':'si',
		'tipo-de-lente':'monofocal',
		'material-lente':'policarbonato-159',
		'tratamiento-cristal':'tenido-para-sol',
		'price': 45000
	}
	
];

function cpFirstMatch(){
	const ftrdCombsVls = {
		'receta':null,
		'tipo-de-lente':null,
		'material-lente':null,
		'tratamiento-cristal':null
	};
	let i = 0;

	selectedFeatures.forEach(function(e){
		//console.log('Valor de ' + e.label + ': ' + e.value + '.');
		ftrdCombsVls[e.field] = e.value;
	});

	if( ftrdCombsVls['receta'] == null ){
		return  [null, null];
	}

	if( ftrdCombsVls['tipo-de-lente'] == null ){
		return [null, null];
	}

	if( ftrdCombsVls['material-lente'] == null ){
		return [null, null];
	}

	if( ftrdCombsVls['tratamiento-cristal'] == null ){
		return [null, null];
	}

	for(i=0; i<  crystalsPrices.length ; i++){
		if(
			crystalsPrices[i]['receta'] == ftrdCombsVls['receta'] &&
			crystalsPrices[i]['tipo-de-lente'] == ftrdCombsVls['tipo-de-lente'] &&
			crystalsPrices[i]['material-lente'] == ftrdCombsVls['material-lente'] &&
			crystalsPrices[i]['tratamiento-cristal'] == ftrdCombsVls['tratamiento-cristal']
		){
			return [crystalsPrices[i],i];
		}
	}

	return [null, null];
}

(function( $ ) {
	'use strict';

	function desplegarSFs(){
		const sfcSlctr = ".selected-features-container";
		let html = '<table><tbody>';

		$(selectedFeatures).each(function(i,e){
			//console.log('Valor de ' + e.label + ': ' + e.value + '.');
			if( e.value != null ){
				html += '<tr><td>'+ e.label + ':</td><td>' + e.value + '</td></tr>';
			}
		});

		html += '</tbody></table>';
		$(sfcSlctr).html(html);
	}

	

	function checkAdditionalFields(){
		const actStep = swiper.activeIndex;
		if( actStep == preAdditionalFieldsStepIndex ){

			/* add new step panel si no existe previamente */

			/* generar nuevo placeholder wrapper */

			/* renderizar nuevos campos adicionales */

			
		}
	}

	function desplegarPrice(){
		const sfcSlctr = ".main-container .price-container";
		
		let price = null;
		let intf = null;
		let i;
		let cp;
		
		[cp,i] = cpFirstMatch();

		if( cp != null){ price = cp['price']; }

		if( price != null ){
			intf = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' });
			$(sfcSlctr).text( intf.format(price) );
			$(cartFormSltr + ' input[name="precio"]').val( price );
		} else {
			$(sfcSlctr).text('');
		}
	}

	$(document).on( 'pumBeforeOpen', selector, function(evnt){
		const speed = 500;
		swiper = new Swiper('#pum-22562 .swiper', {
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

		swiper.on('slidePrevTransitionEnd',function(){
			const actSldr = swiper.activeIndex;
			let radio;
			switch(actSldr){
				case 0:
					radio = $(selectedFeatures[1].vlSltr + ':checked');
					$(radio).prop('checked', false);
					selectedFeatures[1].value = null;
					break;

				case 1:
					radio = $(selectedFeatures[2].vlSltr + ':checked');
					$(radio).prop('checked', false);
					selectedFeatures[2].value = null;
					break;

			}
			desplegarSFs();
			desplegarPrice();
			console.log('Slider activo: ' + actSldr + '.');
		});


		$(selectedFeatures[0].vlSltr).click(function(){
			selectedFeatures[0].value = $(selectedFeatures[0].vlSltr + ':checked').val();
			$(cartFormSltr + ' ' + selectedFeatures[0].vlSltr).val( selectedFeatures[0].value );
			desplegarSFs();
			desplegarPrice();
			checkAdditionalFields();
		});

		$(selectedFeatures[1].vlSltr).click(function(){
			selectedFeatures[1].value = $(selectedFeatures[1].vlSltr + ':checked').val();
			$(cartFormSltr + ' ' + selectedFeatures[1].vlSltr).val( selectedFeatures[1].value );
			desplegarSFs();
			desplegarPrice();
			checkAdditionalFields();
		});

		$(selectedFeatures[2].vlSltr).click(function(){
			selectedFeatures[2].value = $(selectedFeatures[2].vlSltr + ':checked').val();
			$(cartFormSltr + ' ' + selectedFeatures[2].vlSltr).val( selectedFeatures[2].value );
			desplegarSFs();
			desplegarPrice();
			checkAdditionalFields();
		});

		$(selectedFeatures[3].vlSltr).click(function(){
			selectedFeatures[3].value = $(selectedFeatures[3].vlSltr + ':checked').val();
			$(cartFormSltr + ' ' + selectedFeatures[3].vlSltr).val( selectedFeatures[3].value );
			desplegarSFs();
			desplegarPrice();
			checkAdditionalFields();
		});

		$('img.spg').attr('src',$('.wp-post-image').attr('src'));

		$('.swiper-slide .step .nav-buttons .next').click(function(){
			swiper.slideNext(speed);
		});

		$('.swiper-slide .step .nav-buttons .forward').click(function(){
			swiper.slidePrev(speed);
		});

		$('.help-box .close-buton').click((evnt)=>{
			if( ! $(evnt.target).closest('.help-box').hasClass('hidden') ){
				$(evnt.target).closest('.help-box').addClass('hidden');
			}
		});

		$('.step .help-link').each((i,e)=>{
			$(e).click((evnt)=>{
				const hbDataIdToShow = $(evnt.target).data('show-hb-id');
				const hbets = $('.help-box[data-hb-id="' + hbDataIdToShow + '"]');
				if( $(hbets).hasClass('hidden') ){
					$(hbets).removeClass('hidden');
				}
			})
		});

	});

})( jQuery );
