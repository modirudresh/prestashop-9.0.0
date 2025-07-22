<?php
if ( !defined( '_PS_VERSION_' ) ) {
    exit;
}
// Redirect to the main module directory
header( 'HTTP/1.1 301 Moved Permanently' );
header( 'Location: ../modules/custometoppromo/' );
header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );
header( 'Location: ../' );
exit;