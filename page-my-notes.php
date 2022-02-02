<?php 

if (!is_user_logged_in()) {
   wp_redirect( esc_url(site_url('/')));
   exit;
}

get_header(); 

while (have_posts()) {
    the_post();
    pageBanner();
     ?>

    <div class="container container--narrow page-section">

      <div class="create-note">
         <h2 class="headline headline--medium">Create New Note</h2>
         <input class="new-note-title" type="text" placeholder="title">
         <textarea class="new-note-body" placeholder="your note here"></textarea>
         <span class="submit-note">Create Note</span>
         <span class="note-limit-message">Note limit reached. Delete existing note to make room for a new one.</span>
      </div>

      <ul id="my-notes" class="min-list link-list">
         <?php
         $userNotes = new WP_Query(array(
            'post_type' => 'note',
            'post_per_page' => '-1',
            'author' => get_current_user_id()
         ));
         
         while( $userNotes->have_posts() ) { $userNotes->the_post(); ?>

            <li data-id="<?php the_ID(); ?>">
               <input readonly class="note-title-field" value="<?php echo str_replace('Private: ', '', esc_attr(get_the_title())); ?>">
                  <span class="edit-note"><i class="fa fa-pencil" aria-hidden="true"></i> Edit Note</span>
                  <span class="delete-note"><i class="fa fa-trash-o" aria-hidden="true"></i> Delete Note</span>
               <textarea readonly class="note-body-field"><?php echo esc_textarea(wp_strip_all_tags(get_the_content())); ?></textarea>
               <span class="update-note btn btn--blue btn--small"><i class="fa fa-arrow-right" aria-hidden="true"></i> Update Note</span>
            </li>

         <?php } ?>
      </ul>

    </div>

    
    <?php
}

get_footer() ?>
