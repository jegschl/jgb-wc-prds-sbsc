<?php

namespace JGB;

class WidgetsFactory{
    private $typesClassesMap;

    function __construct(){
        $this->typesClassesMap = WidgetsFactory::default_types_class_map();
    }

    static function default_types_class_map(){
        $dtcm = [
            'text'      => 'FormWidgetText',
            'radio'     => 'FormWidgetRadioGroup',
            'check'     => 'FormWidgetCheck',
            'select'    => 'FormWidgetSelect'
        ];

        $dtcm = apply_filters('JGB/wpsbsc/defaultTypesClassesMap', $dtcm );

        return $dtcm;
    }

    function create_widget($type, $params){
        $widget_class = null;
        if( array_key_exists( $type, $this->typesClassesMap ) ){
            $widget_class = $this->typesClassesMap[ $type ];
        }
        $widget = new $widget_class( $params );

        $widget = apply_filters('JGB/wpsbsc/createWidget',$widget,$type,$params);

        return $widget;
    }
} 