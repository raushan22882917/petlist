<?php
/**
 * Dashboard Tab: Billing / Payment History
 * @package Petslist Dog Directory
 */

use RadiusTheme\Petslist\DogDirectory\Subscription;

if ( ! defined( 'ABSPATH' ) ) exit;

$user_id  = get_current_user_id();
$payments = Subscription::get_payment_history( $user_id, 50 );
$total    = array_sum( array_column( (array)$payments, 'amount' ) );
?>

<div class="dd-tab-billing">

    <div class="dd-tab-dogs__header">
        <h2><?php _e( 'Billing & Payment History', 'petslist' ); ?></h2>
        <p class="dd-tab-dogs__subtitle"><?php _e('Review your payment history, transactions, and membership invoices.', 'petslist'); ?></p>
    </div>

    <?php if ( $payments ) : ?>

    <div class="dd-dogs-card-panel">

        <div class="dd-billing-summary">
            <div class="dd-billing-summary__item">
                <div class="dd-billing-summary__icon">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div class="dd-billing-summary__info">
                    <span class="dd-billing-summary__label"><?php _e( 'Total Spent', 'petslist' ); ?></span>
                    <span class="dd-billing-summary__value">$<?php echo number_format( $total, 2 ); ?></span>
                </div>
            </div>
            <div class="dd-billing-summary__item">
                <div class="dd-billing-summary__icon" style="background: #e0faf9; color: #02c5bd;">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div class="dd-billing-summary__info">
                    <span class="dd-billing-summary__label"><?php _e( 'Transactions', 'petslist' ); ?></span>
                    <span class="dd-billing-summary__value"><?php echo count( $payments ); ?></span>
                </div>
            </div>
        </div>

        <div class="dd-dogs-table-wrap">
            <table class="dd-dogs-table">
                <thead>
                    <tr>
                        <th><?php _e( 'Date', 'petslist' ); ?></th>
                        <th><?php _e( 'Plan', 'petslist' ); ?></th>
                        <th><?php _e( 'Amount', 'petslist' ); ?></th>
                        <th><?php _e( 'Method', 'petslist' ); ?></th>
                        <th><?php _e( 'Status', 'petslist' ); ?></th>
                        <th><?php _e( 'Transaction ID', 'petslist' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $payments as $pay ) :
                        $status_class = $pay->status === 'completed' ? 'active' : ( $pay->status === 'failed' ? 'draft' : 'pending' );
                    ?>
                    <tr>
                        <td style="color: #64748b; font-weight: 500; font-size: 13.5px;"><?php echo date( 'M j, Y', strtotime( $pay->created_at ) ); ?></td>
                        <td style="font-weight: 600; color: #1e293b;"><?php echo esc_html( $pay->plan_name ?: '—' ); ?></td>
                        <td style="font-weight: 700; color: #0f172a; font-size: 14.5px;">$<?php echo number_format( $pay->amount, 2 ); ?></td>
                        <td style="color: #475569; text-transform: uppercase; font-size: 12px; font-weight: 600; letter-spacing: 0.5px;"><?php echo esc_html( $pay->payment_method ?: 'card' ); ?></td>
                        <td>
                            <span class="dd-status-pill dd-status-pill--<?php echo esc_attr($status_class); ?>">
                                <?php echo esc_html( ucfirst( $pay->status ) ); ?>
                            </span>
                        </td>
                        <td>
                            <span class="dd-transaction-id" style="font-family: Consolas, Monaco, monospace; font-size: 12px; color: #64748b; background: #f1f5f9; padding: 3px 8px; border-radius: 6px;">
                                <?php echo $pay->transaction_id ? esc_html( substr($pay->transaction_id,0,16).'...' ) : '—'; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <?php else : ?>
    <div class="dd-empty-state">
        <div class="dd-empty-state__icon">💳</div>
        <h3><?php _e( 'No payments yet', 'petslist' ); ?></h3>
        <p><?php _e( 'Your payment history will appear here once you subscribe.', 'petslist' ); ?></p>
        <a href="<?php echo esc_url( dd_pricing_url() ); ?>" class="dd-btn dd-btn--primary">
            <?php _e( 'View Plans', 'petslist' ); ?>
        </a>
    </div>
    <?php endif; ?>

</div>
