<?php

define( 'ABSPATH', __DIR__ . '/' );
function add_filter() {}
function trailingslashit( $value ) { return rtrim( $value, '/\\' ) . '/'; }
function untrailingslashit( $value ) { return rtrim( $value, '/\\' ); }
function updater_expect( $condition, $message ) { if ( ! $condition ) { fwrite( STDERR, "FAIL: $message\n" ); exit( 1 ); } echo "PASS: $message\n"; }

require dirname( __DIR__ ) . '/inc/updater-source-selection.php';

$temp = sys_get_temp_dir() . '/global-blocks-updater-' . uniqid();
mkdir( $temp );
$unrelated = $temp . '/my-360-global-blocks-helper';
mkdir( $unrelated );
file_put_contents( $unrelated . '/helper.php', "<?php\n/* Plugin Name: Unrelated */\n" );
$original = trailingslashit( $unrelated );
$result = sb_global_blocks_rename_github_package( $original, $temp, null, array( 'type'=>'plugin' ) );
updater_expect( $original === $result && is_dir( $unrelated ), 'slug substring alone does not claim an unrelated upload' );
$result = sb_global_blocks_rename_github_package( $original, $temp, null, array( 'type'=>'plugin', 'plugin'=>'unrelated/helper.php' ) );
updater_expect( $original === $result && is_dir( $unrelated ), 'explicit unrelated plugin basename is ignored' );

$github_dir = $temp . '/360-global-blocks-main';
mkdir( $github_dir );
file_put_contents( $github_dir . '/360-global-blocks.php', "<?php\n/* Plugin Name: 360 Global Blocks */\n" );
$renamed = sb_global_blocks_rename_github_package( trailingslashit( $github_dir ), $temp, null, array( 'type'=>'plugin' ) );
$canonical = $temp . '/360-global-blocks';
updater_expect( trailingslashit( $canonical ) === $renamed && is_dir( $canonical ), 'proven Blocks GitHub package normalizes with a trailing slash' );

$collision = $temp . '/360-global-blocks-feature';
mkdir( $collision );
file_put_contents( $collision . '/360-global-blocks.php', "<?php\n/* Plugin Name: 360 Global Blocks */\n" );
$collision_source = trailingslashit( $collision );
$result = sb_global_blocks_rename_github_package( $collision_source, $temp, null, array( 'type'=>'plugin', 'plugin'=>'360-global-blocks/360-global-blocks.php' ) );
updater_expect( $collision_source === $result && is_dir( $collision ) && is_dir( $canonical ), 'existing canonical target is never overwritten' );

unlink( $unrelated . '/helper.php' );
rmdir( $unrelated );
unlink( $canonical . '/360-global-blocks.php' );
rmdir( $canonical );
unlink( $collision . '/360-global-blocks.php' );
rmdir( $collision );
rmdir( $temp );

echo "Global Blocks updater tests passed.\n";
