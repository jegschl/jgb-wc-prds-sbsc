<?php

namespace JGB;

class FormWidgetRadioGroup extends FormWidgetWithVisualOptsBase{

    function __construct( $params )
    {
        $params['type'] = 'radio';
        $this->type = $params['type'];
        
        parent::__construct( $params );

    }
}