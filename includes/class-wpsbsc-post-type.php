<?php

namespace JGB\WPSBSC;

define( 'JGB_WPSBSC_HOOK_DEF_SBSC_DEFINITION_CPT_ARGS','JGB/WPSBSC/DefinitionCptDefaultArgs');
define( 'JGB_WPSBSC_CPT_NM_SBSC_DEFINITION','jgb_wpsbsc_def');
define( 'JGB_WPSBSC_CPT_URP_NM_MAIN_CONTENT', 'main_json_content' );
define( 'JGB_WPSBSC_CPT_MKNM_OPTIONS', 'jgb_wpsbsc_cpt_def_opts');
class SBSCDefinitionPostType{

    protected $args;

    protected $main_content_json;

    function __construct(){

    }

    private function default_args(){
        return apply_filters(
            JGB_WPSBSC_HOOK_DEF_SBSC_DEFINITION_CPT_ARGS,
            [
                'labels' => [
                    'name' => __('Step by step flows'),
                    'singular_name' => __('Step by step flow')
                ],
                'public' => true,
                'has_archive' => false,
                'publicly_queryable' => false,
                'supports' => [
                    'title'
                ],
                'rewrite'   => [ 'slug' => 'wc-prods-sbsc-flows' ],
                'menu_position' => 5,
                'menu_icon' => 'dashicons-arrow-right'
            ]
        );
    }

    public function register(){
        $args = $this->default_args();
        register_post_type( JGB_WPSBSC_CPT_NM_SBSC_DEFINITION, $args ); 
    } 

    public function enqueue_admin_scripts($hook) {
        global $post_type, $post;
        
        if (($hook == 'post-new.php' || $hook == 'post.php') && $post_type == JGB_WPSBSC_CPT_NM_SBSC_DEFINITION ) {
        
            $this->main_content_json = $post->post_content;

            $plg_dir = str_replace( "/includes", "", plugin_dir_url( __FILE__ ) );

            $dt = [
                'jsonEdtrSelectr' => JGB_WPSBSC_CPT_URP_NM_MAIN_CONTENT,
                'main_content_json' => $this->main_content_json
            ];

            

            $sbn   = "jsoneditor.min";
            $sid   = $sbn . "-js";
            $sbfn  = $sbn . ".js";
            $surl  = $plg_dir . 'admin/js/jsoneditor/dist/' . $sbfn;
            $spath = $plg_dir . 'admin/js/jsoneditor/dist/' . $sbfn;
            $sftk  = filemtime($spath);

            wp_enqueue_script( $sid, $surl, array('jquery'), $sftk, true );


            $sbn   = "jgb-mem-sheet-mtx";
            $sid   = $sbn . "-js";
            $sbfn  = $sbn . ".js";
            $surl  = $plg_dir . 'admin/js/' . $sbfn;
            $spath = $plg_dir . 'admin/js/' . $sbfn;
            $sftk  = filemtime($spath);

            wp_enqueue_script( $sid, $surl, array('jquery'), $sftk, true );


            $sbn   = "jquery.blockUI";
            $sid   = $sbn . "-js";
            $sbfn  = $sbn . ".js";
            $surl  = $plg_dir . 'assets/js/lib/' . $sbfn;
            $spath = $plg_dir . 'assets/js/lib/' . $sbfn;
            $sftk  = filemtime($spath);

            wp_enqueue_script( $sid, $surl, array('jquery'), $sftk, true );


            $sbn   = JGB_WPSBSC_CPT_NM_SBSC_DEFINITION . "-admin";
            $sid   = $sbn . "-js";
            $sbfn  = $sbn . ".js";
            $surl  = $plg_dir . 'admin/js/' . $sbfn;
            $spath = $plg_dir . 'admin/js/' . $sbfn;
            $sftk  = filemtime($spath);

            wp_enqueue_script( $sid, $surl, array('jquery','jsoneditor.min-js','jgb-mem-sheet-mtx-js','jquery.blockUI-js'), $sftk, true );


            wp_localize_script( $sid, 'JGB_WPSBSC_CPT_DEF_DATA', $dt );
        
        }
    }

    public function enqueue_admin_styles( $hook ){
        global $post_type;
       
        if (($hook == 'post-new.php' || $hook == 'post.php') && $post_type == JGB_WPSBSC_CPT_NM_SBSC_DEFINITION) {
            
            $plg_dir = str_replace( "/includes", "", plugin_dir_url( __FILE__ ) );

            $sbn   = "jsoneditor.min";
            $sid   = $sbn . "-css";
            $sbfn  = $sbn . ".css";
            $surl  = $plg_dir . 'admin/css/jsoneditor/' . $sbfn;
            $spath = $plg_dir . 'admin/css/jsoneditor/' . $sbfn;
            $sftk  = filemtime($spath);

            wp_enqueue_style($sid, $surl, [], $sftk);
        }
    }

