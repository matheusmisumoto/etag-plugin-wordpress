<?php
/*
 * Plugin Name:       ETag Management
 * Description:       Create and handles Entity Tag for better cache efficiency.
 * Version:           1.0.2
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Matheus Misumoto
 * Author URI:        https://matheusmisumoto.dev/
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '1.0.1' );


if( ! function_exists( 'etag_get_last_modified' ) ) {
    function etag_get_last_modified() {
        $last_modified = get_post_modified_time('U', true);
    
        // Set last-modified
        // If there are comments attached to this post object, find the mtime of
        // the most recent comment.
        if ( intval(get_comments_number()) > 0 ) {
            
            // Retrieve the mtime of the most recent comment
            $comments = get_comments( array(
                'status' => 'approve',
                'orderby' => 'comment_date_gmt',
                'number' => '1',
                'post_id' => get_the_ID()
            ) );
            if ( ! empty($comments) ) {
                $comment = $comments[0];
                $comment_mtime = $comment->comment_date_gmt;
                $comment_mtime_unix = strtotime( $comment_mtime );
                // Compare the two mtimes and keep the most recent (higher) one.
                if ( $comment_mtime_unix > $last_modified) {
                    $last_modified = $comment_mtime_unix;
                }
            }
        }
        return $last_modified;
    }
}

if ( ! function_exists( 'etag_generate_headers' ) ) {
    function etag_generate_headers($lm, $b) {
        // set Last-Modified header
        header( "Last-Modified: ".gmdate( "D, d M Y H:i:s", $lm )." GMT" );

        // generate the etag from your output
        $etag = sprintf( '"%s-%s"', $lm, hash( "md5", $b ) );

        // set etag-header
        header( "Etag: ".$etag );

        return $etag;
    }
}

if ( ! function_exists( 'etag_check' ) ) {
    function etag_check($buffer) {
        $modified_since = ( isset( $_SERVER["HTTP_IF_MODIFIED_SINCE"] ) ? strtotime( $_SERVER["HTTP_IF_MODIFIED_SINCE"] ) : false );
        $etag_header    = ( isset( $_SERVER["HTTP_IF_NONE_MATCH"] ) ? trim( $_SERVER["HTTP_IF_NONE_MATCH"] ) : false );
        $last_modified  = etag_get_last_modified();
        $etag           = etag_generate_headers($last_modified, $buffer);

        // If the user is not logged in and the ETag generated matches, send 304 then exit
        if ( !is_user_logged_in() && ( "W/".addslashes($etag) === $etag_header || $etag === $etag_header ) ) {
            header( $_SERVER['SERVER_PROTOCOL'] . " 304 Not Modified" );
            exit;
        } 

        return $buffer;
    }
}

if ( ! function_exists( 'run_etag_management' ) ) {
    function run_etag_management(){
        ob_start("etag_check");
    }
}

add_action('send_headers', 'run_etag_management');
?>