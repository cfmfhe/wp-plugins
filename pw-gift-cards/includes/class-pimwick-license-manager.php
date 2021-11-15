<?php

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'Pimwick_License_Manager' ) ) :

final class Pimwick_License_Manager {

    // Keeps from running multiple instances of the license manager per plugin.
    public static $instances = array();

    public $error = '';

    private $license_url = 'https://pimwick.com';
    private $updater_url = 'https://pimwick.com/plugin-updater.php';
    private $license_secret = '588ba467a728d3.17738635';
    private $license_product;
    private $license_data_option_name;
    private $plugin_file;
    private $slug;
    private $premium;
    private $license_data;

    function __construct( $plugin_file ) {
        if ( isset( Pimwick_License_Manager::$instances[ $plugin_file ] ) ) {
            return Pimwick_License_Manager::$instances[ $plugin_file ];
        } else {
            Pimwick_License_Manager::$instances[ $plugin_file ] = $this;
        }

        require 'plugin-update-checker/plugin-update-checker.php';
        $myUpdateChecker = PucFactory::buildUpdateChecker(
            $this->updater_url,
            $plugin_file
        );

        $plugin_data = get_file_data( $plugin_file, array( 'Name' => 'Name' ), 'plugin');

        $this->plugin_file = $plugin_file;
        $this->slug = basename( $plugin_file, '.php' );
        $this->license_product = $plugin_data['Name'];
        $this->license_data_option_name = $this->slug . '-license-data';
        $this->get_license_data();

        add_filter( 'puc_request_info_query_args-' . $this->slug, array( $this, 'puc_request_info_query_args' ) );
        add_action( 'in_plugin_update_message-' . plugin_basename( $plugin_file ), array( $this, 'in_plugin_update_message' ), 10, 2 );
        add_filter( 'plugin_action_links_' . plugin_basename( $plugin_file ), array( $this, 'plugin_action_links' ), 10, 4 );
        add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 99, 4);
        add_action( 'wp_ajax_pimwick_change_license_key', array( $this, 'ajax_change_license_key' ) );
        register_deactivation_hook( $plugin_file, array( $this, 'plugin_deactivation' ) );
    }

    function puc_request_info_query_args( $query_args ) {
        $query_args['license_key'] = $this->license_data->license_key;
        $query_args['slug'] = $this->slug;
        $query_args['registered_domain'] = $_SERVER['SERVER_NAME'];

        return $query_args;
    }

    function in_plugin_update_message( $plugin_data, $response ) {
        if ( empty( $response->package ) ) {
            if ( $this->has_activated() ) {
                echo ' Renew your license to receive this and future updates.';
            } else {
                echo ' Enter your license key on the plugin page to receive updates.';
            }
        }
    }

    function plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {
        if ( $this->has_activated() && $this->is_expired() ) {
            $actions['renew_license'] = '<a href="' . $this->get_renew_url() . '" target="_blank" aria-label="Renew License"><span style="color: red;">License expired</span></a>';
        }

        return $actions;
    }

    function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
        if ( $plugin_file == plugin_basename( $this->plugin_file ) ) {
            $refreshUrl = wp_nonce_url(
                add_query_arg(
                    array(
                        'puc_check_for_updates' => 1,
                        'puc_slug' => $this->slug,
                        'pw_refresh' => 'true',
                    ),
                    self_admin_url('plugins.php')
                ),
                'puc_check_for_updates'
            );

            if ( true === $this->is_expired() ) {
                if ( $this->has_activated() ) {
                    $plugin_meta[] = 'To continue receiving updates you must <a href="' . $this->get_renew_url() . '" target="_blank" aria-label="Renew License"><span style="color: red;">renew your license.</span></a>';
                    $plugin_meta[] = 'Already renewed? <a href="' . $refreshUrl . '" aria-label="Refresh">Click here to refresh.</a>';
                }
            }

            if ( isset( $this->license_data ) && isset( $this->license_data->license_key ) && !empty( $this->license_data->license_key ) ) {
                $nonce = wp_create_nonce( 'pimwick-change-license-key' );
                $plugin_meta[] = "
                    <a href='$refreshUrl' id='pimwick-license-link-{$this->slug}' aria-label='View / Edit license key'>View / Edit license key</a>
                    <script>
                        jQuery('#pimwick-license-link-{$this->slug}').click(function(e) {
                            var editLink = jQuery(this);
                            var href = jQuery(this).attr('href');
                            var key = prompt('License Key', '{$this->license_data->license_key}');
                            if (key && key != '{$this->license_data->license_key}') {
                                editLink.hide().after('<div style=\"color: red; font-weight: 600; font-size: 1.5em;\">Please wait...</div>');
                                jQuery.post(ajaxurl, {'action': 'pimwick_change_license_key', 'plugin': '{$this->license_data_option_name}', 'license_key': key, 'security': '$nonce'}, function(result) {
                                    if (!result.success) {
                                        alert(result.data.message);
                                    }
                                    window.location.href = href;
                                }).fail(function(xhr, textStatus, errorThrown) {
                                    if (errorThrown) {
                                        alert(errorThrown);
                                    } else {
                                        alert('Unknown error');
                                    }
                                    window.location.href = href;
                                });
                            }
                            e.preventDefault();
                            return false;
                        });
                    </script>
                ";
            }
        }

        return $plugin_meta;
    }

    function has_activated() {
        if ( isset( $this->license_data->license_key ) && !empty( $this->license_data->license_key ) ) {
            return true;
        } else {
            return false;
        }
    }

    function is_premium() {
        if ( is_null( $this->premium ) ) {
            $this->premium = $this->validate_license();
        }

        return $this->premium;
    }

    function is_expired() {
        if ( !isset( $this->license_data->date_expiry ) || $this->license_data->date_expiry >= date( 'Y-m-d' ) ) {
            return false;
        } else {
            return true;
        }
    }

    function get_renew_url() {
        $this->get_license_data();
        return $this->updater_url . '?action=renew&license_key=' . $this->license_data->license_key;
    }

    function activate_license( $license_key ) {
        $this->premium = false;

        $result = $this->license_action( $license_key, 'slm_activate' );
        if ( false !== $result ) {
            $this->get_license_data( true, $license_key );
            if ( $this->validate_license() ) {
                $this->premium = true;
                return true;
            }
        } else {
            $this->error = 'An unknown error encountered while calling slm_activate.';
        }

        return false;
    }

    function deactivate_license() {
        $license_key = $this->license_data->license_key;
        if ( !empty( $license_key ) ) {
            if ( $this->license_action( $license_key, 'slm_deactivate' ) ) {
                $this->license_data = new stdClass();
                $this->license_data->license_key = $license_key;

                $this->reset_cache();
                $this->premium = false;
                return true;
            }
        }
        return false;
    }

    function validate_license() {
        $valid = false;

        $this->get_license_data();

        if ( isset( $this->license_data->result ) ) {
            if ( 'success' === $this->license_data->result ) {
                if ( property_exists( $this->license_data, 'status' ) ) {
                    if ( $this->license_data->status != 'expired' && $this->license_data->status != 'blocked' ) {
                        $valid = true;
                    } else {
                        $this->error = sprintf( 'License is %s', $this->license_data->status );
                    }
                }
            } else if ( false !== strpos( $this->license_data->message, 'License key already in use on' ) ) {
                $valid = true;
            } else {
                $this->error = 'Error: ' . $this->license_data->message;
            }
        }

        return $valid;
    }

    function get_license_data( $force_download = false, $license_key = '' ) {
        $this->license_data = get_option( $this->license_data_option_name, '' );

        if ( empty( $this->license_data ) || !isset( $this->license_data->license_key ) || empty( $this->license_data->license_key ) ) {
            $this->license_data = new stdClass();

            // Maybe retrieve the license key stored the old way?
            $this->license_data->license_key = get_option( $this->slug . '-license', '' );
            if ( empty( $this->license_data->license_key ) ) {
                // Some plugins used all underscores instead.
                $this->license_data->license_key = get_option( str_replace( '-', '_', $this->slug . '-license' ), '' );
                if ( empty( $this->license_data->license_key ) ) {
                    // Stragglers...
                    if ( $this->slug == 'pw-woocommerce-bogo-free' ) {
                        $this->license_data->license_key = get_option( 'pw-bogo-license', '' );
                    }
                }
            }
        }

        if ( !empty( $license_key ) ) {
            $this->license_data->license_key = $license_key;
        }

        if ( $force_download || !isset( $this->license_data->cached_on ) || $this->license_data->cached_on != date( 'Y-m-d' ) || isset( $_REQUEST['pw_refresh'] ) ) {
            if ( !empty( $this->license_data->license_key ) ) {
                $license_data = $this->license_action( $this->license_data->license_key, 'slm_check' );
                if ( false !== $license_data && !empty( $license_data ) ) {
                    if ( !isset( $license_data->license_key ) || empty( $license_data->license_key ) ) {
                        $license_data->license_key = $this->license_data->license_key;
                    }
                    if ( isset( $license_data->license_key ) && !empty( $license_data->license_key ) ) {
                        $this->license_data = $license_data;
                    }

                    $this->save_license_data();

                // If there was a problem retrieving the license data (but we have before since it has a cached_on value) then we'll assume a temporary
                // unreachable server. Therefore we won't panic, we just won't update the cached_on date and proceed with the cached data.
                } else if ( !isset( $this->license_data->cached_on ) || empty( $this->license_data->cached_on ) ) {
                    $this->error = 'Error: License action slm_check failed.';
                    return;
                }
            }
        }
    }

    function save_license_data() {
        if ( isset( $this->license_data->license_key ) ) {
            $this->license_data->cached_on = date( 'Y-m-d' );
            update_option( $this->license_data_option_name, $this->license_data, true );
        }
    }

    function delete_license_data() {
        delete_option( $this->license_data_option_name );
        unset( $this->license_data );
    }

    function reset_cache() {
        $this->license_data->cached_on = '';
        update_option( $this->license_data_option_name, $this->license_data, true );
    }

    function license_action( $license_key, $action ) {
        if ( empty( $license_key ) || empty( $action ) ) {
            return false;
        }

        $this->error = '';

        $api_params = array(
            'slm_action' => $action,
            'secret_key' => $this->license_secret,
            'license_key' => $license_key,
            'registered_domain' => $_SERVER['SERVER_NAME'],
            'item_reference' => urlencode( $this->license_product ),
        );

        $query = esc_url_raw( add_query_arg( $api_params, $this->license_url ) );
        $response = wp_remote_get( $query, array( 'timeout' => 240 ) );

        if ( !is_wp_error( $response ) ) {
            $license_data = json_decode( wp_remote_retrieve_body( $response ) );
            if ( !is_null( $license_data ) && !empty( $license_data ) ) {
                return $license_data;
            }
        } else {
            $error_message = $response->get_error_message();
            if ( false !== stripos( $error_message, 'curl error 28: connection timed out after' ) ) {
                $this->error = 'Connection to pimwick.com timed out. Please try your request again. If you continue seeing this message please email us@pimwick.com';
            } else {
                $this->error = "Error while validating license: $error_message";
            }
        }

        return false;
    }

    function ajax_change_license_key() {
        check_ajax_referer( 'pimwick-change-license-key', 'security' );

        $this->license_data_option_name = wc_clean( $_REQUEST['plugin'] );
        $this->license_data = get_option( $this->license_data_option_name, '' );
        if ( !empty( $this->license_data ) && isset( $this->license_data->license_key ) ) {
            $new_license_key = wc_clean( $_REQUEST['license_key'] );
            $old_license_key = $this->license_data->license_key;

            $this->deactivate_license();

            $this->license_data->license_key = $new_license_key;
            $this->activate_license( $new_license_key );

            if ( empty( $this->error ) ) {
                wp_send_json_success();
            } else {
                wp_send_json_error( array( 'message' => $this->error ) );
            }
        } else {
            wp_send_json_error( array( 'message' => 'Existing license data not found.' ) );
        }
    }

    function plugin_deactivation() {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        $this->deactivate_license();
    }
}

endif;
