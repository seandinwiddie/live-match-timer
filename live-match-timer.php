<?php
/**
 * @package Live_Match_Timer
 * @version 1.0
 */
/*
Plugin Name: Live Match Timer
Description: Live Match Timer is a plugin for displaying the match time of an event w/ shortcode[live_match_timer].
Author: Sean Dinwiddie
Version: 1.0
Author URI: https://SeanDinwiddie.github.io/
Text Domain: live-match-timer
*/

function ini_time( $option_name )
{
  function query( $o_n )
  {
    return mysqli_query
    (
      mysqli_connect( "localhost", "radioten_wp", "y6yPY3pf6sKy", "radioten_wp" ),
      'select option_value from wp_options where option_name = "' . $o_n . '"'
    );
  }
  function option_value( $o_n )
  {
    $value =  mysqli_fetch_assoc( query( $o_n ) )[ 'option_value' ];
    return $value == ''? 0: $value;
  }
  mysqli_free_result( query( $option_name ) );
  function elapsed( $elapsed )
  {
    /*$r = "" + $elaped;
    while( strlen( $r ) < 4 ) $r = "0" + $r;
    return str_replace( /\b(\d{1,2})(\d{2})/g, "$1:$2", $r );*/
    return $elapsed;
  }
  $elaped_time = date( 'Hi' ) < option_value( $option_name )?
    ( date( 'Hi' ) + 2400 ) - option_value( $option_name ):
    date( 'Hi' ) - option_value( $option_name );
  $elaped_time = option_value( $option_name ) == 0? 0: $elaped_time;
  echo elapsed( $elaped_time );
}
if( $_POST[ 'time' ] == 'broadcast' ) ini_time( "broadcast-time" );
if( $_POST[ 'time' ] == 'match' ) ini_time( "match-time" );

if ( ! defined( 'ABSPATH' ) ) exit;

