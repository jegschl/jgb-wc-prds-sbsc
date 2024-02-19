<?php

namespace JGB;

class FormWidgetText extends FormWidgetBase{
    protected $place_holder;

    function __construct( $params )
    {
        $params['type'] = 'text';
        parent::__construct( $params );
        
        $this->place_holder = $params['place_holder'];
    }

    protected function get_place_holder(){
        return $this->place_holder;
    }
}