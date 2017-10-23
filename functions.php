 <?php
/*
Modul Name: Child Theme Development - Custom Features
Description: Custom Functionality Specifically Built for Site Network project with custom taxonomy
Version: 1.0.0
Created by: XYZ
*/


/*--------------------------------------------------------------
>>> TABLE OF CONTENTS
----------------------------------------------------------------
1.0   Custom Taxonomy URLs
2.0   Listing Pages - Reinstate Query Variables
3.0   Dequeue Parent Theme CSS
4.0   Enqueue Additional CSS & JS
5.0   Enqueue Foundation CSS framework

6.0   MyAccount Login / Logout Dropdown Menu
7.0   Login Style
8.0   Sidebar Widget - Most Read Posts
9.0   Sidebar Widget - Related Posts
10.0  Sidebar Widget - GlobalData Reports

11.0  AJAX Infinite Scroll
12.0  Timeline Widget - Articles
13.0  Mega Menu - Custom Walker
14.0  Social Media Share, Email Share
15.0  Options Page

16.0  Author Meta
17.0  Vertical Pagination on Projects
18.0  Excerpt
19.0  Initialize Sidebar Widget Areas
20.0  Dashboard Arrangements

21.0  Cheking mobile on back-end
22.0  Sort My Sites on the Network Admin Dashboard
23.0  Count Number of Any Given Post Type in the Taxonomy Term

*/






/*--------------------------------------------------------------
1.0 CUSTOM TAXONOMY URLS
--------------------------------------------------------------*/


add_filter('query_vars', 'add_state_var', 0, 1);
function add_state_var($vars){
    $vars[] = 'pmg_cat';
    $vars[] = 'post_type';
    $vars[] = 'sector';
    return $vars;
}


 function custom_rewrite_rule() {
    $url = $_SERVER['REQUEST_URI'];
    $subsiteurl= explode(basename(site_url()),$url);
    if($subsiteurl[1]){
      $url_disect = explode('/', $subsiteurl[1]);
    }else{
      $url_disect = explode('/', $url);
    }

     if( (term_exists( $url_disect[1], 'category')) && (term_exists( $url_disect[2], 'sector'))) {
      add_rewrite_rule('^([^/]*)/([^/]*)/?$','index.php?pmg_cat=$matches[1]&sector=$matches[2]','top');
      add_rewrite_rule('^([^/]*)/([^/]*)/page/([0-9]{1,})?$','index.php?pmg_cat=$matches[1]&sector=$matches[2]&paged=$matches[3]','top');
    }elseif ((term_exists( $url_disect[1], 'sector') && $url_disect[2]=='') || (term_exists( $url_disect[1], 'kgi_taxonomy') && $url_disect[2]=='page')) {
     add_rewrite_rule('^([^/]*)/?$','index.php?sector=$matches[1]','top');
  }elseif (( post_type_exists($url_disect[1])) && (term_exists( $url_disect[2], 'sector'))) {
      add_rewrite_rule('^([^/]*)/([^/]*)/?$','index.php?post_type=$matches[1]&sector=$matches[2]','top');
      add_rewrite_rule('^([^/]*)/([^/]*)/page/([0-9]{1,})?$','index.php?post_type=$matches[1]&sector=$matches[2]&paged=$matches[3]','top');
    }elseif (( post_type_exists($url_disect[1])) && (term_exists( $url_disect[3], 'region'))) {
      add_rewrite_rule('^([^/]*)/region/([^/]*)/?$','index.php?post_type=$matches[1]&region=$matches[2]','top');
      add_rewrite_rule('^([^/]*)/region/([^/]*)/page/([0-9]{1,})?$','index.php?post_type=$matches[1]&region=$matches[2]&paged=$matches[3]','top');
    }elseif (( $url_disect[1] == 'suppliers') && (term_exists( $url_disect[2], 'sector'))) {
      add_rewrite_rule('^([^/]*)/([^/]*)/?$','index.php?post_type=storefronts&sector=$matches[2]','top');
      add_rewrite_rule('^([^/]*)/([^/]*)/page/([0-9]{1,})?$','index.php?post_type=storefronts&sector=$matches[2]&paged=$matches[3]','top');
    }

    add_rewrite_tag('%supplier_name%','(.*)');
    flush_rewrite_rules();

  }
 add_action('init', 'custom_rewrite_rule', 10, 0);


//function so that pagination works on category pages
add_filter( 'category_rewrite_rules', 'vipx_filter_category_rewrite_rules' );
function vipx_filter_category_rewrite_rules( $rules ) {
    $categories = get_categories( array( 'hide_empty' => false ) );
    if ( is_array( $categories ) && ! empty( $categories ) ) {
        $slugs = array();
        foreach ( $categories as $category ) {
            if ( is_object( $category ) && ! is_wp_error( $category ) ) {
                if ( 0 == $category->category_parent ) {
                    $slugs[] = $category->slug;
                } else {
                    $slugs[] = trim( get_category_parents( $category->term_id, false, '/', true ), '/' );
                }
            }
        }
        if ( ! empty( $slugs ) ) {
            $rules = array();

            foreach ( $slugs as $slug ) {
                $rules[ '(' . $slug . ')/feed/(feed|rdf|rss|rss2|atom)?/?$' ] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
                $rules[ '(' . $slug . ')/(feed|rdf|rss|rss2|atom)/?$' ] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
                $rules[ '(' . $slug . ')(/page/([0-9]{1,})+/?)?$' ] = 'index.php?category_name=$matches[1]&paged=$matches[3]';
            }
        }
    }
    return $rules;
}






/*--------------------------------------------------------------
2.0 ARCHIVE PAGES
--------------------------------------------------------------*/

add_filter('pre_get_posts', 'query_post_type');
function query_post_type($query) {
  if( is_archive() ) {
   $pmg_cat = get_query_var('pmg_cat');
   $sector = get_query_var('sector');
   if($pmg_cat && $sector && $query->is_main_query() && !is_admin()){
        $post_type = 'post';
        $query->set('post_type',$post_type);
  }
    }
}





/*--------------------------------------------------------------
3.0 DEQUEUE PARENT THEME CSS
--------------------------------------------------------------*/


add_action( 'wp_enqueue_scripts', 'remove_default_stylesheet', 20 );

function remove_default_stylesheet() {
    wp_dequeue_style( 'thb-app' );
    wp_deregister_style( 'thb-app' );
}





/*--------------------------------------------------------------
4.0 ENQUEUE ADDITIONAL CSS AND JS
--------------------------------------------------------------*/

function additional_css() {
        wp_enqueue_style( 'additional', get_stylesheet_directory_uri() . '/assets/css/additional.css', array(), '2.1.4');
}
add_action( 'template_redirect', 'additional_css', 11 );

function additional_js() {
        wp_enqueue_script( 'additional', get_stylesheet_directory_uri() . '/assets/js/ux.js', array( 'jquery' ) );
}
add_action( 'wp_footer', 'additional_js', 11 );





/*--------------------------------------------------------------
5.0 ENQUEUE FOUNDATION CSS FRAMEWORK
--------------------------------------------------------------*/

function enqueue_foundation() {
    if ( ! is_admin() ) {
        wp_enqueue_script( 'foundation', get_stylesheet_directory_uri() . '/assets/js/foundation.min.js', array( 'jquery' ) );
        wp_enqueue_style( 'foundation', get_stylesheet_directory_uri() . '/assets/css/foundation.css' );
    }
}
add_action( 'init', 'enqueue_foundation', 11 );





