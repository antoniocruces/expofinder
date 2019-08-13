SELECT DISTINCT
    post_type,
    taxonomy, 
    term
FROM
	wpaef_xtr_vw_complete_tax_meta_posts;

/***/	

SELECT DISTINCT
    post_type,
    meta_key, 
    n_year,
    n_month,
    n_day,
    n_century,
    n_half_century
FROM
	wpaef_xtr_vw_complete_tax_meta_posts
WHERE
	d_date IS NOT NULL;

/***/	

SELECT DISTINCT
    post_type,
    meta_key, 
    s_geo_country,
    s_geo_region,
    s_geo_town
FROM
	wpaef_xtr_vw_complete_tax_meta_posts
WHERE
	s_geo_country IS NOT NULL;

/***/	

SELECT DISTINCT
	ID,
    post_type,
    meta_key, 
    n_latitude,
    n_longitude
FROM
	wpaef_xtr_vw_complete_tax_meta_posts
WHERE
	n_latitude IS NOT NULL
	AND
	n_longitude IS NOT NULL;

/***/	

SELECT DISTINCT
	ID,
    post_title,
    term,
    n_latitude,
    n_longitude
FROM
	wpaef_xtr_vw_complete_tax_meta_posts
WHERE
	post_type = "entity"
	AND
	taxonomy = "tax_typology"
	AND
	n_latitude IS NOT NULL
	AND
	n_longitude IS NOT NULL;
	
/***/	

SELECT DISTINCT
	ID,
    post_title,
    term,
    n_latitude,
    n_longitude
FROM
	wpaef_xtr_vw_complete_tax_meta_posts
WHERE
	post_type = "entity"
	AND
	taxonomy = "tax_ownership"
	AND
	n_latitude IS NOT NULL
	AND
	n_longitude IS NOT NULL;
	
/***/
/*** OJO SELECTOR EXPOSICIONES Y ENTODADES ORGANIZADORAS ***/
SELECT DISTINCT
	e.ID,
    e.post_title,
    e.n_latitude,
    e.n_longitude,
    ev.s_exhibition_site,
    et.s_exhibition_types,
    em.s_exhibition_movements,
    df.d_date AS d_date_from,
    dt.d_date AS d_date_to,
    ls.n_ent_id,
    se.s_ent_post_title,
    se.s_ent_typology,
    sc.n_ent_latitude,
    sc.n_ent_longitude
FROM
	(SELECT DISTINCT ID, post_title, n_latitude, n_longitude FROM wpaef_xtr_vw_complete_tax_meta_posts WHERE post_type = "exhibition" AND n_latitude IS NOT NULL and n_longitude IS NOT NULL) AS e 
    LEFT JOIN
	(SELECT DISTINCT ID, meta_value AS s_exhibition_site FROM wpaef_xtr_vw_complete_tax_meta_posts WHERE meta_key = "_cp__exh_exhibition_site" AND meta_value IS NOT NULL) AS ev
    ON
    (e.ID = ev.ID)
    LEFT JOIN
	(SELECT DISTINCT ID, GROUP_CONCAT(DISTINCT term ORDER BY term ASC SEPARATOR ", ") AS s_exhibition_types FROM wpaef_xtr_vw_complete_tax_meta_posts WHERE taxonomy = "tax_exhibition_type" GROUP BY ID) AS et
    ON
    (e.ID = et.ID)
    LEFT JOIN
	(SELECT DISTINCT ID, GROUP_CONCAT(DISTINCT term ORDER BY term ASC SEPARATOR ", ") AS s_exhibition_movements FROM wpaef_xtr_vw_complete_tax_meta_posts WHERE taxonomy = "tax_movement" GROUP BY ID) AS em
    ON
    (e.ID = em.ID)
    LEFT JOIN
	(SELECT DISTINCT ID, d_date FROM wpaef_xtr_vw_complete_tax_meta_posts WHERE meta_key = "_cp__exh_exhibition_start_date" AND d_date IS NOT NULL) AS df
    ON
    (e.ID = df.ID)
    LEFT JOIN
	(SELECT DISTINCT ID, d_date FROM wpaef_xtr_vw_complete_tax_meta_posts WHERE meta_key = "_cp__exh_exhibition_end_date" AND d_date IS NOT NULL) AS dt
    ON
    (e.ID = dt.ID)
    LEFT JOIN
    (SELECT DISTINCT ID, n_ext_id AS n_ent_id FROM wpaef_xtr_vw_complete_tax_meta_posts WHERE meta_key = "_cp__exh_supporter_entity" AND n_ext_id IS NOT NULL) AS ls
    ON
    (e.ID = ls.ID)
    LEFT JOIN
    (SELECT DISTINCT ID, post_title AS s_ent_post_title, GROUP_CONCAT(DISTINCT term ORDER BY term ASC SEPARATOR ", ") AS s_ent_typology FROM wpaef_xtr_vw_complete_tax_meta_posts WHERE post_type = "entity" AND taxonomy = "tax_typology" AND term IS NOT NULL GROUP BY ID) AS se
    ON
    (ls.n_ent_id = se.ID)
    LEFT JOIN
    (SELECT DISTINCT ID, n_latitude AS n_ent_latitude, n_longitude AS n_ent_longitude FROM wpaef_xtr_vw_complete_tax_meta_posts WHERE post_type = "entity" AND n_latitude IS NOT NULL and n_longitude IS NOT NULL) AS sc
    ON
    (ls.n_ent_id = sc.ID);   
    
