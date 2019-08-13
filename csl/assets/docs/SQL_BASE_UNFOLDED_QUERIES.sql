SELECT 
    m.meta_id,
    m.post_id, 
    m.meta_key, 
    m.meta_value, 
    IF(
        m.meta_value LIKE "%: %" 
        AND 
        CAST(SUBSTRING_INDEX(m.meta_value, ": ", 1) AS UNSIGNED) * 1 = CAST(SUBSTRING_INDEX(m.meta_value, ": ", 1) AS UNSIGNED), 
        CAST(SUBSTRING_INDEX(m.meta_value, ": ", 1) AS UNSIGNED), 
        NULL
        ) AS n_ext_id,
    IF(m.meta_value LIKE "%; %; %", SUBSTRING_INDEX( m.meta_value, "; ", 1 ), NULL) AS s_geo_town,
    IF(m.meta_value LIKE "%; %; %", SUBSTRING_INDEX( SUBSTRING_INDEX( m.meta_value, "; ", 2 ), "; ", -1 ), NULL) AS s_geo_region,
    IF(m.meta_value LIKE "%; %; %", SUBSTRING_INDEX( m.meta_value, "; ", -1 ), NULL) AS s_geo_country, 
    p.ID,
    p.post_type,
    p.post_title   
FROM 
    wpaef_postmeta AS m 
    LEFT JOIN
    wpaef_posts AS p
    ON
    p.ID = CAST(SUBSTRING_INDEX(m.meta_value, ": ", 1) AS UNSIGNED) 
WHERE
    p.post_status = "publish"   
    
/***/  

CREATE OR REPLACE VIEW wpaef_xtr_vw_unfolded_postmeta AS

SELECT SQL_CACHE
    x.ID,
    x.post_type,
    x.post_title,
    x.post_status,   
    x.meta_id,
    x.meta_key, 
    x.meta_value, 
    x.n_ext_id,
    x.s_geo_town,
    x.s_geo_region,
    x.s_geo_country,
    x.n_year,
    x.n_month,
    x.n_day,
    r.post_type AS s_ext_post_type,
    r.post_title AS s_ext_post_title,
    r.post_status AS s_ext_post_status
FROM
    (
    SELECT
        p.ID,
        p.post_type,
        p.post_title,
        p.post_status,   
        m.meta_id,
        m.meta_key, 
        m.meta_value, 
        IF(
            m.meta_value LIKE "%: %" 
            AND 
            CAST(SUBSTRING_INDEX(m.meta_value, ": ", 1) AS UNSIGNED) * 1 = CAST(SUBSTRING_INDEX(m.meta_value, ": ", 1) AS UNSIGNED), 
            CAST(SUBSTRING_INDEX(m.meta_value, ": ", 1) AS UNSIGNED), 
            NULL
            ) AS n_ext_id,
        IF(m.meta_value LIKE "%; %; %", SUBSTRING_INDEX( m.meta_value, "; ", 1 ), NULL) AS s_geo_town,
        IF(m.meta_value LIKE "%; %; %", SUBSTRING_INDEX( SUBSTRING_INDEX( m.meta_value, "; ", 2 ), "; ", -1 ), NULL) AS s_geo_region,
        IF(m.meta_key = "_cp__peo_country", m.meta_key, IF(m.meta_value LIKE "%; %; %", SUBSTRING_INDEX( m.meta_value, "; ", -1 ), NULL)) AS s_geo_country,
        IF(STR_TO_DATE(m.meta_value, "%Y-%m-%d"), STR_TO_DATE(m.meta_value, "%Y-%m-%d"), NULL) AS d_date,
        IF(STR_TO_DATE(m.meta_value, "%Y-%m-%d"), YEAR(STR_TO_DATE(m.meta_value, "%Y-%m-%d")), NULL) AS n_year,
        IF(STR_TO_DATE(m.meta_value, "%Y-%m-%d"), MONTH(STR_TO_DATE(m.meta_value, "%Y-%m-%d")), NULL) AS n_month,
        IF(STR_TO_DATE(m.meta_value, "%Y-%m-%d"), DAY(STR_TO_DATE(m.meta_value, "%Y-%m-%d")), NULL) AS n_day
    FROM 
        wpaef_postmeta AS m 
        LEFT JOIN
        wpaef_posts AS p
        ON
        p.ID = m.post_id
    ) AS x
    LEFT JOIN
    wpaef_posts AS r
    ON
    r.ID = x.n_ext_id
    
/***/

CREATE OR REPLACE VIEW wpaef_xtr_vw_unfolded_taxonomies AS

SELECT SQL_CACHE 
    p.ID, 
    p.post_type, 
    p.post_title, 
    tt.taxonomy,
    t.name
