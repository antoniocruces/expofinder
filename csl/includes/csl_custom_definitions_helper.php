<?php

/**
 * GLOBAL DEFINES 
 */

global $wpdb;
 
/**
 * DEFINEs
 */
define('CSL_DATA_PREFIX', '_csl_');
define('CSL_DATA_FIELD_PREFIX', '_cp_');
define('CSL_TEXT_DOMAIN_PREFIX', 'csl');

// Language load for text domain
load_theme_textdomain(CSL_TEXT_DOMAIN_PREFIX, get_template_directory() . '/includes/languages');

if ( substr(PHP_VERSION, 0, 4) < '5.6.' ) {
    load_theme_textdomain(CSL_TEXT_DOMAIN_PREFIX, get_template_directory() . '/includes/languages');
    wp_die( sprintf(__('Is absolutely essential a PHP version greater or equal to 5.6.0 in order to run this application. This server has %s avaliable. The system will be immediately deactivated.', CSL_TEXT_DOMAIN_PREFIX), PHP_VERSION));
}

define('CSL_NAME', 'ExpoFinder');
define('CSL_PROJECT_NAME', isset(get_option('csl_settings')['project_name']) ? get_option('csl_settings')['project_name'] : 'Exhibitium');
define('CSL_PROJECT_LOGO', '<span class="logo-project">E<span></span>XHIBITIUM</span>');
define('CSL_VERSION', '2.0.0');
define('CSL_DESCRIPTION', __('Distributed application using client-server paradigm for <a href="http://www.exhibitium.com" target="_blank"><strong>Exhibitium</strong></a> project and ArtCatalogs (HAR2014-51915-P) project specific information management, developed by Department of Art History staff at the University of Malaga (Spain).', CSL_TEXT_DOMAIN_PREFIX));
define('CSL_ORGANIZATION', __('<a href="http://iarthis.hdplus.es" target="_blank"><strong>iArtHis_LAB</strong> Research Group. <a href="http://www.uma.es/departamento-de-historia-del-arte" target="_blank">Art History Department</a>. <a href="http://www.uma.es" target="_blank">University of Málaga</a>.', CSL_TEXT_DOMAIN_PREFIX));
define('CSL_ORGANIZATION_SHORT', __('<a href="http://iarthis.hdplus.es" target="_blank"><strong>iArtHis_LAB</strong> <a href="http://www.uma.es/departamento-de-historia-del-arte" target="_blank">DHA</a>. <a href="http://www.uma.es" target="_blank">UMA</a>.', CSL_TEXT_DOMAIN_PREFIX));
define('CSL_AUTHOR', __('<a href="mailto:antonio.cruces@uma.es">Antonio Cruces Rodríguez</a>', CSL_TEXT_DOMAIN_PREFIX));
define('CSL_AUTHOR_WEB', __('<a href="http://hdplus.es" target="_blank">Antonio Cruces Rodríguez</a>', CSL_TEXT_DOMAIN_PREFIX));
define('CSL_LOGO', '<i class="fa fa-search-plus" style="color: #2d99e5; margin-right: 2px;"></i>Expo<span style="color: #2d99e5; font-weight: bolder;">Finder</span>');
define('CSL_NEGATIVE_LOGO', '<i class="fa fa-search-plus" style="color: #ebebeb; margin-right: 2px;"></i>Expo<span style="color: #e9e9e9; font-weight: bolder;">Finder</span>');
define('CSL_WHITE_LOGO', '<i class="fa fa-search-plus" style="color: #dbdbdb; margin-right: 2px;"></i><span style="color: #ffffff; font-weight: bolder;">Expo</span><span style="color: #afafaf; font-weight: bolder;">Finder</span>');
define('CSL_MIN_LOGO', '<span class="pull-left" style="margin-right: 5px;"><img src="' . get_template_directory_uri() . '/assets/img/csl-ab-logo.png"></span><span style="font-family: Oswald; color: #cfcfcf; font-weight: bolder;">Expo</span><span style="font-family: Oswald; color: #afafaf; font-weight: bolder;">Finder</span>');
define('CSL_MIN_LOGO_COMPACT', '<span style="margin-right: 5px;"><img src="' . get_template_directory_uri() . '/assets/img/csl-ab-logo.png"></span><span style="font-family: Oswald; color: #ffffff; font-weight: bolder;">Expo</span><span style="font-family: Oswald; color: #cfcfcf; font-weight: bolder;">Finder</span>');
define('CSL_TEXT_ICON', '<i class="fa fa-search-plus" margin-right: 2px;"></i>');
define('CSL_FIRST_LOGO_PART', 'Expo');
define('CSL_SECOND_LOGO_PART', 'Finder');

