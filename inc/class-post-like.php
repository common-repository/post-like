<?php
/**
 * Class Post_Like_Display
 */
class Post_Like {

	/**
	 * @var $pl_svg_icons class object
	 */
	public $pl_svg_icons;

	/**
	 * @var $pl_options plugin options
	 */
	public $pl_options;

    /**
     * Post_Like_Display constructor.
     */
    public function __construct() {
	    $this->pl_svg_icons = new PL_SVG_ICONS;

	    // get options
	    $pl_basic_options = get_option('pl_basic');
	    $pl_style_options = get_option('pl_style');
		// store options in single array
	    if ( !empty( $pl_basic_options ) && !empty( $pl_style_options )) {
		    $this->pl_options = array_merge( $pl_basic_options, $pl_style_options );
	    }

	    // check if user is logged in then apply filters & actions hooks
	    if ( is_user_logged_in() ) {
		    add_filter( 'the_content', array( $this, 'pl_display_post_like_link' ), 100 );
		    add_action( 'wp_enqueue_scripts', array( $this, 'pl_front_end_scripts' ) );
	    }

        add_action( 'wp_ajax_post_like', array( $this, 'pl_process_post_like' ) );
        add_action( 'wp_ajax_post_unlike', array( $this, 'pl_process_post_unlike' ) );

	    add_filter( 'pl_display_post_like_link_on', array( $this, 'pl_add_post_like_on_cpts') );
    }

	/**
	 * run on plugin activation
	 * set default options if for plugin
	 */
	static function pl_install() {
		// debug plugin using logs.
		//file_put_contents( PL_BASE_DIR . '/pl_log.txt', ob_get_contents() );
		
		/**
		 * check if there are any previous options stored
		 * update/add options if no options stored
		 */
		if ( empty( get_option('pl_basic') ) ) {
		    $default_basic = array(
			    'pl_like_text'  => 'Love',
			    'pl_liked_text' => 'Loved',
			    'pl_like_icon'  => 'heart'
		    );
		    update_option( 'pl_basic', $default_basic );
		}
		/**
		 * check if there are any previous options stored
		 * update/add options if no options stored
		 */
		if ( empty( get_option('pl_style') ) ) {
			$default_style = array(
				'pl_font_size' => '16',
				'pl_txt_color' => '#1e73be',
				'pl_alignment' => 'left'
			);
			update_option( 'pl_style', $default_style );
		}

    }

	/**
	 * check post_id and verify nonce before process post like
	 */
    public function pl_process_post_like() {

        if ( isset( $_POST['post_id'] ) && wp_verify_nonce( $_POST['post_like_nonce'], 'post-like-nonce' ) ) {
            if ( $this->pl_mark_post_as_liked( $_POST['post_id'], $_POST['user_id'] ) ) {
                echo 'liked';
            } else {
                echo 'failed';
            }
        }
        die();
    }

	/**
	 * check post_id and verify nonce before process post unlike
	 */
    public function pl_process_post_unlike() {
        if ( isset( $_POST['post_id'] ) && wp_verify_nonce( $_POST['post_unlike_nonce'], 'post-unlike-nonce' ) ) {
            if ( $this->pl_mark_post_as_liked( $_POST['post_id'], $_POST['user_id'], true ) ) {
                echo 'unliked';
            } else {
                echo 'failed';
            }
        }
        die();
    }

	/**
	 * Mark the post as liked/unliked
	 * update the post meta for like counts
	 *
	 * @param $post_id
	 * @param $user_id
	 * @param bool $unlike
	 *
	 * @return bool
	 */
    public function pl_mark_post_as_liked( $post_id, $user_id, $unlike = false ) {

        // retrieve the love count for $post_id
        $like_count = get_post_meta( $post_id, '_pl_like_count', true );
        if ( $unlike == false ) {
            if ( $like_count ) {
                $like_count = $like_count + 1;
            } else {
                $like_count = 1;
            }
            if ( update_post_meta( $post_id, '_pl_like_count', $like_count ) ) {
                // store this post as liked for $user_id
                $this->pl_store_liked_post_id_for_user( $user_id, $post_id );
                return true;
            }
        } else {
            if ( $like_count ) {
                $like_count = $like_count - 1;
            } else {
                $like_count = 0;
            }
            if ( update_post_meta( $post_id, '_pl_like_count', $like_count ) ) {
                // store this post as liked for $user_id
                $this->pl_store_liked_post_id_for_user( $user_id, $post_id, true );
                return true;
            }
        }

        return false;
    }

	/**
	 * Add or Remove liked post IDs in user meta
	 *
	 * @param $user_id
	 * @param $post_id
	 * @param bool $delete
	 */
    public function pl_store_liked_post_id_for_user( $user_id, $post_id, $delete = false ) {
        $liked_post_ids = get_user_option( 'pl_user_post_likes', $user_id );

        if ( $delete == false ) {
            if ( is_array( $liked_post_ids ) ) {
                $liked_post_ids[] = $post_id;
            } else {
                $liked_post_ids = array( $post_id );
            }
        } else {
            if ( is_array( $liked_post_ids ) ) {
                if ( ( $key = array_search( $post_id, $liked_post_ids ) ) !== false ) {
                    unset( $liked_post_ids[$key] );
                }
            }
        }

        update_user_option( $user_id, 'pl_user_post_likes', $liked_post_ids );
    }

