<?php
/**
 * html template for emails sent by the plugin
 *
 * This template can be overridden by copying it to yourtheme/userspace/templates/usp-mail.php
 * or from a special plugin directory wp-content/userspace/templates/usp-mail.php
 *
 * HOWEVER, on occasion UserSpace Chat will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.user-space.com/document/template-structure/
 *
 * @version 1.0.0
 *
 * @param string $mail_content id of user
 */
defined( 'ABSPATH' ) || exit;

$site_color = usp_get_option_customizer( 'usp_primary_color', '#0369a1' ); ?>
<table style="border-collapse:collapse !important;border-spacing:0 !important;margin-bottom:0 !important;margin-left:auto !important;margin-right:auto !important;margin-top:0 !important;table-layout:fixed !important"
       cellspacing="0" cellpadding="0" bgcolor="#F7F3F0" height="100%" border="0" width="100%">
    <tbody>
    <tr>
        <td valign="top">
            <center style="text-align:left;width:100%">
                <div style="display:none;font:1px / 1px sans-serif;overflow:hidden"></div>
                <div style="margin:auto;max-width:600px">
                    <table style="border-collapse:collapse !important;border-spacing:0 !important;border-top-color:<?php echo $site_color; ?>;border-top-style:solid;border-top-width:7px;margin-bottom:0 !important;margin-left:auto !important;margin-right:auto !important;margin-top:0 !important;max-width:600px;table-layout:fixed !important"
                           cellspacing="0" cellpadding="0" bgcolor="#F7F3F0" align="center" border="0" width="100%">
                        <tbody>
                        <tr>
                            <td style="color:#000000;font:bold 30px sans-serif;padding:18px 0 18px 0;text-align:center;background-color:#e2e2f4;"><?php echo get_option( 'blogname' ); ?></td>
                        </tr>
                        </tbody>
                    </table>
                    <table style="border-collapse:collapse !important;border-radius:5px;border-spacing:0 !important;margin-bottom:0 !important;margin-left:auto !important;margin-right:auto !important;margin-top:0 !important;max-width:600px;table-layout:fixed !important"
                           cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" align="center" border="0" width="100%">
                        <tbody>
                        <tr>
                            <td>
                                <table style="border-collapse:collapse !important;border-spacing:0 !important;margin-bottom:0 !important;margin-left:auto !important;margin-right:auto !important;margin-top:0 !important;table-layout:fixed !important"
                                       cellspacing="0" cellpadding="0" border="0" width="100%">
                                    <tbody>
                                    <tr>
                                        <td style="color:#555555;font:15px / 24px sans-serif;padding:24px">
											<?php echo $mail_content; ?>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <br>
                    <table style="border-collapse:collapse !important;border-radius:5px;border-spacing:0 !important;margin-bottom:0 !important;margin-left:auto !important;margin-right:auto !important;margin-top:0 !important;max-width:600px;table-layout:fixed !important"
                           cellspacing="0" cellpadding="0" bgcolor="#F7F3F0" align="left" border="0" width="100%">
                        <tbody>
                        <tr>
                            <td style="color:#525252;font:13px / 19px sans-serif;padding:0 0 24px 0;text-align:left;width:100%">
                                <span>© <?php echo date( 'Y' ); ?> <?php echo get_bloginfo( 'name' ); ?></span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </center>
        </td>
    </tr>
    </tbody>
</table>
