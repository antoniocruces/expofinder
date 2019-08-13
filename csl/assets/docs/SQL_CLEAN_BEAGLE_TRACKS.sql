					UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, 'â€œ', '\"');
					UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, 'â€', '\"');
					UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, 'â€™', '’');
					UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, 'â€˜', '‘');
					UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, 'â€”', '–');
					UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, 'â€“', '—');
					UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, 'â€¢', '-');
					UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, 'â€¦', '…');
										
					UPDATE {$wpdb->posts} SET post_excerpt = REPLACE(post_excerpt, 'â€œ', '“');
					UPDATE {$wpdb->posts} SET post_excerpt = REPLACE(post_excerpt, 'â€', '”');
					UPDATE {$wpdb->posts} SET post_excerpt = REPLACE(post_excerpt, 'â€™', '’');
					UPDATE {$wpdb->posts} SET post_excerpt = REPLACE(post_excerpt, 'â€˜', '‘');
					UPDATE {$wpdb->posts} SET post_excerpt = REPLACE(post_excerpt, 'â€”', '–');
					UPDATE {$wpdb->posts} SET post_excerpt = REPLACE(post_excerpt, 'â€“', '—');
					UPDATE {$wpdb->posts} SET post_excerpt = REPLACE(post_excerpt, 'â€¢', '-');
					UPDATE {$wpdb->posts} SET post_excerpt = REPLACE(post_excerpt, 'â€¦', '…');
					
					UPDATE {$wpdb->posts} SET post_title = REPLACE(post_title, 'â€œ', '“');
					UPDATE {$wpdb->posts} SET post_title = REPLACE(post_title, 'â€', '”');
					UPDATE {$wpdb->posts} SET post_title = REPLACE(post_title, 'â€™', '’');
					UPDATE {$wpdb->posts} SET post_title = REPLACE(post_title, 'â€˜', '‘');
					UPDATE {$wpdb->posts} SET post_title = REPLACE(post_title, 'â€”', '–');
					UPDATE {$wpdb->posts} SET post_title = REPLACE(post_title, 'â€“', '—');
					UPDATE {$wpdb->posts} SET post_title = REPLACE(post_title, 'â€¢', '-');
					UPDATE {$wpdb->posts} SET post_title = REPLACE(post_title, 'â€¦', '…');

DELETE a
FROM 
	wpaef_terms a 
WHERE 
	a.term_id IN (SELECT term_id FROM wpaef_term_taxonomy tt WHERE count = 0 );
	
DELETE a
FROM 
	wpaef_term_taxonomy a 
WHERE 
	a.term_id NOT IN (SELECT term_id FROM wpaef_terms t);
	
DELETE a
FROM 
	wpaef_term_relationships a
WHERE 
	a.term_taxonomy_id NOT IN (SELECT term_taxonomy_id FROM wpaef_term_taxonomy tt);


DELETE FROM `wp_options` WHERE `option_name` LIKE ('_transient%_feed_%');

DELETE 
	a,b,c 
FROM 
	(
    wpaef_posts a 
    LEFT JOIN 
    wpaef_term_relationships b 
    ON 
    a.ID = b.object_id
    ) 
    LEFT JOIN 
    wpaef_postmeta c 
    ON 
    a.ID = c.post_id
WHERE 
	a.post_type = 'revision';
	

OPTIMIZE TABLE 
	wpaef_terms, 
	wpaef_term_taxonomy, 
	wpaef_term_relationships, 
	wpaef_posts, 
	wpaef_postmeta,
	wpaef_users, 
	wpaef_usermeta, 
	wpaef_xtr_activity_log, 
	wpaef_xtr_beaglecr_log,
	wpaef_xtr_urierror,
	wpaef_options;

DELETE a, b, c, d 
FROM wpaef_posts a
LEFT JOIN wpaef_term_relationships b ON ( a.ID = b.object_id )
LEFT JOIN wpaef_postmeta c ON ( a.ID = c.post_id )
LEFT JOIN wpaef_term_taxonomy d ON ( d.term_taxonomy_id = b.term_taxonomy_id )
LEFT JOIN wpaef_terms e ON ( e.term_id = d.term_id )
WHERE a.post_type = 'exhibition' AND a.post_status = 'draft';

DELETE a
FROM wpaef_xtr_activity_log a
WHERE
a.object_type = 'auto_exhibition';