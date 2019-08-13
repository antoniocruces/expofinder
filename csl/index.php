<?php

	if ( ( is_single() || is_front_page() || is_page() )  && !is_page( 'login' ) && !is_user_logged_in() ) { 
    auth_redirect(); 
} 
	
global $wp_query;
global $current_user;
wp_get_current_user();

$user_age = sprintf( 
	__( 'Registered %s ago', CSL_TEXT_DOMAIN_PREFIX ), 
	human_time_diff( strtotime( get_userdata( $current_user->ID )->user_registered ), current_time('timestamp') )
	);
$user_role = csl_get_current_user_role();
$admin_lnk = in_array( csl_get_current_user_role( false ), array( 'test_subscribers', 'subscribers' ) ) ? admin_url( 'profile.php' ) : admin_url();

//is_post_type_archive() & is_404()

$filter = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$filter_color = $filter == '' ? '' : CSL_CUSTOM_POST_COLOR_ARRAY[$filter];

$all_valid_posts = array_merge( CSL_CUSTOM_POST_TYPE_ARRAY, array ( 'page' ) );
$args = array_merge( $wp_query->query_vars, array( 'post_type' => isset($_REQUEST['type']) ? $_REQUEST['type'] : $all_valid_posts ) ); 
query_posts($args);
if(!in_array($filter, CSL_CUSTOM_POST_TYPE_ARRAY) && $filter !== '' ) {
	$wp_query->set_404();
	status_header(404);
}

$message    = isset($_GET['debug']) && $csl_s_user_is_manager ? csl_list_performance() : ""; 
$filter_url = get_permalink( get_option( 'page_for_posts' ) ) . ( get_search_query() == '' ? '/?type=' : '/?s=' .  get_search_query()  . '&type=' );
$filter_txt = $filter == '' ? __( 'No filtered', CSL_TEXT_DOMAIN_PREFIX ) : __( 'Filtered by', CSL_TEXT_DOMAIN_PREFIX ) . ' <strong>' . strtolower(get_post_type_object( $filter )->labels->name) . '</strong>';
$filter_col = $filter == '' ? 'default' : CSL_CUSTOM_POST_COLOR_ARRAY[$filter];
$post_count = number_format_i18n( $wp_query->found_posts, 0 );

$sURL_type  = get_bloginfo('url');

$userevals  = csl_survey_evaluations_by_user( $current_user->ID );
$bpageeval  = !$post ? false : csl_survey_current_page_has_been_evaluated( $post->ID, $current_user->ID ); 
$pageiseval = $bpageeval  
    ?
    '<span class="dashicons dashicons-awards" style="color: #5cb85c;"></span>' 
    : 
    '<span class="dashicons dashicons-awards" style="color: #d9534f;"></span>' 
    ;
$suserevals = sprintf(
    __( '%s evaluated, %s of %s pages', CSL_TEXT_DOMAIN_PREFIX ),
    number_format_i18n( round( ( $userevals['eval_pages'] / $userevals['total_pages'] ) * 100 , 0 ), 0 ) . '%',
    number_format_i18n( $userevals['eval_pages'], 0 ),  
    number_format_i18n( $userevals['total_pages'], 0 )  
);
$levelevals  = round( ( $userevals['eval_pages'] / $userevals['total_pages'] ) * 100, 0 );
$levelcolor  = $levelevals < 25 ? "#d9534f" : ( $levelevals > 75 ? "#5cb85c" : "#f0ad4e" );
$barevals    = '
<div style="background: #ddd; width: 100%; height: 20px;">
	<div style="width: ' . $levelevals . '%; background: ' . $levelcolor . '; height: 20px;"></div>        
</div>
';

switch (1) {
	case is_single():
	case is_page():
		$sURL_type = get_permalink();
		break;
	case is_tax():
		$sURL_type = get_term_link( get_queried_object()->term_id, get_queried_object()->taxonomy );
		break;
	case is_search():
		$sURL_type = get_bloginfo('url')."/index.php?s=" . get_search_query() . (isset($_REQUEST['type']) ? "&type=" . $_REQUEST['type'] : "") . "&submit=";
		break;
	default:
		break;
} 