define('CSL_SIRIUS_M_LOGO', '<i class="fa fa-diamond" style="color: #2d99e5; margin-right: 10px;"></i>SIRIUS<span style="color: #c0c0c0; font-weight: bolder;">-</span><span style="color: #2d99e5; font-weight: bolder;">M</span>');

//define('CLS_LATERAL_SUSCRIPTION_KEY', 'c590eaf977f30a4e43fb7f18302bbf42');

define('CSL_FIELD_MARK_VERIFIED', '&nbsp;<span style="color: #c0c0c0; font-size: 90%;" class="dashicons dashicons-editor-spellcheck"></span>');
define('CSL_FIELD_MARK_GEONAMES', '&nbsp;<span style="color: #c0c0c0; font-size: 90%;" class="dashicons dashicons-admin-site"></span>');
define('CSL_FIELD_MARK_POST', '&nbsp;<span style="color: #c0c0c0; font-size: 90%;" class="dashicons dashicons-index-card"></span>');
define('CSL_FIELD_MARK_URL', '&nbsp;<span style="color: #c0c0c0; font-size: 90%;" class="dashicons dashicons-admin-links"></span>');
define('CSL_FIELD_MARK_RSS', '&nbsp;<span style="color: #c0c0c0; font-size: 90%;" class="dashicons dashicons-rss"></span>');
define('CSL_FIELD_MARK_EMAIL', '&nbsp;<span style="color: #c0c0c0; font-size: 90%;" class="dashicons dashicons-email"></span>');
define('CSL_FIELD_MARK_LOCATION', '&nbsp;<span style="color: #c0c0c0; font-size: 90%;" class="dashicons dashicons-location-alt"></span>');
define('CSL_FIELD_MARK_PHONE', '&nbsp;<span style="color: #c0c0c0; font-size: 90%;" class="dashicons dashicons-phone"></span>');
define('CSL_FIELD_MARK_DATE', '&nbsp;<span style="color: #c0c0c0; font-size: 90%;" class="dashicons dashicons-calendar-alt"></span>');
define('CSL_FIELD_MARK_VOCABULARY', '&nbsp;<span style="color: #c0c0c0; font-size: 90%;" class="dashicons dashicons-editor-spellcheck"></span>');
define('CSL_FIELD_MARK_LOOKUP', '&nbsp;<span style="color: #c0c0c0; font-size: 90%;" class="dashicons dashicons-search"></span>');
define('CSL_FIELD_MARK_CLOUD', '&nbsp;<span style="color: #c0c0c0; font-size: 90%;" class="dashicons dashicons-cloud"></span>');
define('CSL_FIELD_WAIT_SIGN', '<i style="color: #c0c0c0; margin-left: 10px; display: none;" id="waitSign" class="fa fa-cog fa-spin"></i>');

define('CSL_ENTITIES_DATA_PREFIX', '_ent_');
define('CSL_PERSONS_DATA_PREFIX', '_peo_');
define('CSL_BOOKS_DATA_PREFIX', '_boo_');
define('CSL_COMPANIES_DATA_PREFIX', '_com_');
define('CSL_EXHIBITIONS_DATA_PREFIX', '_exh_');
define('CSL_ARTWORKS_DATA_PREFIX', '_art_');

define('CSL_DOCUMENTS_DATA_PREFIX', '_doc_');
define('CSL_QUERIES_DATA_PREFIX', '_qry_');

