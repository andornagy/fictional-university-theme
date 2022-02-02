<?php

add_action('rest_api_init', 'university_register_search');

function university_register_search() {
   register_rest_route( 'university/v1', 'search', array(
      'methods' => WP_REST_SERVER::READABLE,
      'callback' => 'university_search_results'
   ));
}

function university_search_results($data) {
   $mainQuery = new WP_Query(array(
      'post_type' => array( 'post', 'page', 'professor', 'program', 'event', 'campus'),
      's' => sanitize_text_field( $data['term'] ) 
   ));

   $results = array(
      'generalInfo' => array(),
      'professors' => array(),
      'programs' => array(),
      'events' => array(),
      'campuses' => array()
   );

   while ($mainQuery->have_posts()) {
      $mainQuery->the_post();
      if (get_post_type() == 'post' OR get_post_type() == 'page') {
         array_push($results['generalInfo'], array(
            'id' => get_the_id(),
            'title'        => get_the_title(),
            'permalink'    => get_the_permalink(),
            'postType'     => get_post_type(),
            'authorName'   => get_the_author(),
         ));
      }

      if (get_post_type() == 'professor') {
         array_push($results['professors'], array(
            'id' => get_the_id(),
            'title'     => get_the_title(),
            'permalink' => get_the_permalink(),
            'post_type' => get_post_type(),
            'image'     => get_the_post_thumbnail_url(0, 'professorLandscape')
         ));
      }

      if (get_post_type() == 'program') {

         $relastedCapuses = get_field('related_campus');

         if($relastedCapuses) {
            foreach($relastedCapuses as $campus) {
               array_push($results['campuses'], array(
                  'title' => get_the_title($campus),
                  'permalink' => get_the_permalink($campus),
               ));
            };
         };

         array_push($results['programs'], array(
            'id' => get_the_id(),
            'title' => get_the_title(),
            'permalink' => get_the_permalink(),
            'post_type' => get_post_type(),
         ));
      }

      if (get_post_type() == 'event') {
         $eventDate = new DateTime(get_field('event_date'));

         $description = null;
         if (has_excerpt()) { 
            $description = get_the_excerpt(); 
         } else { 
            $description = wp_trim_words( get_the_content(),18 ); 
         }

         array_push($results['events'], array(
            'id' => get_the_id(),
            'title' => get_the_title(),
            'permalink' => get_the_permalink(),
            'post_type' => get_post_type(),
            'month' => $eventDate->format('M'),
            'day' => $eventDate->format('d'),
            'excerpt' => $description
         ));
      } 

      if (get_post_type() == 'campus') {
         array_push($results['campuses'], array(
            'id' => get_the_id(),
            'title' => get_the_title(),
            'permalink' => get_the_permalink(),
            'post_type' => get_post_type(),
         ));
      }       
      
   };

   if ($results['programs']) {
      $programsMetaQuery = array('relation' => 'OR');

      foreach ($results['programs'] as $item) {
         array_push($programsMetaQuery, array(
               'key' => 'related_programs',
               'compare' => 'LIKE',
               'value' => '"' . $item['id'] . '"',
            ),
         );
      };

      $programRelationshipQuery = new WP_Query(array(
         'post_type' => array('professor', 'event'),
         'meta_query' => $programsMetaQuery
      ));

      while($programRelationshipQuery->have_posts()) {
         $programRelationshipQuery->the_post(); 

         if (get_post_type() == 'event') {
            $eventDate = new DateTime(get_field('event_date'));
   
            $description = null;
            if (has_excerpt()) { 
               $description = get_the_excerpt(); 
            } else { 
               $description = wp_trim_words( get_the_content(),18 ); 
            }
   
            array_push($results['events'], array(
               'id' => get_the_id(),
               'title' => get_the_title(),
               'permalink' => get_the_permalink(),
               'post_type' => get_post_type(),
               'month' => $eventDate->format('M'),
               'day' => $eventDate->format('d'),
               'excerpt' => $description
            ));
         } ;

         if (get_post_type() == 'professor') {
            array_push($results['professors'], array(
               'title'     => get_the_title(),
               'permalink' => get_the_permalink(),
               'post_type' => get_post_type(),
               'image'     => get_the_post_thumbnail_url(0, 'professorLandscape')
            ));
         };
      };

      $results['professors']  = array_values(array_unique($results['professors'], SORT_REGULAR));
      $results['events']      = array_values(array_unique($results['events'], SORT_REGULAR));
   }
   return $results;
}