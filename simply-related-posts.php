<?php
     /*
     Plugin Name: Simply Related Posts
     Plugin URI: http://www.danielauener.com/simply-related-posts/
     Description: A widget that simply gives you related posts by taxonomy.
     Version: 1.3
     Author: @danielauener
     Author URI: http://www.danielauener.com
     */
     class SimplyRelatedPosts extends WP_Widget {


          function SimplyRelatedPosts() {

               $widget_ops = array(
                    'classname' => 'SimplyRelatedPosts',
                    'description' => __( 'Shows up when is_single() is true and contains
                                      posts with one or more matching terms.' , 'simply-related-posts')
               );
               $this->WP_Widget( 'SimplyRelatedPosts', __( 'Simply Related Posts' , 'simply-related-posts'), $widget_ops );

               add_action( 'init', array( $this , 'load_text_domain' ) );
          }


		  function load_text_domain() {
			 load_plugin_textdomain( 'simply-related-posts', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		  }

          function form( $instance ) {

               $instance = wp_parse_args( (array) $instance, array( 'title' => __( 'Related Posts' , 'simply-related-posts'), 'exclude' => '', 'taxonomy' => 'post_tag', 'post_type' => 'post', 'related_count' => '5' ) );
               $title = $instance['title'];
               $post_type = $instance['post_type'];
               $taxonomy = $instance['taxonomy'];
               $exclude = $instance['exclude'];
               $related_count = $instance['related_count']; ?>
               <p>
                    <label for="<?php echo $this->get_field_id( 'title' ); ?>">
                         <?php _e( 'Title' , 'simply-related-posts'); ?>: <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo attribute_escape( $title ); ?>" />
                    </label>
               </p>
               <p>
                    <label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>">
                         <?php _e( 'Related by taxonomy' , 'simply-related-posts'); ?>: <select class="widefat" id="<?php echo $this->get_field_id( 'taxonomy' ); ?>" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>"><?php
                              $taxonomies = get_taxonomies( array( 'show_ui' => true ), 'objects' );
                              foreach ( $taxonomies as $slug => $tax ): ?>
                                   <option value="<?php echo $slug; ?>" <?php echo ( $slug == $taxonomy ) ? 'selected="selected"' : ''; ?>><?php echo $tax->labels->name; ?></option><?php
                              endforeach; ?>
                         </select>
                    </label>
               </p>
               <p>
                    <label for="<?php echo $this->get_field_id( 'post_type' ); ?>">
                         <?php _e( 'Related post-type' , 'simply-related-posts'); ?>: <select class="widefat" id="<?php echo $this->get_field_id( 'post_type' ); ?>" name="<?php echo $this->get_field_name( 'post_type' ); ?>"><?php
                              $post_types = get_post_types( array( 'show_ui' => true, 'public' => true ), 'objects', 'and' );
                              foreach ( $post_types as $slug => $pt ): ?>
                                   <option value="<?php echo $slug; ?>" <?php echo ( $slug == $post_type ) ? 'selected="selected"' : ''; ?>><?php echo $pt->labels->name; ?></option><?php
                              endforeach; ?>
                         </select>
                    </label>
               </p>
               <p>
                    <label for="<?php echo $this->get_field_id( 'related_count' ); ?>">
                         <?php _e( 'How many posts to show' , 'simply-related-posts'); ?>: <input class="widefat" id="<?php echo $this->get_field_id( 'related_count' ); ?>" name="<?php echo $this->get_field_name( 'related_count' ); ?>" type="text" value="<?php echo attribute_escape( $related_count ); ?>" />
                    </label>
               </p>
               <p>
                    <label for="<?php echo $this->get_field_id( 'exclude' ); ?>">
                         <?php _e( 'Term ids to exclude (e.g 5,4,2)' , 'simply-related-posts'); ?>: <input class="widefat" id="<?php echo $this->get_field_id( 'exclude' ); ?>" name="<?php echo $this->get_field_name( 'exclude' ); ?>" type="text" value="<?php echo attribute_escape( $exclude ); ?>" />
                    </label>
               </p><?php

          }


          function update( $new_instance, $old_instance ) {

               $instance = $old_instance;
               $instance['title'] = $new_instance['title'];
               $instance['post_type'] = $new_instance['post_type'];
               $instance['related_count'] = $new_instance['related_count'];
               $instance['exclude'] = $new_instance['exclude'];
               $instance['taxonomy'] = $new_instance['taxonomy'];
               return $instance;

          }


          function widget( $args, $instance ) {

               if ( !is_singular() )
                    return;

               extract( $args, EXTR_SKIP );

               // Thanks to David Gil (github.com/dgilperez) for the custom-post-type fix
               $post_type = $instance['post_type'];
               $taxonomy = ( $instance['taxonomy'] == "" ) ? 'post_tag' : $instance['taxonomy'];
               $terms = wp_get_post_terms( get_the_ID(), $taxonomy, array( 'fields' => 'ids' ));

               $exclude = ( empty( $instance['exclude'] ) ) ? array() : explode( ',', $instance['exclude'] );
               if ( count( ( $terms = array_diff( $terms, $exclude ) ) ) == 0 )
                    return;

               $related_posts = get_posts( array(
                    'tax_query' => array(
                         array(
                              'taxonomy' => $taxonomy,
                              'field' => 'id',
                              'terms' => $terms,
                              'operator' => 'IN'
                         )
                    ),
                    'post_type' => $post_type,
                    'posts_per_page' => $instance['related_count'],
                    'exclude' => get_the_ID()
               ) );

               if ( count( $related_posts ) == 0 )
                    return;

               echo $before_widget;

               $title = empty( $instance['title'] ) ? ' ' : apply_filters( 'widget_title', $instance['title'] );
               if ( !empty( $title ) )
                    echo $before_title . $title . $after_title; ?>
               <ul><?php
                    foreach ( $related_posts as $related_post ) : ?>
                         <li>
                              <a class="related-post" href="<?php echo get_permalink( $related_post->ID ); ?>">
                                   <?php echo apply_filters( 'simply_related_posts_title', $related_post->post_title, $related_post, $instance ); ?>
                              </a>
                         </li><?php
                    endforeach; ?>
               </ul><?php

               echo $after_widget;

          }

     }

     add_action( 'widgets_init', create_function( '', 'return register_widget( "SimplyRelatedPosts" );' ) );
