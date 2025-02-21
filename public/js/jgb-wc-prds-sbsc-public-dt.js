let swiper;
let ppuMkrSlctr;
let selectedFeatures = [];
let optionsData = [];
let maxStepIndex;
let dtFields, dtChoicesAvailables, dtChoicesCombinations, dtVcsItems, dtItemsData, dtItemsField;
let vrwlIsLastStep = false;

const speed = 500;
const preAdditionalFieldsStepIndex = 2;
const cartFormSltr = "form.cart";

const jwpsbscAfterRenderStep = new CustomEvent('jwpsbscAfterRenderStep');


const eventParamsFeatureValue = {
	'fieldId': null,
	'field': null,
	'value': null,
	'valueLabel': null,
	'valueRegId': null,
	'fieldType': null,
	'label': null,
	'priorityInStep': null,
	'stepOnStore': null
};
const jwpsbscSetFeatureValue = new CustomEvent('jwpsbscSetFeatureValue', {detail: eventParamsFeatureValue});

const eventParamsPrepareFieldHtmlTemplate = {
	'field': null,
	'htmlTpl': null,
	'fieldType': null
};
const jwpsbscPrepareFieldHtmlTemplate = new CustomEvent('jwpsbscPrepareFieldHtmlTemplate', {detail: eventParamsPrepareFieldHtmlTemplate});

const eventParamsPrepareFieldOptionHtmlTemplate = {
	'field': null,
	'option': null,
	'htmlTpl': null,
	'fieldType': null
};
const jwpsbscPrepareFieldOptionHtmlTemplate = new CustomEvent('jwpsbscPrepareFieldOptionHtmlTemplate', {detail: eventParamsPrepareFieldOptionHtmlTemplate});