/*--------------------------------------------------------------
6.0 MY ACCOUNT LOGIN/LOGOUT MENU
--------------------------------------------------------------*/

function add_login_logout_register_menu() {
     if ( is_user_logged_in() ) {
       $items .= '<div class="dropdown">';
       $items .= '<div class="dropbtn" id="myDropdown" ><span class="myaccount">My Account</span>';
       $items .= '<div class="dropdown-content">';
       $lostpasswordURL = wp_login_url();
       $items .= '<li><a class="change-password" href="' . $lostpasswordURL .'?action=lostpassword">Change Password</a></li>';
       $items .= '<li><a href="' . get_site_url() .'/manage-profile">Manage Profile</a></li>';
       $items .= '<li><a href="' . wp_logout_url() . '">' . __( 'Log Out' ) . '</a></li>';
       $items .= '</div>';
       $items .= '</div>';
       $items .= '</div>';
     } else {
        /**** stored site setttings ****/
        $args = array(
            'echo'           => false,
            'remember'       => true,
            'redirect'       => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
            'form_id'        => 'loginform',
            'id_username'    => 'user_login',
            'id_password'    => 'user_pass',
            'id_remember'    => 'rememberme',
            'id_submit'      => 'wp-submit',
            'label_username' => __( 'Username or Email Address' ),
            'label_password' => __( 'Password' ),
            'label_remember' => __( 'Remember Me' ),
            'label_log_in'   => __( 'Log In' ),
            'value_username' => '',
            'value_remember' => false
        );
        $custom_login_form = wp_login_form( $args );
        $items .= '<li><span class="login-link">Log In</span>';
        $items .= '<div class="login-form"><h2>Login/Register</h2><div class="close-modal">Close</div>'. $custom_login_form;
        $items .= '<a class="forgot-password" href="' . wp_login_url() . '?action=lostpassword">Forgotten Password?</a> ';
        $items .= '<a class="login-popup-register" href="' . home_url('/register') . '">' . __( 'Register' ) . '</a></div></li>';
     }
     return $items;
}

add_action( 'init', 'add_login_logout_register_menu', 2 );

// add market insight for CUSTOM login form
function pmg_custom_login_footer() {
  $url = get_field('market_insight_tool_url', 'option');
  $demo_url = get_field('market_insight_tool_request_demo', 'option');
  $about_url = get_field('market_insight_tool_about', 'option');
  $html = '<div class="market-insight-section">';
    $html .= '<a target="_blank" style="display:inline-block; color:white" href="' . $url . '"> Market &amp; Customer Insight <u>Log In</u> </a> | ';
    $html .= '<a target="_blank" style="display:inline-block; color:white" href="' . $demo_url . '"> Request Demo </a> | ';
    $html .= '<a target="_blank" style="display:inline-block; color:white" href="' . $about_url . '"> About Market &amp; Customer Insight </a> | ';
  $html .= '</div>';
  return $html;
}
add_filter('login_form_bottom', 'pmg_custom_login_footer');



/******* custom fix to handle site urls includes urls sent to user via emal *******/
function get_custom_login_url(){
    $login_path = 'login'; //changed in security plugin
    return $login_path;
}

// for lost password
add_filter("lostpassword_url", function ($url, $redirect) {
    $login_url = get_custom_login_url();
    $args = array( 'action' => 'lostpassword' );
    if ( !empty($redirect) )
        $args['redirect_to'] = $redirect;
    return add_query_arg( $args, site_url( $login_url ) );
}, 10, 2);

// fixes other password reset related urls
add_filter( 'network_site_url', function($url, $path, $scheme) {
    $login_path = get_custom_login_url();
    if (stripos($url, "action=lostpassword") !== false)
        return site_url( $login_path . '?action=lostpassword', $scheme);
    if (stripos($url, "action=resetpass") !== false)
        return site_url( $login_path . '?action=resetpass', $scheme);
    if (stripos($url, "action=rp") !== false){
        $queries = parse_url($url, PHP_URL_QUERY);
        parse_str($queries, $query);
        $key = $query['key'];
        $login = rawurlencode( $query['login'] );
        return site_url( $login_path . "?action=rp&key=$key&login=" . $login, $scheme);
    }
    return $url;
}, 10, 3 );

// fixes URL links in the email
add_filter("retrieve_password_message", function ($message, $key) {
    $main_site = get_site_url(1);
    if( strstr( $message, $main_site  )){
        return preg_replace( '~' . $main_site . '(\/\s|\s)~', get_site_url(), $message );
    }else return $message;
}, 10, 2);

// fixes the email title
add_filter("retrieve_password_title", function($title) {
    return "[" . wp_specialchars_decode(get_option('blogname'), ENT_QUOTES) . "] Password Reset";
});

// disallow user - paid subscriber & registered user from viewing the dashboard
function block_user_dashboard() {
    $user = wp_get_current_user();
    if ( !(defined('DOING_AJAX') && DOING_AJAX) && (in_array( 'subscriber', (array) $user->roles ) || in_array( 'contributor', (array) $user->roles ) ) ) {
        wp_redirect( home_url() );
        exit();
    }
}
add_action( 'admin_init', 'block_user_dashboard' );

// redirects to home page after log out
function my_logout_page() {
    wp_redirect( home_url() );
    exit();
}
add_action('wp_logout', 'my_logout_page');

// removes admin bar for paid subscribers & registered user
function remove_admin_bar() {
    $user = wp_get_current_user();
    if ( !current_user_can( 'manage_options' ) ) {
        show_admin_bar(false);
    }
    if( in_array( 'editor', (array) $user->roles ) ){
        show_admin_bar(true);
    }
}

add_action('init', 'remove_admin_bar');


function custom_user_profile_custom_fields($user){
    echo '<h3>Newsletter Preferences</h3>
        <table class="form-table">
          <tr>
                <th><label for="newsletter-weekly">Newsletter Weekly </label></th>
                <td>
                    <input type="text" class="regular-text" name="newsletter-weekly" value="' . esc_attr( get_user_option( 'newsletter_weekly', $user->ID ) ) . '" id="newsletter-weekly" placeholder="e.g: newsletter-weekly" /><br />
                    <span class="description">Newsletter Weekly</span>
                </td>
            </tr>

          <tr>
                <th><label for="newsletter-daily">Newsletter Daily </label></th>
                <td>
                    <input type="text" class="regular-text" name="newsletter-daily" value="' . esc_attr( get_user_option( 'newsletter_daily', $user->ID ) ) . '" id="newsletter-daily" placeholder="e.g: newsletter-daily" /><br />
                    <span class="description">Newsletter Daily</span>
                </td>
            </tr>

            <tr>
                <th><label for="magazine">Magazine </label></th>
                <td>
                    <input type="text" class="regular-text" name="magazine" value="' . esc_attr( get_user_option( 'magazine', $user->ID ) ) . '" id="magazine" placeholder="e.g: magazine" /><br />
                    <span class="description">Magazine</span>
                </td>
            </tr>
        </table>';
}
add_action( 'show_user_profile', 'custom_user_profile_custom_fields' );
add_action( 'edit_user_profile', 'custom_user_profile_custom_fields' );


