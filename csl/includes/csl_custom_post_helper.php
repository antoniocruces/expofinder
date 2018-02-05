<?php

class CSL_Custom_Post_Helper {
    public $post_type_name;
    public $post_type_args;
    public $post_type_labels;
    public $post_text_domain;
    public $data_prefix;
    
    /* Class constructor */
    public function __construct( $name, $prfx, $text_domain, $args = array(), $labels = array() )
    {
        // Set some important variables
        $this->post_type_name       = self::uglify( $name );
        $this->post_text_domain     = $text_domain;
        $this->post_type_args       = $args;
        $this->post_type_labels     = $labels;
        $this->data_prefix          = $prfx;

        // Add action to register the post type, if the post type doesnt exist
        if( ! post_type_exists( $this->post_type_name ) )
        {
            add_action( 'init', array( &$this, 'register_post_type' ) );
        }
        //Add the required js and css files
        if(is_admin())
        {
            add_action('admin_head', array( &$this, 'add_custom_js_css' ) );
            add_action('admin_head', array( &$this, 'add_custom_scripts' ) );
        }

        // Listen for the save post hook
        $this->save();
    }
    
    /* Method which registers the post type */
    public function register_post_type()
    {       
        //Capitilize the words and make it plural
        $name       = self::beautify( $this->post_type_name  );
        $plural     = self::pluralize( $name );

        // We set the default labels based on the post type name and plural. We overwrite them with the given labels.
        $labels = array_merge(
            // Default
            array(
                'name'               => _x( $plural, 'post type general name', $this->post_text_domain ),
                'singular_name'      => _x( $name, 'post type singular name', $this->post_text_domain ),
                'menu_name'          => _x( $plural, 'admin menu', $this->post_text_domain ),
                'name_admin_bar'     => _x( $name, 'add new on admin bar', $this->post_text_domain ),
                'add_new'            => _x( 'Add New', strtolower($name), $this->post_text_domain ), 
                'add_new_item'       => sprintf( __( 'Add new %s', $this->post_text_domain ), strtolower($name) ),
                'new_item'           => sprintf( __( 'New %s', $this->post_text_domain ), strtolower($name) ), 
                'edit_item'          => sprintf( __( 'Edit %s', $this->post_text_domain ), strtolower($name) ), 
                'view_item'          => sprintf( __( 'View %s', $this->post_text_domain ), strtolower($name) ), 
                'all_items'          => sprintf( __( 'All %s', $this->post_text_domain ), $plural ), 
                'search_items'       => sprintf( __( 'Search %s', $this->post_text_domain ), strtolower($plural) ),
                'parent_item_colon'  => sprintf( __( 'Parent %s:', $this->post_text_domain ), strtolower($plural) ), 
                'not_found'          => sprintf( __( 'No %s found', $this->post_text_domain ), strtolower($plural) ),
                'not_found_in_trash' => sprintf( __( 'No %s found in Trash', $this->post_text_domain ), strtolower($plural) ), 
            ),                
            // Given labels
            $this->post_type_labels
        );
        // Same principle as the labels. We set some default and overwite them with the given arguments.
        $args = array_merge(

            // Default
            array(
        		'labels'              => $labels,
        		'public'              => true,
        		'publicly_queryable'  => true,
        		'show_ui'             => true,
        		'show_in_menu'        => true,
                'show_in_nav_menus'   => true,
                'show_in_admin_bar'   => true,
                'exclude_from_search' => false,
        		'query_var'           => true,
        		'rewrite'             => array( 'slug' => strtolower($name) ),
        		'capability_type'     => 'post',
        		'has_archive'         => true,
        		'hierarchical'        => false,
        		'has_archive'         => true,
        		'menu_position'       => 5,
        		'menu_icon'           => 'dashicons-index-card',
        		'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
            ),

            // Given args
            $this->post_type_args

        );

        // Register the post type
        register_post_type( $this->post_type_name, $args );
    }
    
