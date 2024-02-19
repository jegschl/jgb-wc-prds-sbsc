<?php

namespace JGB;

class FormWidgetCheck extends FormWidgetBase{
    function __construct( $params )
    {
        $params['type'] = 'check';
        parent::__construct( $params );
    }
}