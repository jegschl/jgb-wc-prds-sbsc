<?php

namespace JGB\WPSBSC;

define( 'JGB_WPSBSC_SC_NM_SBSC_DEFINITION_CPT','JGB_WPSBSC_CptDefinition');

class SBSCDefPTShortCode{

    protected $plg_path;

    protected $tplsPaths;

    protected $popup_maker_id;

    function __construct( $plugin_path ){
        
        $this->plg_path = $plugin_path;

        $this->tplsPaths = [];

        $this->tplsPaths['template'] = get_template_directory() . '/wcpsbsc/short-code-wpsbsc-cpt/';

        $this->tplsPaths['plugin'] = $this->plg_path . "public/partials/short-code-wpsbsc-cpt/";

        $this->popup_maker_id = apply_filters('JGB/WPSBSC/popupmaker_popup_id', '22562' );

        add_shortcode( JGB_WPSBSC_SC_NM_SBSC_DEFINITION_CPT,[ $this, 'execute' ] );

        add_action('JGB/WPSBSC/render_fields',[$this,'render_fields']);

        add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'render_button_crystal_selection' ], 90 );

    }

    public function render_button_crystal_selection() {
        $pm_id = apply_filters('JGB/WPSBSC/popupmaker_popup_id', '22562' );
        ?>
        <div class="button-select-crystals">
            <button class="popmake-<?= $pm_id ?> pum-trigger" style="cursor: pointer;">Seleccionar cristales</button>
        </div>
        <?php
    }
    

    public function generate_decision_tree_json_database( $post_id ){
        global $wpdb;

        $pfx = $wpdb->prefix;

        $tblpn = $pfx . 'jgb_wpsbsc';

        $r = [];

        $tblnm = $tblpn . '_fields';
        $sql  = "SELECT * FROM {$tblnm} ";
        $sql .= "WHERE post_id = %d ";

        $fields = $wpdb->get_results( $wpdb->prepare( $sql, $post_id ), ARRAY_A );

        $r['fields'] = $fields;
        
        $tblnm = $tblpn . '_choices_availables';
        $sql  = "SELECT * FROM {$tblnm} ";
        $sql .= "WHERE post_id = %d ";

        $choices_availables = $wpdb->get_results( $wpdb->prepare( $sql, $post_id ), ARRAY_A );

        $r['choices_availables'] = $choices_availables;

       
        
       
        $tblnm = $tblpn . '_choices_combinations';
        
        $sql  = "SELECT * FROM {$tblnm} ";
        
        $sql .= "WHERE post_id = %d ";
        
        $r['choices_combinations'] = $wpdb->get_results( $wpdb->prepare( $sql, $post_id ), ARRAY_A );

        
        $vcs_items = [];

        $tblnm = $tblpn . '_vcs_items';
        $sql = "SELECT * FROM $tblnm ";
        $sql .= "WHERE post_id = %d ";

        $vcs_items = $wpdb->get_results( $wpdb->prepare( $sql, $post_id ), ARRAY_A );

        $r['vcs_items'] = $vcs_items;


        $tblnm = $tblpn . '_vcs_items';

        $tblItmData = $tblpn . '_items_data';

        $sql = "SELECT itd.*,vi.priority_in_step FROM $tblnm vi ";

        $sql .= "JOIN $tblItmData itd ON itd.id = vi.id_item ";

        $sql .= "WHERE vi.post_id = %d ";

        $sql .= "AND item_type = 'DATA'";

        $r['items_data'] = $wpdb->get_results( $wpdb->prepare( $sql, $post_id ), ARRAY_A );


        $tblItmFld = $tblpn . '_items_field';

        $sql = "SELECT itf.*,vi.priority_in_step FROM $tblnm vi ";

        $sql .= "JOIN $tblItmFld itf ON itf.id = vi.id_item ";

        $sql .= "WHERE vi.post_id = %d ";
        
        $sql .= "AND vi.item_type = 'FIELD'";

        $r['items_field'] = $wpdb->get_results( $wpdb->prepare( $sql, $post_id ), ARRAY_A );


        return $r;

    }

    public function load_fields_data( $post_id ){
        global $wpdb;

        $pfx = $wpdb->prefix;

        $tblpn = $pfx . 'jgb_wpsbsc';

        $r = [];

        $sql  = "SELECT * FROM {$tblpn}_fields ";
        $sql .= "WHERE post_id = %d ";

        $fields = $wpdb->get_results( $wpdb->prepare( $sql, $post_id ), ARRAY_A );

        foreach( $fields as &$fld ){
            $slug = $fld['slug'];

            $fld['label'] = $fld['name'];

            $r[$slug] = $fld;

            $sql  = "SELECT * FROM {$tblpn}_choices_availables ";
            $sql .= "WHERE field_id = %d ";

            $raw_opts = $wpdb->get_results( $wpdb->prepare( $sql, $fld['id'] ), ARRAY_A );
            foreach( $raw_opts as $raw_opt){
                if( !isset( $r[$slug]['options'] ) ){
                    $r[$slug]['options'] = [];
                }

                $r[$slug]['options'][] = [
                    'id'    => $raw_opt['id'],
                    'label' => $raw_opt['selectable_value_label'],
                    'slug'  => $raw_opt['selectable_value_slug'],
                    'value' => $raw_opt['selectable_value_slug']
                ];
            }
            
        
        }
        return $r;
            
    }

    private function get_selectable_field_types(){
        return [
            'radio',
            'select'];
    }

    public function get_fields_html_templates( $post_id ){
        
        $r = [];

        $fields = $this->load_fields_data( $post_id );

        foreach( $fields as $fld ){
            $slug = $fld['slug'];
            $tpls = [];

            $tpls['wrapper'] = $this->render_fields_wrapper_template( [$fld] );

            if( in_array( $fld['type'], $this->get_selectable_field_types() ) ){

                $tp = $this->render_fields_options_template( [ $fld ] );

                $tpls['options'] = [];

                foreach( $tp as $k => $v ){
                    
                    $indexSlugHomolgated = false;
                    
                    $fieldOptionKey = 0;
                    
                    foreach( $fld['options'] as $optk => $fldOpt ){
                        
                        if( $fldOpt['slug'] == $k ){
                            
                            $indexSlugHomolgated = true;
                           
                            $fieldOptionKey = $optk;

                            break;

                        }

                    }

                    if( $indexSlugHomolgated ){

                        ob_start();
                       
                        \load_template( $v, false, ['option' => $fld['options'][$fieldOptionKey] ] );
                        
                        $tpls['options'][$k] = ob_get_clean();

                    } else {
                        $tpls['options'][$k] = '';
                    }
                    
                }

            }

            $r[$slug] = $tpls;

        }

        return $r;
    }

    private function load_addtnl_selection_templates_definitions(){

        $tplFl = $this->plg_path . 'includes/widgetsman/widgets/frontend/additional-selection-wrapper.php';

        ob_start();
                       
        load_template( $tplFl, false );

        $tplWrppr = ob_get_clean();


        $tplFl = $this->plg_path . 'includes/widgetsman/widgets/frontend/additional-selection-option.php';

        ob_start();
                       
        load_template( $tplFl, false );

        $tplOptions = ob_get_clean();


        $styleId = 'adtnl-sel-css';
        
        wp_register_style( $styleId, plugin_dir_url( $this->plg_path ) . 'public/css/additional-selection.css' );

        
        $version = filemtime( $this->plg_path . 'public/js/additional-selection.js' );

        $scriptId = 'adtnl-sel-js';

        wp_register_script( $scriptId, plugin_dir_url( $this->plg_path ) . 'public/js/additional-selection.js', ['jquery'], $version, true );

        $defs = [
                    'default' => [
                        'choiceCombinationId' => null,
                        'htmlTplWrapper' => $tplWrppr,
                        'htmlTplOptions'  => $tplOptions,
                        'scriptIdCss' => $styleId,
                        'scriptIdJs' => $scriptId
                    ]
                ];

        return apply_filters('JGB/WPSBSC/additional_selection_templates_definitions', $defs );

    }

    public function get_step_wraper_begin_tpl(){
        ob_start();
        ?>
        <div class="swiper-slide">

            <div class="step step-{{step_index}}">

                <div class="title">{{title}}</div>
                
                    <div class="content"> 
        <?php
        return apply_filters('JGB/WPSBSC/step_wraper_begin_tpl', ob_get_clean());   
    }

    public function get_step_wraper_end_tpl(){
        ob_start();
        ?>
                    </div>

                </div>

            </div>

        </div>
        <?php
        return apply_filters('JGB/WPSBSC/step_wraper_end_tpl', ob_get_clean());

    }

    public function get_step_titles( $post_id ){
        global $wpdb;

        $pfx = $wpdb->prefix;

        $s  = "SELECT DISTINCT priority_in_step as steps FROM {$pfx}jgb_wpsbsc_fields ";
        $s .= "WHERE post_id = %d ";
        $s .= "UNION ";
        $s .= "SELECT DISTINCT priority_in_step as steps FROM {$pfx}jgb_wpsbsc_vcs_items ";
        $s .= "WHERE post_id = %d ";
        $s .= "ORDER BY steps ASC";

        $steps = $wpdb->get_results( $wpdb->prepare( $s, $post_id, $post_id ), ARRAY_A );

        $r = [];
        foreach( $steps as $step ){
            $r[ $step['steps'] ] = "Paso " . ($step['steps']+1);
        }

        return apply_filters('JGB/WPSBSC/step_titles', $r, $post_id );

    }

    function enqueue_scripts( &$atts ){

        $this->popup_maker_id = apply_filters('JGB/WPSBSC/popupmaker_popup_id', '22562' );

        $opts = get_post_meta( $atts['id'], JGB_WPSBSC_CPT_MKNM_OPTIONS, true );

        $atts['visualization-mode'] = $opts['visualization-mode'];

        $atts['product-categories'] = $opts['product-categories'];

        $script_array_info = [
            'visualizationMode' => $opts['visualization-mode']
        ];

        if( $opts['visualization-mode'] == 'tree-choices' ){

            $script_array_info['dtDataBase'] = $this->generate_decision_tree_json_database( $atts['id'] );

            $script_array_info['fieldsTemplates'] = $this->get_fields_html_templates( $atts['id'] );

            $script_array_info['additionalSelectionTemplates'] = $this->load_addtnl_selection_templates_definitions();
            
            $script_array_info['beginStepWraperTpl'] = $this->get_step_wraper_begin_tpl();

            $script_array_info['endStepWraperTpl'] = $this->get_step_wraper_end_tpl();

            $script_array_info['stepTitles'] = $this->get_step_titles( $atts['id'] );

            $script_array_info['popupMakerId'] = $this->popup_maker_id;
            
            $jsid = 'taffy-min';
            $jsfn = $jsid . '.js';
            $js_base_path = $this->plg_path . 'public/js/lib/taffy/';
            $js_script_fl_jcplg_path = $js_base_path . $jsfn;
            $js_script_fl_jcplg = plugin_dir_url( $js_script_fl_jcplg_path ) . $jsfn;
            $tversion = filemtime($js_script_fl_jcplg_path);
            wp_enqueue_script( 
                $jsid, 
                $js_script_fl_jcplg, 
                array( 
                    'jquery',
                    'swiper-bundle',
                    'jgb-ir-select-color'
                ), 
                $tversion,
                false 
            );
        
        }   

        $jsid = $opts['visualization-mode'] == 'json-data' ? 'jgb-wc-prds-sbsc-public' : 'jgb-wc-prds-sbsc-public-dt';
        $jsfn = $jsid . '.js';
        $js_base_path = $this->plg_path . 'public/js/';
        $js_script_fl_jcplg_path = $js_base_path . $jsfn;
        $js_script_fl_jcplg = plugin_dir_url( $js_script_fl_jcplg_path ) . $jsfn;
        $tversion = filemtime($js_script_fl_jcplg_path);
        wp_enqueue_script( 
            $jsid, 
            $js_script_fl_jcplg, 
            array( 
                'jquery',
                'swiper-bundle',
                'jgb-ir-select-color'
            ), 
            $tversion,
            false 
        );

        wp_localize_script( $jsid, 'JGB_WPSBSC_DATA', $script_array_info );
        
    }

    public function execute( $atts ){
        $output = '';

        if( !isset( $atts['id'] ) ){
            $output = $this->render_error_html( 'Id de definición de wsbsc no válido.');
        } else {
            global $post;
            $prod = wc_get_product( $post->ID );
            if( $prod && is_a( $prod, 'WC_Product' ) ){
                $atts['sku'] = $prod->get_sku();

            
                $wpsbsc_post = get_post( $atts['id'] );
                if( $wpsbsc_post && ( get_post_type( $wpsbsc_post ) == JGB_WPSBSC_CPT_NM_SBSC_DEFINITION ) ){
                    // cargar contenido JSON del CPT.
                    $json = get_post_field('post_content', $atts['id']);

                    // procesar JSON para generar los datos.
                    $atts['steps'] = json_decode( $json, true );

                    $this->enqueue_scripts( $atts );

                }

                $output = $this->render_html( $atts );

            } else {
                $output = $this->render_error_html( 'No es un producto válido.');
            }

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

        $template_file_name = ( $atts['visualization-mode'] == 'json-data' ) || ( empty( $atts['visualization-mode'] ) ) ? 'main.php' : 'main-tree.php';

        $tpl_path = '/wcpsbsc/short-code-wpsbsc-cpt/'.$template_file_name;

        ob_start();
        $template = locate_template($tpl_path, true, false, $atts);
        $output = ob_get_clean();

        if( empty($template)){
            ob_start();
            load_template( $this->tplsPaths['plugin'].$template_file_name, false,  $atts );
            $output = ob_get_clean();
        }

        return $output;
    }

    public function render_fields_wrapper_template( $fields ){
        $wf = new \JGB\WidgetsFactory();
        $r = [];
        foreach( $fields as $k => $fld ){
            $widget = $wf->create_widget( $fld['type'],$fld);
            $r[ $fld['slug'] ] = $widget->get_field_wrapper_template();
        }

        return $r;
    }

    public function render_fields_options_template( $fields ){
        $wf = new \JGB\WidgetsFactory();
        $otpls = [];
        $r = null;

        foreach( $fields as $k => $fld ){
            $widget = $wf->create_widget( $fld['type'],$fld);
            $r = $otpls[ $fld['slug'] ] = $widget->get_field_options_template();
        }

        return $r;
    }
}