function save_custom_user_profile_custom_fields($user_id) {
  if ( !current_user_can( 'edit_user', $user_id ) )
    return false;
    update_user_option( $user_id, 'newsletter_weekly', $_POST['newsletter-weekly'] );
    update_user_option( $user_id, 'newsletter_daily', $_POST['newsletter-daily'] ); //changing back to us format date
    update_user_option( $user_id, 'magazine', $_POST['magazine'] );
}

add_action( 'personal_options_update', 'save_custom_user_profile_custom_fields' );
add_action( 'edit_user_profile_update', 'save_custom_user_profile_custom_fields' );





/*--------------------------------------------------------------
7.0 LOGIN STYLE
--------------------------------------------------------------*/

function kgi_login_style() {
$mobile_logo = get_field('mobile_logo', 'option'); ?>
    <style type="text/css">
        .login {
          background: url('/wp-content/uploads/sites/2/2017/09/loginbackground.jpeg');
          background-size: cover;
      }
      .login h1 a {
        background-image: url(<?php echo $mobile_logo['url']; ?>) !important;
        width: auto !important;
        background-size: 100% !important;
        max-width: 9em !important;
        margin-bottom: 0 !important;
      }
      .button-large {
          width: 100%;
          height: 3em !important;
          background: orange !important;
          color: black !important;
          text-shadow: none !important;
          font-family: 'Montserrat';
          border-radius: 0 !important;
          box-shadow: none !important;
          border: none !important;
      }
      .login form {
          border-radius: 1em;
          border-bottom-left-radius: 0;
          border-bottom-right-radius: 0;
      }
      #login {
          width: 320px;
          padding: 10% 0 0 !important;
          margin: auto;
          z-index: 22222;
          display: block;
          position: relative;
      }
      .login #login_error, .login .message {
          border-left: 4px solid orange !important;
          padding: 12px;
          margin-left: 0;
          margin-bottom: 20px;
          background-color: rgb(255, 255, 255);
          -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
          box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
          font-family: 'montserrat';
      }
      .login #nav {
          margin: 0 !important;
          padding: 1em !important;
          background: rgba(11, 45, 76, 0.79);
      }
      .login #backtoblog, .login #nav {
          margin: 0 !important;
          padding: 0 !important;
          text-align: center;
      }
      .login h1 {
            text-align: center;
      }
      .login #backtoblog a, .login #nav a {
        text-decoration: none;
        color: #555d66;
        width: 100%;
        padding: 1em;
        color: white !important;
        display: block;
        margin: 0;
      }
      #backtoblog a {
        background: rgba(86, 84, 79, 0.6);
        border-bottom-left-radius: 1em;
        border-bottom-right-radius: 1em;
      }
      .login:after {
        background: rgba(0, 11, 21, 0.4);
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        z-index: 1;
    }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'kgi_login_style' );









/*--------------------------------------------------------------
8.0 SIDEBAR WIDGET - MOST READ POSTS
--------------------------------------------------------------*/

class widget_mostread extends WP_Widget {
  function __construct() {
    $widget_ops = array(
    'classname'   => 'widget_mostread',
    'description' => esc_html__('Display tagged posts in a list','goodlife')
    );
    parent::__construct(
      'thb_widget_mostread',
      esc_html__( 'KGI - Most Read' , 'goodlife' ),
      $widget_ops
    );
    $this->defaults = array( 'title' => 'Widget Title', 'show' => '3', 'tag' => '', 'thumbs' => 'thumbs-no');
  }

  function widget($args, $instance) {
    extract($args);
    $title = apply_filters('widget_title', $instance['title']);
    $show = $instance['show'];
    $thumbs = $instance['thumbs'];
    $tags = $instance['tag'];
     $getdate = getdate();
     $args = array(
      'post_type'=>'post',
      'post_status' => 'publish',
      'ignore_sticky_posts' => 1,
      'no_found_rows' => true,
      'tag' => 'most-read',
      'posts_per_page' => $show
    );
    $posts = new WP_Query( $args );
    echo $before_widget;
    $counts = 0;
?>
<div class="si most-read">
  <h3><?php echo $title;?></h3>
    <ol class="sal">
      <?php
      if( $posts->have_posts() ) {
      while ($posts->have_posts()) : $posts->the_post();
        echo '<li>';
        echo '<a href="'.esc_url( get_permalink() ).'" class="wpp-post-title" target="_self">';
        echo get_the_title();
        echo '</a>';
        echo '</li>';
      endwhile;
      wp_reset_query();
      }
      ?>
    </ol>
</div>
</div>

<?php
    wp_reset_query();
  }

  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    /* Strip tags (if needed) and update the widget settings. */
    $instance['title'] = strip_tags( $new_instance['title'] );
    $instance['show'] = strip_tags( $new_instance['show'] );
    $instance['category'] = strip_tags( $new_instance['category'] );
    $instance['thumbs'] = strip_tags( $new_instance['thumbs'] );
    $instance['tag'] = strip_tags( $new_instance['tag'] );
    return $instance;
  }
  function form($instance) {
    $defaults = $this->defaults;
    $instance = wp_parse_args( (array) $instance, $defaults );
    $thumbs = $instance['thumbs'];
    $categories = get_categories();
    $tags = get_tags(array('get'=>'all')); ?>
    <p>
     <label for="<?php echo $this->get_field_id( 'title' ); ?>">Widget Title:</label>
     <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
    </p>
    </p>
    <p>
     <label for="<?php echo $this->get_field_id( 'show' ); ?>">Number of Posts:</label>
     <input id="<?php echo $this->get_field_id( 'show' ); ?>" name="<?php echo $this->get_field_name( 'show' ); ?>" value="<?php echo $instance['show']; ?>" style="width:100%;" />
    </p>
  <?php
  }
}
function widget_mostread_init() {
  register_widget('widget_mostread');
}
add_action('widgets_init', 'widget_mostread_init');





/*--------------------------------------------------------------
9.0 SIDEBAR WIDGET - RELATED POSTS
--------------------------------------------------------------*/

class widget_relatedposts extends WP_Widget {
  function __construct() {
    $widget_ops = array(
    'classname'   => 'widget_relatedposts',
    'description' => esc_html__('Display tagged posts in a list','goodlife')
    );
    parent::__construct(
      'thb_widget_relatedposts',
      esc_html__( 'KGI - Related Posts Widget' , 'goodlife' ),
      $widget_ops
    );
    $this->defaults = array( 'title' => 'Widget Title', 'show' => '3', 'tag' => '', 'thumbs' => 'thumbs-no');
  }