define('CSL_CUSTOM_POST_ENTITY_TYPE_NAME', 'entity');
define('CSL_CUSTOM_POST_PERSON_TYPE_NAME', 'person');
define('CSL_CUSTOM_POST_BOOK_TYPE_NAME', 'book');
define('CSL_CUSTOM_POST_COMPANY_TYPE_NAME', 'company');
define('CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME', 'exhibition');
define('CSL_CUSTOM_POST_ARTWORK_TYPE_NAME', 'artwork');

define('CSL_CUSTOM_POST_DOCUMENT_TYPE_NAME', 'document');
define('CSL_CUSTOM_POST_QUERY_TYPE_NAME', 'custom_query');

define('CSL_CUSTOM_POST_ENTITY_COLOR', 'primary');
define('CSL_CUSTOM_POST_PERSON_COLOR', 'success');
define('CSL_CUSTOM_POST_BOOK_COLOR', 'info');
define('CSL_CUSTOM_POST_COMPANY_COLOR', 'warning');
define('CSL_CUSTOM_POST_EXHIBITION_COLOR', 'danger');
define('CSL_CUSTOM_POST_ARTWORK_COLOR', 'purple');

define('CSL_ENABLE_LINKS_MENU', false);

define('CSL_EXHIBITION_HASH_INCLUDE_LINK', false);
define('CSL_EXHIBITION_HASH_INCLUDE_AUTHOR', false);
define('CSL_EXHIBITION_HASH_INCLUDE_DATE', false);

define('CSL_RELATED_POSTS_HEIGHT', 250);

define('CSL_MAX_MEMOTY_LIMIT', '4098M');

// SMTP Server definitions
// *** Normal ports: 25, 465 or 587
// *** Normal crypt ("SECURE"): ssl (deprecated) or tls
define( 'CSL_PHPMAILER_SMTP_HOST', 'correo.uma.es' );
define( 'CSL_PHPMAILER_SMTP_AUTH', true );
define( 'CSL_PHPMAILER_SMTP_PORT', '587' );
define( 'CSL_PHPMAILER_SMTP_USER', 'antonio.cruces@uma.es' );
define( 'CSL_PHPMAILER_SMTP_PASSWORD', '25871762HDPlus' );
define( 'CSL_PHPMAILER_SMTP_SECURE', 'tls' );
define( 'CSL_PHPMAILER_SMTP_FROM_MAIL', 'antonio.cruces@uma.es' );
define( 'CSL_PHPMAILER_SMTP_FROM_NAME', CSL_NAME );
define( 'CSL_PHPMAILER_SMTP_DEBUG', false );

// Bug administrative mail definitions
define ( 'CSL_BUG_PAGE_SLUG', 'bug' );
define ( 'CSL_LEGAL_PAGE_SLUG', 'legal' );

// Valid tables for stats
define( 'CSL_VALID_STATS_TABLES', "$wpdb->posts,$wpdb->postmeta,$wpdb->terms,$wpdb->term_relationships,$wpdb->term_taxonomy");

const CSL_CUSTOM_POST_TYPE_ARRAY = array(
    CSL_CUSTOM_POST_ENTITY_TYPE_NAME, 
    CSL_CUSTOM_POST_PERSON_TYPE_NAME,
    CSL_CUSTOM_POST_BOOK_TYPE_NAME,
    CSL_CUSTOM_POST_COMPANY_TYPE_NAME,
    CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME,
    CSL_CUSTOM_POST_ARTWORK_TYPE_NAME,
    );

const CSL_CUSTOM_POST_COLOR_ARRAY = array(
    CSL_CUSTOM_POST_ENTITY_TYPE_NAME => CSL_CUSTOM_POST_ENTITY_COLOR, 
    CSL_CUSTOM_POST_PERSON_TYPE_NAME => CSL_CUSTOM_POST_PERSON_COLOR,
    CSL_CUSTOM_POST_BOOK_TYPE_NAME => CSL_CUSTOM_POST_BOOK_COLOR,
    CSL_CUSTOM_POST_COMPANY_TYPE_NAME => CSL_CUSTOM_POST_COMPANY_COLOR,
    CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME => CSL_CUSTOM_POST_EXHIBITION_COLOR,
    CSL_CUSTOM_POST_ARTWORK_TYPE_NAME => CSL_CUSTOM_POST_ARTWORK_COLOR,
    );

