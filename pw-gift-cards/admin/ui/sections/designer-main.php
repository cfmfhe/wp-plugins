<?php

defined( 'ABSPATH' ) or exit;

$designs = pwgc_get_designs();
$design = reset( $designs );
$design_id = key( $designs );

?>
<div id="pwgc-designer-main">
    <div style="margin-bottom: 24px;">
        <div style="margin-bottom: 4px;">
            <?php _e( 'Select a design to edit or add a new design.', 'pw-woocommerce-gift-cards' ); ?>
        </div>
        <div id="pwgc-select-design-message"></div>
        <select id="pwgc-design-selector" name="design" style="margin-right: 16px;">
            <?php
                foreach ( $designs as $id => $design_option ) {
                    ?>
                    <option value="<?php echo $id; ?>"><?php echo esc_html( $design_option['name'] ); ?></option>
                    <?php
                }
            ?>
        </select>
        <button class="button" id="pwgc-add-design-button"><i class="fas fa-plus"></i> <?php _e( 'Create a new design', 'pw-woocommerce-gift-cards' ); ?></button>
        <hr>
    </div>
    <div id="pwgc-designer-panel-container">
        <?php
            require_once( 'designer-panel.php' );
        ?>
    </div>
</div>