<?php
namespace JGB;

define('ERR_FWB_CREATE_TYPE_INVALID',1);
define('ERR_FWB_CREATE_NAME_INVALID',2);
define('ERR_FWB_CREATE_NAME_EMPTY',3);
define('ERR_FWB_BASE_PATH_INVALID',4);

class FormWidgetBase{
    protected $type;

    protected $configurator_template_path;

    protected $frontend_template_path;

    protected $creation_passed_params;

    protected $id;

    protected $name;

    protected $default_value;

    protected $classes;

    protected $label;

    protected $description;

    protected $tooltip;

    protected $errors_on_validate_params;

    protected $base_path;

    function __construct($params)
    {

        if( $this->validate_params( $params ) ){
            $this->creation_passed_params = $params;
            $this->type = $params['type'];
            $this->name = $params['name'];
            $this->id = $params['id'];
            $this->label = $params['label'];
            $this->description = $params['description'];
            $this->tooltip = $params['tooltip'];
            $this->default_value = $params['default_value'];
            $this->classes = $params['classes'];
            $this->base_path = $params['base_path'];
        }
    }

    protected function get_classes(){
        return $this->classes;
    }

    protected function get_default_value(){
        return $this->default_value;
    }

    protected function get_tooltip(){
        return $this->tooltip;
    }

    protected function get_description(){
        return $this->description;
    }

    protected function get_label(){
        return $this->label;
    }

    protected function get_name(){
        return $this->name;
    }

    protected function get_id(){
        return $this->id;
    }

    protected function validate_params( $params, $thrown_error = false ){
        $this->errors_on_validate_params = [];

        if( !is_array( $params ) ){
            if( $thrown_error ){
                throw new \Exception('Field type value in Form Widgets creation is invalid',ERR_FWB_CREATE_TYPE_INVALID);
            } else {
                $this->errors_on_validate_params[] = ERR_FWB_CREATE_TYPE_INVALID;
            }

        }

        if( !array_key_exists('type',$params ) ){
            if( $thrown_error ){
                throw new \Exception('Field type value in Form Widgets creation is invalid',ERR_FWB_CREATE_TYPE_INVALID);
            } else {
                $this->errors_on_validate_params[] = ERR_FWB_CREATE_TYPE_INVALID;
            }
        }

        if( !in_array( $params['type'], FormWidgetBase::get_allowed_types() ) ){
            if( $thrown_error ){
                throw new \Exception('Field type value in Form Widgets creation is invalid',ERR_FWB_CREATE_TYPE_INVALID);
            } else {
                $this->errors_on_validate_params[] = ERR_FWB_CREATE_TYPE_INVALID;
            }
        }

        if( !array_key_exists('name',$params ) ){
            if( $thrown_error ){
                throw new \Exception('Field type name in Form Widgets creation is invalid',ERR_FWB_CREATE_NAME_INVALID);
            } else {
                $this->errors_on_validate_params[] = ERR_FWB_CREATE_NAME_INVALID;
            }
        }

        if( empty( $params['name'] ) ){
            if( $thrown_error ){
                throw new \Exception('Field type name in Form Widgets creation is empty',ERR_FWB_CREATE_NAME_EMPTY);
            } else {
                $this->errors_on_validate_params[] = ERR_FWB_CREATE_NAME_EMPTY;
            }
        }

        if( !array_key_exists('base_path',$params ) ){
            if( $thrown_error ){
                throw new \Exception('Field type base_path in Form Widgets creation is invalid',ERR_FWB_BASE_PATH_INVALID);
            } else {
                $this->errors_on_validate_params[] = ERR_FWB_BASE_PATH_INVALID;
            }
        }

        if( !file_exists( $params['base_path'] ) ){
            if( $thrown_error ){
                throw new \Exception("Field type base_path in Form Widgets creation isn't exist",ERR_FWB_BASE_PATH_INVALID);
            } else {
                $this->errors_on_validate_params[] = ERR_FWB_BASE_PATH_INVALID;
            }
        }


        if( count( $this->errors_on_validate_params ) > 0 ){
            return false;
        }

        return true;
    }

    static function get_allowed_types(){
        $awt = [
            'text',
            'radio',
            'check',
            'select'
        ];

        $awt = apply_filters('JGB/wpsbsc/allowedWidgetTypes',$awt);

        return $awt;
    }

    protected function get_type(){
        return $this->type;
    }

    protected function render_configurator()
    {

    }

    protected function set_configurator_template_path(){

    }

    protected function render_frontend()
    {
        if( file_exists( $this->frontend_template_path ) ){
            \load_template( $this->frontend_template_path, false, ['widget' => $this ] );
        }
    }

    protected function set_frontend_template_path(){
        $subdir = '/JGB/wpsbsc/widgets/frontend';
        $filenm = $this->type . '.php';

        $ptt  = get_stylesheet_directory();
        $ptt .= $subdir;
        $ptt .= $filenm;

        if( file_exists( $ptt ) ){
            $this->frontend_template_path = apply_filters('JGB/wpsbsc/setFrontendTemplate',$ptt,$this->type,$this->name);
            return;
        }

        $ptt  = get_template_directory();
        $ptt .= $subdir;
        $ptt .= $filenm;

        if( file_exists( $ptt ) ){
            $this->frontend_template_path = apply_filters('JGB/wpsbsc/setFrontendTemplate',$ptt,$this->type,$this->name);
            return;
        }

        $ptt  = trailingslashit( $this->base_path );
        $ptt .= $subdir;
        $ptt .= $filenm;

        if( file_exists( $ptt ) ){
            $this->frontend_template_path = apply_filters('JGB/wpsbsc/setFrontendTemplate',$ptt,$this->type,$this->name);
            return;
        }

        $this->frontend_template_path = '';
        
        
    }
}

class FormWidgetWithVisualOptsBase extends FormWidgetBase {
    protected $options;

    function __construct( $params )
    {
        parent::__construct( $params );

        $this->options = $params['options'];
    }

    protected function get_options(){
        return $this->options;
    }
}