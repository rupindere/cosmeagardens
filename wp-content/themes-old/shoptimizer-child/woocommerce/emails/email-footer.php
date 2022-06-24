<?php
/**
 * Email Footer
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-footer.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 3.7.0
 */

defined( 'ABSPATH' ) || exit;
?>
															</div>
														</td>
													</tr>
												</table>
												<!-- End Content -->
											</td>
										</tr>
									</table>
									<!-- End Body -->
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center" valign="top">
						<!-- Footer -->
						<table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer">
							<tr>
								<td style="background: #fff; border-radius: 0; padding: 10px 15px; border: 1px solid #dedede; border-top: none;">
									<img src="<?php echo site_url() . '/wp-content/uploads/2021/08/banner.jpg'; ?>" style="width: 100%; display: block;" />
								</td>
							</tr>
							<tr>
								<td width="50%">
									<table width="100%" style=" padding: 15px 50px; background: #fff; border: 1px solid #dedede; border-top: none;">
										<tr>
											<td valign="top" align="center" style="padding-right: 15px; border-right: 1px solid #dedede; border-radius: 0;">
												<table width="100%">
													<tr>
														<td>
															<h2 style="margin-bottom: 5px;">KEEP IN TOUCH</h2>
															<h4 style="color: #a9a9a9; font-size: 14px; font-weight: normal; margin: 0;">Connect with us for great ideas.</h4>
														</td>
													</tr>
												</table>
												<table width="100%">
													<tr>
														<td>
															<a href="https://www.facebook.com/CosmeaGardens" target="_blank">
																<img src="https://www.cosmeagardens.com/wp-content/uploads/2017/05/facebook.png" style="margin-right: 0;" />
															</a>
														</td>
														<td>
															<a href="https://plus.google.com/+Cosmeagardens2" target="_blank">
																<img src="https://www.cosmeagardens.com/wp-content/uploads/2017/05/google.png" style="margin-right: 0;" />
															</a>
														</td>
														<td>
															<a href="https://twitter.com/CosmeaGardens" target="_blank">
																<img src="https://www.cosmeagardens.com/wp-content/uploads/2017/05/twitter.png" style="margin-right: 0;"  />
															</a>
														</td>
														<td>
															<a href="https://www.pinterest.com/cosmeagardens/" target="_blank">
																<img src="https://www.cosmeagardens.com/wp-content/uploads/2017/05/pinterest.png" style="margin-right: 0;" />
															</a>
														</td>
														<td>
															<a href="https://www.instagram.com/cosmeagardens/" target="_blank">
																<img src="https://www.cosmeagardens.com/wp-content/uploads/2019/06/pinterest-3.png" style="margin-right: 0;" />
															</a>
														</td>
													</tr>
												</table>
											</td>
											<td valign="top" align="center" width="50%" style="padding-left: 15px; border-radius: 0;">
												<table width="100%">
													<tr>
														<td valign="top" align="top">
															<h2 style="margin-bottom: 5px;">CONTACT WITH US</h2>
															<h4 style="color: #a9a9a9; font-size: 14px; font-weight: normal; margin-bottom: 10px; margin-top: 0;">If you have any quesitons or comments:</h4>
															<p style="margin-bottom: 3px; margin-top: 0;"><a href="tel:+35724638777">+ 357-24-638777</a></p>
															<p style="margin-bottom: 3px; margin-top: 0;"><a href="mailto:info@cosmeagardens.com">info@cosmeagardens.com</a></p>
															<p style="margin-bottom: 3px; margin-top: 0;"><a href="cosmeagardens.com">cosmeagardens.com</a></p>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td style="background: #ffffff; padding: 15px 20px; border: 1px solid #dedede; border-top: none; line-height: 25px;">Send Flowers to Cyprus from CosmeaGardens.com. We are a local florist and we offer bouquets and arrangements delivery all over cyprus including Larnaca, Nicosia, Limassol, Paphos, Ayia Napa and Paralimni. We have got you covered for any occasion. Whether you are sending a dozen of tulips to your mom for Mother's Day or an Arrangements to your coworker wishing congratulations for their achievements.<br/><br/><b>For more information click here</b> <a href="https://www.cosmeagardens.com" target="_blank">Cosmeagardens.com</a></td>
							</tr>
							<!--<tr>
								<td valign="top">
									<table border="0" cellpadding="10" cellspacing="0" width="100%">
										<tr>
											<td colspan="2" valign="middle" id="credit">
												<?php echo wp_kses_post( wpautop( wptexturize( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ) ) ); ?>
											</td>
										</tr>
									</table>
								</td>
							</tr>-->
						</table>
						<!-- End Footer -->
					</td>
				</tr>
			</table>
		</div>
	</body>
</html>