  function widget($args, $instance) {
    extract($args);
    $title = apply_filters('widget_title', $instance['title']);
    $show = $instance['show'];
    $thumbs = $instance['thumbs'];
    $tags = $instance['tag'];
    global $post;
    $id = isset($_POST['post_id']) ? $_POST['post_id'] : false;
    $post = get_post( $id );
    $terms = wp_get_post_terms( $post->ID, 'sector', array("fields" => "all") );
    $mainterm = $terms[0];
    $args = array(
      'post_type'=>'storefronts',
      'post_status' => 'publish',
      'ignore_sticky_posts' => 1,
      'no_found_rows' => true,
      'posts_per_page' => $show,
      'tax_query' => array(
                array(
                    'taxonomy' => 'sector',
                    'field' => 'slug',
                    'terms' => $mainterm->slug,
                ),
            ),
    );

    $posts = new WP_Query( $args );
    echo $before_widget;
    $counts = 0;
    if( $posts->have_posts() ) { ?>
<div class="si related-companies">
  <h3><?php echo $title; ?></h3>
  <ul class="sal">
<?php
      while ($posts->have_posts()) : $posts->the_post();
        $thumbnail = get_field('company_logo');
        echo '<li>';
        echo '<div class="row">';
        echo '<div class="large-3 columns r-c-img">';
        echo '<a href="'.esc_url( get_permalink() ).'" class="wpp-post-title" target="_self">';
        echo '<img src="'.$thumbnail['url'].'" alt="'.$thumbnail['alt'].'" />';
        echo '</a>';
        echo '</div>';
        echo '<div class="large-9 columns r-c-title">';
        echo '<h4>';
        echo '<a href="'.esc_url( get_permalink() ).'" class="wpp-post-title" target="_self">';
        echo get_the_title();
        echo '</a>';
        echo '</h4>';
        echo '</div>';
        echo '</div>';
        echo '</li>';
      endwhile;
      wp_reset_query();
?>
  </ul>
</div>
</div>
<?php
      }
    wp_reset_query();
  }
  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance['title'] = strip_tags( $new_instance['title'] );
    $instance['show'] = strip_tags( $new_instance['show'] );
    $instance['category'] = strip_tags( $new_instance['category'] );
    $instance['thumbs'] = strip_tags( $new_instance['thumbs'] );
    $instance['tag'] = strip_tags( $new_instance['tag'] );
    return $instance;
  }
  function form($instance) {
    $defaults = $this->defaults;
    $instance = wp_parse_args( (array) $instance, $defaults );
    $thumbs = $instance['thumbs'];
    $categories = get_categories();
    $tags = get_tags(array('get'=>'all')); ?>
    <p>
     <label for="<?php echo $this->get_field_id( 'title' ); ?>">Widget Title:</label>
     <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
    </p>
    <p>
     <label for="<?php echo $this->get_field_id( 'show' ); ?>">Number of Posts:</label>
     <input id="<?php echo $this->get_field_id( 'show' ); ?>" name="<?php echo $this->get_field_name( 'show' ); ?>" value="<?php echo $instance['show']; ?>" style="width:100%;" />
    </p>
  <?php
  }
}
function widget_relatedposts_init() {
  register_widget('widget_relatedposts');
}
add_action('widgets_init', 'widget_relatedposts_init');





/*--------------------------------------------------------------
10.0 SIDEBAR WIDGET - GLOBALDATA REPORTS
--------------------------------------------------------------*/

class widget_reports extends WP_Widget {
  function __construct() {
    $widget_ops = array(
    'classname'   => 'widget_reports',
    'description' => esc_html__('Display tagged posts in a list','goodlife')
    );
    parent::__construct(
      'thb_widget_reports',
      esc_html__( 'KGI - Reports Widget' , 'goodlife' ),
      $widget_ops
    );
    $this->defaults = array( 'title' => 'Widget Title', 'show' => '3', 'tag' => '', 'thumbs' => 'thumbs-no');
  }

  function widget($args, $instance) {
    extract($args);
    $title = apply_filters('widget_title', $instance['title']);
    $show = $instance['show'];
    $thumbs = $instance['thumbs'];
    $tags = $instance['tag'];
    echo $before_widget;
    $counts = 0;
    ?>
    <div id="top_selling" class="si">
      <div class="top_selling_box">
      <h3 class="sc-heading">
        <img src="/wp-content/uploads/sites/2/2017/08/gd_logo_black.png" alt="GlobalData"><?php the_field('globaldata_title', 'option'); ?>
      </h3>
      </div>
      <ul class="sal reports-list">
      <?php
        if( have_rows('globaldata_reports', 'option') ):
            while ( have_rows('globaldata_reports', 'option') ) : the_row();
            ?>
               <li><?php $feature_title = wp_trim_words( get_sub_field('globaldata_widget_title' ), $num_words = 8, $more = '...' ); ?>
                <a href="<?php the_sub_field('globaldata_widget_link'); ?>" target="_blank"><?php echo $feature_title; ?></a>
                <div class="top_s_bottom">
                  <span class="price">$<?php the_sub_field('globaldata_widget_price'); ?></span>
                </div>
               </li>
               <?php
            endwhile;
        else :
            echo 'There arent available reports.';
        endif;
      ?>
      </ul>
    </div>
    </div>
    <?php
    wp_reset_query();
  }
  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance['title'] = strip_tags( $new_instance['title'] );
    $instance['show'] = strip_tags( $new_instance['show'] );
    $instance['category'] = strip_tags( $new_instance['category'] );
    $instance['thumbs'] = strip_tags( $new_instance['thumbs'] );
    $instance['tag'] = strip_tags( $new_instance['tag'] );
    return $instance;
  }
  function form($instance) {
    $defaults = $this->defaults;
    $instance = wp_parse_args( (array) $instance, $defaults );
    $thumbs = $instance['thumbs'];
    $categories = get_categories();
    $tags = get_tags(array('get'=>'all')); ?>
    <p>
     <label for="<?php echo $this->get_field_id( 'title' ); ?>">Widget Title:</label>
     <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
    </p>
    <p>
     <label for="<?php echo $this->get_field_id( 'show' ); ?>">Number of Posts:</label>
     <input id="<?php echo $this->get_field_id( 'show' ); ?>" name="<?php echo $this->get_field_name( 'show' ); ?>" value="<?php echo $instance['show']; ?>" style="width:100%;" />
    </p>
  <?php
  }
}
function widget_reports_init() {
  register_widget('widget_reports');
}
add_action('widgets_init', 'widget_reports_init');





/*--------------------------------------------------------------
11.0 AJAX INFINITE SCROLL
--------------------------------------------------------------*/

$exclusion_list=array();
add_action("wp_ajax_nopriv_thb_infinite_ajax", "thb_infinite_ajax2");
add_action("wp_ajax_thb_infinite_ajax", "thb_infinite_ajax2");
function thb_infinite_ajax2() {
  global $post;
  $id = isset($_POST['post_id']) ? $_POST['post_id'] : false;
  $post = get_post( $id );
  $terms = wp_get_post_terms( $post->ID, 'sector', array("fields" => "all") );
  $mainterm = $terms[0];
  if ($id) {
    $alreadylisted = $id;
    $year = get_the_date( 'Y' );
    $month = get_the_date( 'n' );
    $day   = get_the_date( 'j' );
    echo $prev_post->post_title;
    if (isset($mainterm) && $mainterm !== ''){
    $args = array(
          'no_found_rows' => true,
          'posts_per_page' => 1,
          'post_status' => 'publish',
          'orderby' => 'date',
          'order' => 'DESC',
          'post_type'=>'post',
          'offset'=>$_COOKIE['articleincrease'],
          'tax_query' => array(
                    array(
                        'taxonomy' => 'sector',
                        'field' => 'slug',
                        'terms' => $mainterm->slug,
                    ),
                ),
          'date_query' => array(
            'before'    => array(
                    'year'  => $year,
                    'month' => $month,
                    'day'   => $day,
                  ),
            'inclusive' => true,
          ),
    );
    } else {
    $args = array(
          'no_found_rows' => true,
          'posts_per_page' => 1,
          'post_status' => 'publish',
          'orderby' => 'date',
          'order' => 'DESC',
          'post_type'=>'post',
          'offset'=>$_COOKIE['articleincrease'],
    );
    }
    $query = new WP_Query($args);
    ob_start();
    do_action("thb_vc_ajax");
    if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post();
      global $more;
      $more = -1;
      $id = get_the_ID();
      global $exclusion_list;
      array_push($exclusion_list,$id);
      $format = get_post_format();
      set_query_var( 'thb_ajax', $exclusion_list );
      get_template_part( 'single-ajax' );
    endwhile; else : endif;
    $out = ob_get_contents();
    if (ob_get_contents()) ob_end_clean();
    echo $out;
  }
}





