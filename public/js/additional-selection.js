/* prefix for additional-selection js functions: adSltn */

function additionalSelectionAssemblyOptions( fvPath, fieldData, step, htmlTplWrapper, htmlTplOptions ){

    const fieldOptions = fieldData['options'];

    let optionsHRd = '';


    htmlTplWrapper = htmlTplWrapper.replaceAll( "{{additional-selection-id}}", fieldData['vcsItemId'] );

    htmlTplWrapper = htmlTplWrapper.replaceAll( "{{additional-selection-slug}}", fieldData['slug'] );

    htmlTplWrapper = htmlTplWrapper.replaceAll( "{{opt-slug}}", "additional-selection-" + fieldData['slug'] );
    
    htmlTplWrapper = htmlTplWrapper.replaceAll( "{{additional-selection-label}}", fieldData['vcsItemLabel'] );

    htmlTplWrapper = htmlTplWrapper.replaceAll( "{{opt-label}}", 'Selecciona una opción' );

    htmlTplWrapper = htmlTplWrapper.replaceAll( "{{as-step-priority}}", fieldData['priorityInStep'] );

    eventParamsPrepareFieldOptionHtmlTemplate.field = fieldData;
    eventParamsPrepareFieldOptionHtmlTemplate.fieldType = 'field:additional-select';

	// unir todas las opciones disponibles en un solo string.
	fieldOptions.forEach( (opt,i)=>{

        eventParamsPrepareFieldOptionHtmlTemplate.option = opt;

        let optHtml = htmlTplOptions.replaceAll("{{subopt-slug}}", opt['slug'] );
        optHtml = optHtml.replaceAll("{{subopt-label}}", opt['label'] );
        
		eventParamsPrepareFieldOptionHtmlTemplate.htmlTpl = optHtml;
		
		document.dispatchEvent( jwpsbscPrepareFieldOptionHtmlTemplate );

		optionsHRd += i>0 ? "\n" : '';
		optionsHRd += eventParamsPrepareFieldOptionHtmlTemplate.htmlTpl;
	});

    

    htmlTplWrapper = htmlTplWrapper.replace( "{{#additional-selection-options}}", optionsHRd );

	return htmlTplWrapper;

}

function adSltnSetEventHandlersForOptions(){

    

    (function( $ ) {

        $('.step .value .wrapper .option-buton:not(.outer)').off('click');
        $('.step .value .wrapper .option-buton:not(.outer)').click(function(evnt){
            const fatherEl = $(evnt.target).closest('.wrapper');
            const fatherFieldEl = $(evnt.target).closest('table.field');
            const fieldId = $(fatherFieldEl).data('field-additional-selection-id');
            const fieldSlug = $(fatherFieldEl).data('field-additional-selection-slug');
            const fatherOptEl   = $(evnt.target).closest('.buton-group');
            const optionSelected = $(evnt.target).text();
            const valueRegId = $(evnt.target).data('option');
            const fieldLabel = $(fatherFieldEl).find('.addtnl-slct-title').text();
            const itemStepPriority = $(fatherFieldEl).data('field-additional-selection-step-priority');
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
                if( (fatherEl.length!=undefined) && fatherEl.length > 0 ){
                    $(fatherEl).find('input[type="radio"]').prop('checked', true);
                    $(fatherEl).find('input[type="radio"]').prop('value', rawBgos);
                    $(fatherEl).find('input[type="radio"]').trigger('click');
                }

                $(fatherOptEl).find('.option-buton.outer').removeClass('selected');

                $(fatherOptEl).find('.option-buton:not(.outer)').each( (i,e)=>{
                    const btnOpt = $(e).data('option');
                    if( arBgos.indexOf( btnOpt ) >= 0 ){
                        $(e).closest('.option-buton.outer').addClass('selected');
                    }
                } );
            }

            setFeatureValue( fieldId, fieldSlug, rawBgos, optionSelected, valueRegId, 'field:additional-select', fieldLabel, itemStepPriority );
            desplegarSFs();
            desplegarPrice();
            document.addEventListener('jwpsbscAfterRenderStep', swiperNextSlide );
            renderNextStep();
        });

    })( jQuery );

    
}

function setFeatureValueForFieldTypeFieldAdditionalSelection( eventSetFeatureValue ){

    //fieldId, fieldSlug, valueSlug, valueLabel, valueRegId, fieldLabel, itemPriority
    if( eventSetFeatureValue.detail.fieldType != 'field:additional-select' ){
        return;
    }

	let vcsItemExist;

	vcsItemExist = vcsItemCheckInFeatures( eventSetFeatureValue.detail.field );
	if( vcsItemExist == null ){
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
		vcsItemExist = selectedFeatures.length - 1;
	} else {
		selectedFeatures[vcsItemExist].value = eventSetFeatureValue.detail.value;
		selectedFeatures[vcsItemExist].valueLabel = eventSetFeatureValue.detail.valueLabel;
		selectedFeatures[vcsItemExist].valueRegId = eventSetFeatureValue.detail.valueRegId;
	}
}

(function( $ ) {

    document.addEventListener('jwpsbscAfterRenderStep', adSltnSetEventHandlersForOptions );

    document.addEventListener('jwpsbscSetFeatureValue', setFeatureValueForFieldTypeFieldAdditionalSelection );

})( jQuery );