    /* Method to attach the taxonomy to the post type */
    public function add_taxonomy( $name, $args = array(), $labels = array(), $ptn = null )
    {
        if( ! empty( $name ) )
        {           
            // We need to know the post type name, so the new taxonomy can be attached to it.
            if(!$ptn) {
                $post_type_name = $this->post_type_name;
            } else {
                $post_type_name = $ptn; // must be an array
            }

            // Taxonomy properties
            $taxonomy_name      = self::uglify( $name );
            $taxonomy_labels    = $labels;
            $taxonomy_args      = $args;

            if( ! taxonomy_exists( $taxonomy_name ) )
                {
                    //unregister_taxonomy_for_object_type($taxonomy_name, $post_type_name);
                    //Capitilize the words and make it plural
                        $name       = self::beautify( $name );
                        $plural     = self::pluralize( $name );
                        // Default labels, overwrite them with the given labels.
                        $labels = array_merge(

                            // Default
                            array(
                                'name'                  => _x( $plural, 'taxonomy general name' ),
                                'singular_name'         => _x( $name, 'taxonomy singular name' ),
                                'search_items'          => __( 'Search' ) . ' ' . $plural,
                                'all_items'             => __( 'All' ) . ' ' . $plural,
                                'parent_item'           => __( 'Parent' ) . ' ' . $name,
                                'parent_item_colon'     => __( 'Parent' ) . ' ' . $name . ':',
                                'edit_item'             => __( 'Edit' ) . ' ' . $name, 
                                'update_item'           => __( 'Update' ) . ' ' . $name,
                                'add_new_item'          => __( 'Add new' ) . ' ' . $name,
                                'new_item_name'         => __( 'New name for' ) . ' ' . $name,
                                'menu_name'             => __( $name ),
                            ),

                            // Given labels
                            $taxonomy_labels

                        );

                        // Default arguments, overwitten with the given arguments
                        $args = array_merge(

                            // Default
                            array(
                                'label'                 => $plural,
                                'labels'                => $labels,
                                'public'                => true,
                                'show_ui'               => true,
                                'show_in_nav_menus'     => true,
								'capabilities'			=> array(
									'manage_terms'	=> 'edit_others_posts',
									'edit_terms'	=> 'edit_others_posts',
									'delete_terms'	=> 'edit_others_posts',
									'assign_terms'	=> 'edit_posts',
								),
                            ),

                            // Given
                            $taxonomy_args

                        );

                        // Add the taxonomy to the post type
                        add_action( 'init',
                            function() use( $taxonomy_name, $post_type_name, $args )
                            {                       
                                register_taxonomy( $taxonomy_name, $post_type_name, $args );
                            }
                        );
                }
                else
                {
                    add_action( 'init',
                        function() use( $taxonomy_name, $post_type_name )
                        {
                            register_taxonomy_for_object_type( $taxonomy_name, $post_type_name );
                        }
                    );
                }
        }
    }
    