FROM 
    (
    wpaef_posts AS p 
    LEFT JOIN 
    wpaef_term_relationships AS tr 
    ON p.ID = tr.object_id
    ) 
    LEFT JOIN 
    (
    wpaef_terms AS t 
    RIGHT JOIN 
    wpaef_term_taxonomy AS tt 
    ON t.term_id = tt.term_id
    ) 
    ON 
    tr.term_taxonomy_id = tt.term_taxonomy_id

/***/

CREATE OR REPLACE VIEW wpaef_xtr_vw_unfolded_person AS
SELECT SQL_CACHE
    p.ID,
    p.post_type,
    p.post_title,
    t.meta_value AS s_person_type,
    c.meta_value AS s_country,
    b.n_year AS n_birth_year,    
    d.n_year AS n_death_year,
    IF(d.n_year IS NOT NULL, d.n_year - b.n_year, YEAR(CURDATE()) - b.n_year) AS n_age,
    IF(b.n_year IS NOT NULL, (b.n_year DIV 100)+1, NULL) AS n_birth_century,
    IF(d.n_year IS NOT NULL, (d.n_year DIV 100)+1, NULL) AS n_death_century,
    IF(b.n_year IS NOT NULL, IF(b.n_year MOD 100 > 49, 2, 1), NULL) AS n_birth_half_century,
    IF(d.n_year IS NOT NULL, IF(d.n_year MOD 100 > 49, 2, 1), NULL) AS n_death_half_century,
    g.meta_value AS s_gender,
    y.taxonomy AS s_taxonomy,
    y.name AS s_term
FROM
    wpaef_posts AS p
    LEFT JOIN
    wpaef_xtr_vw_unfolded_postmeta AS t
    ON
    t.ID = p.ID
    LEFT JOIN
    wpaef_xtr_vw_unfolded_postmeta AS c
    ON
    c.ID = p.ID
    LEFT JOIN
    wpaef_xtr_vw_unfolded_postmeta AS b
    ON
    b.ID = p.ID
    LEFT JOIN
    wpaef_xtr_vw_unfolded_postmeta AS d
    ON
    d.ID = p.ID
    LEFT JOIN
    wpaef_xtr_vw_unfolded_postmeta AS g
    ON
    g.ID = p.ID
    LEFT JOIN
    wpaef_xtr_vw_unfolded_taxonomies AS y
    ON
    y.ID = p.ID
WHERE
    p.post_status = "publish"
    AND
    t.meta_key = "_cp__peo_person_type"
    AND
    c.meta_key = "_cp__peo_country"
    AND
    b.meta_key = "_cp__peo_birth_date"
    AND
    d.meta_key = "_cp__peo_death_date"
    AND
    g.meta_key = "_cp__peo_gender"
                
/***/

CREATE OR REPLACE VIEW wpaef_xtr_vw_unfolded_exhibition AS
SELECT SQL_CACHE
    p.ID,
    p.post_type,
    p.post_title,
    c.s_geo_country AS s_geo_country,
    c.s_geo_region AS s_geo_region,
    c.s_geo_town AS s_geo_town,
    s.n_year AS n_start_year,    
    s.n_month AS n_start_month,    
    s.n_day AS n_start_day,
    s.meta_value AS n_start_date,    
    e.n_year AS n_end_year,    
    e.n_month AS n_end_month,    
    e.n_day AS n_end_day,
    e.meta_value AS n_end_date,    
    IF(e.meta_value IS NOT NULL, DATEDIFF(e.meta_value, s.meta_value), NULL) AS n_duration,
    y.taxonomy AS s_taxonomy,
    y.name AS s_term
FROM
    wpaef_posts AS p
    LEFT JOIN
    wpaef_xtr_vw_unfolded_postmeta AS c
    ON
    c.ID = p.ID
    LEFT JOIN
    wpaef_xtr_vw_unfolded_postmeta AS s
    ON
    s.ID = p.ID
    LEFT JOIN
    wpaef_xtr_vw_unfolded_postmeta AS e
    ON
    e.ID = p.ID
    LEFT JOIN
    wpaef_xtr_vw_unfolded_taxonomies AS y
    ON
    y.ID = p.ID
WHERE
    p.post_status = "publish"
    AND
    p.post_type = "exhibition"
    AND
    c.meta_key = "_cp__exh_exhibition_town"
    AND
    s.meta_key = "_cp__exh_exhibition_start_date"
    AND
    e.meta_key = "_cp__exh_exhibition_end_date"

/***/