/*--------------------------------------------------------------
12.0 TIMELINE WIDGET - ARTICLES
--------------------------------------------------------------*/

function custom_content_articles(){
?>
    <div class="scrolling-content">
      <h2>Timeline</h2>
      <ul class="sal">
<?php
        global $post;
        $id = isset($_POST['post_id']) ? $_POST['post_id'] : false;
        $post = get_post( $id );
        $terms = wp_get_post_terms( $post->ID, 'sector', array("fields" => "all") );
        $mainterm = $terms[0];
        $year = get_the_date( 'Y' );
        $month = get_the_date( 'n' );
        $day   = get_the_date( 'j' );
        if (isset($terms) && $terms != NULL){
        $argsarticle = array(
          'no_found_rows' => true,
          'posts_per_page' => 8,
          'post_status' => 'publish',
          'orderby' => 'date',
          'order' => 'DESC',
          'post_type'=>'post',
          'tax_query' => array(
                    array(
                        'taxonomy' => 'sector',
                        'field' => 'slug',
                        'terms' => $mainterm->slug,
                    ),
                ),
          'date_query' => array(
            'before'    => array(
                    'year'  => $year,
                    'month' => $month,
                    'day'   => $day,
                  ),
            'inclusive' => true,
          ),
        );
      } else {
        $argsarticle = array(
          'no_found_rows' => true,
          'posts_per_page' => 8,
          'post_status' => 'publish',
          'orderby' => 'date',
          'order' => 'DESC',
          'category__in' => wp_get_post_categories($post->ID),
          'post_type'=>'post',
        );
        }

        $my_query = new WP_Query($argsarticle);
        if( $my_query->have_posts() ) {
        while ($my_query->have_posts()) : $my_query->the_post();
          echo '<li class="cf timeline-article">';
          echo '<span class="progress"></span>';
          echo '<div class="article-date">';
          echo '<b>' . esc_html( get_the_date("j") ) . '</b>';
          echo ' ' . esc_html( get_the_date("M") );
          echo '<i>' . esc_html( get_the_date("Y") ) . '</i>';
          echo '</div>';
          echo '<h3><a href="'.esc_url( get_permalink() ).'">'.get_the_title().'</a></h3>';
          echo '</li>';
        endwhile;
        }
        wp_reset_query();
?>
      </ul>
      <?php $top_mpu_opt_div = get_field('top_mpu_opt_div', 'option'); ?>
      <div class="mpu sidebar-mpu-1">
       <div id="<?php echo $top_mpu_opt_div;?>">
        <script type="text/javascript">
          googletag.cmd.push(function() { googletag.display('<?php echo $top_mpu_opt_div;?>'); });
        </script>
       </div>
      </div>
    </div>
<?php
}
add_action('article-timeline-widget','custom_content_articles');





/*--------------------------------------------------------------
13.0 MEGA MENU - CUSTOM WALKER
--------------------------------------------------------------*/



global $i;
$i = 0;

class KGI_Walker_Nav_Menu extends Walker_Nav_Menu {

    /**
     * Starts the list before the elements are added.
     *
     * Adds classes to the unordered list sub-menus.
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param int    $depth  Depth of menu item. Used for padding.
     * @param array  $args   An array of arguments. @see wp_nav_menu()
     */
    function start_lvl( &$output, $depth = 0, $args = array() ) {
        // Depth-dependent classes.
        $indent = ( $depth > 0  ? str_repeat( "\t", $depth ) : '' ); // code indent
        $display_depth = ( $depth + 1); // because it counts the first submenu as 0
        $classes = array(
            'sub-menu',
            ( $display_depth % 2  ? 'menu-odd' : 'menu-even' ),
            ( $display_depth >=2 ? 'sub-sub-menu' : '' ),
            'menu-depth-' . $display_depth
        );
        $class_names = implode( ' ', $classes );

        // Build HTML for output.
        $output .= "\n" . $indent . '<ul class="' . $class_names . '">' . "\n";
    }



    /**
     * Start the element output.
     *
     * Adds main/sub-classes to the list items and links.
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param object $item   Menu item data object.
     * @param int    $depth  Depth of menu item. Used for padding.
     * @param array  $args   An array of arguments. @see wp_nav_menu()
     * @param int    $id     Current item ID.
     */
    function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
        global $wp_query;
        $indent = ( $depth > 0 ? str_repeat( "\t", $depth ) : '' ); // code indent

        // Depth-dependent classes.
        $depth_classes = array(
            ( $depth == 0 ? 'main-menu-item' : 'sub-menu-item' ),
            ( $depth >=2 ? 'sub-sub-menu-item' : '' ),
            ( $depth % 2 ? 'menu-item-odd' : 'menu-item-even' ),
            'menu-item-depth-' . $depth
        );
        $depth_class_names = esc_attr( implode( ' ', $depth_classes ) );