    public function add_meta_box_json_editor() {
        add_meta_box(
            'meta_box_json_editor', // ID único de la meta caja
            'Datos JSON', // Título de la meta caja
            [$this,'render_metabox_json_field'], // Función para mostrar el contenido del campo
            JGB_WPSBSC_CPT_NM_SBSC_DEFINITION, // Nombre de tu CPT
            'normal', // Contexto (puede ser 'normal', 'advanced', o 'side')
            'high' // Prioridad (puede ser 'high', 'core', 'default' o 'low')
        );
    }

    public function add_meta_box_choices_importer() {
        add_meta_box(
            'meta_box_choices_importer',
            'Importador de arbol decisivo',
            [ $this, 'render_metabox_choices_tree_importer'],
            JGB_WPSBSC_CPT_NM_SBSC_DEFINITION,
            'normal',
            'high'
        );
    }

    public function add_meta_box_options(){
        add_meta_box(
            'meta_box_options',
            'Opciones',
            [ $this, 'render_metabox_options'],
            JGB_WPSBSC_CPT_NM_SBSC_DEFINITION,
            'normal',
            'high'
        );
    }

    public function render_metabox_json_field( $post ){
        // Recupera el valor actual del contenido del campo
        $this->main_content_json = get_post_field('post_content', $post->ID);
        ?>
        <input name="<?= JGB_WPSBSC_CPT_URP_NM_MAIN_CONTENT ?>" type="hidden">
        <div id="<?= JGB_WPSBSC_CPT_URP_NM_MAIN_CONTENT ?>"  style="width: 100%; height: 600px"></div>
        <?php
    }

    public function render_metabox_choices_tree_importer( $post ){
        ?>
        <input name="post-id" type="hidden" value="<?= $post->ID ?>">
        <div id="choices-tree-input">
            <textarea></textarea>
        </div>
        <div class="import-button-wrapper">
            <div class="button">Importar</div>
        </div>
        <?php
    }

    public function render_metabox_options( $post ){
        $opts = get_post_meta( $post->ID, JGB_WPSBSC_CPT_MKNM_OPTIONS, true );
        $opts = $opts ? $opts : [
            'visualization-mode' => 'json-data',
            'product-categories' => 0
        ];
        $vmjd = $opts['visualization-mode'] == 'json-data' ? 'checked' : '';
        $vmdt = $opts['visualization-mode'] == 'tree-choices' ? 'checked' : '';
        $nocat = $opts['product-categories'] == 0 ? 'selected' : '';

        ?>
        <div id="visualization-mode">

            <div class="radio">
                <input type="radio" name="visualization-mode" value="json-data" id="r-vm-json" <?= $vmjd ?>>
                <label  for="r-vm-json"><p>Datos JSON</p></label>
            </div>

            <div class="radio">
                <input type="radio" name="visualization-mode" value="tree-choices" id="r-vm-dtree" <?= $vmdt ?> >
                <label  for="r-vm-dtree"><p>Arbol decisivo</p></label>
            </div>
    
        </div>
        
        <div id="woocommerce-categories">
            <select id="product-categories" name="product-category">
                <option value="0" <?= $nocat ?>>Seleccionar categoría</option>
                <?php
                    $product_categories = get_terms( 'product_cat', [
                        'hide_empty' => true
                    ] );
                   
                    foreach( $product_categories as $category ){
                        $selected = $opts['product-categories'] == $category->term_id ? 'selected' : '';
                        echo '<option value="' . $category->term_id . '" '.$selected.'>' . $category->name . '</option>';
                    }
                ?>
            </select>
        </div>
        <?php

    }

    function save_post($post_id) {
        if (isset($_POST[JGB_WPSBSC_CPT_URP_NM_MAIN_CONTENT])) {
            $rjson_data = wp_kses_post($_POST[JGB_WPSBSC_CPT_URP_NM_MAIN_CONTENT]);
            $json_data = json_encode( $rjson_data );
            // Actualizar el contenido del CPT

            remove_action('save_post',[ $this , 'save_post' ]);

            wp_update_post(array('ID' => $post_id, 'post_content' => $rjson_data));

            $opts = [
                'visualization-mode' => $_POST['visualization-mode'],
                'product-categories' => $_POST['product-category']
            ];
            update_post_meta( $post_id, JGB_WPSBSC_CPT_MKNM_OPTIONS, $opts );

            add_action('save_post', [ $this, 'save_post' ]);
        }
    }
}