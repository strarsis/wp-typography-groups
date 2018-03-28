<?php
/*
Plugin Name: WP Word Groups
Description: Plugin for grouping words (derived from WP Typography plugin)
Version:     1.0.0
*/

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) )
    require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/class-public-interface-mod.php';
require __DIR__ . '/class-typography-groups.php';

$in = new Public_Interface_Mod();
$wg = new Typography_Groups();

$in->run( $wg );
