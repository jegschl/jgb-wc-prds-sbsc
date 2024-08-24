/* prefix for additional-selection js functions: adSltn */

function additionalSelectionAssemblyOptions( fvPath, fieldData, step, htmlTplWrapper, htmlTplOptions ){

    const fieldOptions = fieldData['options'];

    let optionsHRd = '';


    htmlTplWrapper = htmlTplWrapper.replace( "{{additional-selection-id}}", fieldData['vcsItemId'] );

    htmlTplWrapper = htmlTplWrapper.replace( "{{additional-selection-slug}}", fieldData['slug'] );

    htmlTplWrapper = htmlTplWrapper.replace( "{{opt-slug}}", "additional-selection-" + fieldData['slug'] );
    
    htmlTplWrapper = htmlTplWrapper.replace( "{{additional-selection-label}}", fieldData['vcsItemLabel'] );

    htmlTplWrapper = htmlTplWrapper.replace( "{{opt-label}}", 'Selecciona una opción' );

    htmlTplWrapper = htmlTplWrapper.replace( "{{as-step-priority}}", fieldData['priorityInStep'] );


	// unir todas las opciones disponibles en un solo string.
	fieldOptions.forEach( (opt,i)=>{
        let optHtml = htmlTplOptions.replace("{{subopt-slug}}", opt['slug'] );
        optHtml = optHtml.replace("{{subopt-label}}", opt['label'] );

		optionsHRd += i>0 ? "\n" : '';
		optionsHRd += optHtml;
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
            renderNextStep();

            swiper.slideNext(speed);
        });

    })( jQuery );

    
}

function setFeatureValueForFieldTypeFieldAdditionalSelection( eventSetFeatureValue ){

    //fieldId, fieldSlug, valueSlug, valueLabel, valueRegId, fieldLabel, itemPriority
    const fieldId = eventSetFeatureValue.detail.fieldId;
	const fieldSlug = eventSetFeatureValue.detail.fieldSlug;
	const valueSlug = eventSetFeatureValue.detail.value;
	const valueLabel = eventSetFeatureValue.detail.valueLabel;
	const valueRegId = eventSetFeatureValue.detail.valueRegId;
    const itemPriority = eventSetFeatureValue.detail.priorityInStep;

	let vcsItemExist;

	vcsItemExist = vcsItemCheckInFeatures( fieldSlug );
	if( vcsItemExist == null ){
		selectedFeatures.push({
			'fieldId': fieldId,
			'field': fieldSlug,
			'fieldType': 'field:additional-select',
			'label': fieldLabel,
			'value': valueSlug,
			'valueLabel': valueLabel,
			'valueRegId': valueRegId,
			'priorityInStep': itemPriority,
			'stepOnStore': swiper.activeIndex
		});
		vcsItemExist = selectedFeatures.length - 1;
	} else {
		selectedFeatures[vcsItemExist].fieldId = fieldId;
		selectedFeatures[vcsItemExist].fieldType = fieldType;
		selectedFeatures[vcsItemExist].value = valueSlug;
		selectedFeatures[vcsItemExist].valueLabel = valueLabel;
		selectedFeatures[vcsItemExist].valueRegId = valueRegId;
		selectedFeatures[vcsItemExist].priorityInStep = itemPriority;
		selectedFeatures[vcsItemExist].stepOnStore = swiper.activeIndex;
	}
}

(function( $ ) {

    document.addEventListener('afterRenderStep', adSltnSetEventHandlersForOptions );

})( jQuery );