/***/
/*** OJO ENTIDADES ORGANIZADORAS Y EXPOSICIONES ***/
SELECT DISTINCT
	e.ID,
    e.n_unique_id,
    e.post_title,
    e.n_ent_latitude,
    e.n_ent_longitude,
    eg.s_ent_geo_country,
    eg.s_ent_geo_region,
    eg.s_ent_geo_town,
    t.s_typology,
    o.s_ownership,
    ds.d_date,
    sc.n_exh_latitude,
    sc.n_exh_longitude,
    xg.s_exh_geo_country,
    xg.s_exh_geo_region,
    xg.s_exh_geo_town,
    ev.s_exhibition_site,
    et.s_exhibition_types,
    em.s_exhibition_movements
FROM
    (SELECT DISTINCT ID, n_unique_id, post_title, n_latitude AS n_ent_latitude, n_longitude AS n_ent_longitude FROM wpaef_xtr_vw_complete_tax_meta_posts WHERE post_type = "entity" AND n_latitude IS NOT NULL and n_longitude IS NOT NULL) AS e
    LEFT JOIN
    (SELECT DISTINCT ID, s_geo_country AS s_ent_geo_country, s_geo_region AS s_ent_geo_region, s_geo_town AS s_ent_geo_town FROM wpaef_xtr_vw_complete_tax_meta_posts WHERE post_type = "entity" AND s_geo_country IS NOT NULL) AS eg
    ON
    (e.ID = eg.ID)
    LEFT JOIN
    (SELECT DISTINCT ID, GROUP_CONCAT(DISTINCT term ORDER BY term ASC SEPARATOR ", ") AS s_typology FROM wpaef_xtr_vw_complete_tax_meta_posts WHERE taxonomy = "tax_typology" GROUP BY ID) AS t
    ON
    (e.ID = t.ID)
    LEFT JOIN
    (SELECT DISTINCT ID, GROUP_CONCAT(DISTINCT term ORDER BY term ASC SEPARATOR ", ") AS s_ownership FROM wpaef_xtr_vw_complete_tax_meta_posts WHERE taxonomy = "tax_ownership" GROUP BY ID) AS o
    ON
    (e.ID = o.ID)      
    LEFT JOIN
    (SELECT DISTINCT ID, n_ext_id AS n_ent_id FROM wpaef_xtr_vw_complete_tax_meta_posts WHERE meta_key = "_cp__exh_supporter_entity" AND n_ext_id IS NOT NULL) AS es
    ON
    (e.ID = es.n_ent_id)
    LEFT JOIN
	(SELECT DISTINCT ID, d_date FROM wpaef_xtr_vw_complete_tax_meta_posts WHERE meta_key = "_cp__exh_exhibition_start_date" AND d_date IS NOT NULL) AS ds
    ON
    (es.ID = ds.ID)
    LEFT JOIN
    (SELECT DISTINCT ID, n_latitude AS n_exh_latitude, n_longitude AS n_exh_longitude FROM wpaef_xtr_vw_complete_tax_meta_posts WHERE post_type = "exhibition" AND n_latitude IS NOT NULL and n_longitude IS NOT NULL) AS sc
    ON
    (es.ID = sc.ID)   
    LEFT JOIN
    (SELECT DISTINCT ID, meta_value AS s_exhibition_site FROM wpaef_xtr_vw_complete_tax_meta_posts WHERE meta_key = "_cp__exh_exhibition_site" AND meta_value IS NOT NULL) AS ev
    ON
    (es.ID = ev.ID)
    LEFT JOIN
	(SELECT DISTINCT ID, GROUP_CONCAT(DISTINCT term ORDER BY term ASC SEPARATOR ", ") AS s_exhibition_types FROM wpaef_xtr_vw_complete_tax_meta_posts WHERE taxonomy = "tax_exhibition_type" GROUP BY ID) AS et
    ON
    (es.ID = et.ID)
    LEFT JOIN
	(SELECT DISTINCT ID, GROUP_CONCAT(DISTINCT term ORDER BY term ASC SEPARATOR ", ") AS s_exhibition_movements FROM wpaef_xtr_vw_complete_tax_meta_posts WHERE taxonomy = "tax_movement" GROUP BY ID) AS em
    ON
    (es.ID = em.ID)
    LEFT JOIN
    (SELECT DISTINCT ID, s_geo_country AS s_exh_geo_country, s_geo_region AS s_exh_geo_region, s_geo_town AS s_exh_geo_town FROM wpaef_xtr_vw_complete_tax_meta_posts WHERE post_type = "exhibition" AND s_geo_country IS NOT NULL) AS xg
    ON
    (es.ID = xg.ID)
    