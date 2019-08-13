CREATE OR REPLACE VIEW wpaef_xtr_vw_meta_tax AS
SELECT SQL_CACHE
	wp.post_type,
	wp.ID as post_id,
	'meta' as info_type,
	wm.meta_key as info_key,
	wm.meta_value as info_value
FROM 
	wpaef_posts wp
	LEFT JOIN 
	wpaef_postmeta wm 
	ON (wm.post_id = wp.ID AND wm.meta_key LIKE '_cp_%')
	LEFT JOIN
	wpaef_users u 
	ON (wp.post_author = u.ID)
WHERE
	wp.post_status = 'publish' 
	AND 
	wp.post_type IN ('entity','person','book','company','exhibition')
	
UNION ALL

SELECT 
	wp.post_type,
	wp.ID as post_id,
	'taxonomy' as info_type,
	wtt.taxonomy as info_key,
	wt.name as info_value
FROM 
	wpaef_posts wp
	LEFT JOIN 
	wpaef_term_relationships wtr 
	ON (wp.ID = wtr.object_id)
	LEFT JOIN 
	wpaef_term_taxonomy wtt 
	ON (wtr.term_taxonomy_id = wtt.term_taxonomy_id AND wtt.taxonomy like 'tax_%')
	LEFT JOIN 
	wpaef_terms wt 
	ON (wt.term_id = wtt.term_id)
	LEFT JOIN
	wpaef_users u 
	ON (wp.post_author = u.ID)
WHERE
	wp.post_status = 'publish' 
	AND 
	wp.post_type IN ('entity','person','book','company','exhibition')


ORDER BY 
	post_type,
	post_id,
	info_type,
	info_key,
	info_value
	


SELECT
	a.info_value AS info_value_a,
	b.info_value AS info_value_b,
	c.info_value AS info_value_c,
	COUNT(a.post_id) AS n_posts
FROM
	wpaef_xtr_vw_meta_tax AS a 
	INNER JOIN
	(
		SELECT 
			aa.info_value,
			aa.post_id
		FROM
			wpaef_xtr_vw_meta_tax AS aa
		WHERE
			aa.info_key = "tax_exhibition_type"
	) AS b 
	ON a.post_id = b.post_id
	INNER JOIN
	(
		SELECT 
			bb.info_value,
			bb.post_id
		FROM
			wpaef_xtr_vw_meta_tax AS bb
		WHERE
			bb.info_key = "tax_movement"
	) AS c 
	ON a.post_id = c.post_id
WHERE
	a.info_key = "_cp__exh_exhibition_town"
GROUP BY
	a.info_value,
	b.info_value,
	c.info_value
HAVING
	COUNT(a.post_id) > 1
ORDER BY
	a.info_value,
	COUNT(a.post_id) DESC,
	b.info_value,
	c.info_value
	
	