let adminFrontJsonEditor;
let msm;

function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
    return false;
};

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

            const postId = getUrlParameter('post');
			
            const t = $('#choices-tree-input textarea').val();
			if( msm == null ){
				msm = new JGBMemSheetMtx( t ); 
			} else {
				msm.read( t );
			}

            const dti = {
                postId: postId,
                data: msm.getMatrix()
            };

			const as = {
				url: '/wp-json/jgb-wpsbsc/v1/td-import/',
				method: 'POST',
				data: JSON.stringify( dti ),
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