    /* Attaches meta boxes to the post type */
    public function add_meta_box( $title, $fields = array(), $context = 'normal', $priority = 'default', $intro_text = NULL )
    {
        if( ! empty( $title ) )
        {       
            // We need to know the Post Type name again
            $post_type_name = $this->post_type_name;
            
            $post_prefix = $this->data_prefix;

            // Meta variables   
            $box_id         = self::uglify( $title );
            $box_title      = self::beautify( $title );
            $box_context    = $context;
            $box_priority   = $priority;
            $box_introtxt   = $intro_text;

            // Make the fields global
            global $custom_fields;
            $custom_fields[$title] = $fields;

            add_action( 'admin_init',
                    function() use( $box_id, $box_title, $post_type_name, $post_prefix, $box_context, $box_priority, $fields, $box_introtxt )
                    {
                        add_meta_box(
                            $box_id,
                            $box_title,
                            function($post, $data) use ( $box_context, $box_introtxt, $post_prefix )
                            {
                                global $post;
                                // Nonce field for some validation
                                wp_nonce_field( trailingslashit( get_template_directory() ) . 'style.css', 'cp_nonce' );

                                // Get all inputs from $data
                                $custom_fields = $data['args'][0];

                                // Get the saved values
                                //$meta = get_post_custom( $post->ID );

                                // Add intro text
                                if ($box_introtxt) {
                                    echo '<p>' . $box_introtxt . '</p><hr />';
                                }
                                
                                // Check the array and loop through it
                                if( ! empty( $custom_fields ) )
                                {
                                      // Begin the field table and loop
                                    echo '<table class="form-table">';
                                    foreach ($custom_fields as $field) {

                                        $prefix = '_cp_' . $post_prefix; //Underscore to keep it hidden from custom fields
                                        $field_id_name = $prefix  . call_user_func("CSL_Custom_Post_Helper::uglify", $field['name']);
                                        $is_repeatable = substr($field['type'], 0, 10) == 'repeatable';

                                        $field['id'] = $field_id_name;
                                        $mandatory = isset($field['mandatory']) ? ' mandatory' : '';

                                        array_push($field, $field['id']);

                                        // get value of this field if it exists for this post
                                        $meta = get_post_meta($post->ID, $field['id'], $is_repeatable ? false : true);

                                        // begin a table row with                                            
                                        echo 
                                            $box_context != 'side' ? 
                                            '<tr>
                                                <th><label for="'.$field['id'].'">'.$field['label'].'</label></th>
                                                <td>' :
                                            '<tr>
                                                <td>
                                                <label for="'.$field['id'].'"><strong>'.$field['label'].'</strong></label>
                                                <br />';
                                            switch($field['type']) {
                                                // small text
                                                case 'small_text':
                                                    echo '<p><input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" class="small-text'.$mandatory.'" /></p>
                                                            <p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // text aka regular text
                                                case 'text':
                                                case 'regular_text':
                                                    echo '<p><input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" class="regular-text'.$mandatory.'" /></p>
                                                            <p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // large text
                                                case 'large_text':
                                                    echo '<p><input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" class="large-text'.$mandatory.'" /></p>
                                                            <p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // autocoordinates (read only)
                                                case 'autocoordinates':
                                                    echo '<p><input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" class="aad-coordinates regular-text readonly" readonly="readonly" /></p>
                                                            <p class="description">'.$field['desc'].'</p>';
                                                    echo "<div id=\"us3\" style=\" height: 25em; width: 100%; margin-top: 10px;\"></div>";
                                                break;
                                                // autoaddress
                                                case 'autoaddress':
                                                    echo '<p><input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" class="aad-address large-text'.$mandatory.'" autocomplete="off" /></p>
                                                            <p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // autogeonames regular
                                                case 'autogeonames_regular':
                                                    echo '<p><input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" class="jeoquery regular-text'.$mandatory.'" autocomplete="off" /></p>
                                                            <p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // autogeonames large
                                                case 'autogeonames_large':
                                                    echo '<p><input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" class="jeoquery large-text'.$mandatory.'" autocomplete="off" /></p>
                                                            <p class="description">'.$field['desc'].'</p>';
                                                break;
                                                 // autocountries regular
                                                case 'autocountries_regular':
                                                    echo '<p><input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" class="jeoquerycountries regular-text'.$mandatory.'" autocomplete="off" /></p>
                                                            <p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // autocountries large
                                                case 'autocountries_large':
                                                    echo '<p><input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" class="jeoquerycountries large-text'.$mandatory.'" autocomplete="off" /></p>
                                                            <p class="description">'.$field['desc'].'</p>';
                                                break; /* OJO */
                                                // autovocabulary regular
                                                case 'autovocabulary_regular':
                                                    echo '<p><input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" class="avoc regular-text'.$mandatory.'" autocomplete="off" /></p>
                                                            <p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // autovocabulary large
                                                case 'autovocabulary_large':
                                                    echo '<p><input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" class="avoc large-text'.$mandatory.'" autocomplete="off" /></p>
                                                            <p class="description">'.$field['desc'].'</p>';
                                                break;
                                               // autolookup regular
                                                case 'autolookup_regular':
                                                	$lnptype = get_post_type_object( $field['pt'] )->labels->add_new_item;
                                                    echo '<p><input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" class="autolookup regular-text'.$mandatory.'" data-pt="'.$field['pt'].'" autocomplete="off" /></p>
                                                            <p class="description">'.$field['desc'].'</p>';
                                                    echo '<p style="text-align: right;"><span class="dashicons dashicons-plus-alt"></span>&nbsp;';
                                                    echo '<a class="opentab" href="' . admin_url() . '/post-new.php?post_type='.$field['pt'].'">' . $lnptype . '</a></p>' . PHP_EOL;
                                                break;
                                                // autolookup large
                                                case 'autolookup_large':
                                                	$lnptype = get_post_type_object( $field['pt'] )->labels->add_new_item;
                                                    echo '<p><input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" class="autolookup large-text'.$mandatory.'" data-pt="'.$field['pt'].'" autocomplete="off" /></p>
                                                            <p class="description">'.$field['desc'].'</p>';
                                                    echo '<p style="text-align: right;"><span class="dashicons dashicons-plus-alt"></span>&nbsp;';
                                                    echo '<a class="opentab" href="' . admin_url() . '/post-new.php?post_type='.$field['pt'].'">' . $lnptype . '</a></p>' . PHP_EOL;
                                                break;
                                                // autourl large
                                                case 'autourl_large':
                                                    echo '<p><input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" class="verifyURL large-text'.$mandatory.'" /></p>
                                                            <p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // autoemail large
                                                case 'autoemail_large':
                                                    echo '<p><input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" class="verifyEMail large-text'.$mandatory.'" /></p>
                                                            <p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // autophone regular
                                                case 'autophone_regular':
                                                    echo '<p><input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" class="verifyPhone regular-text'.$mandatory.'" /></p>
                                                            <p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // textarea
                                                case 'textarea':
                                                    echo '<p><textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="60" rows="4">'.$meta.'</textarea></p>
                                                            <p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // text only (not writable)
                                                case 'text_only':
                                                	if(!empty($meta)) {
                                                		echo '<p><span class="highlight-green ellipses">'.$meta . '</span></p>';
                                                	} else {
                                                		echo '<p><span class="highlight-yellow">[' . __('Not stored value', $this->post_text_domain) . ']</span></p>';
                                                	}
                                                    echo '<p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // related_posts_by_meta (not writable)
                                                case 'related_posts_by_meta':
                                                    echo '<p class="highlight-cyan">' . implode( ', ', csl_get_related_posts( $post->ID, $field['pt'], $field['label'], $this->post_text_domain  ) ) . '</p>';
                                                    echo '<p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // NBC valuation (not writable)
                                                case 'nbc_valuation':
													$stext = $post->post_title . ' ' . $post->post_excerpt . ' ' . $post->post_content;
													$nncb  = csl_naive_bayesian_classification($stext, 'CL');
                                                    echo '<span class="highlight-green">' . PHP_EOL;
													echo $nncb < 0.3 
														? 
														'<span class="dashicons dashicons-arrow-up text-green"></span><span class="text-green">' . 
														number_format_i18n($nncb * 100, 1) . '%</span>'
														:
														(
															$nncb < 0.6 
															?
															'<span class="dashicons dashicons-leftright text-orange"></span><span class="text-orange">' . 
															number_format_i18n($nncb * 100, 1) . '%</span>'
															:
															'<span class="dashicons dashicons-arrow-down text-red"></span><span class="text-red">' . 
															number_format_i18n($nncb * 100, 1) . '%</span>'
														);
                                                    echo '</span>' . PHP_EOL;
                                                    echo '<p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // PST Possible Significant Tokens (not writable)
                                                case 'pst':
                                                    $surl = get_post_meta($post->ID, CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'original_source_link', true);
                                                    $list = csl_get_pst($surl);
                                                    //var_dump($list);
                                                    //echo '<span class="highlight-green">' . PHP_EOL;
                                                    if($list) {
                                                        foreach($list as $l) {
                                                            $al = explode('||', $l);
                                                            echo '<p><a href="' . $surl . '" target="_blank">' . $al[0] . '</a></p>' . 
                                                                '<p class="description highlight-yellow" style="font-size: smaller;">' . 
                                                                wp_trim_words($al[1], 40) . '</p><hr />' . PHP_EOL;
                                                        }
                                                    } else {
                                                        echo __('Unable to locate any PST in original information source', $this->post_text_domain) . '.' . PHP_EOL;
                                                    }
                                                    //echo '</span>' . PHP_EOL;
                                                    echo '<p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // external link (not writable)
                                                case 'text_link':
                                                 	if(!empty($meta)) {
	                                                	if(is_array($meta)) {
		                                                	foreach($meta as $km => $vm) {
			                                                	if(strpos($vm, ': ') !== false) {
																	$vmlink = admin_url() . 'post.php?post=' . explode(': ', $vm)[0] . '&action=edit';				                                                			                                                	} else {
				                                                	$vmlink = $vm;
			                                                	}
                                                				echo '<p><span class="highlight-green break-word">' . $vm .'<a class="break-word" href="'. $vmlink . '" target="_blank">' . mb_strimwidth($vma, 0, 80, "&hellip;", "UTF-8") .'</a></span></p>';
		                                                	}
	                                                	} else {
		                                                	if(strpos($meta, ': ') !== false) {
																$vmlink = admin_url() . '/post.php?post=' . explode(': ', $meta)[0] . '&action=edit';				                                                			                                                } else {
			                                                	$vmlink = $meta;
		                                                	}
                                                			echo '<p><span class="highlight-green break-word"><a class="break-word" href="'.$vmlink . '" target="_blank">' . mb_strimwidth($meta, 0, 80, "&hellip;", "UTF-8") .'</a></span></p>';
	                                                	}
                                                	} else {
                                                		echo '<p><span class="highlight-yellow">[' . __('Not stored value', $this->post_text_domain) . ']</span></p>';
                                                	}
                                                    echo '<p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // stars 0-1
                                                case 'stars_0_10':
                                                    $meta = !$meta ? 0 : $meta;
                                                    echo '<input type="hidden" name="'.$field['id'].'_hst" id="'.$field['id'].'_hst" value="'.$meta.'">';
                                                    echo '<div style="display: block-inline;">';
                                                    $max = 10; 
                                                    $rgb = ''; 
                                                    for ($i=1; $i<$max+1; $i++) {
                                                        $typstar = $i > $meta ? 'empty' : 'filled';
                                                        echo '<span '.$rgb.'id="sta_'.$i.'_'.$field['id'].'" class="dashicons dashicons-star-'.$typstar.'"></span>';
                                                    }
                                                    echo '</div/>';
                                                break;
                                                // range 0-10
                                                case 'range_0_10':
                                                    $meta = !$meta ? 0 : $meta;
                                                    echo '<p>
                                                        <input type="hidden" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'">
                                                        <input type="range" name="'.$field['id'].'_rng" id="'.$field['id'].'_rng" value="'.$meta.'" step="1" min="0" max="10" style="width: 50%;" />
                                                        <input type="number" name="'.$field['id'].'_val" id="'.$field['id'].'_val" value="'.$meta.'"  style="width: 30%; float: right" step="1" min="0" max="10" /></p>
                                                        <p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // checkbox
                                                case 'checkbox':
                                                    echo '<p><input type="checkbox" name="'.$field['id'].'" id="'.$field['id'].'" ',$meta ? ' checked="checked"' : '','/>
                                                            <label for="'.$field['id'].'">'.$field['desc'].'</label></p>';
                                                break;
                                                // select
                                                case 'select':
                                                    echo '<p><select name="'.$field['id'].'" id="'.$field['id'].'">';
                                                    foreach ($field['options'] as $option) {
                                                        echo '<option', $meta == $option['value'] ? ' selected="selected"' : '', ' value="'.$option['value'].'">'.$option['label'].'</option>';
                                                    }
                                                    echo '</select></p><p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // radio
                                                case 'radio':
                                                	echo '<p>';
                                                	$citer = 0;
                                                    foreach ( $field['options'] as $option ) {
                                                        echo '<input type="radio" name="'.$field['id'].'" id="'.$option['value'].'" value="'.$option['value'].'" ',$meta == $option['value'] ? ' checked="checked"' : ($citer == 0 ? ' checked="checked"' : ''),' />
                                                                <label style="margin-right: 10px;" for="'.$option['value'].'">'.$option['label'].'</label>';
                                                                $citer++;
                                                    }
                                                    echo '</p><p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // checkbox_group
                                                case 'checkbox_group':
                                                	echo '<p>';
                                                    foreach ($field['options'] as $option) {
                                                        echo '<input type="checkbox" value="'.$option['value'].'" name="'.$field['id'].'[]" id="'.$option['value'].'"',$meta && in_array($option['value'], $meta) ? ' checked="checked"' : '',' /> 
                                                                <label for="'.$option['value'].'">'.$option['label'].'</label><br />';
                                                    }
                                                    echo '</p><p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // tax_select
                                                case 'tax_select':
                                                    echo '<p><select name="'.$field['id'].'" id="'.$field['id'].'">
                                                            <option value="">' . __('Select one', $this->post_text_domain) . '</option>'; // Select One
                                                    $terms = get_terms($field['id'], 'get=all');
                                                    $selected = wp_get_object_terms($post->ID, $field['id']);
                                                    foreach ($terms as $term) {
                                                        if (!empty($selected) && !strcmp($term->slug, $selected[0]->slug)) 
                                                            echo '<option value="'.$term->slug.'" selected="selected">'.$term->name.'</option>'; 
                                                        else
                                                            echo '<option value="'.$term->slug.'">'.$term->name.'</option>'; 
                                                    }
                                                    $taxonomy = get_taxonomy($field['id']);
                                                    echo '</select></p><p class="description"><a href="'.get_bloginfo('home').'/wp-admin/edit-tags.php?taxonomy='.$field['id'].'">' . __('Manage' ) . ' '.$taxonomy->label.'</a></p>';
                                                break;
                                                // post_list
                                                case 'post_list':
                                                $items = get_posts( array (
                                                    'post_type' => $field['post_type'],
                                                    'posts_per_page' => -1
                                                ));
                                                 	echo '<p>';
                                                    echo '<select name="'.$field['id'].'" id="'.$field['id'].'">
                                                            <option value="">' . __('Select one', $this->post_text_domain) . '</option>'; // Select One
                                                        foreach($items as $item) {
	                                                        $lnptype = get_post_type_object( $item->post_type )->labels->singular_name;
                                                            echo '<option value="'.$item->ID.'"',$meta == $item->ID ? ' selected="selected"' : '','>'.$lnptype.': '.$item->post_title.'</option>';
                                                        } // end foreach
                                                    echo '</p></select><p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // date
                                                case 'date':
                                                    echo '<p><input type="text" class="datepicker'.$mandatory.'" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="30" /></p>
                                                            <p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // slider
                                                case 'slider':
                                                $value = $meta != '' ? $meta : '0';
                                                    echo '<div id="'.$field['id'].'-slider"></div>
                                                            <p><input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$value.'" size="5" /></p>
                                                            <p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // image
                                                case 'image':
                                                    $image = get_template_directory_uri().'/assets/img/image.png';  
                                                    echo '<span class="custom_default_image" style="display:none">'.$image.'</span>';
                                                    if ($meta) { $image = wp_get_attachment_image_src($meta, 'medium'); $image = $image[0]; }               
                                                    echo    '<input name="'.$field['id'].'" type="hidden" class="custom_upload_image" value="'.$meta.'" />
                                                                <img src="'.$image.'" class="custom_preview_image" alt="" /><br />
                                                                    <input class="custom_upload_image_button button" type="button" value="' . __( 'Choose Image', $this->post_text_domain ) . '" data-cp-post-id="'.$post->ID.'" />
                                                                    <small>&nbsp;<a href="#" class="custom_clear_image_button">' . __( 'Remove Image', $this->post_text_domain ) . '</a></small>
                                                                    <br clear="all" /><p class="description">'.$field['desc'].'</p>';
                                                break;
                                                // repeatable (not serialized)
                                                case 'repeatable':
                                                    echo '<p><ul id="'.$field['id'].'-repeatable" class="custom_repeatable">';
                                                    $i = 0;
                                                    if ($meta) {
                                                        foreach($meta as $row) {
                                                            echo '<li><span class="sort hndle">|||</span>
                                                                        <input type="text" name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" value="'.$row.'" class="repeatable-center" />
                                                                        <a class="repeatable-remove" href="#">' . __('Remove', $this->post_text_domain) . '</a></li>';
                                                            $i++;
                                                        }
                                                    } else {
                                                        echo '<li><span class="sort hndle">|||</span>
                                                                    <input type="text" name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" value="" class="repeatable-center" />
                                                                    <a class="repeatable-remove" href="#">' . __('Remove', $this->post_text_domain) . '</a></li>';
                                                    }
                                                    echo '</ul></p><p class="description">'.$field['desc'].'</p>';
                                                    echo '<p><a class="repeatable-add button" href="#">' . __('Create a new item in the list', $this->post_text_domain) . '</a></p>';
                                                break;
                                                // repeatable autolookup (not serialized)
                                                case 'repeatable_autolookup':
                                                    echo '<ul id="'.$field['id'].'-repeatable" class="custom_repeatable">';
                                                    $i = 0;
                                                    if ($meta) {
                                                        foreach($meta as $row) {
                                                            echo '<li class="repeatable-li">';
                                                            echo '<input type="text" name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" value="'.$row.'" class="autolookup repeatable-center'.$mandatory.'" data-pt="'.$field['pt'].'" autocomplete="off" />';
                                                            echo '<a class="repeatable-remove" href="#">' . __('Remove', $this->post_text_domain) . '</a>';
                                                            echo '</li>';
                                                            $i++;
                                                        }
                                                    } else {
                                                        echo '<li class="repeatable-li">';
                                                        echo '<input type="text" name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" value="" class="autolookup repeatable-center field-verifyEMPTY'.$mandatory.'" data-pt="'.$field['pt'].'" autocomplete="off" />';
                                                        echo '<a class="repeatable-remove" href="#">' . __('Remove', $this->post_text_domain) . '</a>';
                                                        echo '</li>';
                                                     }
                                                    echo '</ul>';
                                                    echo '<p class="description">'.$field['desc'].'</p>';
                                                    echo '<p>';
                                                    echo '<a class="repeatable-add button" href="#" style="margin-right: 10px;">' . __('Create a new item in the list', $this->post_text_domain) . '</a>';
                                                    if( 'country' != $field['pt'] ) {
                                                        if( strpos( $field['pt'], ',' ) === false ) {
                                                        	echo '<a class="opentab button repeatable-enlist" href="' . admin_url() . '/post-new.php?post_type='.$field['pt'].'">' . 
                                                        		get_post_type_object( $field['pt'] )->labels->add_new_item . '</a></p>' . PHP_EOL;
                                                        } else {
    	                                                	$lnptype = array();
    	                                                	foreach( explode( ',', $field['pt'] ) as $sTMP ) {
    	                                                    	echo '<a class="opentab button repeatable-enlist" href="' . admin_url() . '/post-new.php?post_type='.$sTMP.'">' . 
    	                                                    		get_post_type_object( $sTMP )->labels->add_new_item . '</a>' . PHP_EOL;
    	                                                	}
    	                                                	echo '</p>' . PHP_EOL;
                                                        }
                                                    }
                                                    echo '</p>';                                                            
                                                break;
                                                // repeatable uri rss (not serialized)
                                                case 'repeatable_rss':
                                                    echo '<ul id="'.$field['id'].'-repeatable" class="custom_repeatable urislist">';
                                                    $i = 0;
                                                    if ($meta) {
                                                        foreach($meta as $row) {
                                                            echo '
                                                            	<li class="repeatable-li">
                                                                <input type="text" name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" value="'.$row.'" class="verifyRSS repeatable-center'.$mandatory.'" style="border: none;"/>
                                                                <a class="repeatable-remove" href="#">' . __('Remove', $this->post_text_domain) . '</a>
                                                                </li>';
                                                             $i++;
                                                        }
                                                    } else {
                                                        echo '
                                                        	<li class="repeatable-li">
                                                            <input type="text" name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" value="" class="verifyRSS repeatable-center field-verifyEMPTY'.$mandatory.'" style="border: none;"/>
                                                            <a class="repeatable-remove" href="#">' . __('Remove', $this->post_text_domain) . '</a>
                                                            </li>';
                                                     }
                                                    echo '</ul>';
                                                    echo '<p class="description">'.$field['desc'].'</p>';
                                                    echo '<p>';
                                                    echo '<a class="repeatable-add button" href="#">' . __('Create a new item in the list', $this->post_text_domain) . '</a>';
                                                    echo '&nbsp;';
                                                    echo '<a href="' . get_template_directory_uri() . '/assets/docs/' . get_locale() . '/specific_help.html?TB_iframe=true&height=600&width=550" title="' . __('Verification status colors', $this->post_text_domain) . '" class="button legend-urlbutton thickbox">' . __('Verification status colors', $this->post_text_domain) . '</a>';
                                                    echo '</p>';                                                            
                                                break;
                                                // repeatable uri html (not serialized)
                                                case 'repeatable_html':
                                                    echo '<ul id="'.$field['id'].'-repeatable" class="custom_repeatable urislist">';
                                                    $i = 0;
                                                    if ($meta) {
                                                        foreach($meta as $row) {
                                                            echo '
                                                            	<li class="repeatable-li">
                                                                <input type="text" name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" value="'.$row.'" class="verifyURL repeatable-center'.$mandatory.'" style="border: none;"/>
                                                                <a class="repeatable-remove" href="#">' . __('Remove', $this->post_text_domain) . '</a>
                                                                </li>';
                                                             $i++;
                                                        }
                                                    } else {
                                                        echo '
                                                        	<li class="repeatable-li">
                                                            <input type="text" name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" value="" class="verifyURL repeatable-center field-verifyEMPTY'.$mandatory.'" style="border: none;"/>
                                                            <a class="repeatable-remove" href="#">' . __('Remove', $this->post_text_domain) . '</a>
                                                            </li>';
                                                     }
                                                    echo '</ul>';
                                                    echo '<p class="description">'.$field['desc'].'</p>';
                                                    echo '<p>';
                                                    echo '<a class="repeatable-add button" href="#">' . __('Create a new item in the list', $this->post_text_domain) . '</a>';
                                                    echo '&nbsp;';
                                                    echo '<a href="' . get_template_directory_uri() . '/assets/docs/' . get_locale() . '/specific_help.html?TB_iframe=true&height=600&width=550" title="' . __('Verification status colors', $this->post_text_domain) . '" class="button legend-urlbutton thickbox">' . __('Verification status colors', $this->post_text_domain) . '</a>';
                                                    echo '</p>';                                                            
                                                break;
                                                //Wordpress Editor
                                                case 'wysiwyg':
                                                        $args = array_merge(
                                                            // Default Options
                                                            array(
                                                                'media_buttons' => true,
                                                            ), $field['options'] 
                                                        );
                                                    wp_editor($meta,$field['id'],$args);
                                                    echo '<p class="description">'.$field['desc'].'</p>';
                                                break;
                                            } //end switch
                                        echo '</td></tr>';
                                    } // end foreach
                                    echo '</table>'; // end table
                                }

                            },
                            $post_type_name,
                            $box_context,
                            $box_priority,
                            array( $fields )
                        );
                    }
                );
        }

    }
    