?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php bloginfo('name'); ?> | <?php if( is_home() ) : echo bloginfo( 'description' ); endif; ?><?php wp_title( '', true ); ?></title>
    
    <link rel="profile" href="http://gmpg.org/xfn/11" />
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="<?php echo get_template_directory_uri(); ?>/assets/css/ie10-viewport-bug-workaround.css" rel="stylesheet">

    
    <?php wp_head(); ?>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body <?php body_class(); ?>>
	<!-- Fixed navbar -->
	<nav class="navbar navbar-default navbar-fixed-top non-printable">
		<div class="container">
			<div class="navbar-header" id="tour-home-link">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
					<span class="sr-only"><?php _e('Toggle navigation', CSL_TEXT_DOMAIN_PREFIX); ?></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
					<?php echo CSL_LOGO; //bloginfo( 'name' ); ?>
				</a>
			</div>
			<div id="navbar" class="collapse navbar-collapse">
				<?php 
					wp_nav_menu( 
						array(
							'menu'       => 'primary',
							'depth'      => 2,
							'container'  => false,
							'menu_class' => 'nav navbar-nav',
							'walker'     => new wp_bootstrap_navwalker()
						)
					);
					
				?>

				<form class="navbar-form navbar-right" method="get" id="search-form" action="<?php echo $sURL_type; ?>">
                    <button id="tour-ef" class="btn btn-info"><span class="glyphicon glyphicon-education" data-demo></span>&nbsp;<?php _e( 'Guided tour', CSL_TEXT_DOMAIN_PREFIX ); ?></button>
					<div class="btn-group" role="group" id="tour-user-menu">
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<?php echo csl_user_initials( $current_user->ID ); ?>
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							<li><a href="<?php echo admin_url( '/profile.php' ); ?>"><strong><?php echo $current_user->display_name; ?></strong></a></li>
							<li role="separator" class="divider"></li>
							<li class="disabled"><a href="#"><?php echo $user_age; ?></a></li>
							<li class="disabled"><a href="#"><?php echo $user_role; ?></a></li>
							<li role="separator" class="divider"></li>
                            <li class="dropdown-header"><?php _e( 'Site evaluation', CSL_TEXT_DOMAIN_PREFIX ); ?></li>
							<li class="disabled"><a href="#"><?php echo $suserevals; ?></a></li>
							<li class="disabled"><a href="#"><?php echo $barevals; ?></a></li>
							<li role="separator" class="divider"></li>
							<li><a href="<?php echo $admin_lnk; ?>"><?php _e( 'Go to backend', CSL_TEXT_DOMAIN_PREFIX ); ?></a></li>
							<li><a href="<?php echo wp_logout_url(); ?>"><?php _e( 'Logout', CSL_TEXT_DOMAIN_PREFIX ); ?></a></li>
						</ul>
					</div>
                </form>
			</div><!--/.nav-collapse -->
		</div>
	</nav>
	
	<div class="container">

        <?php echo $message; ?>
<?php

/*-----------------------------------------------------------------------------------*/
/* Start Home loop
/*-----------------------------------------------------------------------------------*/

