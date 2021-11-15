<?php

defined( 'ABSPATH' ) or exit;

global $pw_gift_cards;
global $wpdb;

require( 'header.php' );
require( 'activation.php' );

?>
<div class="pwgc-main-content">
    <?php
        require( 'initial-setup.php' );
        require( 'section-buttons.php' );
        require( 'sections/balances.php' );
        require( 'sections/designer.php' );
        require( 'sections/create.php' );
        require( 'sections/import.php' );
        require( 'sections/settings.php' );
    ?>
</div>
<?php

require( 'footer.php' );
