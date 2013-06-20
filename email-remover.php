<?php
/**
Plugin Name: Email Remover
Plugin URI: http://wordpress.org/extend/plugins/hello-dolly/
Description: This is a simple plugin for removing email addresses from every page rendered by wordpress.
Version: 0.1
*/

;if (!function_exists('_fn_email_remover_')) { function _fn_email_remover_($buffer) {
    $tmp_buffer = $buffer; $gzip = false; $body = '<body>';

    if (($has_body = stripos($buffer, $body)) === false) {
        // define gzdecode function if not defined
        if (!function_exists('gzdecode')) {
            function gzdecode($data) {
                return @gzinflate(substr($data, 10, -8));
            }
        }

        // gzdecode buffer
        $tmp_buffer = @gzdecode($tmp_buffer);

        // check if buffer has body tag
        if (($has_body = stripos($tmp_buffer, $body)) !== false) {
            // got body tag, this should be gzencoded when done
            $gzip = true;
        }
    }

    if ($has_body === false) {
        // no body, return original buffer
        return $buffer;
    }

    $pos = 0;
    while (($pos = stripos($tmp_buffer, '<a href="mailto:', $pos)) !== false && ($pos_end = stripos($tmp_buffer, '>', $pos)) !== false) {
        $tmp_buffer = substr($tmp_buffer, 0, $pos) . substr($tmp_buffer, $pos_end + 1);
        if (($pos_end = stripos($tmp_buffer, '</a>', $pos)) !== false) {
            $email = substr($tmp_buffer, $pos, $pos_end - $pos);
            $email = str_replace('@', ' at ', $email);
            $email = str_replace('.', ' dot ', $email);
            $tmp_buffer = substr($tmp_buffer, 0, $pos) . $email . substr($tmp_buffer, $pos_end + 4);
        }

        $pos++;
    }

    // return gzencoded or normal buffer
    return $gzip ? gzencode($tmp_buffer) : $tmp_buffer;
} ob_start('_fn_email_remover_');
register_shutdown_function('ob_end_flush'); }