if( ( is_home() || is_archive() || is_search() || is_tax() ) && !is_404() ) {
	if ( have_posts() ) : 
		if( is_tax() ) {
		    global $wp_query;
		    $term = $wp_query->get_queried_object();
		    $filter_txt .= '. ' . sprintf( __( 'Taxonomy: %s', CSL_TEXT_DOMAIN_PREFIX ), '<em>' . $term->name . '</em>' );
		}
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		$total_post_count = wp_count_posts();
		$published_post_count = $total_post_count->publish;
		$total_pages = ceil( $published_post_count / $posts_per_page );
		
		if ( "0" < $paged ) :
			echo '<div class="page-header">' . PHP_EOL;
			echo '<h1>' . PHP_EOL;
            echo __( 'Data bank', CSL_TEXT_DOMAIN_PREFIX );
			echo '</h1>' . PHP_EOL;
			echo '<h2>' . PHP_EOL;
            echo get_search_query() 
            	? 
            	sprintf( 
            		__( 'Searching for %s', CSL_TEXT_DOMAIN_PREFIX ), 
            		'<em>' . get_search_query() . '</em>'
            	)
            	: 
            	__( 'Showing all records', CSL_TEXT_DOMAIN_PREFIX );
			echo '</h2>' . PHP_EOL;
			echo '</div>' . PHP_EOL;
            
            echo '<div class="row">' . PHP_EOL;
            echo '<div class="col-md-12">' . PHP_EOL;
			echo '<div class="panel panel-' . $filter_col . '" id="tour-searchform">' . PHP_EOL;
			echo '<div class="panel-heading"><h3 class="panel-title">' . $filter_txt . '</h3></div>' . PHP_EOL;
            echo '<div class="panel-body">' . PHP_EOL;
?>
        <form class="form-inline" method="get" id="search-form" action="<?php echo $sURL_type; ?>">
<?php
            echo sprintf( 
            	__('%s records. Page %s of %s', CSL_TEXT_DOMAIN_PREFIX ), 
            	'<code>' . $post_count . '</code>', 
            	'<code>' . $paged . '</code>', 
            	'<code>' . number_format_i18n($wp_query->max_num_pages, 0) . '</code>' 
            ); 

?>        
            <?php if( $filter !== '' ) { ?>
            <input type="hidden" name="type" id="type" value="<?php echo $filter; ?>" />            
            <?php } ?>
            <input type="hidden" name="search_param" value="all" id="search_param">
			<div class="form-group"> 
				<input type="text" placeholder="<?php _e( 'Search ', CSL_TEXT_DOMAIN_PREFIX ); ?>" class="form-control" name="s" id="s" value="<?php echo get_search_query(); ?>">
				<button type="submit" class="btn btn-default">
					<?php _e( 'Search ', CSL_TEXT_DOMAIN_PREFIX ); ?>
					<span class="glyphicon glyphicon-search separate-left"></span>
				</button>
                
                <div class="btn-group">      
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<?php _e( 'Filter ', CSL_TEXT_DOMAIN_PREFIX ); ?> <i class="fa fa-filter"></i>
					<span class="caret"></span>
					<span class="sr-only"><?php _e( 'Toggle dropdown', CSL_TEXT_DOMAIN_PREFIX ); ?></span>
				</button>
				<ul class="dropdown-menu">
					<li<?php echo $filter ==  CSL_CUSTOM_POST_ENTITY_TYPE_NAME ? ' class="active"' : ''; ?>>
						<a href="<?php echo $filter_url . CSL_CUSTOM_POST_ENTITY_TYPE_NAME; ?>">
							<span class="label label-<?php echo CSL_CUSTOM_POST_ENTITY_COLOR; ?> separate-right">
								<?php echo strtoupper( substr( get_post_type_object( CSL_CUSTOM_POST_ENTITY_TYPE_NAME )->labels->singular_name, 0, 3 ) ); ?></span>
							<?php echo get_post_type_object( CSL_CUSTOM_POST_ENTITY_TYPE_NAME )->labels->name; ?>
						</a>
					</li>
					<li<?php echo $filter ==  CSL_CUSTOM_POST_PERSON_TYPE_NAME ? ' class="active"' : ''; ?>>
						<a href="<?php echo $filter_url . CSL_CUSTOM_POST_PERSON_TYPE_NAME; ?>">
							<span class="label label-<?php echo CSL_CUSTOM_POST_PERSON_COLOR; ?> separate-right">
								<?php echo strtoupper( substr( get_post_type_object( CSL_CUSTOM_POST_PERSON_TYPE_NAME )->labels->singular_name, 0, 3 ) ); ?></span>
							<?php echo get_post_type_object( CSL_CUSTOM_POST_PERSON_TYPE_NAME )->labels->name; ?>
						</a>
					</li>
					<li<?php echo $filter ==  CSL_CUSTOM_POST_BOOK_TYPE_NAME ? ' class="active"' : ''; ?>>
						<a href="<?php echo $filter_url . CSL_CUSTOM_POST_BOOK_TYPE_NAME; ?>">
							<span class="label label-<?php echo CSL_CUSTOM_POST_BOOK_COLOR; ?> separate-right">
								<?php echo strtoupper( substr( get_post_type_object( CSL_CUSTOM_POST_BOOK_TYPE_NAME )->labels->singular_name, 0, 3 ) ); ?></span>
							<?php echo get_post_type_object( CSL_CUSTOM_POST_BOOK_TYPE_NAME )->labels->name; ?>
						</a>
					</li>
					<li<?php echo $filter ==  CSL_CUSTOM_POST_COMPANY_TYPE_NAME ? ' class="active"' : ''; ?>>
						<a href="<?php echo $filter_url . CSL_CUSTOM_POST_COMPANY_TYPE_NAME; ?>">
							<span class="label label-<?php echo CSL_CUSTOM_POST_COMPANY_COLOR; ?> separate-right">
								<?php echo strtoupper( substr( get_post_type_object( CSL_CUSTOM_POST_COMPANY_TYPE_NAME )->labels->singular_name, 0, 3 ) ); ?></span>
							<?php echo get_post_type_object( CSL_CUSTOM_POST_COMPANY_TYPE_NAME )->labels->name; ?>
						</a>
					</li>
					<li<?php echo $filter ==  CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME ? ' class="active"' : ''; ?>>
						<a href="<?php echo $filter_url . CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME; ?>">
							<span class="label label-<?php echo CSL_CUSTOM_POST_EXHIBITION_COLOR; ?> separate-right">
								<?php echo strtoupper( substr( get_post_type_object( CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME )->labels->singular_name, 0, 3 ) ); ?></span>
							<?php echo get_post_type_object( CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME )->labels->name; ?>
						</a>
					</li>
					<li class="divider"></li>
					<li<?php echo $filter ==  '' ? ' class="active"' : ''; ?>>
						<a href="<?php echo get_permalink( get_option('page_for_posts') ); ?>">
							<span class="label label-default separate-right">
								<?php echo strtoupper( substr( __( 'Anything', CSL_TEXT_DOMAIN_PREFIX ), 0, 3 ) ); ?></span>
							<?php _e( 'Anything', CSL_TEXT_DOMAIN_PREFIX ); ?>
						</a>
					</li>
				</ul>
                </div>
			</div>
		</form>	
        
<?php
			echo '</div>' . PHP_EOL;
            echo '</div>' . PHP_EOL;
            echo '</div>' . PHP_EOL;
            echo '</div>' . PHP_EOL;
        else : 
            echo $filter_txt;        
		endif; 

		while ( have_posts() ) : the_post(); 
			if( 'page' != $post->post_type && 'post' != $post->post_type ) {
                $pvalidtitle = get_the_title() ? get_the_title() : '<em>[' . __( 'Untitled record', CSL_TEXT_DOMAIN_PREFIX ) . ']</em>';
?>
		<div class="row margin-bottom-25 border-bottom">
			<div class="col-md-2">
				<p>
					<span class="label label-<?php echo CSL_CUSTOM_POST_COLOR_ARRAY[get_post_type()]; ?>">
					<?php echo strtoupper( substr( get_post_type_object( get_post_type() )->labels->singular_name, 0, 3 ) ); ?></span>
				</p>
				<p>
					<?php echo csl_user_initials( get_the_author_meta('ID') ) . '. ' . sprintf( __( '%s ago', CSL_TEXT_DOMAIN_PREFIX ), human_time_diff( strtotime (get_the_time( ) ) , time() ) ) . '.'; ?>
				</p>
				<p>
					<?php echo sprintf( __( 'ID: %s', CSL_TEXT_DOMAIN_PREFIX ), get_the_ID() ); ?> 
				</p>
			</div>
			<div class="col-md-10">
				<h3 class="no-margin-top">
					<a href="<?php the_permalink(); ?>" title="<?php echo $pvalidtitle; ?>">                                
						<span class="csl-<?php echo get_post_type(); ?>"><?php echo $pvalidtitle; ?></span>
					</a>
				</h3>
				
				<p>
					<?php //echo wp_trim_words( wp_strip_all_tags( get_the_excerpt() ) ); ?>
				</p>
				
				<p>
					<?php wp_link_pages(); ?>
				</p>
				<p><?php echo csl_custom_taxonomies_terms_links( $post->ID ); ?></p>
				<p><?php echo get_the_category_list(); ?></p>
				<p><?php echo get_the_tag_list( '| &nbsp;', '&nbsp;' ); ?></p>
			</div>
		</div>

<?php
			}
		endwhile; 
		csl_bootstrap_pagination();
    else : 

			echo '<div class="page-header">' . PHP_EOL;
			echo '<h1>' . PHP_EOL;
            echo __( 'No records to display.', CSL_TEXT_DOMAIN_PREFIX );
            echo ' ';
            echo get_search_query() 
            	? 
            	sprintf( 
            		__( 'Searching for %s', CSL_TEXT_DOMAIN_PREFIX ), 
            		'<em>' . get_search_query() . '</em>'
            	)
            	: 
            	__( 'Showing all records', CSL_TEXT_DOMAIN_PREFIX );
			echo '</h1>' . PHP_EOL;
			echo '</div>' . PHP_EOL;
            echo '<div class="row">' . PHP_EOL;
            echo '<div class="col-md-12">' . PHP_EOL;
			echo '<div class="panel panel-' . $filter_col . '">' . PHP_EOL;
			echo '<div class="panel-heading"><h3 class="panel-title">' . $filter_txt . '</h3></div>' . PHP_EOL;
            echo '<div class="panel-body">' . PHP_EOL;
            echo sprintf( 
            	__('%s records. Page %s of %s', CSL_TEXT_DOMAIN_PREFIX ), 
            	'<code>' . $post_count . '</code>', 
            	'<code>' . $paged . '</code>', 
            	'<code>' . number_format_i18n($wp_query->max_num_pages, 0) . '</code>' 
            ); 
			echo '</div>' . PHP_EOL;
            echo '</div>' . PHP_EOL;
            echo '</div>' . PHP_EOL;
            echo '</div>' . PHP_EOL;
    endif; 
?>
		
<?php } //end is_home(); ?>

