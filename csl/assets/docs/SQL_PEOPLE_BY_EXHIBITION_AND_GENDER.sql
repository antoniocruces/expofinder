SELECT 
    p.ID AS id,
    SUBSTRING_INDEX( SUBSTRING_INDEX( m.meta_value, ",", 1 ), ",", -1 ) AS latitude,
    SUBSTRING_INDEX( SUBSTRING_INDEX( m.meta_value, ",", 2 ), ",", -1 ) AS longitude,
    REPLACE(QUOTE(REPLACE(p.post_title, ",", "&#44;")),"'","") AS name,
    d.d_exh_date,
    u.meta_key AS role,
    g.s_gender AS gender,
    COUNT(u.n_ext_id) AS n_people
FROM  
    wpaef_posts AS p
    INNER JOIN
    (
	    SELECT 
	    	post_id,
	    	meta_value
	    FROM  
	    	wpaef_postmeta
	    WHERE
	    	meta_key LIKE "%coordinates"
    ) AS m
    ON
    p.ID = m.post_id
    INNER JOIN
    (
	    SELECT 
	    	post_id,
	    	STR_TO_date(meta_value, "%Y-%m-%d") AS d_exh_date
	    FROM  
	    	wpaef_postmeta
	    WHERE
	    	meta_key = "_cp__exh_exhibition_start_date"
            AND
            meta_value IS NOT NULL
    ) AS d
    ON
    p.ID = d.post_id
    INNER JOIN
    (
		SELECT
			ID,
			n_ext_id,
			meta_key
		FROM
			wpaef_xtr_vw_unfolded_postmeta
		WHERE
			meta_key IN ("_cp__exh_artwork_author", "_cp__exh_curator", "_cp__exh_art_collector")
    ) AS u
    ON
    p.ID = u.ID
    INNER JOIN
    (
		SELECT
			ID,
			s_gender
		FROM
			wpaef_xtr_vw_unfolded_person
    ) AS g
    ON
    u.n_ext_id = g.ID
WHERE
	p.post_type = "exhibition"
	AND 
    p.post_status = "publish"
GROUP BY 
    p.ID ,
    SUBSTRING_INDEX( SUBSTRING_INDEX( m.meta_value, ",", 1 ), ",", -1 ),
    SUBSTRING_INDEX( SUBSTRING_INDEX( m.meta_value, ",", 2 ), ",", -1 ),
    REPLACE(QUOTE(REPLACE(p.post_title, ",", "&#44;")),"'",""),
    d.d_exh_date,
    u.meta_key,
    g.s_gender
ORDER BY 
	post_type ASC,
    post_title ASC;


/*** 60 segundos ***/

SELECT SQL_CACHE
	n_latitude,
	n_longitude,
	s_geo_country,
	s_geo_region,
	s_geo_town,
	s_term,
	COUNT(DISTINCT s_rss_uri) AS n_rss_uris,
	COUNT(DISTINCT s_rss_uri) AS n_html_uris,	
	COUNT(DISTINCT n_exh_id) AS n_exhibitions
FROM
	wpaef_xtr_vw_unfolded_entity
WHERE
	s_taxonomy = "tax_typology"
GROUP BY
	n_latitude,
	n_longitude,
	s_geo_country,
	s_geo_region,
	s_geo_town,
	s_term;

/*** NO PELIGROSO  ***/
/*** Productividad de URIs por localización y tipología; cambiar "tax_typology" por "tax_ownership" para agrupar pot titularidad ***/
CREATE OR REPLACE VIEW wpaef_xtr_vw_uris_productivity AS
SELECT SQL_CACHE
    p.ID,
    p.post_type,
    p.post_title,
    y.name AS term,
    SUBSTRING_INDEX( SUBSTRING_INDEX( g.meta_value, ",", 1 ), ",", -1 ) AS latitude,
    SUBSTRING_INDEX( SUBSTRING_INDEX( g.meta_value, ",", 2 ), ",", -1 ) AS longitude,
    SUBSTRING_INDEX( c.meta_value, "; ", -1 ) AS country,
    SUBSTRING_INDEX( SUBSTRING_INDEX( c.meta_value, "; ", 2 ), "; ", -1 ) AS region,
    SUBSTRING_INDEX( c.meta_value, "; ", 1 ) AS town,
    COUNT(DISTINCT h.meta_id) AS html_uris,
    COUNT(DISTINCT r.meta_id) AS rss_uris,
    COUNT(DISTINCT x.meta_id) AS exhibitions
FROM
    wpaef_posts AS p
    LEFT JOIN
    wpaef_postmeta AS g
    ON
    g.post_id = p.ID
    LEFT JOIN
    wpaef_postmeta AS c
    ON
    c.post_id = p.ID
    LEFT JOIN
    (SELECT post_id, meta_id FROM wpaef_postmeta WHERE meta_key = "_cp__ent_html_uri") AS h
    ON
    h.post_id = p.ID
    LEFT JOIN
    (SELECT post_id, meta_id FROM wpaef_postmeta WHERE meta_key = "_cp__ent_rss_uri") AS r
    ON
    r.post_id = p.ID
    LEFT JOIN
    (SELECT ID, name FROM wpaef_xtr_vw_unfolded_taxonomies WHERE taxonomy = "tax_typology") AS y
    ON
    y.ID = p.ID
    LEFT JOIN
    (SELECT meta_id,meta_value FROM wpaef_postmeta WHERE meta_key = "_cp__exh_info_source") AS x
    ON
    CONCAT(p.ID, ": ", p.post_title) = x.meta_value
WHERE
    p.post_status = "publish"
    AND
    p.post_type = "entity"
    AND
    g.meta_key = "_cp__ent_coordinates"
    AND
    c.meta_key = "_cp__ent_town"
GROUP BY
    p.ID,
    p.post_type,
    p.post_title,
    y.name,
    SUBSTRING_INDEX( SUBSTRING_INDEX( g.meta_value, ",", 1 ), ",", -1 ),
    SUBSTRING_INDEX( SUBSTRING_INDEX( g.meta_value, ",", 2 ), ",", -1 ),
    SUBSTRING_INDEX( c.meta_value, "; ", 1 ),
    SUBSTRING_INDEX( SUBSTRING_INDEX( c.meta_value, "; ", 2 ), "; ", -1 ),
    SUBSTRING_INDEX( c.meta_value, "; ", -1 )
    
/***/
SELECT SQL_CACHE
	term,
	country,
	region,
	town,
	(SUM(html_uris) + SUM(rss_uris)) AS uris,
	SUM(exhibitions) AS generated_exhibitions,
	IF(SUM(html_uris) + SUM(rss_uris) > 0, SUM(exhibitions) / (SUM(html_uris) + SUM(rss_uris)), 0) AS productivity_index
FROM
	wpaef_xtr_vw_uris_productivity
GROUP BY
	term,
	country,
	region,
	town
	
/***/
    
SELECT
	p.ID,
    COUNT(m.meta_id) AS exh_id
FROM
	wpaef_posts AS p 
    LEFT OUTER JOIN
    (SELECT meta_id,meta_value FROM wpaef_postmeta WHERE meta_key = "_cp__exh_info_source")AS m 
    ON 
    CONCAT(p.ID, ": ", p.post_title) = m.meta_value
WHERE 
	p.post_type = "entity"
    AND
    p.post_status = "publish"
GROUP BY 
	p.ID