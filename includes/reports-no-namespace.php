<?php

/**
 * Report Widget, needs to be outside namespace because
 * function name is 'calculated' in core
 */
function pmpro_report_pmpro_sws_reports_widget() {
	esc_html_e( 'View reports for your most recent sales.', 'pmpro-sitewide-sale' );
	$sitewide_sales = get_posts(
		[
			'post_type' => 'pmpro_sitewide_sale',
			'post_status' => 'publish',
			'numberposts' => 5,
			'orderby' => 'ID',
			'order' => 'DESC',
		]
	);
	if ( ! empty ( $sitewide_sales ) ) {
		foreach ( $sitewide_sales as $sitewide_sale ) {
			echo '<p>';
			echo '<strong><a href="' . admin_url( 'admin.php?page=pmpro-reports&report=pmpro_sws_reports' ) . '">' . esc_html( get_the_title( $sitewide_sale->ID ) ) . '</a></strong>';
			echo ' (';
			echo date_i18n( get_option( 'date_format' ), ( new \DateTime( get_post_meta( $sitewide_sale->ID, 'pmpro_sws_start_date', true ) ) )->format( 'U' ) );
			echo ' - ';
			echo date_i18n( get_option( 'date_format' ), ( new \DateTime( get_post_meta( $sitewide_sale->ID, 'pmpro_sws_end_date', true ) ) )->format( 'U' ) );
			echo ')';
			echo '</p>';
		}
	}
}

/**
 * Report Page, needs to be outside namespace because
 * function name is 'calculated' in core
 */
