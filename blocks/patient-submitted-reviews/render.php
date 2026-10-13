<?php
/** Dynamic public projection only; private review records never enter block attributes. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function global360blocks_submitted_reviews_no_cache() {
 if ( ! defined( 'DONOTCACHEPAGE' ) ) { define( 'DONOTCACHEPAGE', true ); }
 if ( ! headers_sent() ) { nocache_headers(); }
 if ( function_exists( 'wpfc_exclude_current_page' ) ) { wpfc_exclude_current_page(); }
}
function global360blocks_render_patient_submitted_reviews( $attributes ) {
 global360blocks_submitted_reviews_no_cache();
 $preview = defined( 'REST_REQUEST' ) && REST_REQUEST && current_user_can( 'edit_posts' );
 $empty = $preview ? '<p class="patient-submitted-reviews-empty">No approved patient reviews match these filters. The slider will be hidden on the public page.</p>' : '';
 if ( ! function_exists( 'global360_platform' ) || ! method_exists( global360_platform(), 'patient_reviews' ) ) {
  return $preview ? '<p>Activate 360 Platform Core to preview patient-submitted reviews.</p>' : '';
 }
 $clinic = absint( $attributes['clinicId'] ?? 0 );
 $doctor = absint( $attributes['doctorId'] ?? 0 );
 $args = array( 'per_page' => max( 1, min( 50, (int) ( $attributes['reviewCount'] ?? 9 ) ) ) );
 if ( $clinic ) {
  $post = get_post( $clinic );
  if ( ! $post || 'clinic' !== $post->post_type || 'publish' !== $post->post_status || $post->post_password ) { return $empty; }
  $args['clinic_id'] = $clinic;
 }
 if ( $doctor ) {
  $post = get_post( $doctor );
  if ( ! $clinic || ! $post || 'doctor' !== $post->post_type || 'publish' !== $post->post_status || $post->post_password || ! in_array( $clinic, global360_platform()->relationships()->clinics_for_doctor( $doctor ), true ) ) { return $empty; }
  $args['doctor_id'] = $doctor;
 }
 $result = global360_platform()->patient_reviews()->query( $args );
 if ( ! $result['items'] ) { return $empty; }
 $cards = array_map( static function ( $review ) {
  return array( 'name' => $review['display_name'], 'review' => nl2br( esc_html( $review['review_text'] ) ), 'rating' => $review['rating'],
   'clinic' => $review['clinic_name'], 'clinic_url' => $review['clinic_url'],
   'doctor' => $review['doctor_id'] ? ( $review['doctor_name'] ?: __( 'Doctor no longer listed', 'global360blocks' ) ) : __( 'Clinic overall / No specific doctor', 'global360blocks' ),
   'doctor_url' => $review['doctor_url'] );
 }, $result['items'] );
 return global360blocks_review_slider( $cards, array_merge( $attributes, array( 'submitted' => true ) ) );
}

// Editor directory choices only: no private review query or extra public API.
add_action( 'rest_api_init', static function () {
 register_rest_route( 'global360blocks/v1', '/patient-review-options', array(
  'methods' => 'GET', 'permission_callback' => static function () { return current_user_can( 'edit_posts' ); },
  'callback' => static function ( $request ) {
   if ( ! class_exists( '\Global360\Platform\Reviews\ReviewForm' ) ) { return new WP_Error( 'missing_core', 'Activate 360 Platform Core.', array( 'status' => 503 ) ); }
   $project = static function ( $post ) { return array( 'value' => (int) $post->ID, 'label' => wp_specialchars_decode( wp_strip_all_tags( get_the_title( $post ) ), ENT_QUOTES ) ); };
   return new WP_REST_Response( array( 'clinics' => array_map( $project, \Global360\Platform\Reviews\ReviewForm::clinics() ), 'doctors' => array_map( $project, \Global360\Platform\Reviews\ReviewForm::doctors( absint( $request->get_param( 'clinic' ) ) ) ) ), 200, array( 'Cache-Control' => 'no-store' ) );
  },
 ) );
} );
function global360blocks_has_submitted_reviews( $content, $seen = array() ) {
 if ( has_block( 'global360blocks/patient-submitted-reviews', $content ) ) { return true; }
 foreach ( parse_blocks( $content ) as $block ) {
  if ( 'core/block' === $block['blockName'] && ! empty( $block['attrs']['ref'] ) ) {
   $id = (int) $block['attrs']['ref'];
   if ( ! in_array( $id, $seen, true ) && global360blocks_has_submitted_reviews( (string) get_post_field( 'post_content', $id ), array_merge( $seen, array( $id ) ) ) ) { return true; }
  }
  if ( ! empty( $block['innerBlocks'] ) && global360blocks_has_submitted_reviews( serialize_blocks( $block['innerBlocks'] ), $seen ) ) { return true; }
 }
 return false;
}
add_action( 'template_redirect', static function () {
 if ( is_singular() && global360blocks_has_submitted_reviews( (string) get_post_field( 'post_content', get_queried_object_id() ) ) ) { global360blocks_submitted_reviews_no_cache(); }
} );
add_action( 'global360_patient_review_moderated', static function () {
 global $wpdb;
 $posts = $wpdb->get_results( $wpdb->prepare( "SELECT ID,post_type FROM {$wpdb->posts} WHERE post_status='publish' AND post_content LIKE %s", '%' . $wpdb->esc_like( 'wp:global360blocks/patient-submitted-reviews' ) . '%' ) );
 foreach ( $posts as $post ) {
  if ( in_array( $post->post_type, array( 'wp_block', 'wp_template', 'wp_template_part' ), true ) ) { do_action( 'wpfc_clear_all_cache' ); return; }
  clean_post_cache( (int) $post->ID );
  do_action( 'wpfc_clear_post_cache_by_id', false, (int) $post->ID );
 }
} );