<?php
	wp_reset_query();

	/*-----------------------------------------------------------------------------------*/
	/* Start Single loop
	/*-----------------------------------------------------------------------------------*/
	
	if( is_single() ) {
?>

			<?php if ( have_posts() ) : ?>

				<?php while ( have_posts() ) : the_post(); ?>

				<div class="page-header">
					<h1 class="title"><?php the_title() ?></h1>
                </div>
                
                <?php if( in_array( $post->post_type, CSL_CUSTOM_POST_TYPE_ARRAY ) ) : ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-<?php echo CSL_CUSTOM_POST_COLOR_ARRAY[get_post_type()]; ?>">
                            <div class="panel-leftheading">
                                <h3 class="panel-lefttitle"><?php echo __( 'Data sheet', CSL_TEXT_DOMAIN_PREFIX ); ?></h3>
                            </div>
                            <div class="panel-rightbody">
                                <ul>
                                    <li><?php echo sprintf( __( 'Record ID: %s', CSL_TEXT_DOMAIN_PREFIX ), '<strong>' . $post->ID . '</strong>' ); ?></li>
                                    <!--
                                    <li><?php //echo sprintf( __( 'Record author: %s', CSL_TEXT_DOMAIN_PREFIX ), '<strong>' . get_the_author() . '</strong>' ); ?></li>
                                    <li><?php //echo sprintf( __( 'Record author position: %s', CSL_TEXT_DOMAIN_PREFIX ), '<strong>' . get_the_author_meta( 'position' ) . '</strong>' ); ?></li>
                                    -->
                                    <li><?php echo sprintf( __( 'Record date created: %s', CSL_TEXT_DOMAIN_PREFIX ), '<strong>' . sprintf( __( '%s ago', CSL_TEXT_DOMAIN_PREFIX ), human_time_diff( strtotime ( $post->post_date ) , time() ) ) . ' [' . strtolower( get_the_date( ) . ', ' . get_the_time( ) ) . ']</strong>' ); ?></li>
                                    <li><?php echo sprintf( __( 'Record date updated: %s', CSL_TEXT_DOMAIN_PREFIX ), '<strong>' . sprintf( __( '%s ago', CSL_TEXT_DOMAIN_PREFIX ), human_time_diff( strtotime ( $post->post_modified ) , time() ) ) . ' [' . strtolower( get_the_modified_date( ) . ', ' . get_the_modified_time( ) ) . ']</strong>' ); ?></li>
                                    <!--
                                    <li><?php echo sprintf( __( 'Content words count: %s', CSL_TEXT_DOMAIN_PREFIX ), '<strong>' . number_format_i18n( str_word_count( strip_tags( get_post_field( 'post_content', $post->ID ) ) ), 0 ) . '</strong>' ); ?></li>
                                    -->
                                    <?php
                                        if( in_array( $post->post_type, CSL_CUSTOM_POST_TYPE_ARRAY ) ) {
                                            echo '<li>' . sprintf( __('Taxonomies: %s', CSL_TEXT_DOMAIN_PREFIX), '<strong>' . csl_custom_taxonomies_terms_links( $post->ID ) . '</strong>' ) . '</li>' . PHP_EOL;
                                        }
                                    ?>
                                </ul>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>

                <?php if( trim( get_the_excerpt() ) !== '' ) : ?> 
                <!--
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-<?php echo CSL_CUSTOM_POST_COLOR_ARRAY[get_post_type()]; ?>">
                            <div class="panel-leftheading">
                                <h3 class="panel-lefttitle"><?php echo __( 'Notes', CSL_TEXT_DOMAIN_PREFIX ); ?></h3>
                            </div>
                            <div class="panel-rightbody">
                                <?php
        	                    the_excerpt( __( 'Continue&hellip;', CSL_TEXT_DOMAIN_PREFIX ) );
                                ?>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
                -->
                <?php endif; ?>        
                
                <?php if( trim( get_the_content() ) !== '' ) : ?> 
                <!--       
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-<?php echo CSL_CUSTOM_POST_COLOR_ARRAY[get_post_type()]; ?>">
                            <div class="panel-leftheading">
                                <h3 class="panel-lefttitle"><?php echo __( 'Text', CSL_TEXT_DOMAIN_PREFIX ); ?></h3>
                            </div>
                            <div class="panel-rightbody">
                                <?php
        	                    the_content( __( 'Continue&hellip;', CSL_TEXT_DOMAIN_PREFIX ) );
                                ?>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
                -->
                <?php endif; ?>        

                <?php
                    $custom_fields = sanitize_record_custom_fields(get_post_custom());
                    if(!empty($custom_fields)) {
                        echo '<div class="row">' . PHP_EOL;
                        echo '<div class="col-md-12">' . PHP_EOL;
                        echo '<div class="panel panel-' . CSL_CUSTOM_POST_COLOR_ARRAY[get_post_type()] . '">' . PHP_EOL;
                        echo '<div class="panel-leftheading">' . PHP_EOL;
                        echo '<h3 class="panel-lefttitle">' . __( 'Metadata', CSL_TEXT_DOMAIN_PREFIX ) . '</h3>' . PHP_EOL;
                        echo '</div>' . PHP_EOL;
                        echo '<div class="panel-rightbody">' . PHP_EOL;
                        echo '<table class="table table-striped table-hover table-condensed full-width-table">' . PHP_EOL;
                        echo '<thead>' . PHP_EOL;
                        echo '<tr><th>' . __('Field', CSL_TEXT_DOMAIN_PREFIX) . '</th><th>' . __('Value', CSL_TEXT_DOMAIN_PREFIX) . '</th></tr>' . PHP_EOL;
                        echo '</thead>' . PHP_EOL;
                        echo '<tbody>' . PHP_EOL;
                        foreach($custom_fields as $key => $value) {
                            echo '<tr>' . PHP_EOL;
                            switch($key) {
                                case __('Coordinates', CSL_TEXT_DOMAIN_PREFIX):
                                    echo '<td scope="row">' . __('Location map', CSL_TEXT_DOMAIN_PREFIX) . '</td>' . PHP_EOL;
                                    echo '<td><a class="static-map" href="https://www.google.com/maps/dir//' . 
                                        rtrim($custom_fields[__('Coordinates', CSL_TEXT_DOMAIN_PREFIX)][0], ",0") .
                                        '/">
                                        <img src="http://maps.googleapis.com/maps/api/staticmap?scale=false&size=600x300&maptype=roadmap&format=png&
                                        visual_refresh=true&markers=size:small%7Ccolor:0xffff00%7Clabel:1%7C' . 
                                        rtrim($custom_fields[__('Coordinates', CSL_TEXT_DOMAIN_PREFIX)][0], ",0") . 
                                        '" alt="' . 
                                        sprintf(__('%s location', CSL_TEXT_DOMAIN_PREFIX), get_the_title() ) . 
                                        '"></a></td>';
                                    break;
                                case __('EMail', CSL_TEXT_DOMAIN_PREFIX):
                                case __('URL', CSL_TEXT_DOMAIN_PREFIX):
                                case __('RSS URI', CSL_TEXT_DOMAIN_PREFIX):
                                case __('HTML URI', CSL_TEXT_DOMAIN_PREFIX):
                                case __('Original source link', CSL_TEXT_DOMAIN_PREFIX):
                                    echo '<td scope="row">' . $key . '</td><td><strong>' .  join(', ', csl_set_href_for_urls_array($value, true, true, false, 45)) . '</strong></td>' . PHP_EOL;
                                    break;
                                case __('Parent entity', CSL_TEXT_DOMAIN_PREFIX):
                                case __('Entity relation', CSL_TEXT_DOMAIN_PREFIX):
                                case __('Paper author', CSL_TEXT_DOMAIN_PREFIX):
                                case __('Paper editor', CSL_TEXT_DOMAIN_PREFIX):
                                case __('Paper illustrator', CSL_TEXT_DOMAIN_PREFIX):
                                case __('Artwork', CSL_TEXT_DOMAIN_PREFIX):
                                case __('Sponsorship', CSL_TEXT_DOMAIN_PREFIX):
                                case __('Source entity', CSL_TEXT_DOMAIN_PREFIX):
                                case __('Parent exhibition', CSL_TEXT_DOMAIN_PREFIX):
								case __('Information source', CSL_TEXT_DOMAIN_PREFIX):
								case __('Artwork author', CSL_TEXT_DOMAIN_PREFIX):
								case __('Supporter entity', CSL_TEXT_DOMAIN_PREFIX):
								case __('Funding entity', CSL_TEXT_DOMAIN_PREFIX):
								case __('Curator', CSL_TEXT_DOMAIN_PREFIX):
								case __('Catalog', CSL_TEXT_DOMAIN_PREFIX):
								case __('Company responsible for the museography', CSL_TEXT_DOMAIN_PREFIX):		            		
                                    echo '<td scope="row">' . $key . '</td><td><strong>' .  join(', ', csl_set_href_for_urls_array($value, true, true, true, 45)) . '</strong></td>' . PHP_EOL;
                                	break;
                                case __('CRC32B identifier', CSL_TEXT_DOMAIN_PREFIX):
                                case __('Global keywords weight', CSL_TEXT_DOMAIN_PREFIX):
                                case __('Relative keywords weight', CSL_TEXT_DOMAIN_PREFIX):
                                case __('Found keywords', CSL_TEXT_DOMAIN_PREFIX):
                                    break;
                                default:                                
                                    echo '<td scope="row">' . $key . '</td><td><strong>' . join(', ', $value) . '</strong></td>' . PHP_EOL;
                                    break;
                            }
                            echo '</tr>' . PHP_EOL;
                        }
                        echo '</tbody></table>' . PHP_EOL;
                        
                        echo '</div>' . PHP_EOL;
                        echo '<div class="clearfix"></div>' . PHP_EOL;
                        echo '</div>' . PHP_EOL;
                        echo '</div>' . PHP_EOL;
                        echo '</div>' . PHP_EOL;
                    }

            		$attachments = get_posts( array(
            			'post_type' => 'attachment',
            			'posts_per_page' => -1,
            			'post_parent' => $post->ID,
            			'exclude'     => get_post_thumbnail_id()
            		) );
            
            		if ( $attachments ) {
                        echo '<div class="row">' . PHP_EOL;
                        echo '<div class="col-md-12">' . PHP_EOL;
                        echo '<div class="panel panel-' . CSL_CUSTOM_POST_COLOR_ARRAY[get_post_type()] . '">' . PHP_EOL;
                        echo '<div class="panel-leftheading">' . PHP_EOL;
                        echo '<h3 class="panel-lefttitle">' . __( 'Attachments', CSL_TEXT_DOMAIN_PREFIX ) . '</h3>' . PHP_EOL;
                        echo '</div>' . PHP_EOL;
                        echo '<div class="panel-rightbody">' . PHP_EOL;
            			foreach ( $attachments as $attachment ) {
            				$class = "post-attachment mime-" . sanitize_title( $attachment->post_mime_type );
            				$thumbimg = wp_get_attachment_link( $attachment->ID, 'thumbnail-size', true );
            				echo '<p class="' . $class . ' image-border">' . $thumbimg . '<span class="image-caption">' . $attachment->post_title . '</span></p>';
            			}
            		}
                    wp_link_pages();
                ?> 
				<?php else : ?>

                <div class="row">
                    <div class="col-md-12">
                        <ul class="list-inline list-unstyled">
                            <li><span><i class="glyphicon glyphicon-calendar"></i> <?php echo sprintf( __( '%s ago', CSL_TEXT_DOMAIN_PREFIX ), human_time_diff( strtotime ( $post->post_date ) , time() ) ); ?></span></li>
                            <li>|</li>
                            <li><span><i class="glyphicon glyphicon-user"></i> <?php echo get_the_author(); ?></span></li>
                        </ul>
                    </div>
                </div>						

                <div class="row">
                    <div class="col-md-12">
						<?php the_content( __( 'Continue&hellip;', CSL_TEXT_DOMAIN_PREFIX ) ); ?>
                    </div>
                </div>						

                <div class="row" style="margin-bottom: 50px;">
                    <div class="col-md-12">
						<div class="category"><?php echo get_the_category_list(); ?></div>
						<div class="tags"><?php echo get_the_tag_list( '| &nbsp;', '&nbsp;' ); ?></div>
                    </div>
                </div>						

				<?php endif; ?>
                
                <?php endwhile; ?>
				
			<?php else : ?>
				
				<article class="post error">
					<h1 class="404">Nothing posted yet</h1>
				</article>

			<?php endif; ?>


	<?php } //end is_single(); ?>
	