function pmpro_report_pmpro_sws_reports_page() {
	global $wpdb;
	$options = PMPro_Sitewide_Sales\includes\classes\PMPro_SWS_Settings::get_options();
	$sitewide_sales = get_posts(
		[
			'post_type' => 'pmpro_sitewide_sale',
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby' => 'ID',
			'order' => 'DESC',
		]
	);
	if( ! empty( $_REQUEST['pmpro_sws_sitewide_sale_id'] ) ) {
		$sitewide_sale_id = intval( $_REQUEST['pmpro_sws_sitewide_sale_id'] );
	} else {
		$sitewide_sale_id = $options['active_sitewide_sale_id'];
	}

	$stats = PMPro_Sitewide_Sales\includes\classes\PMPro_SWS_Reports::get_stats_for_sale( $sitewide_sale_id );

	?>
	<form id="posts-filter" method="get" action="">
		<h1>
			<?php esc_html_e( 'Sitewide Sales Report', 'pmpro-sitewide-sales' );?>
		</h1>
		<ul class="subsubsub">
			<li>
				<?php esc_html_e( 'Show reports for ', 'pmpro-sitewide-sales' );?>
				<select name="pmpro_sws_sitewide_sale_id">
				<?php
					foreach ( $sitewide_sales as $sitewide_sale ) {
						echo '<option value="' . esc_attr( $sitewide_sale->ID ) . '" ' . selected( $sitewide_sale_id, $sitewide_sale->ID ) . '>' . esc_html( get_the_title( $sitewide_sale->ID ) ) . '</option>';
					}
				?>
				</select>

				<input type="hidden" name="page" value="pmpro-reports" />
				<input type="hidden" name="report" value="pmpro_sws_reports" />
				<input type="submit" class="button" value="<?php echo esc_attr_e( 'Generate Report', 'pmpro-sitewide-sales' );?>" />
			</li>
		</ul>
		<div class="clear"></div>
		<hr />
		<br />
		<h1 class="wp-heading-inline"><?php echo get_the_title( $sitewide_sale_id ); ?></h1>
		<a href="<?php edit_post_link( $sitewide_sale_id ); ?>" class="page-title-action"><?php esc_html_e( 'Edit', 'pmpro-sitewide-sale' ); ?></a>
		<p>
		<?php
			printf( wp_kses_post( 'From %s to %s using discount code %s on landing page <a target="_blank" href="%s">%s</a>.', 'pmpro-sitewide-sales' ),
					$stats['start_date'],
					$stats['end_date'],
					$stats['discount_code'],
					$stats['landing_page_url'],
					$stats['landing_page_title']
				);
		?>
		</p>
		<style>
		.pmpro_sws_reports-box {
			background: #FFF;
			border: 1px solid #CCC;
			margin: 2rem 0;
			padding: 2rem;
			text-align: center;
		}
		.pmpro_sws_reports-box h1.pmpro_sws_reports-box-title {
			color: #999;
			font-family: Georgia, Times, "Times New Roman", serif;
			margin: 0;
			padding: 0;
		}
		.pmpro_sws_reports-box hr {
			margin: 2rem 0;
		}
		.pmpro_sws_reports-data {
			display: grid;
			grid-gap: 1rem;
		}
		.pmpro_sws_reports-data-3col {
			grid-template-columns: 1fr 1fr 1fr;
		}
		.pmpro_sws_reports-data-4col {
			grid-template-columns: 1fr 1fr 1fr 1fr;
		}
		.pmpro_sws_reports-data-section {
		}
		.pmpro_sws_reports-data-section h1 {
			font-size: 30px;
			line-height: 40px;
			margin: 0;
		}
		@media screen and (max-width: 768px) {
			.pmpro_sws_reports-box {
				margin: 0 1rem;
				padding: 1rem;
			}
			.pmpro_sws_reports-box hr {
				margin: .5rem 0;
			}
			.pmpro_sws_reports-data {
				display: block;
			}
			.pmpro_sws_reports-data-section h1,
			.pmpro_sws_reports-data-section p {
				display: inline-block;
				font-size: 18px;
			}
			.pmpro_sws_reports-data-section br {
				display: none;
			}
			.pmpro_sws_reports-data-section h1:after {
				content: ": ";
			}
		}
		</style>
		<div class="pmpro_sws_reports-box">
			<h1 class="pmpro_sws_reports-box-title"><?php esc_html_e( 'Overall Sale Performance', 'pmpro-sitewide-sales' ); ?></h1>
			<hr />
			<div class="pmpro_sws_reports-data pmpro_sws_reports-data-3col">
				<div class="pmpro_sws_reports-data-section">
					<h1><?php echo esc_attr( $stats['new_rev_with_code'] ); ?></h1>
					<p><?php esc_html_e( 'Sales With Discount', 'pmpro-sitewide-sales' ); ?></p>
				</div>
				<div id="pmpro_sws_reports-data-section_banner" class="pmpro_sws_reports-data-section">
					<h1><?php echo esc_attr( $stats['banner_impressions'] ); ?></h1>
					<p><?php esc_html_e( 'Banner Impressions', 'pmpro-sitewide-sales' ); ?></p>
				</div>
				<div id="pmpro_sws_reports-data-section_sales" class="pmpro_sws_reports-data-section">
					<h1><?php echo esc_attr( $stats['landing_page_visits'] ); ?></h1>
					<p><?php esc_html_e( 'Landing Page Visits', 'pmpro-sitewide-sales' ); ?></p>
				</div>
			</div>
		</div>
		<div class="pmpro_sws_reports-box">
			<h1 class="pmpro_sws_reports-box-title"><?php esc_html_e( 'Sales Comparision Data', 'pmpro-sitewide-sales' ); ?></h1>
			<hr />
			<div class="pmpro_sws_reports-data pmpro_sws_reports-data-3col">
				<div class="pmpro_sws_reports-data-section">
					<h1><?php echo esc_attr( $stats['checkout_conversions_with_code'] ); ?></h1>
					<p><?php esc_html_e( 'Checkouts Using Discount', 'pmpro-sitewide-sales' ); ?></p>
				</div>
				<div id="pmpro_sws_reports-data-section_banner" class="pmpro_sws_reports-data-section">
					<h1><?php echo esc_attr( $stats['checkout_conversions_without_code'] ); ?></h1>
					<p><?php esc_html_e( 'Checkouts Without Discount', 'pmpro-sitewide-sales' ); ?></p>
				</div>
				<div id="pmpro_sws_reports-data-section_sales" class="pmpro_sws_reports-data-section">
					<h1><?php echo esc_attr( $stats['checkout_conversions_with_code'] ) + esc_attr( $stats['checkout_conversions_with_code'] ); ?></h1>
					<p><?php esc_html_e( 'Total Checkouts in Period', 'pmpro-sitewide-sales' ); ?></p>
				</div>
			</div>
			<hr />
			<div class="pmpro_sws_reports-data pmpro_sws_reports-data-4col">
				<?php
					$total_sales_in_period = $stats['new_rev_with_code'] + $stats['new_rev_without_code'] + $stats['old_rev'];
				?>
				<div class="pmpro_sws_reports-data-section">
					<h1><?php echo esc_attr( $stats['new_rev_with_code'] ); ?></h1>
					<p>
						<?php esc_html_e( 'Sales With Discount', 'pmpro-sitewide-sales' ); ?>
						<br />
						(<?php echo round ( PMPro_Sitewide_Sales\includes\classes\PMPro_SWS_Reports::divide_into_percent( $stats['new_rev_with_code'], $total_sales_in_period ), 2 ); ?>%)
					</p>
				</div>
				<div class="pmpro_sws_reports-data-section">
					<h1><?php echo esc_attr( $stats['new_rev_without_code'] ); ?></h1>
					<p>
						<?php esc_html_e( 'Sales Without Discount', 'pmpro-sitewide-sales' ); ?>
						<br />
						(<?php echo round ( PMPro_Sitewide_Sales\includes\classes\PMPro_SWS_Reports::divide_into_percent( $stats['new_rev_without_code'], $total_sales_in_period ), 2 ); ?>%)
					</p>
				</div>
				<div class="pmpro_sws_reports-data-section">
					<h1><?php echo esc_attr( $stats['old_rev'] ); ?></h1>
					<p>
						<?php esc_html_e( 'Other Sales (including Renewals)', 'pmpro-sitewide-sales' ); ?>
						<br />
						(<?php echo round ( PMPro_Sitewide_Sales\includes\classes\PMPro_SWS_Reports::divide_into_percent( $stats['old_rev'], $total_sales_in_period ), 2 ); ?>%)
					</p>
				</div>
				<div class="pmpro_sws_reports-data-section">
					<h1><?php echo esc_attr( $stats['new_rev_with_code'] ) + esc_attr( $stats['new_rev_without_code'] ) + esc_attr( $stats['old_rev'] ); ?></h1>
					<p><?php esc_html_e( 'Total Sales in Period', 'pmpro-sitewide-sales' ); ?></p>
				</div>
			</div>
		</div>
		<pre>
			<?php var_dump( $stats ); ?>
		</pre>
	</form>
	<?php
}
