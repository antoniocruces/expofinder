SELECT
	e.ID AS ent_n_id,
	e.post_title AS ent_entity_name,
	e.n_latitude AS ent_n_latitude,
	e.n_longitude AS ent_n_longitude,
	x.n_latitude AS exh_n_latitude,
	x.n_longitude AS exh_n_longitude,
	s.meta_value AS exh_site,
	COUNT(DISTINCT x.ID) AS n_num_exhibitions
FROM
	(SELECT post_id, n_ext_id FROM wpaef_xtr_vw_normalized_relations WHERE s_int_post_type = "exhibition" AND s_ext_post_type = "entity" AND meta_key = "_cp__exh_supporter_entity") AS l 
	INNER JOIN
	(SELECT ID, post_title, n_latitude, n_longitude FROM wpaef_xtr_vw_normalized_coordinates WHERE post_type = "entity") AS e
	ON
	l.n_ext_id = e.ID
	INNER JOIN 
	(SELECT ID, n_latitude, n_longitude FROM wpaef_xtr_vw_normalized_coordinates WHERE post_type = "exhibition") AS x 
	ON 
	l.post_id = x.ID 
	INNER JOIN
	(SELECT post_id, meta_value FROM wpaef_postmeta WHERE meta_key = "_cp__exh_exhibition_site") AS s
	ON
	l.post_id = s.post_id
GROUP BY
	e.ID,
	e.post_title,
	e.n_latitude,
	e.n_longitude,
	x.n_latitude,
	x.n_longitude,
	s.meta_value;