    /* Listens for when the post type being saved */
    public function save()
    {
        // Need the post type name again
        // var_dump( $_POST );
        
        $post_type_name = $this->post_type_name;
        $post_data_prefix = $this->data_prefix;

        add_action( 'save_post',
            function() use( $post_type_name, $post_data_prefix )
            {
                // Deny the wordpress autosave function
                if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

                if ( isset($_POST['cp_nonce']) ) {
                    if ($_POST && ! wp_verify_nonce( $_POST['cp_nonce'], trailingslashit( get_template_directory() ) . 'style.css' ) ) return;
                }

                global $post;

                if( isset( $_POST ) && isset( $post->ID ) && get_post_type( $post->ID ) == $post_type_name )
                {
                    if (!current_user_can('edit_page', $post->ID))
                    {    
                        return $post->ID;
                    } elseif (!current_user_can('edit_post', $post->ID)) 
                    {
                        return $post->ID;
                    }    
                    global $custom_fields;

                    csl_assign_parent_terms( $post->ID );
                    
                    // Loop for repeated groups
                    foreach( $custom_fields as $title => $fields ) {
	                    foreach ($fields as $field) {
		                    if($field['type'] == 'repeatable_group') {
			                    foreach($field['group'] as $k => $v) {
				                	$custom_fields[$title][] = array('name' => $field['name'] . '_' . $v['subid'], 'type' => 'repeatable_group');    
			                    }
			                }
		                }
					}
					
                    // Loop through each meta box
                    foreach( $custom_fields as $title => $fields )
                    {
                        foreach ($fields as $field) {                                

                            $prefix = '_cp_' . $post_data_prefix; //Underscore to keep it hidden from custom fields
                            $field_id_name = $prefix  . call_user_func("CSL_Custom_Post_Helper::uglify", $field['name']);
							
                            $field['id'] = $field_id_name;
                            $is_repeatable = substr($field['type'], 0, 10) == 'repeatable';
                            $is_rep_group  = $field['type'] == 'repeatable_group';
							
							//if(isset($_POST[$field['id']]) && !empty($_POST[$field['id']])) {
							if(isset($_POST[$field['id']])) {	
                            	array_push($field, $field['id']);

	                            if($field['type'] == 'tax_select') continue;
	                            if($field['type'] == 'wysiwyg'){ 
	                                $new = wpautop(wptexturize($_POST[$field['id']]));
	                            }else{
	                                $new = $_POST[$field['id']];
	                            }
	                            $old = get_post_meta($post->ID, $field['id'], ($is_repeatable || $is_rep_group) ? false : true);
	                            
	                            if ($new && $new != $old) {
			                        delete_post_meta($post->ID, $field['id']);
	                                if(is_array($new)) {
		                                foreach($new as $k => $v) {
			                                if(!empty($v))
			                                	if(is_array($v)) {
				                                	if(!empty($vv)) {
					                                	foreach($v as $kk => $vv) {	
						                                	$vv = trim($vv);
						                                	add_post_meta($post->ID, $field['id'], $vv, false); 
						                                }
						                            } else {
							                            $v = trim($v);
						                                add_post_meta($post->ID, $field['id'], $v, false); 
							                        }   
			                                	} else {
				                                	$v = trim($v);
													add_post_meta($post->ID, $field['id'], $v, false); 
			                                	}
		                                }
	                                } else {
		                                if(!empty($new))
		                                	$new = trim($new);
	                                		update_post_meta($post->ID, $field['id'], $new);									
	                                }
	                            } elseif ('' == $new && $old) {
	                                if(is_array($old)) {
		                                foreach($old as $k => $v) {
			                                if(is_array($v)) {
			                                	foreach($v as $ke => $va) {
				                                	$va = trim($va);
				                                	delete_post_meta($post->ID, $field['id'], $va);
				                                }
				                            } else {
					                            $v = trim($v);
				                            	delete_post_meta($post->ID, $field['id'], $v);
				                            }
		                                }
		                                
	                                } else {
		                                $old = trim($old);
	                                	delete_post_meta($post->ID, $field['id'], $old);									
	                                }
	                            }
							}

                        } // end foreach
                    }                    
                }
            }
        );
    }

