let adminFrontJsonEditor;
let msm;

(function( $ ) {
	'use strict';

    function prepareJsonUpdate(){
        const jsd = adminFrontJsonEditor.getText();

        $('input[name="' + JGB_WPSBSC_CPT_DEF_DATA.jsonEdtrSelectr + '"').val( jsd );

    }

    $( document ).ready(function() {

        //const jsonEdtrPostSelectr = 'input[name="' + JGB_WPSBSC_CPT_DEF_DATA.jsonEdtrSelectr + '"]';

        const originalData = JGB_WPSBSC_CPT_DEF_DATA.main_content_json;

        const container = $('#' + JGB_WPSBSC_CPT_DEF_DATA.jsonEdtrSelectr);

        
        if( container.length > 0 ){
            const conf = {
                language: 'es',
                modes: ['tree','code'],
                onChange: prepareJsonUpdate
            };

            adminFrontJsonEditor = new JSONEditor(container[0], conf)

            // set json
            adminFrontJsonEditor.set( JSON.parse( originalData ) );

            

        }

        $('#choices-tree-input textarea').on('change paste', () => {
            
		} );

		$('.import-button-wrapper .button').click( () => {
            $.blockUI();
			
            const t = $('#choices-tree-input textarea').val();
			if( msm == null ){
				msm = new JGBMemSheetMtx( t ); 
			} else {
				msm.read( t );
			}

			const as = {
				url: '/wp-json/jgb-wpsbsc/v1/td-import/',
				method: 'POST',
				data: msm.getJson(),
				contentType: 'application/json; charset=UTF-8',
				error: ( jqXHR, textStatus, errorThrown ) => {
                    console.log( 'Error procesando solicitud...');
                    console.log( textStatus );
                },
				success: ( data, textStatus, jqXHR ) => {
                    console.log( 'Solicitud procesada exitosamente!');
                },
				complete: ( jqXHR, textStatus ) => {
                    $.unblockUI();
                }
			};

            $.ajax( as );

            
		} );
        
    });

})( jQuery );