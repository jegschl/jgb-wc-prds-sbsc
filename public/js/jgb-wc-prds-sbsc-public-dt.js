let swiper;
let selector = '#pum-22562';
let selectedFeatures = [];
let maxStepIndex;
let dtFields, dtChoicesAvailables, dtChoicesCombinations, dtVcsItems, dtItemsData, dtItemsField;


const preAdditionalFieldsStepIndex = 2;
const cartFormSltr = "form.cart";
const crystalsPrices = [];


TAFFY.extend('max', function (column) {

	this.context({
		results: this.getDBI().query(this.context())
 	});

	var maxUntilNow = -Infinity;

	TAFFY.each(this.context().results,function (r) {
		if( r[column] > maxUntilNow ){
			maxUntilNow = r[column];
		}
    })

	return maxUntilNow;

});



function initializeDb(){
	dtFields 				= TAFFY( JGB_WPSBSC_DATA['dtDataBase']['fields']  );
	dtChoicesAvailables 	= TAFFY( JGB_WPSBSC_DATA['dtDataBase']['choices_availables']  );
	dtChoicesCombinations 	= TAFFY( JGB_WPSBSC_DATA['dtDataBase']['choices_combinations']  );
	dtVcsItems 				= TAFFY( JGB_WPSBSC_DATA['dtDataBase']['vcs_items']  );
	dtItemsData 			= TAFFY( JGB_WPSBSC_DATA['dtDataBase']['items_data']  );
	dtItemsField 			= TAFFY( JGB_WPSBSC_DATA['dtDataBase']['items_field']  );

	maxStepIndex  = dtFields().max('step_index');
}

function loadFeatures(){
	selectedFeatures = [];
	dtFields().each( (record)=>{
		const feature = {
			'field': record["slug"],
			'label': record["name"],
			'vlSltr': "input[name='" + record["slug"] + "']",
			'value': null
		};
		selectedFeatures.push(feature);
	});

}

function getFeatureValue( fieldSlug ){
	let i;
	for(i=0; i<selectedFeatures.length; i++){
		if( selectedFeatures[i].field == fieldSlug ){
			return selectedFeatures[i].value;
		}
	}
}

function removeValuesWithoutParent( step ){
	const hesRadioInputs = '.swiper-slide .step.step-' + step + ' .value .wrapper input[type="radio"]';
	let trValuesToRemove = [];
	
	jQuery(hesRadioInputs).each( (i,e)=>{

		// obtener nombre de campo y slug del valor seleccionable.
		const fieldId = jQuery(e).closest('table.field').data('field-id');
		const valueSlug = jQuery(e).val();
		const fieldSlug = getFieldSlugById( fieldId );

		/* buscar en la tabla de available choices. */
		dtChoicesAvailables({field_id:fieldId,selectable_value_slug:valueSlug}).each( (record)=>{
			/*  si el valor seleccionable tiene par campo-valor padre hay que comparar el valor
				con el subelemento value del elemento en selectedFeatures cuyo subelemento 
				field sea igual al slug del campo padre indicado en el registro. */
			let tr;
			const parentFieldSlug = getFieldSlugById( record['parent_field_id'] );
			
			/* Si el valor es diferente entonces hay que buscar el elmento closest('tr')
	   		   y agregarlo al array trValuesToRemove. */
			if( getFeatureValue( parentFieldSlug ) != record['parent_on_browser_selected_slug_value'] ){
				tr = jQuery(e).closest('tr');
				trValuesToRemove.push( tr );
			}
		});

	});

	/* Recorrer cada elemento en trValuesToRemove y removerlo del DOM. */
	let j;
	for( j = 0; j < trValuesToRemove.length; j++ ){
		trValuesToRemove[j].remove()
			
	}
}

function renderStep( step ){

	let i;

	if( step > 0 ){

		for(i = step; i <= maxStepIndex; i++){

			swiper.removeSlide(i);

		}

	}

	let fieldsToRender = [];

	let ts = JGB_WPSBSC_DATA['beginStepWraperTpl'].replace("{{step_index}}",step.toString());

	ts = ts.replace("{{title}}", JGB_WPSBSC_DATA['stepTitles'][step.toString()] );

	fieldsToRender.push( ts );

	dtFields( { step_index:step.toString() } ).each( (record)=>{

		fieldsToRender.push( JGB_WPSBSC_DATA['fieldsTemplates'][ record['slug'] ] );

	} );

	fieldsToRender.push( JGB_WPSBSC_DATA['endStepWraperTpl'] );

	swiper.appendSlide( fieldsToRender.join('') );

	removeValuesWithoutParent( step );

	setEventHandlersForAvailablesValuesChoicesSelectors();

}