	/**
	 * load scripts & styles on frontend
	 */
    public function pl_front_end_scripts() {
        // load script if user logged in.
        if ( is_user_logged_in() ) {
            wp_enqueue_style( 'pl-styles', PL_BASE_URI . 'assets/css/pl-styles.css', '', '1.0.3', 'all' );
            wp_enqueue_script( 'post-like-js', PL_BASE_URI . 'assets/js/post-like.js', array( 'jquery' ), false, true );
            wp_localize_script( 'post-like-js', 'pl_vars', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce_like' => wp_create_nonce( 'post-like-nonce' ),
                'nonce_unlike' => wp_create_nonce( 'post-unlike-nonce' ),
                'already_liked_msg' => __( 'You have already like this post', 'post_like' ),
                'already_unliked_msg' => __( 'You have already unlike this post', 'post_like' ),
                'error_mesg' => __( 'Sorry, there was a problem processing your request.', 'post_like' )
            ) );
        }
    }

    /**
     * create post like link to display in posts/pages.
     *
     * @param string $like_text
     * @param string $liked_text
     *
     * @return mixed
     */
    public function pl_like_link( $like_text = null, $liked_text = null, $like_icon = null ) {
        global $user_ID, $post;

	    $like_link = '';
	    
        /**
         * display only when user is logged in
         * and is on single post or page
         */
        if ( is_user_logged_in() ) :

            ob_start();
            $post_like_count = $this->pl_get_like_count( $post->ID );

            // conditional classes
            $classes = 'post--like-wrap';
            $styles = '';

            // add post--liked class if user has already liked this post
	        if ( $this->pl_user_has_liked_post( $user_ID, get_the_ID() ) ) {
				$classes .= ' post--liked';
	        }

	        // add alignment class
            if ( $this->pl_options['pl_alignment'] != null ) {
	            $styles .= 'text-align: ' . $this->pl_options['pl_alignment'] . '; ';
            }

            // add color
	        if ( $this->pl_options['pl_txt_color'] != null ) {
		        $styles .= 'color: ' . $this->pl_options['pl_txt_color'] . '; ';
	        }

	        // font size
	        if ( $this->pl_options['pl_font_size'] != null ) {
		        $styles .= 'font-size: ' . $this->pl_options['pl_font_size'] . 'px; ';
	        }

            // post like wrapper start
            echo '<div class="'.$classes.'" style="' . $styles . '">';

	        $like_text  = is_null( $like_text ) ? __( 'Like', 'post_like' ) : $like_text;
	        $liked_text = is_null( $liked_text ) ? __( 'Liked', 'post_like' ) : $liked_text;
	        $like_icon  = is_null( $like_icon ) ? 'heart' : $like_icon;

            // check if user has not liked the post opened.
            if ( ! $this->pl_user_has_liked_post( $user_ID, get_the_ID() ) ) {
	            echo '<a href="#" class="post--like" data-post-id="' . esc_attr( get_the_ID() ) . '" data-user-id="' .  esc_attr( $user_ID ) . '">' . $like_text . '</a> '. $this->pl_svg_icons->pl_get_svg( array( 'icon' => $like_icon ) ) . ' <span class="post--like-count">' . $post_like_count . '</span>';
            } else {
	            echo '<a href="#" class="post--unlike" data-post-id="' . esc_attr( get_the_ID() ) . '" data-user-id="' .  esc_attr( $user_ID ) . '">' . $liked_text . '</a> '. $this->pl_svg_icons->pl_get_svg( array( 'icon' => $like_icon ) ) . ' <span class="post--like-count">' . $post_like_count . '</span>';
            }

            // post like wrapper end
	        echo '</div>';

            $like_link = ob_get_clean();

        endif;

        return $like_link;

    }

    /**
     * get the post like count from the post meta.
     *
     * @param $post_id
     *
     * @return int
     */
    public function pl_get_like_count( $post_id ) {
        // get counts from post meta.
        $like_count = get_post_meta( $post_id, '_pl_like_count', true );
        // if there are counts
        if ( $like_count ) {
            return $like_count;
        }

        // if no count, return 0
        return 0;
    }

	/**
	 * Display post like link in post.
	 *
	 * @param $content
	 *
	 * @return content + post like link
	 */
    public function pl_display_post_like_link( $content ) {
	    // Don't show on custom page templates.
	    if ( is_page_template() ) {
		    return $content;
	    }

        /**
         * filter to add other post types
         * to show the post like link.
         */
        $post_types = apply_filters( 'pl_display_post_like_link_on', array( 'post' ) );
        // check is post_type is singular & user logged in
        if ( is_singular( $post_types ) && is_user_logged_in() ) {
            $content .= $this->pl_like_link(
            	$this->pl_options['pl_like_text'],
	            $this->pl_options['pl_liked_text'],
	            $this->pl_options['pl_like_icon']
            );
        }

        return $content;
    }

    /**
     * Check if user has liked a post or not
     * @param $user_id
     * @param $post_id
     *
     * @return bool
     */
    public function pl_user_has_liked_post( $user_id, $post_id ) {
        // get all IDs user has liked
        $user_liked_posts = get_user_option( 'pl_user_post_likes', $user_id );

        if ( is_array( $user_liked_posts ) && in_array( $post_id, $user_liked_posts ) ) {
            return true; // user has liked post
        }

        return false; // user has not liked post
    }

	/**
	 * Filter existing post types to show post like link
	 * add new post types to array to show post like link
	 *
	 * @param $old_cpts
	 *
	 * @return array of post types
	 */
	public function pl_add_post_like_on_cpts( $old_cpts ) {
		$new_cpts = array( 'page' );
		$all_cpts = array_merge( $old_cpts, $new_cpts );

		return $all_cpts;
	}
}