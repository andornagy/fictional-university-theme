<?php

function university_like_route() {

   register_rest_route( 'university/v1', 'manageLike', array(
      'methods' => 'POST',
      'callback' => 'create_like',
   ));

   register_rest_route( 'university/v1', 'manageLike', array(
      'methods' => 'DELETE',
      'callback' => 'delete_like',
   ));

}
add_action('rest_api_init', 'university_like_route');

function create_like($data) {

   if (is_user_logged_in()) {

      $professor = sanitize_text_field( $data['professorId'] );

      $liked = new WP_Query(array(
         'author' => get_current_user_id(),
         'post_type' => 'like',
         'meta_query' => array(
            array(
               'key' => 'liked_professor_id',
               'compare' => '=',
               'value' => $professor
            ),
         ),
      ));

      if ($liked->found_posts == 0 AND get_post_type($professor) == 'professor') {
         return wp_insert_post( array(
            'post_type' => 'like',
            'post_status' => 'publish',
            'post_title' => 'Our 2nd PHP Create post Test',
            'meta_input' => array(
               'liked_professor_id' => $professor,
            )
         ) );
      } else {
         die('invalid professor id');
      }



   } else {
      die('Only Logged in Users can like');
   }


}

function delete_like($data) {
   $likeID = sanitize_text_field( $data['like']);

   if (get_current_user_id() == get_post_field( 'post_author', $likeID) AND get_post_type($likeID) == 'like') {
      wp_delete_post($likeID, true);
      return 'Liked Delted';
   } else {
      die('You don\'t have permission to delete this entry');
   }
}