    public function add_custom_js_css()
    {
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-slider');
        wp_enqueue_style('jquery-ui-custom', get_template_directory_uri().'/assets/css/jquery-ui-custom.css');
    }

    public function add_custom_scripts() 
    {
        global $custom_fields, $post;
        
        $output = '<script type="text/javascript">
                    jQuery(function() {';
		
		if(count($custom_fields) > 0) {
            foreach ($custom_fields as $title => $fields) { // loop through the fields looking for certain types
                foreach ($fields as $field) {
                    if ($field['type'] == 'slider' && $is_sl_added) {
                        $value = get_post_meta($post->ID, $field['id'], true);
                        if ($value == '') $value = $field['min'];
                        $output .= '
                            jQuery( "#'.$field['id'].'-slider" ).slider({
                                value: '.$value.',
                                min: '.$field['min'].',
                                max: '.$field['max'].',
                                step: '.$field['step'].',
                                slide: function( event, ui ) {
                                    jQuery( "#'.$field['id'].'" ).val( ui.value );
                                }
                            });';
                    }
                }
            }
		}		
        $output .= '});
            </script>';
        echo $output;
    }

    public static function calculateRGB ( $from, $to, $pos = 0.5 ) 
    { 
        list($fr, $fg, $fb) = sscanf($from, '%2x%2x%2x'); 
        list($tr, $tg, $tb) = sscanf($to, '%2x%2x%2x'); 
        $r = (int) ($fr - (($fr - $tr) * $pos)); 
        $g = (int) ($fg - (($fg - $tg) * $pos)); 
        $b = (int) ($fb - (($fb - $tb) * $pos)); 
        return sprintf('%02x%02x%02x', $r, $g, $b); 
    }
    
    public static function beautify( $string )
    {
        return ucfirst( str_replace( '_', ' ', $string ) );
    }

    public static function uglify( $string )
    {
        return strtolower(str_replace(')','_',str_replace('(','_', str_replace( ' ', '_', $string))));
    }
    public static function pluralize( $string )
    {
        $last = $string[strlen( $string ) - 1];

        if( $last == 'y' )
        {
            $cut = substr( $string, 0, -1 );
            //convert y to ies
            $plural = $cut . 'ies';
        }
        else
        {
            // just attach an s
            $plural = $string . 's';
        }

        return $plural;
    }

}
 
// Customized "enter title here"message for any post type
function csl_generic_custom_default_title( $title ) {
    $screen = get_current_screen();
    $txtnam = get_post_type_object($screen->post_type)->labels->singular_name || wp_die('LACAGASTE' . $screen->post_type);
    if  ( 'add' == $screen->action ) {
        $title = sprintf(__('Enter the official name for %s', CSL_TEXT_DOMAIN_PREFIX), strtolower($txtnam));
    } else {
        $title = "";
    }
    return $title;
}
add_filter( 'enter_title_here', 'csl_generic_custom_default_title');
  
function csl_assign_parent_terms( $post_id ){
    global $cls_a_custom_taxonomies_hierarchical;
    foreach( $cls_a_custom_taxonomies_hierarchical as $t_name ){
        $terms = wp_get_post_terms( $post_id, $t_name );
        if( $terms ) {
            foreach( $terms as $term ){
                while( $term->parent != 0 && !has_term( $term->parent, $t_name, get_post( $post_id ) ) ) {
                    wp_set_object_terms($post_id, array($term->parent), $t_name, true);
                    $term = get_term($term->parent, $t_name);
                }
            }
        }
    }
}

function csl_get_related_posts( $post_id, $custom_field, $label, $textdomain ) {
    global $wpdb;
    $postids=$wpdb->get_results( 
    	"
        SELECT
            post_id 
        FROM 
        	{$wpdb->postmeta}
        WHERE 
        	meta_value LIKE CONCAT(CONCAT( '$post_id', ': '), '%')
            AND
            meta_key = '$custom_field' 
            AND
            post_id IN (SELECT ID from {$wpdb->posts} WHERE post_status = 'publish' )
    	"
    );
    /*
    if ( $postids ) {
        echo '<div id="listrel_' . $post_id . '_' . $custom_field . '" style="overflow-y: scroll; height: ' . $height . 'px;">' .  PHP_EOL;
        echo '<ul>' .  PHP_EOL;
    	foreach ( $postids as $id ) { 
            if( get_post( $id->post_id ) ) {
                echo '<li><a target="_blank" href="' . get_post_permalink( $id->post_id ) . '">' . get_post( $id->post_id )->post_title . '</a></li>' . PHP_EOL;            
            } 		
        }
        echo '</ul>' .  PHP_EOL;
        echo '</div>' .  PHP_EOL;
    }
    */
    $aOut = array();
    if ( $postids ) {
    	foreach ( $postids as $id ) { 
            if( get_post( $id->post_id ) ) {
                $aOut []= '<a target="_blank" href="' . get_post_permalink( $id->post_id ) . '">' . get_post( $id->post_id )->post_title . '</a>';            
            } 		
        }        
    } else {
        $aOut []= sprintf(
            __('There is not related %s records', $textdomain),
            strtolower( $label )
            );
    }
    return $aOut;    
}

?>