CREATE OR REPLACE VIEW wpaef_xtr_vw_normalized_relations AS
SELECT SQL_CACHE
    m.post_id,
    m.meta_id,
    m.meta_key, 
    m.meta_value,
    p.ID AS n_int_id, 
    p.post_title AS s_int_post_title,
    p.post_type AS s_int_post_type,
    x.ID AS n_ext_id,
    x.post_title AS s_ext_post_title,
    x.post_type AS s_ext_post_type
FROM
    wpaef_postmeta AS m
    LEFT JOIN
    wpaef_posts AS x
    ON
    CAST(SUBSTRING_INDEX(m.meta_value, ": ", 1) AS UNSIGNED) = x.ID
    LEFT JOIN
    wpaef_posts AS p
    ON
    m.post_id = p.ID
WHERE
    m.meta_key IN("_cp__ent_parent_entity","_cp__boo_paper_author","_cp__boo_sponsorship","_cp__peo_entity_relation","_cp__peo_person_relation","_cp__exh_source_entity","_cp__exh_parent_exhibition","_cp__exh_info_source","_cp__exh_artwork_author","_cp__exh_supporter_entity","_cp__exh_funding_entity_cp__exh_curator","_cp__exh_catalog","_cp__exh_museography","_cp__exh_art_collector") 
    AND
    CAST(SUBSTRING_INDEX(m.meta_value, ": ", 1) AS UNSIGNED) <> 0
    AND
    x.post_status = "publish";
    
/***/

CREATE OR REPLACE VIEW wpaef_xtr_vw_normalized_dates AS
SELECT SQL_CACHE
    p.ID, 
    p.post_title,
    p.post_type,
    m.meta_id,
    m.meta_key, 
    m.meta_value,
    STR_TO_DATE(m.meta_value, "%Y-%m-%d") AS d_date,
    YEAR(STR_TO_DATE(m.meta_value, "%Y-%m-%d")) AS n_year,
    MONTH(STR_TO_DATE(m.meta_value, "%Y-%m-%d")) AS n_month,
    DAY(STR_TO_DATE(m.meta_value, "%Y-%m-%d")) AS n_day,
    (YEAR(STR_TO_DATE(m.meta_value, "%Y-%m-%d")) DIV 100)+1 AS n_century,
    IF(YEAR(STR_TO_DATE(m.meta_value, "%Y-%m-%d")) MOD 100 > 49, 2, 1) AS n_half_century
FROM
    wpaef_postmeta AS m
    LEFT JOIN
    wpaef_posts AS p
    ON
    m.post_id = p.ID
WHERE
    m.meta_key IN("_cp__boo_publishing_date","_cp__peo_birth_date","_cp__peo_death_date","_cp__exh_exhibition_start_date","_cp__exh_exhibition_end_date") 
    AND
    STR_TO_DATE(m.meta_value, "%Y-%m-%d");
    
/***/

CREATE OR REPLACE VIEW wpaef_xtr_vw_normalized_places AS
SELECT SQL_CACHE
    p.ID, 
    p.post_title,
    p.post_type,
    m.meta_id,
    m.meta_key, 
    m.meta_value,
    IF(m.meta_key = "_cp__peo_country", m.meta_key, SUBSTRING_INDEX( m.meta_value, "; ", -1 )) AS s_geo_country,
    SUBSTRING_INDEX( SUBSTRING_INDEX( m.meta_value, "; ", 2 ), "; ", -1 ) AS s_geo_region,
    SUBSTRING_INDEX( m.meta_value, "; ", 1 ) AS s_geo_town
FROM
    wpaef_postmeta AS m
    LEFT JOIN
    wpaef_posts AS p
    ON
    m.post_id = p.ID
WHERE
    m.meta_key IN("_cp__exh_exhibition_town","_cp__com_company_headqarter_place","_cp__boo_publishing_place","_cp__peo_country","_cp__ent_town") 
    AND
    m.meta_value LIKE "%; %; %";
    
/***/

CREATE OR REPLACE VIEW wpaef_xtr_vw_normalized_coordinates AS
SELECT SQL_CACHE
    p.ID, 
    p.post_title,
    p.post_type,
    m.meta_id,
    IF(g.meta_key = "_cp__peo_country", g.meta_key, SUBSTRING_INDEX( g.meta_value, "; ", -1 )) AS s_geo_country,
    SUBSTRING_INDEX( SUBSTRING_INDEX( g.meta_value, "; ", 2 ), "; ", -1 ) AS s_geo_region,
    SUBSTRING_INDEX( g.meta_value, "; ", 1 ) AS s_geo_town,
    STR_TO_DATE(d.meta_value, "%Y-%m-%d") AS d_date,
    YEAR(STR_TO_DATE(d.meta_value, "%Y-%m-%d")) AS n_year,
    MONTH(STR_TO_DATE(d.meta_value, "%Y-%m-%d")) AS n_month,
    DAY(STR_TO_DATE(d.meta_value, "%Y-%m-%d")) AS n_day,
    (YEAR(STR_TO_DATE(d.meta_value, "%Y-%m-%d")) DIV 100)+1 AS n_century,
    IF(YEAR(STR_TO_DATE(d.meta_value, "%Y-%m-%d")) MOD 100 > 49, 2, 1) AS n_half_century,
    m.meta_value AS s_coordinates,
    CAST(SUBSTRING_INDEX( SUBSTRING_INDEX( m.meta_value, ",", 1 ), ",", -1 ) AS DECIMAL(11,8)) AS n_latitude,
    CAST(SUBSTRING_INDEX( SUBSTRING_INDEX( m.meta_value, ",", 2 ), ",", -1 ) AS DECIMAL(11,8)) AS n_longitude
FROM
    wpaef_postmeta AS m
    LEFT JOIN
    wpaef_posts AS p
    ON
    m.post_id = p.ID
    LEFT JOIN
    (SELECT post_id, meta_key, meta_value FROM wpaef_postmeta WHERE meta_key IN("_cp__exh_exhibition_town","_cp__com_company_headqarter_place","_cp__boo_publishing_place","_cp__peo_country","_cp__ent_town")) AS g
    ON
    m.post_id = g.post_id
    LEFT JOIN
    (SELECT post_id, meta_key, meta_value FROM wpaef_postmeta WHERE meta_key IN("_cp__boo_publishing_date","_cp__peo_birth_date","_cp__exh_exhibition_start_date")) AS d
    ON
    m.post_id = d.post_id    
WHERE
    m.meta_key IN("_cp__exh_coordinates","_cp__ent_coordinates") 
    AND
    m.meta_value IS NOT NULL;