SELECT SQL_CACHE
	post_type,
	meta_key,
	s_typ_ref_id,
	COUNT(id) as n_rels
FROM
	wpaef_xtr_vw_postmeta_referenced
WHERE 
	n_ref_id IS NOT NULL
	AND
	s_typ_ref_id IS NOT NULL
GROUP BY
	post_type,
	meta_key,
	s_typ_ref_id	
	
/** **/

SELECT SQL_CACHE
	post_type,
	s_typ_ref_id,
	COUNT(id) as n_rels
FROM
	wpaef_xtr_vw_postmeta_referenced
WHERE 
	n_ref_id IS NOT NULL
	AND
	s_typ_ref_id IS NOT NULL
GROUP BY
	post_type,
	s_typ_ref_id	
	
/** **/

SELECT SQL_CACHE
	YEAR(meta_value) AS n_year,
	MONTH(meta_value) AS n_month,
	COUNT(post_id) as n_rels
FROM
	wpaef_postmeta
WHERE 
	DATE(meta_value)
	AND
	YEAR(meta_value) BETWEEN (YEAR(CURDATE())-1) AND YEAR(CURDATE())
	AND
	meta_key = "_cp__exh_exhibition_start_date"
GROUP BY
	YEAR(meta_value),
	MONTH(meta_value)
	
/** **/
SELECT SQL_CACHE
	SUBSTRING_INDEX(meta_value, ";", 1) AS s_town,
	COUNT(post_id) as n_towns
FROM
	wpaef_postmeta
WHERE 
	meta_key = "_cp__exh_exhibition_town"
	AND
	meta_value IS NOT NULL
GROUP BY
	SUBSTRING_INDEX(meta_value, ";", 1)
ORDER BY 
	COUNT(post_id) DESC 
LIMIT
	10


/** **/
CREATE OR REPLACE VIEW wpaef_xtr_vw_related_posts AS 
SELECT SQL_CACHE
	p.ID AS n_post_id,
	p.post_title AS s_post_title,
	p.post_type AS s_post_type,
	r.ID AS n_ref_id,
	r.post_title AS s_ref_title,
	r.post_type AS s_ref_type,
	m.meta_key,
	m.meta_value
FROM
	wpaef_postmeta AS m
	INNER JOIN 
	wpaef_posts AS p 
	ON
	p.ID = m.post_id
	INNER JOIN
	wpaef_posts AS r 
	ON
	r.ID = CAST(SUBSTRING_INDEX(m.meta_value, ": ", 1) AS UNSIGNED)
WHERE
	m.meta_key IN ("_cp__boo_paper_author", "_cp__boo_sponsorship", "_cp__ent_parent_entity", "_cp__exh_art_collector", "_cp__exh_artwork_author", "_cp__exh_catalog", "_cp__exh_curator", "_cp__exh_funding_entity", "_cp__exh_info_source", "_cp__exh_museography", "_cp__exh_parent_exhibition", "_cp__exh_source_entity", "_cp__exh_supporter_entity", "_cp__peo_person_relation")
	AND
	m.meta_value IS NOT NULL;
	
/** **/
SELECT SQL_CACHE
	c.s_post_type,
	c.meta_key,
	c.s_ref_type,
	COUNT(c.n_post_id) AS n_rel_count
FROM
	(
		SELECT 
			p.ID AS n_post_id,
			p.post_title AS s_post_title,
			p.post_type AS s_post_type,
			r.ID AS n_ref_id,
			r.post_title AS s_ref_title,
			r.post_type AS s_ref_type,
			m.meta_key,
			m.meta_value
		FROM
			wpaef_postmeta AS m
			INNER JOIN 
			wpaef_posts AS p 
			ON
			p.ID = m.post_id
			INNER JOIN
			wpaef_posts AS r 
			ON
			r.ID = CAST(SUBSTRING_INDEX(m.meta_value, ": ", 1) AS UNSIGNED)
		WHERE
			m.meta_key IN ("_cp__boo_paper_author", "_cp__boo_sponsorship", "_cp__ent_parent_entity", "_cp__exh_art_collector", "_cp__exh_artwork_author", "_cp__exh_catalog", "_cp__exh_curator", "_cp__exh_funding_entity", "_cp__exh_info_source", "_cp__exh_museography", "_cp__exh_parent_exhibition", "_cp__exh_source_entity", "_cp__exh_supporter_entity", "_cp__peo_person_relation")
			AND
			m.meta_value IS NOT NULL
	) AS c 
GROUP BY 
	c.s_post_type,
	c.meta_key,
	c.s_ref_type	