const CSL_CUSTOM_POST_TYPE_GLOBAL_STATUS_ARRAY = array(
    CSL_CUSTOM_POST_ENTITY_TYPE_NAME, 
    CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME,
    );

const CSL_CUSTOM_POST_TAXONOMIES_ARRAY = array(
    'tax_typology', 
    'tax_ownership',
    'tax_activity',
    'tax_isic4_category',
    'tax_exhibition_type',
    'tax_artwork_type',
    'tax_movement',
    'tax_period',
    );

const CSL_CUSTOM_POST_TAXONOMIES_FOR_MAPPING_ARRAY = array(
    'tax_typology', 
    'tax_exhibition_type',
    );

const CSL_CUSTOM_POST_TAXONOMIES_COMPLETE_ARRAY = array(
    'tax_typology', 
    'tax_ownership',
    'tax_keyword',
    'tax_activity',
    'tax_publisher',
    'tax_catalog_typology',
    'tax_isic4_category',
    'tax_exhibition_type',
    'tax_artwork_type',
    'tax_topic',
    'tax_movement',
    'tax_period',
    );

const CSL_CUSTOM_XMLRPC_OUTPUT_FORMATS_ALLOWED = array(
    'json',
    'xml',
    );

// Custom fields for normalized queries

const CSL_NORMALIZED_RELATIONS_META_KEYS = array(
    '_cp__ent_parent_entity',
    '_cp__boo_paper_author',
    '_cp__boo_paper_editor',
    '_cp__boo_paper_illustrator',
    '_cp__boo_sponsorship',
    '_cp__boo_artwork_author',
    '_cp__peo_entity_relation',
    '_cp__peo_person_relation',
    '_cp__exh_source_entity',
    '_cp__exh_parent_exhibition',
    '_cp__exh_info_source',
    '_cp__exh_artwork_author',
    '_cp__exh_supporter_entity',
    '_cp__exh_funding_entity_cp__exh_curator',
    '_cp__exh_catalog',
    '_cp__exh_museography',
    '_cp__exh_art_collector',
    '_cp__art_artwork_author',
    '_cp__art_entity_when_cataloging',
    '_cp__art_related_boo_catalogs',
    );

const CSL_NORMALIZED_DATES_META_KEYS = array(
    '_cp__boo_publishing_date',
    '_cp__peo_birth_date',
    '_cp__peo_death_date',
    '_cp__exh_exhibition_start_date',
    '_cp__exh_exhibition_end_date',
    '_cp__art_artwork_start_date',
    '_cp__art_artwork_end_date'
    );

const CSL_NORMALIZED_FIRST_DATES_META_KEYS = array(
    '_cp__boo_publishing_date',
    '_cp__peo_birth_date',
    '_cp__exh_exhibition_start_date',
    '_cp__art_artwork_start_date'
    );

const CSL_NORMALIZED_PLACES_META_KEYS = array(
    '_cp__exh_exhibition_town',
    '_cp__com_company_headqarter_place',
    '_cp__boo_publishing_place',
    '_cp__peo_country',
    '_cp__ent_town',
    );

const CSL_NORMALIZED_COORDINATES_META_KEYS = array(
    '_cp__exh_coordinates',
    '_cp__ent_coordinates',
    );

