<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  ?>
<div class="wrap tc_wrap">
    <div class="barcode_api_keys">
        <?php if ( ! tickera_iw_is_pr() || \Tickera\tets_fs()->is_free_plan() ) : ?>
            <a class="tc_link tc_checkinera_link" target="_blank" href="https://tickera.com/checkinera-app/"><?php esc_html_e( 'Check in attendees faster with the premium Checkinera app', 'tickera-event-ticketing-system' ); ?></a>
        <?php endif;
        $current_user = wp_get_current_user();
        $current_user_name = $current_user->user_login;
        $staff_api_keys_num = false; // Set 0 for number of current user API key available
        $wp_api_keys_search = new \Tickera\TC_API_Keys_Search( '', '', '', 9999 ); //$ticket_event_id

        // Count current user API keys available for non-admin users
        if ( ! current_user_can( 'manage_options' ) ) {
            foreach ( $wp_api_keys_search->get_results() as $api_key ) {
                $api_key_obj = new \Tickera\TC_API_Key( $api_key->ID );
                if ( ( $api_key_obj->details->api_username && strtolower( $api_key_obj->details->api_username ) == strtolower( $current_user_name ) ) ) {
                    $staff_api_keys_num = true;
                    break;
                }
            }
        }

        if ( count( $wp_api_keys_search->get_results() ) > 0 && ( current_user_can( 'manage_options' ) || ( ! current_user_can( 'manage_options' ) && $staff_api_keys_num ) ) ) { ?>
            <form action="" method="post" enctype="multipart/form-data">
                <table class="checkin-table">
                    <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="api_key"><?php esc_html_e( 'API Key', 'tickera-event-ticketing-system' ) ?></label></th>
                        <td>
                            <select name="api_key" id="api_key">
                                <?php
                                foreach ( $wp_api_keys_search->get_results() as $api_key ) {
                                    $api_key_obj = new \Tickera\TC_API_Key( $api_key->ID );
                                    if ( current_user_can( 'manage_options' ) || ( $api_key_obj->details->api_username && strtolower( $api_key_obj->details->api_username ) == strtolower( $current_user_name ) ) ) { ?>
                                        <option value="<?php echo esc_attr( $api_key->ID ); ?>"><?php echo esc_html( $api_key_obj->details->api_key_name ); ?></option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
        <?php } ?>
    </div>
    <div class="barcode_holder">
        <h1><?php esc_html_e( 'Barcode Reader', 'tickera-event-ticketing-system' ); ?></h1>
        <div><input type="text" name="barcode" id="barcode"/></div>
        <p class="barcode_status"><?php esc_html_e( 'Select input field and scan a barcode located on the ticket.', 'tickera-event-ticketing-system' ); ?></p>
    </div>
</div><?php
