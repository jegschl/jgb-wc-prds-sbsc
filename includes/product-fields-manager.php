<?php
namespace JGB\WPSBSC;

define( 'JGB_WPSBSC_PROD_DATA_CONFIG_CID','jwps_prod_data_cfg' );
class ProductFieldsManager{
    protected $product_fields;

    protected $items_data_keys;

    function __construct()
    {
        $this->set_product_fields();
        $this->items_data_keys = [];
    }

    public function set_product_fields(){
        $pf = [
            'jwps-prod-data'
        ];

        $this->product_fields = apply_filters('jgb/wpsbsc/setProductFields',$pf);
    }

    public function render_fields(){
        // chequear que sea un oproducto al que se le haya asignado un grupo de campos de configuración.
        ob_start();
        foreach( $this->product_fields as $pf ){
            ?><input type="hidden" name="<?= $pf ?>" value=""><?php
        }
        $output = ob_get_clean();
        echo $output;
    }

    public function process_product_fields($cart_item_data){
        foreach( $this->product_fields as $pf ){
            if(isset( $_POST[ $pf ] ) && !empty($_POST[ $pf ])) {
                
                $pfdata_bs64_encoded = sanitize_text_field($_POST[ $pf ]);
                $pfdata_json_encoded = mb_convert_encoding( rawurldecode( base64_decode($pfdata_bs64_encoded) ), 'UTF-8', 'UTF-8' );
                $pfdata = json_decode($pfdata_json_encoded, true);
                $product = wc_get_product( $_POST['add-to-cart'] );
                foreach( $pfdata as $v ){
                    $cart_item_data[ $v['field'] ] = [
                        'title' => $v['label'],
                        'value_label' => $v['valueLabel']
                    ];
                    if( $v['field'] == 'precio' ){
                        $cart_item_data[ $v['field'] ]['value'] = $v['value'];

                        $precio_montura = $product->get_price();
                        $precio_montura = number_format( $precio_montura, 0, ',', '.' );
                        $cart_item_data[ 'precio-montura' ] = [
                            'title' => 'Precio Montura',
                            'value_label' => '$' . $precio_montura
                        ];
                        $this->items_data_keys[] = 'precio-montura';
                    }
                    $this->items_data_keys[] = $v['field'];
                }

                $cart_item_data[ JGB_WPSBSC_PROD_DATA_CONFIG_CID ] = [
                    'items_data_keys' => $this->items_data_keys
                ];
            }
        }

        return $cart_item_data;
    }

    public function reload_items_data_keys( $visible, $cart_item_data, $cart_item){
        
        if( isset( $cart_item_data[ JGB_WPSBSC_PROD_DATA_CONFIG_CID ] ) 
            && is_array( $cart_item_data[ JGB_WPSBSC_PROD_DATA_CONFIG_CID ] ) 
            && ( count( $cart_item_data[ JGB_WPSBSC_PROD_DATA_CONFIG_CID ] ) > 0 )
        ){
            $this->items_data_keys = $cart_item_data[ JGB_WPSBSC_PROD_DATA_CONFIG_CID ]['items_data_keys'];
        }

        return $visible;
    }

    public function save_order_line_item($item, $cart_item_key, $values, $order) {
        foreach( $this->items_data_keys as $pf ){
            if(!empty($values[ $pf ])) {
                $item->add_meta_data( $pf , $values[ $pf ]);
            }
        }
    }

    public function update_product_price($cart) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) )
            return;

        // Recorre cada producto en el carrito y modifica su precio
        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
            if( isset( $cart_item['precio'] ) ){
                $prod_price = $cart_item['data']->get_price();
                $cart_item['data']->set_price( $prod_price + $cart_item[ 'precio' ]['value'] );
                return;
            }
            
        }
        
    }

    public function poduct_item_data($item_data, $cart_item){
        foreach( $this->items_data_keys as $k ){
            if(isset( $cart_item[ $k ] ) && !empty($cart_item[ $k ])) {
                $pf = $cart_item[ $k ];
                $item_data[] = [
                    'key' => $pf['title'],
                    'value' => $pf['value_label']
                ];
            }
        }

        return $item_data;
    }

}