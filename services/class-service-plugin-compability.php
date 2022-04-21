<?php

class PPWP_SEC_Service_Plugin_Compatibility {

    // phpcs:ignore
    const SCREENS = array(
        'INSTALLED_PLUGINS' => 'plugins',
        'INSTALL_PLUGINS'   => 'plugin-install',
        'EDIT_PLUGINS'      => 'plugin-editor',
        'PPWP_PS'           => 'password-protect-wordpress_page_ppwp-ps',
        'ALL_POSTS'         => 'edit-post',
        'ALL_PAGES'         => 'edit-page',
        'PPWP_SETTING_PAGE' => 'toplevel_page_wp_protect_password_options',
    );

    /**
     * Get message to show notices when activate plugin
     *
     * @return bool|string
     * String: if exist 1 case don't pass
     * False: All case pass
     */
    public static function get_message_before_activate() {
        if ( ! self::is_ppwp_free_activate() ) {
            return self::message()['activate_ppwp_free'];
        }

        if ( ! self::is_ppwp_pro_activate() ) {
            return self::message()['activate_ppwp'];
        }

        if ( ! self::is_ppwp_pro_enter_license() ) {
            return self::message()['check_license_ppwp'];
        }

        $message = self::check_invalid_addon();
        if ( $message ) {
            return $message;
        }

        return false;
    }

    /**
     * Show message if license is not valid.
     *
     * @return True|False False add-on is not contains in license.
     */
    public static function check_invalid_addon() {
        $configs         = require( PPWP_SEC_PLUGIN_DIR . 'includes/class-ppwp-sec-configs.php' );
        $addon_id        = $configs->addonProductId;
        $default_message = self::message()['check_license_ppwp'];
        $license         = get_option('wp_protect_password_license_key', '');
        if ( empty( $license ) || ! class_exists( 'YME_Addon' ) ) {
            return $default_message;
        }

        if ( method_exists( 'PPW_Pro_License_Services', 'check_addon' ) ) {
            $result = PPW_Pro_License_Services::get_instance()->check_addon( $addon_id );
            if ( $result['success'] ) {
                return false;
            }

            return $result['message'] ? $result['message'] : $default_message;
        }
        $yme_addon = new YME_Addon( 'ppwp-ps' );
        $data      = $yme_addon->isValidPurchased( $addon_id, $license );

        if ( ! isset( $data['isValid'] ) || ! $data['isValid'] ) {
            return $default_message;
        }

        return false;
    }



    /**
     * Get message to show on admin notices
     *
     * @return bool|string
     * String: if exist 1 case don't pass
     * False: All case pass
     */
    public static function get_message_for_admin_notices() {
        if ( ! self::is_show_admin_notices() ) {
            return false;
        }

        if ( ! self::is_ppwp_pro_activate() ) {
            return self::message()['activate_ppwp'];
        }

        if ( ! self::is_ppwp_free_activate() ) {
            return false;
        }

        if ( ! self::is_ppwp_pro_enter_license() ) {
            return self::message()['check_license_ppwp'];
        }

        return false;
    }

    /**
     * Show message for admin notices
     */
    public static function show_message() {
        $message = self::get_message_for_admin_notices();
        if ( false !== $message ) {
            $class = 'notice notice-error is-dismissible';
            printf( '<div class="%1$s"><p><b>%2$s: </b>%3$s</p></div>', esc_attr( $class ), PPWP_PS_PLUGIN_NAME, $message ); // phpcs:ignore
        }
    }

    /**
     * Check screen to show admin notices
     *
     * @return bool
     * True: screen access show notices
     * Otherwise: false
     */
    public static function is_show_admin_notices() {
        $current_screen = get_current_screen();
        if ( null === $current_screen ) {
            return false;
        }

        $screens_is_show = array(
            self::SCREENS['INSTALLED_PLUGINS'],
            self::SCREENS['PPWP_PS'],
            self::SCREENS['PPWP_SETTING_PAGE'],
        );

        return in_array( $current_screen->id, $screens_is_show, true );
    }