function setEventHandlersForAvailablesValuesChoicesSelectors(){

	(function( $ ) {
		'use strict';
		$('.step .value .wrapper .select-buton:not(.outer)').off('click');
		$('.step .value .wrapper .select-buton:not(.outer)').click(function(evnt){

			const fatherEl = $(evnt.target).closest('.wrapper');
			const fatherFieldEl = $(evnt.target).closest('table.field');
			const fieldId = $(fatherFieldEl).data('field-id');
			const fieldSlug = getFieldSlugById( fieldId );
			const valueSelected = $(fatherEl).find('input[type="radio"]').val();

			if( (fatherEl.length!=undefined) && fatherEl.length > 0 ){
				$(fatherEl).find('input[type="radio"]').prop('checked', true);
				$(fatherEl).find('input[type="radio"]').trigger('click');
			}

			if( (fatherEl.length!=undefined) && fatherFieldEl.length > 0 ){
				$(fatherFieldEl).find('.value .select-buton.outer, .value .option-buton.outer').removeClass('selected');
				$(fatherFieldEl).find('.value .buton-group.multiple').data('opts-sels','');
			}

			$(fatherEl).find('.select-buton.outer').addClass('selected');
			setFeatureValue( fieldSlug, valueSelected );
			renderNextStep();
		});

		$('.step .value .wrapper .option-buton:not(.outer)').off('click');
		$('.step .value .wrapper .option-buton:not(.outer)').click(function(evnt){
			const fatherEl = $(evnt.target).closest('.wrapper');
			const fatherFieldEl = $(evnt.target).closest('table.field');
			const fatherOptEl   = $(evnt.target).closest('.buton-group');
			const optionSelected = $(evnt.target).data('option');
			let i;
			let rawBgos = $(fatherOptEl).data('opts-sels');
			let arBgos;
			if( rawBgos != ""){
				arBgos = rawBgos.split(',');
			} else {
				arBgos = Array();
			}
			

			if( (fatherEl.length!=undefined) && fatherEl.length > 0 ){
				$(fatherEl).find('input[type="radio"]').prop('checked', true);
				$(fatherEl).find('input[type="radio"]').trigger('click');
			}

			if( (fatherEl.length!=undefined) && fatherFieldEl.length > 0 ){
				$(fatherFieldEl).find('.value .select-buton.outer').removeClass('selected');
				
			}
			$(fatherEl).find('.select-buton.outer').addClass('selected');


			if( $(fatherOptEl).hasClass('multiple') ){
				i = arBgos.indexOf( optionSelected );
				if( i >= 0 ){
					arBgos.splice( i, 1 );
				} else {
					arBgos.push( optionSelected );
				}

				rawBgos = arBgos.join(',');
				$(fatherOptEl).data('opts-sels',rawBgos);

				$(fatherOptEl).find('.option-buton.outer').removeClass('selected');

				$(fatherOptEl).find('.option-buton:not(.outer)').each( (i,e)=>{
					const btnOpt = $(e).data('option');
					if( arBgos.indexOf( btnOpt ) >= 0 ){
						$(e).closest('.option-buton.outer').addClass('selected');
					}
				} );
			}
		});
	})( jQuery );
}

function renderFirstStep(){
	renderStep(0);
}

function renderNextStep(){
	const actSldr = swiper.activeIndex;
	renderStep( actSldr + 1 );
}

function getFieldSlugById( id ){
	return dtFields({id:id}).first().slug;
}

function setFeatureValue( fieldSlug, value ){
	let i;
	for(i=0; i<selectedFeatures.length; i++){
		if( selectedFeatures[i].field == fieldSlug ){
			selectedFeatures[i].value = value;
		}
	}
}



function checkButtonsNavigationStatus(){
	
	if( swiper.activeIndex < 1 ){
		if(	!$('.left-container .nav-buttons .forward').hasClass('hidden') ){
			$('.left-container .nav-buttons .forward').addClass('hidden');
		}
	} else {
		if(	$('.left-container .nav-buttons .forward').hasClass('hidden') ){
			$('.left-container .nav-buttons .forward').removeClass('hidden');
		}
	}
		
}



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

	function setEventHandlersForSelectedFeaturesRadios(){
		let i;
		for(i=0; i<selectedFeatures.length; i++){
			if( $(selectedFeatures[i].vlSltr).length > 0 ){
				$(selectedFeatures[i].vlSltr).off('click');
				$(selectedFeatures[i].vlSltr).click(function(){
					selectedFeatures[i].value = $(selectedFeatures[i].vlSltr + ':checked').val();
					$(cartFormSltr + ' ' + selectedFeatures[i].vlSltr).val( selectedFeatures[i].value );
					desplegarSFs();
					desplegarPrice();
					checkAdditionalFields();
				});
			}
		}
	
	}

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


		loadFeatures();

		renderFirstStep();

		setEventHandlersForSelectedFeaturesRadios();

	

		$('img.spg').attr('src',$('.wp-post-image').attr('src'));

		$('.left-container .nav-buttons .next').off('click');
		$('.left-container .nav-buttons .next').click(function(){
			swiper.slideNext(speed);
		});

		$('.left-container .nav-buttons .forward').off('click');
		$('.left-container .nav-buttons .forward').click(function(){
			swiper.slidePrev(speed);
		});

		$('.help-box .close-buton').off('click');
		$('.help-box .close-buton').click((evnt)=>{
			if( ! $(evnt.target).closest('.help-box').hasClass('hidden') ){
				$(evnt.target).closest('.help-box').addClass('hidden');
			}
		});

		$('.step .help-link').each((i,e)=>{
			$(e).off('click');
			$(e).click((evnt)=>{
				const hbDataIdToShow = $(evnt.target).data('show-hb-id');
				const hbets = $('.help-box[data-hb-id="' + hbDataIdToShow + '"]');
				if( $(hbets).hasClass('hidden') ){
					$(hbets).removeClass('hidden');
				}
			})
		});

		

	});

	$(document).ready( ()=>{
		initializeDb();
	});

})( jQuery );
