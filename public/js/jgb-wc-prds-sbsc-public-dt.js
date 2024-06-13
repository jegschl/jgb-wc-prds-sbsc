let swiper;
let selector = '#pum-22562';
let selectedFeatures = [];
let maxStepIndex;
let dtFields, dtChoicesAvailables, dtChoicesCombinations, dtVcsItems, dtItemsData, dtItemsField;
let stepPriorityCheks = [];

const speed = 500;
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

	maxStepIndex  = dtFields().max('priority_in_step');
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


function getFieldsPriority(){
	let r = [];
	dtFields().order('priority_in_step').each( (record)=>{
		if( r[ record['priority_in_step'] ] == undefined ){
			r[ record['priority_in_step'] ] = [];
		}
		r[ record['priority_in_step'] ].push( record['slug'] );
	} );
	return r;
}

function get_first_field_to_render(){

	const fieldsPriority = getFieldsPriority();

	const priorities = dtFields().order('priority_in_step').distinct('priority_in_step');

	const options = []; 
	
	dtChoicesAvailables({parent_field_id:null,parent_on_browser_selected_slug_value:null}).each(function(record){
		
		const choice = {
			slug: record['selectable_value_slug'], 
			label: record['selectable_value_label']
		}; 
		
		options.push( choice );

	});
	
	const r = [
		{
			slug: fieldsPriority[ priorities[0] ],
			options: options
		}		
	];
	
	return r;
}

function get_next_fields_to_render( parentFfieldId, parentFieldvalueSelected ){
	
	const q = {parent_field_id:parentFfieldId.toString(), parent_on_browser_selected_slug_value:parentFieldvalueSelected.toString() }; 

	let r = [];

	let fieldsToRender = [];

	let i = 0;
	
	dtChoicesAvailables(q).each(function(record){

		const fieldSlug = getFieldSlugById( record['field_id'] );
		
		let fieldOptionsUpdated = false;

		const currentFieldOption = {
			slug: record['selectable_value_slug'],
			label: record['selectable_value_label']
		};

		for( i=0; i<r.length; i++ ){

			if( r[i]['slug'] == fieldSlug ){

				r[i]['options'].push( currentFieldOption );

				fieldOptionsUpdated = true;

				break;

			}
		}

		if( !fieldOptionsUpdated ){

			r.push({
				slug: fieldSlug,
				options: [ currentFieldOption ]
			});

		}

	});

	for(i=0; i<r.length; i++){
		if( ( r[i]['options'] != undefined ) && ( r[i]['options'].length > 0 ) ){
			fieldsToRender.push( r[i] );
		}
	}

	return fieldsToRender;

}

function renderStep( parentFfieldId, parentFieldvalueSelected ){

	let incrmtr;

	if( parentFfieldId == undefined && parentFieldvalueSelected == undefined ){
		incrmtr = 1;
	} else {
		incrmtr = 2;
	}

	const step = swiper.activeIndex + incrmtr;

	let fieldsToRender = [];

	let templatesToRender = [];

	if( parentFfieldId == undefined && parentFieldvalueSelected == undefined ){
		fieldsToRender = get_first_field_to_render();
	} else {
		fieldsToRender = get_next_fields_to_render( parentFfieldId, parentFieldvalueSelected );
	}

	const stepFieldsTobeRendered = {
		'stepIndex': swiper.activeIndex,
		'fields': fieldsToRender
	};

	

	let ts = JGB_WPSBSC_DATA['beginStepWraperTpl'].replace("{{step_index}}",step.toString());

	ts = ts.replace("{{title}}", JGB_WPSBSC_DATA['stepTitles'][(step-1).toString()] );

	templatesToRender.push( ts );

	
	fieldsToRender.forEach( ( fld )=>{
		let fieldSlug = null;
		if( typeof fld['slug'] == 'string' ){
			fieldSlug = fld['slug'];
		} else {
			fieldSlug = fld['slug'][0];
		}

		const fieldOptions = fld['options'];

		let optionsHRd = '';

		// unir todas las opciones disponibles en un solo string.
		fieldOptions.forEach( (opt,i)=>{
			optionsHRd += i>0 ? "\n" : '';
			optionsHRd += JGB_WPSBSC_DATA['fieldsTemplates'][ fieldSlug ]['options'][ opt['slug'] ];
		});

		const fieldWrapperTpl = JGB_WPSBSC_DATA['fieldsTemplates'][ fieldSlug ]['wrapper'][ fieldSlug ].replace("{{#radio-options}}", optionsHRd );

		templatesToRender.push( fieldWrapperTpl );

	});
	
		
	templatesToRender.push( JGB_WPSBSC_DATA['endStepWraperTpl'] );

	swiper.appendSlide( templatesToRender.join("\n") );

	setEventHandlersForAvailablesValuesChoicesSelectors();

}

