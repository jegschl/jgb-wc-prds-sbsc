<?php

class JgBWPSTemplater{

    function __construct()
    {
        
    }

    public function wc_get_template_single_product_add_to_cart_variable( $template, $template_name, $args, $template_path, $default_path ){
        $tt = '';
        if( $template_name == "single-product/add-to-cart/variable.php"){
            $template = Jgb_Wc_Prds_Sbsc::get_plugin_home_path() . "/public/partials/wc_variable.php";
            //$tt = $template;
        }
        return $template;
    }
}