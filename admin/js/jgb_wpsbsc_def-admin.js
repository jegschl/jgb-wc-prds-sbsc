let adminFrontJsonEditor;

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
        
    });

})( jQuery );