function renderFirstStep(){

	swiper.removeAllSlides();

	renderStep();

}

function renderNextStep( fieldId, valueSelected ){

	const actSldr = swiper.activeIndex + 1;

	removeSlidesFrom( actSldr );

	renderStep( fieldId, valueSelected );

}

function removeSlidesFrom( index ){
	let i = maxStepIndex;
	for(i; i >= index; i--){
		swiper.removeSlide(i);
	}
}

function desplegarSFs(){
	(function( $ ) {
		const sfcSlctr = ".selected-features-container";
		let html = '<table><tbody>';

		$(selectedFeatures).each(function(i,e){
			//console.log('Valor de ' + e.label + ': ' + e.value + '.');
			if( e.value != null ){
				html += '<tr><td>'+ e.label + ':</td><td>' + e.valueLabel + '</td></tr>';
			}
		});

		html += '</tbody></table>';
		$(sfcSlctr).html(html);

	})( jQuery );
}

function desplegarPrice(){
	(function( $ ) {

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

	})( jQuery );
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
			const valueLabel = $(fatherEl).find('label').text();

			if( (fatherEl.length!=undefined) && fatherEl.length > 0 ){
				$(fatherEl).find('input[type="radio"]').prop('checked', true);
				$(fatherEl).find('input[type="radio"]').trigger('click');
			}

			if( (fatherEl.length!=undefined) && fatherFieldEl.length > 0 ){
				$(fatherFieldEl).find('.value .select-buton.outer, .value .option-buton.outer').removeClass('selected');
				$(fatherFieldEl).find('.value .buton-group.multiple').data('opts-sels','');
			}

			$(fatherEl).find('.select-buton.outer').addClass('selected');
			setFeatureValue( fieldSlug, valueSelected, valueLabel );
			desplegarSFs();
			desplegarPrice();
			renderNextStep( fieldId, valueSelected );
			//checkButtonsNavigationStatus();
			swiper.slideNext(speed);
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

function getFieldSlugById( id ){
	const s = dtFields({id:id.toString() }).first().slug;
	return s;
}

function setFeatureValue( fieldSlug, valueSlug, valueLabel ){
	let i;
	for(i=0; i<selectedFeatures.length; i++){
		if( selectedFeatures[i].field == fieldSlug ){
			selectedFeatures[i].value = valueSlug;
			selectedFeatures[i].valueLabel = valueLabel;
		}
	}
}



function checkButtonsNavigationStatus(){
	(function( $ ) {
		'use strict';
		const btnForward = $('.left-container .nav-buttons .forward');
		const btnNext = $('.left-container .nav-buttons .next');
		
		if( swiper.isBeginning ){
			btnForward.removeClass('hidden').addClass('hidden');
			
		} else {
			btnForward.removeClass('hidden');
		}

		if( swiper.isEnd ){
			btnNext.removeClass('hidden').addClass('hidden');
		} else {
			btnNext.removeClass('hidden');
		}
		
	})( jQuery );
		
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

	

	function checkAdditionalFields(){
		const actStep = swiper.activeIndex;
		if( actStep == preAdditionalFieldsStepIndex ){

			/* add new step panel si no existe previamente */

			/* generar nuevo placeholder wrapper */

			/* renderizar nuevos campos adicionales */

			
		}
	}

	$(document).on( 'pumBeforeOpen', selector, function(evnt){

		
		swiper = new Swiper('#pum-22562 .swiper', {
			// Optional parameters
			direction: 'horizontal',
			loop: false,

			// If we need pagination
			pagination: {
				el: '.swiper-pagination',
			},

			// And if we need scrollbar
			scrollbar: {
				el: '.swiper-scrollbar',
			},

			autoHeight: false,

			//slideChangeTransitionEnd: checkButtonsNavigationStatus
			//slideChange: checkButtonsNavigationStatus
		});

		swiper.on('slideChange',checkButtonsNavigationStatus);

		swiper.on('slidePrevTransitionEnd',function(){
			
		});


		loadFeatures();

		renderFirstStep();

		setEventHandlersForSelectedFeaturesRadios();

		checkButtonsNavigationStatus();

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