<?php
	/*-----------------------------------------------------------------------------------*/
	/* Start Page loop
	/*-----------------------------------------------------------------------------------*/
	
	if( is_page() && !is_404() ) {
?>

			<?php if ( have_posts() ) : ?>

				<?php while ( have_posts() ) : the_post(); ?>

				<div class="page-header" style="border: 0px;">
					<?php if(!is_front_page()) : ?>
					<h1 class="title"><?php the_title() ?></h1>
					<?php endif; ?>
                </div>
                <div class="row">
                    <div class="col-md-12">
						<?php the_content(); ?>
						<?php wp_link_pages(); ?>
                    </div>
                </div>
				<?php endwhile; ?>

			<?php else : ?>
				
				<article class="post error">
					<h1 class="404">Nothing posted yet</h1>
				</article>

			<?php endif; ?>

	<?php } // end is_page(); ?>

		</div><!-- #content .site-content -->
	</div><!-- #primary .content-area -->

</div><!-- / container-->

<?php
	/*-----------------------------------------------------------------------------------*/
	/* Start Footer
	/*-----------------------------------------------------------------------------------*/
?>


<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php _e( 'Page Quality Evaluation Form', CSL_TEXT_DOMAIN_PREFIX ); ?></h4>
            </div>
            <div class="modal-body">
                <?php echo csl_survey_single_call(); ?>                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e( 'Close', CSL_TEXT_DOMAIN_PREFIX ); ?></button>
            </div>
        </div>
    </div>