        // Passed classes.
        $classes = empty( $item->classes ) ? array() : (array) $item->classes;
        $class_names = esc_attr( implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) ) );

        // Build HTML.
        $output .= $indent . '<li id="nav-menu-item-'. $item->ID . '" class="' . $depth_class_names . ' ' . $class_names . '">';

        // Link attributes.
        $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
        $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
        $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
        $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
        $attributes .= ' class="menu-link ' . ( $depth > 0 ? 'sub-menu-link' : 'main-menu-link' ) . '"';

        $current_url = $item->url;
        $site_url = site_url();
        $last_segment = basename(parse_url($current_url, PHP_URL_PATH));

        /* Most Recent 3 Articles under each Main Taxonomy Term */
        if ($last_segment == 'videos') {
        $queryargs = array(
          'no_found_rows' => true,
          'posts_per_page' => 3,
          'post_status' => 'publish',
          'post_type' => $last_segment,
          'orderby' => 'date',
          'order' => 'DESC',
        );
        } else {
        $queryargs = array(
          'no_found_rows' => true,
          'posts_per_page' => 3,
          'post_status' => 'publish',
          'orderby' => 'date',
          'order' => 'DESC',
          'tax_query' => array(
                    array(
                        'taxonomy' => 'sector',
                        'field' => 'slug',
                        'terms' => $last_segment,
                    ),
                ),
        );
        }
        $my_query = new WP_Query($queryargs);
        if( $my_query->have_posts() ) {
        while ($my_query->have_posts()) : $my_query->the_post();
         $post_type_obj = get_post_type_object (get_post_type( get_the_ID() ) );
          $post_type_name = $post_type_obj->labels->singular_name;

           if ( has_post_thumbnail() ) {
                $thumbnailimage = get_the_post_thumbnail( $post->ID, 'medium' );
            } else {
                  if($post_type_name=='Press Release' || $post_type_name=='White Paper' || $post_type_name=='Video' || $post_type_name=='Product'){
                  $company_id = get_field('company_name');
                  $image = get_field('company_logo',$company_id);
                    if( !empty($image) ){
                      $img=$image['url'];
                       $thumbnailimage ='<img src="' . $img .'"/>';
                     }

                  }else{
                       $category=get_the_category()[0]->name;
                       if ($category == 'comment') {
                           $thumbnail_temp = get_field('comments_placeholder_image', 'option');
                           $thumbnailimage = '<img src="'.$thumbnail_temp['url'].'" />';
                       } else {
                           $thumbnail_temp = get_field('news_placeholder_image', 'option');
                           $thumbnailimage = '<img src="'.$thumbnail_temp['url'].'" />';
                       }
                  }
            }
           if ($post_type_name == 'Storefront'){
              $post_type_name = 'Suppliers';
           };
           if ($post_type_name == 'Post'){
              $post_type_array = get_the_category();
              $post_type_name = $post_type_array[0]->name;
           };
           if ($last_segment == 'events') {
           $result .=
                    '<div class="large-4 medium-4 columns">
                      <article>
                        <a href="'.get_site_url().'/events">'.$thumbnailimage.'</a>
                        <div class="article-category"><a href="'.get_site_url().'/events">'.$post_type_name.'</a></div>
                        <h3><a href="'.get_site_url().'/events">'.get_the_title().'</a></h3>
                      </article>
                    </div>';
           } else {
           $result .=
                    '<div class="large-4 medium-4 columns">
                      <article>
                        <a href="'.esc_url( get_permalink() ).'">'.$thumbnailimage.'</a>
                        <div class="article-category"><a href="'.esc_url( get_permalink() ).'">'.$post_type_name.'</a></div>
                        <h3><a href="'.esc_url( get_permalink() ).'">'.get_the_title().'</a></h3>
                      </article>
                    </div>';
           }
        endwhile;
        }
        wp_reset_postdata();
        $parentslisted = wp_get_nav_menu_items( 'Main Navigation' );
        $arrayParents=array();
        if( $parentslisted ) {
          foreach( $parentslisted as $index => $item_current ) {
            if( $item_current->menu_item_parent == 0 )
              if (!in_array($item_current->ID, $arrayParents)) {
                array_push($arrayParents,$item_current->ID);
              }
          }
        }
        global $i;
        $itemslisted = wp_get_nav_menu_items( 'Main Navigation' );
        $parent_ID = $arrayParents[$i];
        if( $itemslisted ) {
          $sub_nav_list = '<ul>';
          foreach( $itemslisted as $index => $item_current ) {
            if( $item_current->menu_item_parent != 0 && $item_current->menu_item_parent == $parent_ID )
              $sub_nav_list .= '<li class="menu"><a href="' . $item_current->url . $last_segment .'">' . $item_current->title . '</a></li>';
          }
          $sub_nav_list .= '</ul>';
        }
        $i += 1;
        $item_output = sprintf( '%1$s<a%2$s>%3$s%4$s%5$s</a>%6$s
          <div class="mega-menu">
            <div class="row">
                <div class="large-3 medium-3 columns">
                  <section class="section-nav">
                    <h2>%3$s%4$s%5$s</h2>
                    <ul class="cf">'.$sub_nav_list.'</ul>
                  </section>
                </div>
                <div class="large-9 medium-9 columns">
                  <div class="row">'. $result .'</div>
                </div>
              </div>
          </div>
          ',
            $queryargs->before,
            $attributes,
            $queryargs->link_before,
            apply_filters( 'the_title', $item->title, $item->ID ),
            $queryargs->link_after,
            $queryargs->after
        );
        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $queryargs );

    }
}
      wp_reset_query();
      add_filter('wp_nav_menu_items','add_additional_to_menu', 10, 2);
      function add_additional_to_menu( $items, $args ) {
      $menu_name = 'nav-menu';
      if ( ( $locations = get_nav_menu_locations() ) ) {
          $menu = wp_get_nav_menu_object( $locations[ $menu_name ] );
          $menu_items = wp_get_nav_menu_items($menu->term_id);
          $menu_list = '<ul id="menu-' . $menu_name . '" class="cf">';
          foreach ( (array) $menu_items as $key => $menu_item ) {
            if ($menu_item->menu_item_parent != 0 ) continue;
              $title = $menu_item->title;
              $url = $menu_item->url;
              $menu_list .= '<li><a href="' . $url . '">' . $title . '</a></li>';
          }
          $menu_list .= '</ul>';
      } else {
          $menu_list = '<ul><li>Menu "' . $menu_name . '" not defined.</li></ul>';
      }
      $menu_all_sections = 'secondary-menu';
      if ( ( $locations = get_nav_menu_locations() ) ) {
          $menu = wp_get_nav_menu_object( $locations[ $menu_all_sections ] );
          $menu_items = wp_get_nav_menu_items($menu->term_id);
          $menu_list_all_sections = '<ul class="row">';
          foreach ( (array) $menu_items as $key => $menu_item ) {
            if ($menu_item->menu_item_parent != 0 ) continue;
              $title = $menu_item->title;
              $url = $menu_item->url;
              $menu_list_all_sections .= '<li class="large-4 columns"><a href="' . $url . '">' . $title . '</a></li>';
          }
          $menu_list_all_sections .= '</ul>';
      } else {
          $menu_list_all_sections = '<ul><li>Menu "' . $menu_all_sections . '" not defined.</li></ul>';
      }
    if( $args->theme_location == 'nav-menu' )
        return $items.'
                <li class="nav-all"><span class="mega-toggle">All</span>
                <div class="mega-menu mega-menu-all">
                    <!-- .top-level-nav -->
                    <div class="all-sections">
                      <h2>All sections</h2>'.$menu_list_all_sections.'</div>
                    <!-- .all-sections -->
                  </div>
                </div>
                <!-- .mega-menu -->
              </li>
              <li class="nav-search">
                <span class="search-toggle"></span>
                  <?php get_search_form(); ?>
                <span class="search-close"></span>
              </li>
      ';
    return $items;
}

 do_action( 'thb_mobile_menu' );





/*--------------------------------------------------------------
14.0 SOCIAL MEDIA SHARE, EMAIL SHARE
--------------------------------------------------------------*/

add_action( 'after_setup_theme', 'remove_my_action' );
function remove_my_action(){
 remove_action( 'thb_social_article_detail_simple', 'thb_social_article_detail_simple', 3, 3 );
}