const CSL_META_FIELDS_FOR_QUERIES = array(
	'_cp__boo_paper_author',
	'_cp__boo_paper_editor',
	'_cp__boo_paper_illustrator',
	'_cp__boo_publishing_date',
	'_cp__boo_publishing_place',
	'_cp__boo_artwork_author',
	'_cp__boo_sponsorship',
	'_cp__com_company_dimension',
	'_cp__com_company_headquarter_place',
	'_cp__ent_coordinates',
	'_cp__ent_parent_entity',
	'_cp__ent_town',
	'_cp__ent_rss_uri',
	'_cp__ent_html_uri',
	'_cp__exh_art_collector',
	'_cp__exh_artwork_author',
	'_cp__exh_catalog',
	'_cp__exh_coordinates',
	'_cp__exh_curator',
	'_cp__exh_end_date',
	'_cp__exh_exhibition_access',
	'_cp__exh_exhibition_end_date',
	'_cp__exh_exhibition_site',
	'_cp__exh_exhibition_start_date',
	'_cp__exh_exhibition_town',
	'_cp__exh_funding_entity',
	'_cp__exh_geotag',
	'_cp__exh_info_source',
	'_cp__exh_museography',
	'_cp__exh_parent_exhibition',
	'_cp__exh_start_date',
	'_cp__exh_supporter_entity',
	'_cp__exh_town',
	'_cp__peo_birth_date',
	'_cp__peo_country',
	'_cp__peo_death_date',
	'_cp__peo_entity_relation',
	'_cp__peo_gender',
	'_cp__peo_person_relation',
	'_cp__peo_person_type',
	'_cp__art_alternative_title',
	'_cp__art_artwork_author',
	'_cp__art_artwork_start_date',
	'_cp__art_artwork_end_date',
	'_cp__art_entity_when_cataloging',
	'_cp__art_related_boo_catalogs',
	);
	
// Naive Bayesian Classifier (NBC)
$csl_config_nbc = array(
    'general' => array(
    	'storage'      => 'mysqli',
    ),
    'storage' => array(
    	'database'     => DB_NAME,
    	'table_name'   => $wpdb->prefix . 'xtr_nbc_b8_wordlist',
    	'host'         => DB_HOST,
    	'user'         => DB_USER,
    	'pass'         => DB_PASSWORD
    ),
    'lexer' => array(
    	'old_get_html' => FALSE,
    	'get_html'     => TRUE
    ),
    'degenerator' => array(
    	'multibyte'   => TRUE
    ),
);

$csl_s_user_is_manager = current_user_can('edit_others_posts');

/*
HELPING EXAMPLES:
- Getting options: $users = get_option( CSL_DATA_PREFIX . 'users', array() );
- Setting options: update_option( CSL_DATA_PREFIX . 'users', array_diff($users , array($user_id)) );	
*/

define('CSL_DEFAULT_NEWS_TO_SHOW', 15);
define('CSL_DEFAULT_RECENT_RECORDS', 10);
define('CSL_ADMISSIBLE_RECORDING_ERROR_RATE', 0.25);
define('CSL_TOP_RECORDS_TO_SHOW', 30);
define('CSL_FAKE_AUTHORS_PREFIX', 'fake_');

// Editable options
$csl_a_default_options = array(
    'project_name' => CSL_PROJECT_NAME,
    'project_start_date' => strtotime('2014-12-15'),
    'project_end_date' => strtotime('2016-12-15'),
    'news_to_show' => CSL_DEFAULT_NEWS_TO_SHOW,
    'recent_records' => CSL_DEFAULT_RECENT_RECORDS,
    'error_rate' => CSL_ADMISSIBLE_RECORDING_ERROR_RATE,
    'top_records' => CSL_TOP_RECORDS_TO_SHOW,
    'entities_target' => 10000,
    'persons_target' => 1000,
    'papers_target' => 1000,
    'companies_target' => 1000,
    'exhibitions_target' => 7000,
    'artworks_target' => 5000,
    'rss_uris_target' => 7000,
    'html_uris_target' => 10000,
    'beagle_global_threshold' => 15,
    'beagle_relative_threshold' => 3,
    'beagle_isactive' => 1,
    'beagle_next_exec_date' => strtotime(date('Y-m-d')),
    'beagle_schedule_plan' => 'daily',
);

$csl_a_options = get_option( 'csl_settings', $csl_a_default_options );

