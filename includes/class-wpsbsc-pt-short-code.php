<?php

namespace JGB\WPSBSC;

define( 'JGB_WPSBSC_SC_NM_SBSC_DEFINITION_CPT','JGB_WPSBSC_CptDefinition');

class SBSCDefPTShortCode{

    protected $plg_path;

    protected $tplsPaths;

    function __construct( $plugin_path )
    {
        $this->plg_path = $plugin_path;

        $this->tplsPaths = [];

        $this->tplsPaths['template'] = get_template_directory() . '/wcpsbsc/short-code-wpsbsc-cpt/';

        $this->tplsPaths['plugin'] = $this->plg_path . "public/partials/short-code-wpsbsc-cpt/";

        add_shortcode( JGB_WPSBSC_SC_NM_SBSC_DEFINITION_CPT,[ $this, 'execute' ] );

        add_action('JGB/WPSBSC/render_fields',[$this,'render_fields']);

    }

    public function execute( $atts ){
        $output = '';

        if( !isset( $atts['id'] ) ){
            $output = $this->render_error_html( 'Id de definición de wsbsc no válido.');
        } else {
            global $post;
            $prod = wc_get_product( $post->ID );
            $atts['sku'] = $prod->get_sku();

            
            $wpsbsc_post = get_post( $atts['id'] );
            if( $wpsbsc_post && ( get_post_type( $wpsbsc_post ) == JGB_WPSBSC_CPT_NM_SBSC_DEFINITION ) ){
                // cargar contenido JSON del CPT.
                $json = get_post_field('post_content', $atts['id']);

                // procesar JSON para generar los datos.
                $atts['steps'] = json_decode( $json, true );
            }

            

            $output = $this->render_html( $atts );

        }

        return $output;
    }

    private function render_error_html( $error_msg ){
       
        $output = '';

        ob_start();
        $template = locate_template('/wcpsbsc/short-code-wpsbsc-cpt/error.php', true, false, ['msg' => $error_msg ]);
        $output = ob_get_clean();

        if( empty($template)){
            ob_start();
            load_template( $this->tplsPaths['plugin'].'error.php', true,  ['msg' => $error_msg ] );
            $output = ob_get_clean();
        }

        return $output;
    }

    private function render_html( $atts ){
        $output = '';

        ob_start();
        $template = locate_template('/wcpsbsc/short-code-wpsbsc-cpt/main.php', true, false, $atts);
        $output = ob_get_clean();

        if( empty($template)){
            ob_start();
            load_template( $this->tplsPaths['plugin'].'main.php', true,  $atts );
            $output = ob_get_clean();
        }

        return $output;
    }

    public function render_fields( $fields ){
        $wf = new \JGB\WidgetsFactory();

        foreach( $fields as $k => $fld ){
            $widget = $wf->create_widget( $fld['type'],$fld);
            $widget->render_frontend();
        }
    }
}