    /**
     * Check ppwp free is activate.
     *
     * @return bool
     * True: PPW_VERSION defined means PPWP Free activating
     * False: PPW_VERSION has never defined
     */
    public static function is_ppwp_free_activate() {
        return defined( 'PPW_VERSION' );
    }

    /**
     * Check ppwp pro is activate.
     *
     * @return bool
     * True: PPW_PRO_VERSION defined means PPWP Pro activating
     * False: PPW_VERSION has never defined
     */
    public static function is_ppwp_pro_activate() {
        return defined( 'PPW_PRO_VERSION' );
    }

    /**
     * Check ppwp pro is enter license.
     *
     * @return bool
     * True: PPWP Pro entered valid license
     * False:PPWP Pro has never entered license
     */
    public static function is_ppwp_pro_enter_license() {
        return function_exists( 'is_pro_active_and_valid_license' ) && is_pro_active_and_valid_license();
    }

    /**
     * Check version compatibility
     *
     * @return bool
     * True: PPWP Free is activate and version >= 1.2.3.1 and PPWP Pro is activate and version >= 1.1.5.1
     * Otherwise: false
     */
    public static function is_ppwp_ps_can_work() {
        return defined( 'PPW_PRO_VERSION' ) && defined( 'PPW_VERSION' ) && version_compare( PPW_VERSION, '1.2.3.1' ) >= 0 && version_compare( PPW_PRO_VERSION, '1.1.5.1' ) >= 0;
    }

    /**
     * Show message if license is not valid.
     *
     * @return True|False False add-on is not contains in license.
     */
    public static function is_license_has_add_on() {
        $configs = require( plugin_dir_path( dirname( __FILE__ ) ) . 'class-ppwp-ps-configs.php' );
        $license = get_option( 'wp_protect_password_license_key', '' );
        if ( empty( $license ) || ! class_exists( 'YME_Addon' ) ) {
            return false;
        }
        $yme_addon = new YME_Addon( 'ppwp-ps' );
        $data      = $yme_addon->isValidPurchased( $configs->addonProductId, $license );

        return isset( $data['isValid'] ) && $data['isValid'];
    }

    /**
     * Message for check plugin compatibility.
     *
     * @return array Message array.
     */
    public static function message() {
        $pricing_url      = 'https://passwordprotectwp.com/pricing/';
        $al_extension_url = 'https://passwordprotectwp.com/extensions/password-sec/';
        $free_url         = 'https://wordpress.org/plugins/password-protect-page/';

        return array(
            /* translators: %1$s expands to URL Link */
            'activate_ppwp_free'   => sprintf( __( 'Please install and activate <a target="_blank" rel="noopener noreferrer" href="%1$s">Password Protect WordPress Free</a> plugin', 'ppwp-password-suite' ), $free_url ),
            /* translators: %1$s expands to URL Link */
            'activate_ppwp'        => sprintf( __( 'Please install and activate <a target="_blank" rel="noopener noreferrer" href="%1$s">Password Protect WordPress Pro</a> plugin', 'ppwp-password-suite' ), $pricing_url ),
            /* translators: %1$s expands to URL Link */
            'check_license_ppwp'   => sprintf( __( 'You didn\'t purchase this add-on with your <a target="_blank" rel="noreferrer noopener" href="%1$s">Password Protect WordPress Pro</a> plugin. Please <a target="_blank" rel="noreferrer noopener" href="%2$s">do it now</a> or drop us an email at <a href="mailto:hello@PreventDirectAccess.com">hello@PreventDirectAccess.com</a> if you have any questions!', 'ppwp-password-suite' ), $pricing_url, $al_extension_url ),
            /* translators: %s Plugin name*/

            'condition_for_plugin' => sprintf( __( 'Please update Password Protect WordPress Lite and Pro to the latest versions for %s extension to work properly.', 'ppwp-password-suite' ), PPWP_SEC_PLUGIN_NAME ),
        );
    }

}
