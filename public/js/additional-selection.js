function additionalSelectionAssemblyOptions( fvPath, fieldData, step, htmlTplWrapper, htmlTplOptions ){

    const fieldOptions = fieldData['options'];

    let optionsHRd = '';

	// unir todas las opciones disponibles en un solo string.
	fieldOptions.forEach( (opt,i)=>{
        let optHtml = htmlTplOptions.replace("{{subopt-slug}}", opt['slug'] );
        optHtml = optHtml.replace("{{subopt-label}}", opt['label'] );

		optionsHRd += i>0 ? "\n" : '';
		optionsHRd += optHtml;
	});

    htmlTplWrapper = htmlTplWrapper.replace( "{{additional-selection-label}}", fieldData['vcsItemLabel'] );

    htmlTplWrapper = htmlTplWrapper.replace( "{{opt-label}}", 'Selecciona una opción' );

    htmlTplWrapper = htmlTplWrapper.replace( "{{#additional-selection-options}}", optionsHRd );

	return htmlTplWrapper;

}