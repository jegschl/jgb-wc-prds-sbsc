<?php

namespace JGB;

class WidgetsFactory{
    private $typesClassesMap;

    function __construct(){
        $this->typesClassesMap = WidgetsFactory::default_types_class_map();
    }

    static function default_base_path(){
        $base_path = plugin_dir_path( __FILE__ );
        $base_path = apply_filters('JGB/wpsbsc/defaultWidgetsTemplateBasePath', $base_path );

        return $base_path;
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
        $params['base_path'] = WidgetsFactory::default_base_path();
        $widget_class = null;
        if( array_key_exists( $type, $this->typesClassesMap ) ){
            $widget_class = '\JGB\\' .  $this->typesClassesMap[ $type ];
        }
        $widget = new $widget_class( $params );

        $widget = apply_filters('JGB/wpsbsc/createWidget',$widget,$type,$params);

        return $widget;
    }
} 