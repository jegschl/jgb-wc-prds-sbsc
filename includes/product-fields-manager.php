<?php
namespace JGB\WPSBSC;
class ProductFieldsManager{
    protected $product_fields;

    function __construct()
    {
        $this->set_product_fields();
    }

    public function set_product_fields(){
        $pf = [
            'receta',
            'tipo-de-lente',
			'material-lente',
			'tratamiento-cristal',
            'precio'
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
            if(!empty($_POST[ $pf ])) {
                $cart_item_data[ $pf ] = sanitize_text_field($_POST[ $pf ]);
            }
        }

        return $cart_item_data;
    }

    public function save_order_line_item($item, $cart_item_key, $values, $order) {
        foreach( $this->product_fields as $pf ){
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
                $cart_item['data']->set_price( $prod_price + $cart_item[ 'precio' ] );
            }
            
        }
        
        /* foreach( $this->product_fields as $pf ){
            if(!empty($values[ $pf ])) {
                $item->add_meta_data( $pf , $values[ $pf ]);
            }
        } */
    }

}