function live_match_timer_page()
{
  global $wpdb;
  $broadcast_time = $wpdb ->
    get_var( 'select option_value from wp_options where option_name = "broadcast-time"' );
  if( $broadcast_time == '' )
  {
    $broadcast_start = "";
    $broadcast_stop = "disabled";
    $broadcast_elaped = 0;
  }
  if( $broadcast_time == '' && $_GET[ 'broadcast-start' ] == 'Start' )
  {
    $wpdb -> insert
    (
      'wp_options',
      array(
        'option_name' => 'broadcast-time',
        'option_value' => date( 'Hi' )
      ),
      array(
        '%s',
        '%d'
      )
    );
    $broadcast_start = "disabled";
    $broadcast_stop = "";
    $broadcast_elaped = 0;
  }
  if( $broadcast_time != '' )
  {
    $broadcast_start = "disabled";
    $broadcast_stop = "";
    $broadcast_elaped = date( 'Hi' ) < $broadcast_time?
      ( date( 'Hi' ) + 2400 ) - $broadcast_time:
      date( 'Hi' ) - $broadcast_time;
  }
  if( $_GET[ 'broadcast-stop' ] == 'Stop' )
  {
    $wpdb -> delete( 'wp_options', array( 'option_name' => 'broadcast-time' ) );
    $broadcast_start = "";
    $broadcast_stop = "disabled";
    $broadcast_elaped = 0;
  }
  $match_time = $wpdb ->
    get_var( 'select option_value from wp_options where option_name = "match-time"' );
  if( $match_time == '' )
  {
    $match_start = "";
    $match_stop = "disabled";
    $match_elaped = 0;
  }
  if( $match_time == '' && $_GET[ 'match-start' ] == 'Start' )
  {
    $wpdb -> insert
    (
      'wp_options',
      array(
        'option_name' => 'match-time',
        'option_value' => date( 'Hi' )
      ),
      array(
        '%s',
        '%d'
      )
    );
    $match_start = "disabled";
    $match_stop = "";
    $match_elaped = 0;
  }
  if( $match_time != '' )
  {
    $match_start = "disabled";
    $match_stop = "";
    $match_elaped = date( 'Hi' ) < $match_time?
      ( date( 'Hi' ) + 2400 ) - $match_time:
      date( 'Hi' ) - $match_time;
  }
  if( $_GET[ 'match-stop' ] == 'Stop' )
  {
    $wpdb -> delete( 'wp_options', array( 'option_name' => 'match-time' ) );
    $match_start = "";
    $match_stop = "disabled";
    $match_elaped = 0;
  }
  ?>
  <style>
    .postbox-footer
    {
      position: relative;
      top: 23px;
      margin: 0 -12px;
      border-top: 1px solid #ddd;
      padding: 10px;
      background: #f5f5f5;
    }
  </style>
  <div class="wrap">
    <h1>Live Match Timer</h1>
    <hr class="wp-header-end">
    <div class="metabox-holder">
      <div class="postbox-container" style="padding: 0 8px;">
        <div class="postbox ">
          <h2 class="hndle ui-sortable-handle">
            <span>This Broadcast Time</span>
          </h2>
          <div class="inside">
            <form method="GET">
              <fieldset>
                <input name="page" value="live-match-timer-page" hidden>
                <input type="submit" class="button button-primary" name="broadcast-start" value="Start" <?php echo $broadcast_start; ?>>
                <input type="submit" class="button" name="broadcast-stop" value="Stop" <?php echo $broadcast_stop; ?>>
              </fieldset>
            </form>
            <div class="postbox-footer">
              Elapsed time: <span id="broadcast-time"></span>
            </div>
          </div>
        </div>
      </div>
      <div class="postbox-container">
        <div class="postbox ">
          <h2 class="hndle ui-sortable-handle">
            <span>This Match Time</span>
          </h2>
          <div class="inside">
            <form method="GET">
              <fieldset>
                <input name="page" value="live-match-timer-page" hidden>
                <input type="submit" class="button button-primary" name="match-start" value="Start" <?php echo $match_start; ?>>
                <input type="submit" class="button" name="match-stop" value="Stop" <?php echo $match_stop; ?>>
              </fieldset>
            </form>
            <div class="postbox-footer">
              Elapsed time: <span id="match-time"></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    jQuery( "document" ).ready( () =>
    {
      const elaped = ( elaped ) =>
      {
        var r = "" + elaped
        while( r.length < 4 ) r = "0" + r
        return r.replace(/\b(\d{1,2})(\d{2})/g, "$1:$2")
      }
      jQuery( "#broadcast-time" ).html( elaped( <?php echo $broadcast_elaped; ?> ) )
      jQuery( "#match-time" ).html( elaped( <?php echo $match_elaped; ?> ) )
      const time = ( χρόνος ) =>
      {
        setTimeout( () =>
        {
          jQuery( "#" + χρόνος + "-time" )
            .load
            (
              "../wp-content/plugins/live-match-timer.php",
              { time: χρόνος },
              ( r ) => jQuery( "#" + χρόνος + "-time" ).html( elaped( r ) )
            )
          time( χρόνος )
        }, 60000 )
      }
      time( "broadcast" )
      time( "match" )
    } )
  </script>
  <?php
}
function admin_menu_action()
{
  add_menu_page
  (
    __( 'Live Match Timer', 'textdomain' ),
    'Live Match Timer',
    'manage_options',
    'live-match-timer-page',
    'live_match_timer_page',
    'dashicons-clock',
    3
  );
}
add_action( 'admin_menu', 'admin_menu_action' );
function live_match_timer_shortcode()
{
  global $wpdb;
  $broadcast_time = $wpdb ->
    get_var( 'select option_value from wp_options where option_name = "broadcast-time"' );
  $broadcast_elaped = $broadcast_time > 0?
    (
      date( 'Hi' )< $broadcast_time?
        ( date( 'Hi' ) + 2400 ) - $broadcast_time:
        date( 'Hi' ) - $broadcast_time
    ):
    0;
  $match_time = $wpdb ->
    get_var( 'select option_value from wp_options where option_name = "match-time"' );
  $match_elaped = $match_time > 0?
    (
      date( 'Hi' )< $match_time?
        ( date( 'Hi' ) + 2400 ) - $match_time:
        date( 'Hi' ) - $match_time
    ):
    0;
  return '
    <style>
      #live-match-timer
      {
        text-align: right;
      }
      #live-match-timer > div
      {
        display: inline-block;
      }
      #live-match-timer .edit-link
      {
        float:none !important;
        border: none;
        text-align: left;
      }
    </style>
    <div id="live-match-timer">
      <div>
        <span class="edit-link"><a class="post-edit-link">This Broadcast Time <span id="broadcast-time"></span></a></span>
        <span class="edit-link"><a class="post-edit-link">This Match Time <span id="match-time"></span></a></span>
      </div>
    </div>
    <script>
      jQuery( "document" ).ready( () =>
      {
        const elaped = ( elaped ) =>
        {
          var r = "" + elaped
          while( r.length < 4 ) r = "0" + r
          return r.replace(/\b(\d{1,2})(\d{2})/g, "$1:$2")
        }
        jQuery( "#broadcast-time" ).html( elaped( ' . $broadcast_elaped . ' ) )
        jQuery( "#match-time" ).html( elaped( ' . $match_elaped . ' ) )
        const time = ( χρόνος ) =>
        {
          setTimeout( () =>
          {
            jQuery( "#" + χρόνος + "-time" )
              .load
              (
                "../wp-content/plugins/live-match-timer.php",
                { time: χρόνος },
                ( r ) => jQuery( "#" + χρόνος + "-time" ).html( elaped( r ) )
              )
            time( χρόνος )
          }, 60000 )
        }
        time( "broadcast" )
        time( "match" )
      } )
    </script>
  ';
}
add_shortcode( 'live_match_timer', 'live_match_timer_shortcode' );

?>