</div>

<footer class="footer" role="contentinfo">
	<div class="container">
		<p class="text-muted">
            <span id="tour-stats">
	        <?php echo csl_load_stats_printing(); ?>            
            </span>
	        
	        <span class="pull-right">
            <small>
	        <a id="tour-legal" href="<?php echo get_home_url( null, CSL_LEGAL_PAGE_SLUG ); ?>"><?php echo __( 'Legal info', CSL_TEXT_DOMAIN_PREFIX ); ?></a>
            &nbsp;&verbar;&nbsp;
            <!-- Button trigger modal -->
            <?php echo $pageiseval; ?>
            <a id="evallink" href="#" data-toggle="modal" data-target="#myModal"><?php _e( 'Evaluation', CSL_TEXT_DOMAIN_PREFIX ); ?></a>
	        &nbsp;&verbar;&nbsp;
	        <span class="dashicons dashicons-thumbs-down" style="color: #d9534f;"></span>
            <a id="tour-bug" href="<?php echo get_permalink( get_page_by_path( basename( untrailingslashit( CSL_BUG_PAGE_SLUG ) ) )->ID ) . '?pid=' . $post->ID; ?>">
            <?php _e( 'Report bug', CSL_TEXT_DOMAIN_PREFIX ); ?></a>
	        &nbsp;&verbar;&nbsp;
			<?php echo CSL_NAME . ' v' . CSL_VERSION . '. <a href="https://es.wordpress.org/" target="_blank">WPAF</a> Engine v' . get_bloginfo('version') . ' ' . get_bloginfo('language'); ?>
            </small>
	        </span>
		</p>
	</div><!-- .container -->
</footer><!-- .footer -->

<?php wp_footer(); ?>

	<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
	<script src="<?php echo get_template_directory_uri(); ?>/assets/js/ie10-viewport-bug-workaround.js"></script>

</body>
</html>
