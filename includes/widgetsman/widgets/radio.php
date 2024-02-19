<?php

namespace JGB;

class FormWidgetRadioGroup extends FormWidgetWithVisualOptsBase{

    function __construct( $params )
    {
        $params['type'] = 'radio';
        parent::__construct( $params );

    }
}