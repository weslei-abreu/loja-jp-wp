<?php

namespace WeDevs\DokanPro\Modules\ReportAbuse;

use WC_Email;

class AdminEmail extends WC_Email {

    /**
     * The report to be emailed
     *
     * @var null|object
     */
    protected $report = null;

    /**
     * Class constructor
     *
     * @since 2.9.8
     *
     * @return void
     */
    public function __construct() {
        $this->id             = 'dokan_report_abuse_admin_email';
        $this->title          = esc_html__( 'Dokan Report Abuse', 'dokan' );
        $this->description    = esc_html__( 'Send abuse report notification to admin.', 'dokan' );
        $this->template_html  = 'emails/report-abuse-admin-email-html.php';
        $this->template_plain = 'emails/plain/report-abuse-admin-email-html.php';
        $this->template_base  = trailingslashit( DOKAN_REPORT_ABUSE_VIEWS );

        // Call parent constructor
        parent::__construct();

        // Set recipient
        $this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );

        // Hook the trigger method
        add_action( 'dokan_report_abuse_send_admin_email', [ $this, 'trigger' ] );
    }

    /**
     * Email settings
     *
     * @since 2.9.8
     *
     * @return void
     */
    public function init_form_fields() {
        /* translators: %s: list of placeholders */
        $placeholder_text  = sprintf( __( 'Available placeholders: %s', 'dokan' ), '<code>' . implode( '</code>, <code>', array_keys( $this->placeholders ) ) . '</code>' );
        $this->form_fields = [
            'enabled'            => [
                'title'   => esc_html__( 'Enable/Disable', 'dokan' ),
                'type'    => 'checkbox',
                'label'   => esc_html__( 'Enable this email', 'dokan' ),
                'default' => 'yes',
            ],
            'recipient' => [
                'title'         => __( 'Recipient(s)', 'dokan' ),
                'type'          => 'text',
                // translators: 1) Email recipients
                'description'   => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'dokan' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
                'placeholder'   => '',
                'default'       => '',
                'desc_tip'      => true,
            ],
            'subject'            => [
                'title'       => esc_html__( 'Subject', 'dokan' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_subject(),
                'default'     => $this->get_default_subject(),
            ],
            'heading'            => [
                'title'       => esc_html__( 'Email heading', 'dokan' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_heading(),
                'default'     => $this->get_default_heading(),
            ],
            'additional_content' => [
                'title'       => __( 'Additional content', 'dokan' ),
                'description' => __( 'Text to appear below the main email content.', 'dokan' ) . ' ' . $placeholder_text,
                'css'         => 'width:400px; height: 75px;',
                'placeholder' => __( 'N/A', 'dokan' ),
                'type'        => 'textarea',
                'default'     => $this->get_default_additional_content(),
                'desc_tip'    => true,
            ],
            'email_type'         => [
                'title'       => __( 'Email type', 'dokan' ),
                'type'        => 'select',
                'description' => __( 'Choose which format of email to send.', 'dokan' ),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
            ],
        ];
    }

    /**
     * Email default subject
     *
     * @since 2.9.8
     *
     * @return string
     */
    public function get_default_subject() {
        return esc_html__( '[{site_title}] A new abuse report has been submitted', 'dokan' );
    }

    /**
     * Email default heading
     *
     * @since 2.9.8
     *
     * @return string
     */
    public function get_default_heading() {
        return esc_html__( 'Product Abuse Report', 'dokan' );
    }

    /**
     * Send email
     *
     * @since 2.9.8
     *
     * @return void
     */
    public function trigger( $report ) {
        if ( ! $this->is_enabled() && ! $this->get_recipient() ) {
            return;
        }
        $this->setup_locale();
        $this->report = $report;

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

        $this->restore_locale();
    }

    /**
     * Email content
     *
     * @since 2.9.8
     *
     * @return string
     */
    public function get_content_html() {
        $product = wc_get_product( $this->report->product_id );
        $vendor  = dokan_get_vendor( $this->report->vendor_id );

        return wc_get_template_html(
            $this->template_html,
            [
                'email_heading'      => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin'      => true,
                'plain_text'         => false,
                'email'              => $this,
                'data'               => [
                    'product_title'  => $product->get_title(),
                    'product_link'   => admin_url( sprintf( 'post.php?post=%d&action=edit', $product->get_id() ) ),
                    'vendor_name'    => $vendor->get_shop_name(),
                    'vendor_link'    => admin_url( sprintf( 'user-edit.php?user_id=%d', $vendor->get_id() ) ),
                    'reason'         => $this->report->reason,
                    'description'    => $this->report->description,
                    'customer'       => $this->report->customer_id ? new \WC_Customer( $this->report->customer_id ) : 0,
                    'customer_name'  => $this->report->customer_name,
                    'customer_email' => $this->report->customer_email,
                    'reported_at'    => strtotime( $this->report->reported_at ),
                    'report'         => $this->report,
                ],
            ],
            'dokan/',
            $this->template_base
        );
    }

    /**
     * Email content plaintext
     *
     * @since 4.0.0
     *
     * @return string
     */
    public function get_content_plain() {
        $product = wc_get_product( $this->report->product_id );
        $vendor  = dokan_get_vendor( $this->report->vendor_id );

        return wc_get_template_html(
            $this->template_plain,
            [
                'email_heading'      => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin'      => true,
                'plain_text'         => true,
                'email'              => $this,
                'data'               => [
                    'product_title'  => $product->get_title(),
                    'product_link'   => admin_url( sprintf( 'post.php?post=%d&action=edit', $product->get_id() ) ),
                    'vendor_name'    => $vendor->get_shop_name(),
                    'vendor_link'    => admin_url( sprintf( 'user-edit.php?user_id=%d', $vendor->get_id() ) ),
                    'reason'         => $this->report->reason,
                    'description'    => $this->report->description,
                    'customer'       => $this->report->customer_id ? new \WC_Customer( $this->report->customer_id ) : 0,
                    'customer_name'  => $this->report->customer_name,
                    'customer_email' => $this->report->customer_email,
                    'reported_at'    => strtotime( $this->report->reported_at ),
                    'report'         => $this->report,
                ],
            ],
            'dokan/',
            $this->template_base
        );
    }
}