$csl_a_targets = array(
    'target_dates' => array(
        'project_start' => $csl_a_options['project_start_date'],
        'project_end' => $csl_a_options['project_end_date'],
    ),
    'target_records' => array(
        CSL_CUSTOM_POST_ENTITY_TYPE_NAME => $csl_a_options['entities_target'],
        CSL_CUSTOM_POST_PERSON_TYPE_NAME => $csl_a_options['persons_target'], 
        CSL_CUSTOM_POST_BOOK_TYPE_NAME => $csl_a_options['papers_target'], 
        CSL_CUSTOM_POST_COMPANY_TYPE_NAME => $csl_a_options['companies_target'],
        CSL_CUSTOM_POST_EXHIBITION_TYPE_NAME => $csl_a_options['exhibitions_target'], 
        CSL_CUSTOM_POST_ARTWORK_TYPE_NAME => $csl_a_options['artworks_target'], 
    ),
    'target_fields' => array(
        CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'rss_uri' => $csl_a_options['rss_uris_target'],
        CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'html_uri' => $csl_a_options['html_uris_target'],    
    ),
    'target_fields_human_name' => array(
        CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'rss_uri' => __('RSS URIs', CSL_TEXT_DOMAIN_PREFIX),
        CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'html_uri' => __('HTML URIs', CSL_TEXT_DOMAIN_PREFIX),    
    ),
    'target_level' => array(
        'error_rate' => $csl_a_options['error_rate'],
        'average_current_level' => 0,
        'average_operations_time' => 0,    
        'due_average_operations_time' => 0,    
    ),
);

// Autolookup fields for quality verification
$csl_a_autolookup_fields = array(
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'parent_entity',
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'entity_relation',
	CSL_DATA_FIELD_PREFIX . CSL_BOOKS_DATA_PREFIX . 'paper_author',
	CSL_DATA_FIELD_PREFIX . CSL_BOOKS_DATA_PREFIX . 'sponsorship',
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'parent_exhibition',
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'info_source',
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'artwork_author',
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'supporter_entity',
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'funding_entity',
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'curator',
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'catalog',
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'museography',
);

$csl_s_timestamp = current_time('timestamp');

