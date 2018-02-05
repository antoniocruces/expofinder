<?php
    
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

    <style>
    // Error template
    .error-template { 
        padding: 40px 15px;
        text-align: center; 
    }
    .error-actions { 
        margin-top:15px;
        margin-bottom:15px;
    }
    .error-actions .btn { 
        margin-right:10px;
    }    
    </style>
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
					<?php echo CSL_LOGO; ?>
				</a>
			</div>
		</div>
	</nav>
	
    <div class="container">
        <div class="jumbotron vertical-center">
            <h1><?php _e( '404 Error', CSL_TEXT_DOMAIN_PREFIX ); ?></h1>
                <p>
                    <span class="glyphicon glyphicon-exclamation-sign"></span>&nbsp;
                    <strong><?php _e( 'An error has occurred!', CSL_TEXT_DOMAIN_PREFIX ); ?></strong>&nbsp;
                    <?php _e( 'Requested page cannot be found in the system.', CSL_TEXT_DOMAIN_PREFIX ); ?>
                </p>
                <p class="text-muted">
                    <?php _e( 'The 404 or Not Found error message is a Hypertext Transfer Protocol (HTTP) standard response code, in computer network communications, to indicate that the client was able to communicate with a given server, but the server could not find what was requested.', CSL_TEXT_DOMAIN_PREFIX ); ?>    
                </p>
                <p class="text-muted">
                    <?php _e( 'Probably this error is because the address in the browser has been misspelled. Check for correctness.', CSL_TEXT_DOMAIN_PREFIX ); ?>    
                </p>
                <p><a class="btn btn-primary btn-lg" href="/" role="button"><span class="glyphicon glyphicon-home"></span> <?php _e( 'Go home page', CSL_TEXT_DOMAIN_PREFIX ); ?></a></p>
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
    			<?php echo CSL_NAME . ' v' . CSL_VERSION . '. <a href="https://es.wordpress.org/" target="_blank">WPAF</a> Engine v' . get_bloginfo('version') . ' ' . get_bloginfo('language'); ?>
                </small>
    	        </span>
    		</p>
    	</div><!-- .container -->
    </footer><!-- .footer -->

	<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
	<script src="<?php echo get_template_directory_uri(); ?>/assets/js/ie10-viewport-bug-workaround.js"></script>

</body>
</html>