add_action( 'thb_social_article_detail_simple1', 'thb_social_article_detail_simple1', 3, 3 );
function thb_social_article_detail_simple1($id = false, $class = false) {
  $id = $id ? $id : get_the_ID();
  $permalink = get_permalink($id);
  $title = the_title_attribute(array('echo' => 0, 'post' => $id) );
  $image_id = get_post_thumbnail_id($id);
  $image = wp_get_attachment_image_src($image_id,'full');
  $username = get_field('twitter_account_username', 'option');
  $twitter_user = ot_get_option('twitter_bar_username', $username);
  $sharing_type = ot_get_option('sharing_buttons') ? ot_get_option('sharing_buttons') : array();
 ?>
  <aside class="share-article share-main simple hide-on-print <?php echo esc_attr($class); ?>">
    <div class="share-title"><em><?php echo thb_social_article_totalshares($id); ?></em><span><?php _e('Shares', 'goodlife'); ?></span></div>
    <a href="<?php echo 'http://www.facebook.com/sharer.php?u=' . urlencode( esc_url( $permalink ) ).''; ?>" class="facebook social"><i class="fa fa-facebook"></i></a>
    <a href="<?php echo 'https://twitter.com/intent/tweet?text=' . htmlspecialchars(urlencode(html_entity_decode($title, ENT_COMPAT, 'UTF-8')), ENT_COMPAT, 'UTF-8') . '&url=' . urlencode( esc_url( $permalink ) ) . '&via=' . urlencode( $twitter_user ? $twitter_user : get_bloginfo( 'name' ) ) . ''; ?>" class="twitter social "><i class="fa fa-twitter"></i></a>
    <a href="<?php echo 'http://pinterest.com/pin/create/link/?url=' . esc_url( $permalink ) . '&media=' . ( ! empty( $image[0] ) ? $image[0] : '' ) . '&description='.htmlspecialchars(urlencode(html_entity_decode($title, ENT_COMPAT, 'UTF-8')), ENT_COMPAT, 'UTF-8'); ?>" class="pinterest social" nopin="nopin" data-pin-no-hover="true"><i class="fa fa-pinterest"></i></a>
    <a href="<?php echo 'https://www.linkedin.com/cws/share?url=' . esc_url( $permalink ) . ''; ?>" class="linkedin social"><i class="fa fa-linkedin"></i></a>
    <a href="mailto:?subject=<?php echo 'Verdict: ' .htmlspecialchars((html_entity_decode($title, ENT_COMPAT, 'UTF-8')), ENT_COMPAT, 'UTF-8') . '&body=' . urlencode( esc_url( $permalink ) ) . '%0D%0AYou can register to the site here:' . get_site_url().'/register' ?>" class="boxed-icon email"><i class="fa fa-envelope"></i></a>
    <a href="<?php echo 'http://www.reddit.com/submit?url=' . esc_url( $permalink ) . '&title=' . htmlspecialchars(urlencode(html_entity_decode($title, ENT_COMPAT, 'UTF-8')), ENT_COMPAT, 'UTF-8'); ?>" class="reddit social"><i class="fa fa-reddit"></i></a>
  </aside>
<?php
}

function thb_social_article_detail_kgi($id = false, $class = false) {
  $id = $id ? $id : get_the_ID();
  $permalink = get_permalink($id);
  $title = the_title_attribute(array('echo' => 0, 'post' => $id) );
  $image_id = get_post_thumbnail_id($id);
  $image = wp_get_attachment_image_src($image_id,'full');
 ?>
  <aside class="share-article hide-on-print <?php echo esc_attr($class); ?>">
    <div class="row">
        <div class="share-title"><?php esc_html_e('Share', 'goodlife'); ?></div>
        <?php do_action('thb_social_article_detail_simple1', false, 'small-only-text-center text-right'); ?>
  </aside>
<?php
}
add_action( 'thb_social_article_detail_kgi', 'thb_social_article_detail_kgi', 3, 3 );





/*--------------------------------------------------------------
15.0 OPTIONS PAGE
--------------------------------------------------------------*/

if( function_exists('acf_add_options_page') ) {
  acf_add_options_page();
}

if( function_exists('acf_set_options_page_title') ) {
    acf_set_options_page_title( __('Site Options') );
}

if( function_exists('acf_set_options_page_capability') ) {
    acf_set_options_page_capability( 'edit_theme_options' );
}


function my_acf_admin_head() {
  ?>
  <style type="text/css">
    .acf-settings-wrap .acf-fields > .acf-field {
      padding: 30px;
    }
    .acf-settings-wrap .acf-postbox {
      box-shadow: 0.2em 0.2em 1em 0em rgba(0, 0, 0, 0.19);
      -moz-box-shadow: 0.2em 0.2em 1em 0em rgba(0, 0, 0, 0.19);
      -webkit-box-shadow: 0.2em 0.2em 1em 0em rgba(0, 0, 0, 0.19);
    }
    .acf-settings-wrap #poststuff h2 {
      font-size: 1.2em;
      font-weight: 300;
      background: linear-gradient(to left, #136a8a, #0b2d4c);
      color: #fff;
      padding: 20px;
    }
    .acf-settings-wrap .postbox .handlediv {
      color: #fff;
      margin-top: 10px;
      margin-right: 10px;
    }
    .acf-settings-wrap #acf-group_5992fe5419f00 .acf-input a {
      color: #222;
    }

  </style>
  <?php
}

add_action('acf/input/admin_head', 'my_acf_admin_head');





/*--------------------------------------------------------------
16.0 AUTHOR META
--------------------------------------------------------------*/

function authorMeta() {
  $post_meta = ot_get_option('post_meta') ? ot_get_option('post_meta') : array();
  $author_first_name = get_the_author_meta( 'first_name', get_the_author_meta( 'ID' ) );
  $author_last_name = get_the_author_meta( 'last_name', get_the_author_meta( 'ID' ) );
  if ($author_first_name != '' && $author_last_name != '') {
  ?>
  <div class="post-author">By
  <strong rel="author" itemprop="author" class="author"><a href="<?php echo get_author_posts_url(get_the_author_meta( 'ID' )); ?>">
  <?php echo $author_first_name; ?>
  <?php echo $author_last_name; ?>
  </a></strong>
  </div>
  <?php
  }
}
add_action( 'authorMeta', 'authorMeta', 10, 5 );





/*--------------------------------------------------------------
17.0 AJAX VERTICAL PAGINATION
--------------------------------------------------------------*/

global $wp_query;
wp_localize_script( 'ajax-pagination', 'ajaxpagination', array(
  'ajaxurl' => admin_url( 'admin-ajax.php' ),
  'query_vars' => json_encode( $wp_query->query )
));

add_action( 'wp_ajax_nopriv_ajax_pagination', 'ajax_pagination' );
add_action( 'wp_ajax_ajax_pagination', 'ajax_pagination' );

function ajax_pagination() {
    $args = array(
      'p' => 136,
      'post_type' => 'any',
      'page' => $_POST['page'],
      'posts_per_page' => 1
    );
    $posts = new WP_Query( $args );
    $GLOBALS['wp_query'] = $posts;
      if( $posts->have_posts() ) {
      while ($posts->have_posts()) : $posts->the_post();
        echo the_content();
      endwhile;
      wp_reset_query();
      }
    die();
}





/*--------------------------------------------------------------
18.0 EXCERPT
--------------------------------------------------------------*/

function wpdocs_excerpt_more( $more ) {
    return '...<br><a href="'.get_the_permalink().'" rel="nofollow">Read More...</a>';
}
add_filter( 'excerpt_more', 'wpdocs_excerpt_more' );

function wpdocs_custom_excerpt_length( $length ) {
    return 20;
}
add_filter( 'excerpt_length', 'wpdocs_custom_excerpt_length', 20 );





/*--------------------------------------------------------------
19.0 INITIALIZE SIDEBAR WIDGET AREAS
--------------------------------------------------------------*/

add_action( 'widgets_init', 'theme_slug_widgets_init' );
function theme_slug_widgets_init() {
    register_sidebar( array(
        'name' => __( 'Project Sidebar', 'theme-slug' ),
        'id' => 'sidebar-1',
        'description' => __( 'Widgets in this area will be shown on all posts and pages.', 'theme-slug' ),
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget'  => '</li>',
        'before_title'  => '<h2 class="widgettitle">',
        'after_title'   => '</h2>',
    ) );
}