CREATE OR REPLACE VIEW wpaef_xtr_vw_unfolded_entity AS
SELECT SQL_CACHE
    p.ID,
    p.post_type,
    p.post_title,
    SUBSTRING_INDEX( SUBSTRING_INDEX( g.meta_value, ",", 1 ), ",", -1 ) AS latitude,
    SUBSTRING_INDEX( SUBSTRING_INDEX( g.meta_value, ",", 2 ), ",", -1 ) AS longitude,
    c.s_geo_country AS s_geo_country,
    c.s_geo_region AS s_geo_region,
    c.s_geo_town AS s_geo_town,
    y.taxonomy AS s_taxonomy,
    y.name AS s_term,
    h.meta_value AS s_html_uri,
    r.meta_value AS s_rss_uri,
    w.ID as n_exh_id,
    w.post_title as s_exh_title
FROM
    wpaef_posts AS p
    LEFT JOIN
    (SELECT ID, meta_value FROM wpaef_xtr_vw_unfolded_postmeta WHERE meta_key = "_cp__ent_coordinates") AS g
    ON
    p.ID = g.ID
    LEFT JOIN
    (SELECT ID, s_geo_country, s_geo_region, s_geo_town FROM wpaef_xtr_vw_unfolded_postmeta WHERE meta_key = "_cp__ent_town") AS c
    ON
    p.ID = c.ID
    LEFT JOIN
    (SELECT ID, meta_value FROM wpaef_xtr_vw_unfolded_postmeta WHERE meta_key = "_cp__ent_html_uri") AS h
    ON
    p.ID = h.ID
    LEFT JOIN
    (SELECT ID, meta_value FROM wpaef_xtr_vw_unfolded_postmeta WHERE meta_key = "_cp__ent_rss_uri") AS r
    ON
    p.ID = r.ID
    LEFT JOIN
    (SELECT ID, post_title,n_ext_id FROM wpaef_xtr_vw_unfolded_postmeta WHERE meta_key IN ("_cp__exh_source_entity", "_cp__exh_info_source")) AS w
    ON
    p.ID = w.n_ext_id
    LEFT JOIN
    (SELECT ID, taxonomy, name FROM wpaef_xtr_vw_unfolded_taxonomies WHERE taxonomy IN ("tax_typology", "tax_ownership")) AS y
    ON
    p.ID = y.ID
WHERE
    p.post_status = "publish"
    AND
    p.post_type = "entity"
         

/*** PROVISIONAL ***/
/*
SELECT SQL_CACHE
    p.ID,
    p.post_type,
    p.post_title,
    p.s_person_type,
    p.s_country,
    p.n_birth_year,    
    p.n_death_year,
    p.n_age,
    p.n_birth_century,
    p.n_death_century,
    p.n_birth_half_century,
    p.n_death_half_century,
    p.s_gender,
    p.s_taxonomy,
    p.s_term,
    e.ID AS s_exh_ID,
    e.post_title AS s_exh_post_title,
    e.s_geo_country AS s_exh_geo_country,
    e.s_geo_country AS s_exh_geo_country,
    e.s_geo_region AS s_exh_geo_region,
    e.s_geo_town AS s_exh_geo_town,
    e.n_start_year AS s_exh_start_year,   
    e.n_start_month AS s_exh_start_month,   
    e.n_start_day AS s_exh_start_day,   
    e.n_end_year AS s_exh_end_year,   
    e.n_end_month AS s_exh_end_month,   
    e.n_end_day AS s_exh_end_day,   
    e.n_duration AS s_exh_duration,   
    e.s_taxonomy AS s_exh_taxonomy,
    e.s_term AS s_exh_term
FROM
    wpaef_xtr_vw_unfolded_person AS p
    LEFT JOIN
    wpaef_xtr_vw_unfolded_postmeta AS m
    ON
    m.n_ext_id = p.ID
    LEFT JOIN
    wpaef_xtr_vw_unfolded_exhibition AS e
    ON
    m.ID = e.ID
WHERE   
    m.meta_key = "_cp_exh_artwork_author" 
    
    
SELECT SQL_CACHE
    p.ID,
    p.post_type,
    p.post_title,
    p.s_person_type,
    p.s_country,
    p.n_birth_year,    
    p.n_death_year,
    p.n_age,
    p.n_birth_century,
    p.n_death_century,
    p.n_birth_half_century,
    p.n_death_half_century,
    p.s_gender,
    p.s_taxonomy,
    p.s_term,
    m.post_title    
FROM
    wpaef_xtr_vw_unfolded_person AS p
    LEFT JOIN
    wpaef_xtr_vw_unfolded_postmeta AS m
    ON
    m.n_ext_id = p.ID
WHERE   
    m.meta_key = "_cp_exh_artwork_author"     
*/