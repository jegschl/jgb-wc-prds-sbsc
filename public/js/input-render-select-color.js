(function( $ ) {
	'use strict';

    $.fn.JgbWPSInputSelectColor = function( opts ){
        let config = $.extend(
            {
                fieldConfig:null,
                templateWrapper:'<div class="jgb-wps-input type-select-color"></div>',
                templateItem:'<div class="itm-wrapper"><div class="color-demo"></div><div class="color-label"></div></div>',
                targetElement:null
            }, 
            opts
        );
        
        return this.each( function(){
            if( $(targetElement ).is("table") ){
                /*
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
                }*/
                let selectColor = $(templateWrapper);
                selectColor.data('form-field-name',config.fieldConfig.fldFormName);
                
                $.each(config.fieldConfig.options, function(i,e){
                    const colorCode = config.fieldConfig.options[i]['color-code'];
                    const label = config.fieldConfig.options[i]['label'];
                    const value = config.fieldConfig.options[i]['value'];
                    const ni = $(templateItem);

                    $(ni).find('.itm-wrapper').data('value',value);
                    $(ni).find('.color-demo').css('backgroud-color',colorCode);
                    $(ni).find('.color-label').text(label);

                    selectColor.append(ni);
                });
            }
        });
    };

})( jQuery );