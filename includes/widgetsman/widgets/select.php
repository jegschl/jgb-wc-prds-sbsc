<?php

namespace JGB;

class FormWidgetSelect extends FormWidgetWithVisualOptsBase{

    function __construct( $params )
    {
        $params['type'] = 'select';
        parent::__construct( $params );

    }
}