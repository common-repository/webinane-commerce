Hello <?php echo $customer_data->name ?>,<br>

<p>Please find the attached invoice for the reference #<?php echo esc_attr( $order['ID'] ) ?></p>

<br>
Thanks, <br>
<?php bloginfo( 'name' ) ?>