/*--------------------------------------------------------------
20.0 DASHBOARD ARRANGEMENTS
--------------------------------------------------------------*/


function child_remove_parent_function() {
    remove_action( 'init', 'thb_goodlife_custom_theme_options', 2 );
}
add_action( 'admin_init', 'child_remove_parent_function' );
add_action( 'admin_init', 'thb_goodlife_custom_theme_options2', 3 );

function thb_goodlife_custom_theme_options2() {
  $saved_settings = get_option( 'option_tree_settings', array() );
  if ( $saved_settings !== $custom_settings ) {
    update_option( 'option_tree_settings', $custom_settings );
  }
}

function hide_menu() {
  remove_menu_page( 'themes.php' );
  add_menu_page( 'Menus', 'Menus', 'edit_theme_options', 'nav-menus.php', '', 'dashicons-admin-generic' );
  add_menu_page( 'Sidebar Widgets', 'Sidebar Widgets', 'edit_theme_options', 'widgets.php', '', 'dashicons-admin-generic' );
}

add_action('admin_menu', 'hide_menu');



/*-------------------------------------
Move Options Page to Bottom of Dashboard
---------------------------------------*/

function custom_menu_order( $menu_ord ) {

    if (!$menu_ord) return true;
    $menu = 'acf-options';
    $menu_ord = array_diff($menu_ord, array( $menu ));
    array_splice( $menu_ord, 23, 0, array( $menu ) );

    return $menu_ord;
}

add_filter('custom_menu_order', 'custom_menu_order');
add_filter('menu_order', 'custom_menu_order');


/*-------------------------------------
Move Yoast to the Bottom
---------------------------------------*/
function yoasttobottom() {
  return 'low';
}
add_filter( 'wpseo_metabox_prio', 'yoasttobottom');


/*-------------------------------------
Set extra media sizes
---------------------------------------*/

add_action( 'init', 'set_thumbnail' );
function set_thumbnail(){
  set_post_thumbnail_size( 300, 200, true );
}





/*--------------------------------------------------------------
20.0 COMPANY A-Z FILTER
--------------------------------------------------------------*/

add_action('wp_ajax_nopriv_companyaz_search', 'companyaz_search');
add_action('wp_ajax_companyaz_search', 'companyaz_search');
function companyaz_search(){
 $sort=$_POST['sort'];
  $list = array();
  $item = array();
foreach($_POST as $key => $value){
   if((($key == 'sector') || ($key == 'products_services' )) && ($value != '0')){
    $item['taxonomy'] = htmlspecialchars($key);
    $item['terms'] = htmlspecialchars($value);
    $item['field'] = 'id';
    $list[] = $item;
  }
}
$taxArray = array_merge(array('relation' => 'AND'), $list);
?>

<?php
$args['post_type'] = 'storefronts';
if($sort!='date' || $sort=='0'){
  $args['meta_key'] = 'alternativeazlisting';
  $args['orderby'] = 'meta_value';
  $args['order'] = 'ASC';
}
$args['showposts'] = -1;
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$args['paged'] = $paged;
$args['post_status'] ='publish';
$args['tax_query'] = $taxArray;
ob_start ();
  ?>

<?php if($sort!='date' || $sort=='0'){ ?>
    <div class="row">
      <div class="listing-letters">
        <?php
        $arr = range( 'a', 'z');
        array_unshift($arr, "[0-9]");
        for($i=0;$i<count($arr);$i++){?>
            <?php $request = $arr[$i]; ?>
            <?php if($request=='[0-9]'){
              $letter='#';
            }else{
              $letter=$request;
            }
            ?>
            <a href="<?php echo the_permalink()."#$letter";?>"><?php echo strtoupper($letter);?></a>
        <?php } ?>
      </div>
      <div class="large-8 small-12 column">
        <?php
        for($i=0;$i<count($arr);$i++)
        {
            $request = $arr[$i];
            $metaArray=array(
                array(
                    'key' => 'alternativeazlisting',
                    'value' => "^$request" ,
                    'compare' => 'REGEXP',

                ),
                array(
                    'key' => 'type',
                    'value' => 'standard' ,
                    'compare' => '=',
                 )
            );
            $args['meta_query']=$metaArray;
            $query = new WP_Query( $args );
             if ($query->have_posts() )  {?>
             <?php if($request=='[0-9]'){
              $letter='#';
            }else{
              $letter=$request;
            }
            ?>
            <div id="<?php echo $letter ?>" class="large-12 small-12 column listing_content">
              <h3 class="alpha_letter"><?php echo strtoupper($letter);?></h3>
                <ul>
                  <?php
                    while ($query->have_posts()) {
                     $query->the_post(); ?>
                        <li>
                            <a href="<?php the_permalink();?>"><span class="companyaz_name"><?php the_field('alternativeazlisting');?>,</span><?php the_field('title');?></a>
                        </li>
                        <?php
                    }
                    ?>
                  </ul>
              </div>
            <?php
             }
        }
        ?>
      </div>
    <?php get_sidebar('category'); ?>
  </div>
<?php }else{ ?>
      <div class="row">
       <div class="large-8 small-12 column">
          <?php $query = new WP_Query( $args );
           if ($query->have_posts()) :  while ($query->have_posts()) : $query->the_post(); ?>
          <div class="large-12 small-12 column listing_content_result">
            <ul>
              <li>
                  <a href="<?php the_permalink();?>"><span class="companyaz_name"><?php the_field('alternativeazlisting');?>,</span><?php the_field('title');?></a>
              </li>
              </ul>
          </div>
          <?php endwhile; ?>
          <?php else : ?>
          <?php get_template_part( 'inc/loop/notfound' ); ?>
          <?php endif; ?>
        </div>
        <?php get_sidebar('category'); ?>
      </div>
<?php } ?>
 <?php
  wp_reset_postdata();
     $response = ob_get_contents();
     ob_end_clean();
     echo $response;
  die(1);
}






/*--------------------------------------------------------------
21.0 CHECKING MOBILE ON BACK-END
--------------------------------------------------------------*/

function checkmobile(){
   $useragent=$_SERVER['HTTP_USER_AGENT'];
     if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
     return true;
   }else{
     return false;
   }
}






/*--------------------------------------------------------------
22.0 SORT MY SITES ON THE NETWORK ADMIN DASHBOARD
--------------------------------------------------------------*/

add_filter('get_blogs_of_user','sort_my_sites');
function sort_my_sites($blogs) {
        $f = create_function('$a,$b','return strcasecmp($a->blogname,$b->blogname);');
        uasort($blogs, $f);
        return $blogs;
}






/*--------------------------------------------------------------
23.0 COUNT NUMBER OF ANY GIVEN POST TYPE IN THE TAXONOMY TERM
--------------------------------------------------------------*/
function count_posts_in_term($taxonomy, $term, $postType) {
    $query = new WP_Query([
        'posts_per_page' => 0,
        'post_type' => $postType,
        'tax_query' => [
            [
                'taxonomy' => $taxonomy,
                'terms' => $term,
                'field' => 'slug'
            ]
        ]
    ]);
    return $query->found_posts;
}
?>