const eventParamsAfterGetVcsItemfieldSubTypeAdditionalSelectionForRenderStep = {
	'fields': [],
	'combinationString': ''
}
const jwpsbscAfterGetVcsItemfieldSubTypeAdditionalSelectionForRenderStep = new CustomEvent('jwpsbscAfterGetVcsItemfieldSubTypeAdditionalSelectionForRenderStep', {detail: eventParamsAfterGetVcsItemfieldSubTypeAdditionalSelectionForRenderStep});

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
	dtFields().order("priority_in_step asec, id asec").each( (record)=>{
		const feature = {
			'fieldId': record["id"],
			'field': record["slug"],
			'fieldType': 'field',
			'label': record["name"],
			'vlSltr': "input[name='" + record["slug"] + "']",
			'value': null,
			'valueLabel': null
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

function getFeatureValueId( fieldSlug ){
	let i;
	for(i=0; i<selectedFeatures.length; i++){
		if( selectedFeatures[i].field == fieldSlug ){
			return selectedFeatures[i].valueRegId;
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

function get_first_item_to_render(){

	const fieldsPriority = getFieldsPriority();

	const priorities = dtFields().order('priority_in_step').distinct('priority_in_step');

	const options = []; 

	const rawData = dtChoicesAvailables({parents_fv_path:null}).get().sort( (a, b) => parseInt(a.id) - parseInt(b.id) );
	
	rawData.forEach(function(record){
		
		const choice = {
			id: record['id'],
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

function get_fields_to_render_type_vcsItem_field_subType_additional_selection( parentFVPath ){
	let q = { vls_ids_combinations_string:parentFVPath.toString() }; 

	let r = [];

	let j,i = 0;

	dtChoicesCombinations(q).each(function(recordChCmb){
		/* choicesCombinationsIds.push( recordChCmb['id'].toString() ); */

		const q = { id_choice_combination: recordChCmb['id'].toString(), item_type: 'FIELD' };
		
		dtVcsItems(q).each(function(recordVcsItem){
			j = vcsItemCheckInFeatures( recordVcsItem['slug'] );
			if( ( j == null ) || !vcsItemValidationInFeaturesByIndex( j ) ){
				r[i] = { 
					vcsItemId: recordVcsItem['id'].toString(),
					slug: recordVcsItem['slug'],
					vcsItemLabel: recordVcsItem['label'],
					priorityInStep: parseInt( recordVcsItem['priority_in_step'] ),
					type: 'field:additional-selection'
				};
				
				const q = { id: recordVcsItem['id_item'].toString() };

				dtItemsField(q).each(function(recordItemField){

					r[i].subType = recordItemField['type'];
					if( r[i].subType == 'RADIO' ){
						r[i].options = JSON.parse( recordItemField['options'] )['selection-options'];
					} else {
						r[i].options = recordItemField['options'];
					}

				});

				i++;
			}

		});

	});

	eventParamsAfterGetVcsItemfieldSubTypeAdditionalSelectionForRenderStep.fields = r;
	eventParamsAfterGetVcsItemfieldSubTypeAdditionalSelectionForRenderStep.combinationString = parentFVPath;
	document.dispatchEvent( jwpsbscAfterGetVcsItemfieldSubTypeAdditionalSelectionForRenderStep )
	
	r = eventParamsAfterGetVcsItemfieldSubTypeAdditionalSelectionForRenderStep.fields;
	return r;

}

function get_fields_to_render( parentFVPath ){
	let q; // Variable para la query.

	let r = []; // Array que se devolverá al finalizar la función.

	let fieldsToRender = []; // Listado de campos detectados.

	let i = 0; // Contador de iteraciones.

	// La query se construye para filtrar todos las posibles selecciones disponibles
	// para el parentFVPath actual.
	q = {parents_fv_path:parentFVPath.toString()};

	// Se itera en cada Selección disponible del parentFVPath actual.
	dtChoicesAvailables(q).each(function(record){

		// Variables para almacenar las optionItems de cada opción y 
		// el segundo elemento en la destructuración del valor
		// devuelto por loadCUrrentOptionData.
		let cp,cpid;

		// Se obtiene el slug del campo al que pertenece la selección disponible actual
		// ya que se está iterando en cada una de las selecciones disponibles del 
		// parentFVPath actual.
		const fieldSlug = getFieldSlugById( record['field_id'] );
		
		// Bandera para indicar si se actualizó la opción de un campo (que aún no determinamos
		// con certeza cuál es el fín práctico de este flag).
		let fieldOptionsUpdated = false; //////////////////////////

		// Estructura que se usa para almacenar cada una de las opciones de un campo en cada
		// iteración.
		const currentFieldOption = {
			id: record['id'],
			slug: record['selectable_value_slug'],
			label: record['selectable_value_label'],
			arvl: JSON.parse( record['arvl'] ),
			optionItems: []
		};

		/* Recuperando datos de la opción actual */
		if( ( parentFVPath !== undefined )
			&& ( parentFVPath !== null )
			&& ( parentFVPath !== '' ) 
		){
			let optFVPath = parentFVPath;
			optFVPath += ',';
			optFVPath += record['field_id'] + '=' + record['id'];
			[cp,cpid] = loadCUrrentOptionData( optFVPath );

			if( 
				( cp !== undefined ) 
				&& ( cp !== null ) 
				&& ( Array.isArray(cp) )
				&& ( cp.length > 0 )
			){
				currentFieldOption.optionItems = cp;
			}
		}

		for( i=0; i<r.length; i++ ){

			if( r[i]['slug'] == fieldSlug ){

				r[i]['priorityInStep'] = parseInt( dtFields({id:record['field_id']}).first()['priority_in_step'] );

				r[i]['options'].push( currentFieldOption );

				fieldOptionsUpdated = true;

				break;

			}
		}

		if( !fieldOptionsUpdated ){

			r.push({
				id: record['field_id'],
				slug: fieldSlug,
				type: 'field',
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

function selectedFeaturesIsSeted( fieldData ){
	let i;
	let fid;

	for(i=0; i<selectedFeatures.length; i++){
		switch( fieldData['type'] ){
			case 'field':
				fid = fieldData.id;
				break;
			case 'field:additional-selection':
				fid = fieldData.vcsItemId;
				break;
		}

		if( selectedFeatures[i].fieldId == fid
			&& selectedFeatures[i].fieldType == fieldData.type
		 ){
			if(  ( selectedFeatures[i].value != null ) 
				 && ( selectedFeatures[i].valueRegId != null )
				 && ( selectedFeatures[i].valueLabel != null ) 
			){
				return true;
			}
		}

	}
	return false;
}

function get_next_item_to_render( parentFVPath ){

	let max_priority = null;

	const fieldsToRender = get_fields_to_render( parentFVPath );

	fieldsToRender.forEach( (r)=>{
		if( ( max_priority == null ) || ( r['priorityInStep'] < max_priority ) ){
			max_priority = r['priorityInStep'];
		}
	} );

	const additionalSelectionItemsToRender = get_fields_to_render_type_vcsItem_field_subType_additional_selection( parentFVPath );

	additionalSelectionItemsToRender.forEach( (r)=>{
		if( ( max_priority == null ) || ( r['priorityInStep'] < max_priority ) ){
			max_priority = r['priorityInStep'];
		}
	} );

	let itemsToRender = [];
	[ ...fieldsToRender, ...additionalSelectionItemsToRender ].forEach( (r)=>{
		if( ( r['priorityInStep'] == max_priority ) && !selectedFeaturesIsSeted( r ) ){
			itemsToRender.push( r );
		}
	});

	return itemsToRender;

}

function currentParentFVPath(){

	let i;	
	let rs = '';

	for(i=0; i<selectedFeatures.length; i++){
		
		if( ( selectedFeatures[i].valueRegId != null ) 
			&& ( selectedFeatures[i].fieldType != undefined )
			&& ( selectedFeatures[i].fieldType == 'field' )
		){
			rs += i > 0 ? ',' : '';
			rs += selectedFeatures[i].fieldId + '=' + selectedFeatures[i].valueRegId;
		} else {
			break;
		}
	}

	return rs;

}

function itemTypeFieldOptionsHtmlAssembly( fld ){

	let fieldSlug = null;
	if( typeof fld['slug'] == 'string' ){
		fieldSlug = fld['slug'];
	} else {
		fieldSlug = fld['slug'][0];
	}
	
	const fieldOptions = fld['options'];

	let optionsHRd = '';

	eventParamsPrepareFieldOptionHtmlTemplate.field = fld;
	eventParamsPrepareFieldOptionHtmlTemplate.fieldType = 'field';

	// unir todas las opciones disponibles en un solo string.
	fieldOptions.forEach( (opt,i)=>{

		eventParamsPrepareFieldOptionHtmlTemplate.option = opt;
		eventParamsPrepareFieldOptionHtmlTemplate.htmlTpl = JGB_WPSBSC_DATA['fieldsTemplates'][ fieldSlug ]['options'][ opt['slug'] ];
		
		document.dispatchEvent( jwpsbscPrepareFieldOptionHtmlTemplate );

		optionsHRd += i>0 ? "\n" : '';
		optionsHRd += eventParamsPrepareFieldOptionHtmlTemplate.htmlTpl;
		optionsHRd;
		
	});

	return JGB_WPSBSC_DATA['fieldsTemplates'][ fieldSlug ]['wrapper'][ fieldSlug ].replace("{{#radio-options}}", optionsHRd );

}

function itemTypeFieldAdditionalSelectionOptionsHtmlAssembly( fld, step){

	let fieldSlug = fld['slug'];

	let fasTplInfo;

	let aohEval;

	let existHandlerFunction;

	if( JGB_WPSBSC_DATA['additionalSelectionTemplates'][fieldSlug] == undefined ){
		fasTplInfo = JGB_WPSBSC_DATA['additionalSelectionTemplates']['default'];
	} else {
		fasTplInfo = JGB_WPSBSC_DATA['additionalSelectionTemplates'][fieldSlug];
	}

	const functionName = fasTplInfo['assemblyOptionsHandler'];

	existHandlerFunction = eval("typeof " + functionName );

	if( existHandlerFunction == 'function' ){

		aohEval = window[ functionName ]( currentParentFVPath(), fld, step, fasTplInfo['htmlTplWrapper'], fasTplInfo['htmlTplOptions'] );

		return aohEval;

	}

}

function prepareTemplatesToRenderForFields( fieldsToRender, step ){

	let templatesToRender = [];	

	let ts = JGB_WPSBSC_DATA['beginStepWraperTpl'].replace("{{step_index}}",step.toString());

	ts = ts.replace("{{title}}", JGB_WPSBSC_DATA['stepTitles'][(step-1).toString()] );

	templatesToRender.push( ts );

	
	fieldsToRender.forEach( ( fld )=>{

		let fieldWrapperTpl;

		jwpsbscPrepareFieldHtmlTemplate.detail.field = fld;
		
		switch( fld['type'] ){

			case 'field:additional-selection':
				jwpsbscPrepareFieldHtmlTemplate.detail.fieldType = 'field:additional-select';
				fieldWrapperTpl = itemTypeFieldAdditionalSelectionOptionsHtmlAssembly( fld, step );
				break;

			default: // type = 'field'
				jwpsbscPrepareFieldHtmlTemplate.detail.fieldType = 'field';
				fieldWrapperTpl = itemTypeFieldOptionsHtmlAssembly( fld );
				

		}
		
		
		jwpsbscPrepareFieldHtmlTemplate.detail.htmlTpl = fieldWrapperTpl;
		
		document.dispatchEvent( jwpsbscPrepareFieldHtmlTemplate );
		
		fieldWrapperTpl = jwpsbscPrepareFieldHtmlTemplate.detail.htmlTpl;
		
		templatesToRender.push( fieldWrapperTpl );

	});
	
		
	templatesToRender.push( JGB_WPSBSC_DATA['endStepWraperTpl'] );

	return templatesToRender;

}



function getFieldsToRender( parentFVPath = '' ){
	
	let fieldsToRender = [];

	if( ( parentFVPath == undefined ) || ( parentFVPath == '' ) ){
		fieldsToRender = get_first_item_to_render();
	} else {
		fieldsToRender = get_next_item_to_render( parentFVPath );
	}	

	return fieldsToRender;
}

function renderStep(){

	if( selectedFeatures[0].value == null ){
		parentFVPath = '';
	} else {
		parentFVPath = currentParentFVPath();
	}

	let incrmtr;

	let fieldsToRender = [];

	let templatesToRender = [];

	fieldsToRender = getFieldsToRender( parentFVPath );

	if( fieldsToRender.length > 0 ){

		vrwlIsLastStep = false;

		incrmtr = ( ( parentFVPath == undefined ) || ( parentFVPath == '' ) ) ? 1 : 2;

		let step = swiper.activeIndex + incrmtr;

		templatesToRender = prepareTemplatesToRenderForFields( fieldsToRender, step );

		swiper.appendSlide( templatesToRender.join("\n") );

		//Se ejecuta setEventHandlersForAvailablesValuesChoicesSelectors() con el evento jwpsbscAfterRenderStep.
		document.dispatchEvent( jwpsbscAfterRenderStep );

	} else {
		//se renderiza la el último step.

		vrwlIsLastStep = true;

		incrmtr = ( ( parentFVPath == undefined ) || ( parentFVPath == '' ) ) ? 1 : 2;

		let step = swiper.activeIndex + incrmtr;

		templatesToRender = renderLastStep( step );

		swiper.appendSlide( templatesToRender.join("\n") );

		//Se ejecuta setEventHandlersForAvailablesValuesChoicesSelectors() con el evento jwpsbscAfterRenderStep.
		document.dispatchEvent( jwpsbscAfterRenderStep );
	}

}

function renderFirstStep(){

	swiper.removeAllSlides();

	renderStep();

}

function renderNextStep(){

	const actSldr = swiper.activeIndex + 1;

	removeSlidesFrom( actSldr );

	renderStep();

}

function renderLastStep( step ){

	let templatesToRender = [];	

	let ts = JGB_WPSBSC_DATA['beginLastStepWraperTpl'].replace("{{step_index}}",step.toString());

	ts = ts.replace("{{title}}", JGB_WPSBSC_DATA['stepTitles'][(step-1).toString()] );

	templatesToRender.push( ts );

	ts = JGB_WPSBSC_DATA[ 'contentLastStepTpl' ];
	
	templatesToRender.push( ts );
		
	templatesToRender.push( JGB_WPSBSC_DATA['endLastStepWraperTpl'] );

	return templatesToRender;
}

function removeSlidesFrom( index ){
	let i = swiper.slides.length;
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

function getItmFromVcsItemsMatchsBySlug( vcims, slug ){
	let i;
	for(i=0; i<vcims.length; i++){
		if( vcims[i].slug == slug ){
			return vcims[i];
		}
	}
	return null;

}

function setFeatureValueForFieldTypeFieldData( eventSetFeatureValue ){

    //fieldId, fieldSlug, valueSlug, valueLabel, valueRegId, fieldLabel, itemPriority
    if( eventSetFeatureValue.detail.fieldType != 'field:data' ){
        return;
    }

  let dataItemExist;

  dataItemExist = dataItemCheckInFeatures( eventSetFeatureValue.detail.field );
  if( dataItemExist == null ){
    selectedFeatures.push({
      'fieldId': eventSetFeatureValue.detail.fieldId,
      'field': eventSetFeatureValue.detail.field,
      'fieldType': eventSetFeatureValue.detail.fieldType,
      'label':  eventSetFeatureValue.detail.label,
      'value': eventSetFeatureValue.detail.value,
      'valueLabel': eventSetFeatureValue.detail.valueLabel,
      'valueRegId': eventSetFeatureValue.detail.valueRegId,
      'priorityInStep': eventSetFeatureValue.detail.priorityInStep,
      'stepOnStore': swiper.activeIndex
    });
    dataItemExist = selectedFeatures.length - 1;
  } else {
    selectedFeatures[dataItemExist].value = eventSetFeatureValue.detail.value;
    selectedFeatures[dataItemExist].valueLabel = eventSetFeatureValue.detail.valueLabel;
    selectedFeatures[dataItemExist].valueRegId = eventSetFeatureValue.detail.valueRegId;
  }
}

function desplegarPrice( ru66 = false){
	(function( $ ) {

		const sfcSlctr = ".main-container .price-container";
		
		let price = null;
		let sellPrice = null;
		let percentDiscount = null;
		let intf = null;
		let cp;
		let priceItem;
		let adicionalRangoSobre66;
		let htmlPrice = '';
		
		[cp,i] = loadCUrrentOptionData();

		if( cp != null){ 
			priceItem = getItmFromVcsItemsMatchsBySlug( cp, 'precio-venta' );
			if( priceItem != null ){
				price = priceItem['data'];
				adicionalRangoSobre66 = getItmFromVcsItemsMatchsBySlug( cp, 'monto-adicional-rango-sobre-6-6' );
				if( ru66 ){
					
					if( adicionalRangoSobre66 != null ){
						price = parseInt(price) + parseInt( adicionalRangoSobre66['data'] );
						price = price.toString();

					} 

				} 
			}
		}

		if( price != null ){
			intf = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' });
			percentDiscount = parseFloat( JGB_WPSBSC_DATA['globalPercentDiscount'] );
			
			if( percentDiscount > 0 ){
				sellPrice = parseInt( price ) - ( parseInt( price ) * percentDiscount / 100 );
				htmlPrice = '<span class="without-discount-price">' + intf.format(price) + '</span> ';
			} else {
				sellPrice = price;
			}
			htmlPrice += '<span class="with-discount-price">' + intf.format(sellPrice) + '</span>';
			if( percentDiscount > 0 ){
				htmlPrice += ' <span class="percent-discount">-' + percentDiscount + '%</span>';
			}
			$(sfcSlctr).html( htmlPrice );
			$(cartFormSltr + ' input[name="percentDiscount"]').val( percentDiscount );
			$(cartFormSltr + ' input[name="precio"]').val( sellPrice );
		} else {
			$(sfcSlctr).html('');
		}

	})( jQuery );
}

function swiperNextSlide(){
	document.removeEventListener('jwpsbscAfterRenderStep', swiperNextSlide );
	swiper.slideNext(speed);
	
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
			let valueLabel;
			const labelElements = $(fatherEl).find('label');
			
			if( labelElements.length > 0 ){
				valueLabel = $(labelElements[0]).text();
			} else {
			 	valueLabel = $(labelElements).text();
			}
			const valueRegId = $(fatherEl).closest('td.value').data('reg-val-id');

			if( (fatherEl.length!=undefined) && fatherEl.length > 0 ){
				$(fatherEl).find('input[type="radio"]').prop('checked', true);
				$(fatherEl).find('input[type="radio"]').trigger('click');
			}

			if( (fatherEl.length!=undefined) && fatherFieldEl.length > 0 ){
				$(fatherFieldEl).find('.value .select-buton.outer, .value .option-buton.outer').removeClass('selected');
				$(fatherFieldEl).find('.value .buton-group.multiple').data('opts-sels','');
			}

			$(fatherEl).find('.select-buton.outer').addClass('selected');
			setFeatureValue( fieldId, fieldSlug, valueSelected, valueLabel, valueRegId );

			let cp, i;
			let adicionalRangoSobre66;
			[cp,i] = loadCUrrentOptionData();
			if( cp != null ){
				
				adicionalRangoSobre66 = getItmFromVcsItemsMatchsBySlug( cp, 'monto-adicional-rango-sobre-6-6' );
				if(    ( adicionalRangoSobre66 != null )
					&& ( $(fatherFieldEl).find('.checkbox-ru66 input[type="checkbox"]:checked').length > 0 ) 
				){

					eventParamsFeatureValue['fieldId'] = "DT-" + adicionalRangoSobre66.id;
					eventParamsFeatureValue['field'] = adicionalRangoSobre66.slug;
					eventParamsFeatureValue['value'] = 'si';
					eventParamsFeatureValue['valueLabel'] = 'Si';
					eventParamsFeatureValue['valueRegId'] = null;
					eventParamsFeatureValue['fieldType'] = "field:data";
					eventParamsFeatureValue['label'] = adicionalRangoSobre66.label;
					eventParamsFeatureValue['priorityInStep'] = 99;
					eventParamsFeatureValue['stepOnStore'] = swiper.activeIndex;
					
						
				} else {
					if( adicionalRangoSobre66 != null ){
						eventParamsFeatureValue['fieldId'] = "DT-" + adicionalRangoSobre66.id;
						eventParamsFeatureValue['field'] = adicionalRangoSobre66.slug;
						eventParamsFeatureValue['value'] = 'no';
						eventParamsFeatureValue['valueLabel'] = 'No';
						eventParamsFeatureValue['valueRegId'] = null;
						eventParamsFeatureValue['fieldType'] = "field:data";
						eventParamsFeatureValue['label'] = adicionalRangoSobre66.label;
						eventParamsFeatureValue['priorityInStep'] = 99;
						eventParamsFeatureValue['stepOnStore'] = swiper.activeIndex;
					}
				}

				if( adicionalRangoSobre66 != null ){
					setFeatureValueForFieldTypeFieldData( {detail: eventParamsFeatureValue} );
				}
				
				
			}
			
			desplegarSFs();
			
			if( $(fatherFieldEl).find('.checkbox-ru66 input[type="checkbox"]:checked').length > 0 ){
				
				desplegarPrice( true );
			} else {
				desplegarPrice( false );
			}
			
			document.addEventListener('jwpsbscAfterRenderStep', swiperNextSlide );
			renderNextStep();
			//checkButtonsNavigationStatus();
			
		});

		
	})( jQuery );
}

function getFieldSlugById( id ){
	const s = dtFields({id:id.toString() }).first().slug;
	return s;
}


function setFeatureValueForFieldTypeField( eventSetFeatureValue ){
	if( eventSetFeatureValue.detail.fieldType != 'field' ){
        return;
    }

	let i;

	for(i=0; i<selectedFeatures.length; i++){
		if( selectedFeatures[i].field == eventSetFeatureValue.detail.field ){
			selectedFeatures[i].fieldId = eventSetFeatureValue.detail.fieldId;
			selectedFeatures[i].value = eventSetFeatureValue.detail.value;
			selectedFeatures[i].valueLabel = eventSetFeatureValue.detail.valueLabel;
			selectedFeatures[i].valueRegId = eventSetFeatureValue.detail.valueRegId;
			selectedFeatures[i].stepOnStore = swiper.activeIndex;
		}
	}
}


function setFeatureValue( fieldId, fieldSlug, valueSlug, valueLabel, valueRegId, fieldType = 'field', fieldLabel = null, itemPriority = null ){
	let i;
	
	eventParamsFeatureValue['fieldId'] = fieldId;
	eventParamsFeatureValue['field'] = fieldSlug;
	eventParamsFeatureValue['value'] = valueSlug;
	eventParamsFeatureValue['valueLabel'] = valueLabel;
	eventParamsFeatureValue['valueRegId'] = valueRegId;
	eventParamsFeatureValue['fieldType'] = fieldType;
	eventParamsFeatureValue['label'] = fieldLabel;
	eventParamsFeatureValue['priorityInStep'] = itemPriority;
	eventParamsFeatureValue['stepOnStore'] = swiper.activeIndex;

	document.dispatchEvent( jwpsbscSetFeatureValue );
	
	for(i=0; i<selectedFeatures.length; i++){

		if( ( ( selectedFeatures[i].fieldType == 'field' )
		      || (selectedFeatures[i].fieldType == 'field:additional-select')
			  || (selectedFeatures[i].fieldType == 'field:data')
		    )
		    && ( selectedFeatures[i].stepOnStore > swiper.activeIndex )  ){
			selectedFeatures[i].value 	   = null;
			selectedFeatures[i].valueLabel = null;
			selectedFeatures[i].valueRegId = null;
		}

	}
}

function vcsItemCheckInFeatures( slug ){
	let i;
	for(i=0; i<selectedFeatures.length; i++){
		if( ( selectedFeatures[i].field == slug ) && ( selectedFeatures[i].fieldType == 'field:additional-select' ) ){
			return i;
		}
	}
	return null;
}

function dataItemCheckInFeatures( slug ){
	let i;
	for(i=0; i<selectedFeatures.length; i++){
		if( ( selectedFeatures[i].field == slug ) && ( selectedFeatures[i].fieldType == 'field:data' ) ){
			return i;
		}
	}
	return null;
}

function vcsItemValidationInFeaturesByIndex( index ){
	if( selectedFeatures[index].value == null ){
		return false;
	}

	if( selectedFeatures[index].valueRegId == null ){
		return false;
	}

	if( selectedFeatures[index].valueLabel == null ){
		return false;
	}

	return true;
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

function getValueFromVCSItem( VCSItem ){
	if( VCSItem['item_type'] == 'DATA' ){
		return dtItemsData({id:VCSItem['id_item']}).first()['value'];
	}
	return null;
}

function getFieldFromVCSItem( VCSItem ){
	if( VCSItem['item_type'] == 'FIELD' ){
		const r = dtItemsField({id:VCSItem['id_item']}).first();
		return { type: r['type'], options: r['options'] };
	}
	return null;
}

function loadCUrrentOptionData( parentFVPath = '' ){

	let i = 0;
	let j = 0;
	let vlsIdsCmbnsStr;
	let r;
	let choiceCmbntId;
	let itemsData = [];
	let curItem = {};

	if( parentFVPath=='' ){
		vlsIdsCmbnsStr = currentParentFVPath();
	} else {
		vlsIdsCmbnsStr = parentFVPath;
	}

	if( vlsIdsCmbnsStr.split(",").length == 1 ){
		optionsData = [];
	}

	r = dtChoicesCombinations({vls_ids_combinations_string:vlsIdsCmbnsStr}).first();

	if( r !== false ){
		choiceCmbntId = r['id'];

		r = dtVcsItems({id_choice_combination:choiceCmbntId}).get();

		for(i=0; i<r.length; i++){
			curItem = {
				id :  r[i]['id'],
				id_item : r[i]['id_item'],
				item_type : r[i]['item_type'],
				slug : r[i]['slug'],
				label: r[i]['label'],
				data_type: r[i]['data_type']
			};

			switch( curItem['item_type'] ){

				case 'DATA':
					curItem['data'] = getValueFromVCSItem( curItem );
					break;

				case 'FIELD':
					curItem['field'] = getFieldFromVCSItem( curItem );
					break;
					
			}

			let optDataUpdtd = false;
			for( j=0; j<optionsData.length; j++ ){
				if( optionsData[j].id == curItem['id'] ){
					optionsData[j] = curItem;
					optDataUpdtd = true;
					break;
				}
			}

			if( !optDataUpdtd ){
				optionsData.push( curItem );
			}

			itemsData.push( curItem );

		}
		optionsData = itemsData;
		return [itemsData,choiceCmbntId];
		
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

	$(document).on( 'pumBeforeOpen', ppuMkrSlctr, function(evnt){

		
		swiper = new Swiper( ppuMkrSlctr + ' .swiper', {
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
		

	});

	$(document).ready( ()=>{
		initializeDb();
		ppuMkrSlctr = '#pum-' + JGB_WPSBSC_DATA['popupMakerId'];

		document.addEventListener('jwpsbscAfterRenderStep', setEventHandlersForAvailablesValuesChoicesSelectors );

		

		document.addEventListener('jwpsbscSetFeatureValue', setFeatureValueForFieldTypeField );

		$('.add-crystal-to-cart').click((evnt)=>{
			evnt.preventDefault();
			const priceField = {
				'field': 'precio',
				'label': 'Precio cristales',
				'valueLabel': $('.cristal-selection.main-container .price-container .with-discount-price').text(),
				'value': null,
			};
			priceField.value = priceField.valueLabel.replace('$','').replace('.','').replace('.','');
			const productData = [ ...selectedFeatures, {...priceField} ];
			
			let jwpsProdData = JSON.stringify( productData );
			jwpsProdData = btoa(encodeURIComponent( jwpsProdData ));
			$('form.cart input[name="jwps-prod-data"]').val( jwpsProdData );
			$('form.cart button.single_add_to_cart_button.button.alt').trigger('click');
		});
	});

})( jQuery );
