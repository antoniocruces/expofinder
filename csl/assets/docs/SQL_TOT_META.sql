SELECT 
    wpaef_posts.post_type, 
    wpaef_posts.ID, 
    wpaef_posts.post_author, 
    wpaef_users.display_name,
    wpaef_posts.post_modified, 
    wpaef_posts.post_date, 
    wpaef_posts.post_title, 
    wpaef_posts.post_excerpt, 
    wpaef_posts.post_status, 
    wpaef_postmeta.meta_key, 
    wpaef_postmeta.meta_value
FROM 
    (
    wpaef_posts 
    LEFT JOIN 
    wpaef_users 
    ON 
    wpaef_posts.ID = wpaef_users.ID
    ) 
    LEFT JOIN 
    wpaef_postmeta 
    ON 
    wpaef_posts.ID = wpaef_postmeta.post_id
ORDER BY 
    wpaef_posts.post_type,
    wpaef_users.display_name, 
    wpaef_posts.post_modified DESC , 
    wpaef_posts.post_date DESC;
    
SELECT 
    p.post_type, 
    p.ID, p.post_author, 
    u.display_name, 
    p.post_modified, 
    p.post_date, 
    p.post_title, 
    p.post_excerpt, 
    p.post_status, 
    tt.taxonomy, t.name
FROM 
    (
    wpaef_posts AS p 
    LEFT JOIN 
    wpaef_users AS u 
    ON 
    p.ID = u.ID
    ) 
    LEFT JOIN 
    (
    (
    wpaef_terms AS t 
    RIGHT JOIN 
    wpaef_term_taxonomy AS tt 
    ON t.term_id = tt.term_id
    ) 
    RIGHT JOIN 
    wpaef_term_relationships AS tr
    ON tt.term_taxonomy_id = tr.term_taxonomy_id
    ) 
    ON p.ID = tr.object_id
ORDER BY 
    p.post_type, 
    u.display_name, 
    p.post_modified DESC, 
    p.post_date DESC;


SELECT 
    tv.display_name,
    DATE_FORMAT(tv.post_date,"%Y-%m-%d") AS post_date, 
    COUNT(DISTINCT tv.taxonomy) AS n_count_taxonomies,    
    IF(post_type='entity',COUNT(DISTINCT tv.ID),0) AS n_count_entities,
    IF(post_type='person',COUNT(DISTINCT tv.ID),0) AS n_count_persons,
    IF(post_type='book',COUNT(DISTINCT tv.ID),0) AS n_count_books,
    IF(post_type='company',COUNT(DISTINCT tv.ID),0) AS n_count_companies,
    IF(post_type='exhibition',COUNT(DISTINCT tv.ID),0) AS n_count_entities,
    COUNT(DISTINCT tv.ID) AS n_count_total
FROM 
    wpaef_xtr_vw_total_taxonomy AS tv
GROUP BY 
    tv.display_name, 
    DATE_FORMAT(tv.post_date,"%Y-%m-%d");
    
SELECT 
    DATE_FORMAT(tv.post_date,"%Y-%m-%d") AS post_date, 
    COUNT(DISTINCT tv.taxonomy) AS n_count_taxonomies,    
    IF(post_type='entity',COUNT(DISTINCT tv.ID),0) AS n_count_entities,
    IF(post_type='person',COUNT(DISTINCT tv.ID),0) AS n_count_persons,
    IF(post_type='book',COUNT(DISTINCT tv.ID),0) AS n_count_books,
    IF(post_type='company',COUNT(DISTINCT tv.ID),0) AS n_count_companies,
    IF(post_type='exhibition',COUNT(DISTINCT tv.ID),0) AS n_count_entities,
    COUNT(DISTINCT tv.ID) AS n_count_total
FROM 
    wpaef_xtr_vw_total_taxonomy AS tv
GROUP BY 
    DATE_FORMAT(tv.post_date,"%Y-%m-%d");
    
    
    
SELECT 
    p.ID, 
    u.display_name,
    p.post_title, 
    m.meta_key, 
    m.meta_value
FROM 
    (
    wpaef_posts p
    LEFT JOIN 
    wpaef_users u
    ON 
    wpaef_posts.ID = wpaef_users.ID
    ) 
    LEFT JOIN 
    wpaef_postmeta 
    ON 
    wpaef_posts.ID = wpaef_postmeta.post_id
WHERE
	p.post_type = 'entity' 
	AND
    p.post_status = 'publish'
    
ORDER BY 
    wpaef_posts.post_type,
    wpaef_users.display_name, 
    wpaef_posts.post_modified DESC , 
    wpaef_posts.post_date DESC;


/* DUPLICATE SEARCH */
SELECT 
    p1.ID as id1, 
    p1.post_title as title1, 
    p1.post_author as post_author1,
    p2.ID as id2, 
    p2.post_title as title2,
    p2.post_author as post_author2
FROM 
	wpaef_posts AS p1 
	JOIN 
	wpaef_posts AS p2 
	ON 
	(
	p1.ID < p2.ID   /* don't duplicate pairs a/b b/a */  
	AND 
	p1.post_title= p2.post_title 
	);
	
	
/************/
SELECT 
meta_value,
LENGTH(REPLACE(meta_value, ', ', ',')) - LENGTH(REPLACE(REPLACE(meta_value, ', ', ','),',','')) AS findme_count

FROM
	wpaef_postmeta
WHERE
	meta_key LIKE "%_town"

/*************/
SELECT 
	IF( LENGTH( meta_value ) - LENGTH( REPLACE( meta_value, ';', '' ) ) + 1 >= 3, 
    SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ';', 3), ';', -1),
    '') AS s_country,
	IF( LENGTH( meta_value ) - LENGTH( REPLACE( meta_value, ';', '' ) ) + 1 >= 3, 
    SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ';', 2), ';', -1),
    '') AS s_autonomous_community,
	IF( LENGTH( meta_value ) - LENGTH( REPLACE( meta_value, ';', '' ) ) + 1 >= 3, 
    SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ';', 1), ';', -1),
    '') AS s_town,
    COUNT(DISTINCT post_id) AS n_posts,
    COUNT(DISTINCT meta_id) AS n_meta
FROM
	wpaef_postmeta
WHERE
	meta_key LIKE "%_town"
GROUP BY
	IF( LENGTH( meta_value ) - LENGTH( REPLACE( meta_value, ';', '' ) ) + 1 >= 3, 
    SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ';', 3), ';', -1),
    ''),
	IF( LENGTH( meta_value ) - LENGTH( REPLACE( meta_value, ';', '' ) ) + 1 >= 3, 
    SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ';', 2), ';', -1),
    ''),
	IF( LENGTH( meta_value ) - LENGTH( REPLACE( meta_value, ';', '' ) ) + 1 >= 3, 
    SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, ';', 1), ';', -1),
    '')

/**************/
/*
AUTOLOOKUP_FIELDS:

ent
parent_entity
entity_relation

boo
paper_author
sponsorship

exh
parent_exhibition
info_source
artwork_author
supporter_entity
funding_entity
curator
catalog
museography

*/