// CUSTOM FIELDS NOMENCLATURE
$cls_a_custom_fields_nomenclature = array(
    CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'alternate_name' => __('Alternate name', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'town' => __('Town', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'url' => __('URL', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'email' => __('EMail', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'phone' => __('Phone', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'fax' => __('Fax', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'chief_executive' => __('Chief executive Officer', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'relationship_type' => __('Relationship type', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'parent_entity' => __('Parent entity', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'coordinates' => __('Coordinates', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'address' => __('Address', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'rss_uri' => __('RSS URI', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'html_uri' => __('HTML URI', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'sirius_m_total_rank' => __('Total rank', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'sirius_m_general_aspects' => __('General aspects', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'sirius_m_identity_and_information' => __('Identity and information', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'sirius_m_navigation_and_structure' => __('Navigation and structure', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'sirius_m_labeling' => __('Labeling', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'sirius_m_page_layout' => __('Page layout', CSL_TEXT_DOMAIN_PREFIX), 
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'sirius_m_understandability_and_ease_of_information' => __('Understandability and ease of information', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'sirius_m_control_and_feedback' => __('Control and feedback', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'sirius_m_multimedia_elements' => __('Multimedia elements', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'sirius_m_search' => __('Search', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'sirius_m_help' => __('Help', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'sirius_m_seo' => __('SEO', CSL_TEXT_DOMAIN_PREFIX), 
	CSL_DATA_FIELD_PREFIX . CSL_ENTITIES_DATA_PREFIX . 'sirius_m_social_networks' => __('Social networks', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_PERSONS_DATA_PREFIX . 'person_type' => __('Person type', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_PERSONS_DATA_PREFIX . 'last_name' => __('Last name', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_PERSONS_DATA_PREFIX . 'first_name' => __('First name', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_PERSONS_DATA_PREFIX . 'gender' => __('Gender', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_PERSONS_DATA_PREFIX . 'birth_date' => __('Birth date', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_PERSONS_DATA_PREFIX . 'death_date' => __('Death date', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_PERSONS_DATA_PREFIX . 'country' => __('Country', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_PERSONS_DATA_PREFIX . 'entity_relation' => __('Entity relation', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_PERSONS_DATA_PREFIX . 'person_relation' => __('Person relation', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_BOOKS_DATA_PREFIX . 'publishing_date' => __('Publishing date', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_BOOKS_DATA_PREFIX . 'publishing_place' => __('Publishing place', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_BOOKS_DATA_PREFIX . 'paper_author' => __('Paper author', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_BOOKS_DATA_PREFIX . 'paper_editor' => __('Paper editor', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_BOOKS_DATA_PREFIX . 'paper_illustrator' => __('Paper illustrator', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_BOOKS_DATA_PREFIX . 'sponsorship' => __('Sponsorship', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_BOOKS_DATA_PREFIX . 'artwork_author' => __('Artwork author', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_BOOKS_DATA_PREFIX . 'artwork' => __('Artwork', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_COMPANIES_DATA_PREFIX . 'company_headquarter_place' => __('Company headquarter place', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_COMPANIES_DATA_PREFIX . 'company_dimension' => __('Company dimension', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'crc32b_identifier' => __('CRC32B identifier', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'global_keywords_weight' => __('Global keywords weight', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'relative_keywords_weight' => __('Relative keywords weight', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'found_keywords' => __('Found keywords', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'original_publishing_date' => __('Publishing date', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'original_reference_author' => __('Reference author', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'original_source_link' => __('Original source link', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'source_entity' => __('Source entity', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'exhibition_start_date' => __('Start date', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'exhibition_end_date' => __('End date', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'exhibition_town' => __('Town', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'exhibition_site' => __('Site', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'exhibition_visitors' => __('Visitors range', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'exhibition_access' => __('Access type', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'coordinates' => __('Coordinates', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'address' => __('Address', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'relational_type' => __('Relational type', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'parent_exhibition' => __('Parent exhibition', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'info_source' => __('Information source', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'artwork_author' => __('Artwork author', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'supporter_entity' => __('Supporter entity', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'funding_entity' => __('Funding entity', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'curator' => __('Curator', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'catalog' => __('Catalog', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'museography' => __('Company responsible for the museography', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'geotag' => __('Geotag', CSL_TEXT_DOMAIN_PREFIX),
	CSL_DATA_FIELD_PREFIX . CSL_EXHIBITIONS_DATA_PREFIX . 'art_collector' => __('Art collector', CSL_TEXT_DOMAIN_PREFIX),		            		
);	
$cls_a_custom_taxonomies_nomenclature = array(
	'tax_typology' => __('Typology', CSL_TEXT_DOMAIN_PREFIX),
	'tax_ownership' => __( 'Ownership', CSL_TEXT_DOMAIN_PREFIX ),
	'tax_keyword' => __( 'Keyword', CSL_TEXT_DOMAIN_PREFIX ),
	'tax_activity' => __( 'Activity', CSL_TEXT_DOMAIN_PREFIX ),
	'tax_publisher' => __( 'Publisher', CSL_TEXT_DOMAIN_PREFIX ),
	'tax_catalog_typology' => __( 'Catalog typology', CSL_TEXT_DOMAIN_PREFIX ),
	'tax_isic4_category' => __( 'ISIC4 Category', CSL_TEXT_DOMAIN_PREFIX ),
	'tax_exhibition_type' => __( 'Exhibition type', CSL_TEXT_DOMAIN_PREFIX ),
	'tax_artwork_type' => __( 'Artwork type', CSL_TEXT_DOMAIN_PREFIX ),
	'tax_topic' => __( 'Topic', CSL_TEXT_DOMAIN_PREFIX ),
	'tax_movement' => __( 'Movement', CSL_TEXT_DOMAIN_PREFIX ),
	'tax_period' => __( 'Period', CSL_TEXT_DOMAIN_PREFIX ),
);

$cls_a_custom_taxonomies_hierarchical = array(
    'tax_topic',
    'tax_artwork_type',
);

$csl_global_nonce = wp_create_nonce( NONCE_KEY );

?>