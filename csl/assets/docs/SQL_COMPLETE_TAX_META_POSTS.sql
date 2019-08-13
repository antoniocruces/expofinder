CREATE OR REPLACE VIEW wpaef_xtr_vw_complete_tax_meta_posts AS

SELECT
	MD5(CONCAT(p.ID, t.term_id, pm.meta_id)) AS n_unique_id,
    p.ID,
    p.post_title, 
    p.post_type,
    tt.taxonomy, 
    t.name as term,
    pm.meta_key,
    pm.meta_value,
    pm.d_date,
    pm.n_year,
    pm.n_month,
    pm.n_day,
    pm.n_century,
    pm.n_half_century,
    pm.s_geo_country,
    pm.s_geo_region,
    pm.s_geo_town,
    pm.n_latitude,
    pm.n_longitude,
    pm.n_ext_id,
    pm.s_ext_post_title
FROM 
    wpaef_posts AS p
    INNER JOIN 
    wpaef_term_relationships AS tr 
    ON 
    (p.ID = tr.object_id)
    INNER JOIN 
    wpaef_term_taxonomy AS tt 
    ON 
    (tr.term_taxonomy_id = tt.term_taxonomy_id)
    INNER JOIN 
    wpaef_terms AS t 
    ON 
    (t.term_id = tt.term_id)
    LEFT JOIN
    (
        SELECT
        	meta_id,
            post_id,
            meta_key,
            meta_value,
            STR_TO_DATE(meta_value, "%Y-%m-%d") AS d_date,
            YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")) AS n_year,
            MONTH(STR_TO_DATE(meta_value, "%Y-%m-%d")) AS n_month,
            DAY(STR_TO_DATE(meta_value, "%Y-%m-%d")) AS n_day,
            (YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")) DIV 100)+1 AS n_century,
            IF(YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")) MOD 100 > 49, 2, 1) AS n_half_century,
            IF(meta_key = "_cp__peo_country", meta_key, IF(meta_key IN ("_cp__exh_exhibition_town","_cp__com_company_headqarter_place","_cp__boo_publishing_place","_cp__peo_country","_cp__ent_town"), SUBSTRING_INDEX(meta_value, "; ", -1), NULL)) AS s_geo_country,
            IF(meta_key IN ("_cp__exh_exhibition_town","_cp__com_company_headqarter_place","_cp__boo_publishing_place","_cp__peo_country","_cp__ent_town"), SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, "; ", 2), "; ", -1), NULL) AS s_geo_region,
            IF(meta_key IN ("_cp__exh_exhibition_town","_cp__com_company_headqarter_place","_cp__boo_publishing_place","_cp__peo_country","_cp__ent_town"), SUBSTRING_INDEX( meta_value, "; ", 1 ), NULL) AS s_geo_town,
            IF(meta_key IN ("_cp__exh_coordinates","_cp__ent_coordinates"), CAST(SUBSTRING_INDEX( SUBSTRING_INDEX( meta_value, ",", 1 ), ",", -1 ) AS DECIMAL(11,8)), NULL) AS n_latitude,
            IF(meta_key IN ("_cp__exh_coordinates","_cp__ent_coordinates"), CAST(SUBSTRING_INDEX( SUBSTRING_INDEX( meta_value, ",", 2 ), ",", -1 ) AS DECIMAL(11,8)), NULL) AS n_longitude,
            IF(meta_key IN ("_cp__ent_parent_entity","_cp__boo_paper_author","_cp__boo_sponsorship","_cp__peo_entity_relation","_cp__peo_person_relation","_cp__exh_source_entity","_cp__exh_parent_exhibition","_cp__exh_info_source","_cp__exh_artwork_author","_cp__exh_supporter_entity","_cp__exh_funding_entity_cp__exh_curator","_cp__exh_catalog","_cp__exh_museography","_cp__exh_art_collector"), CAST(SUBSTRING_INDEX(meta_value, ": ", 1) AS UNSIGNED), NULL) AS n_ext_id,    
            IF(meta_key IN ("_cp__ent_parent_entity","_cp__boo_paper_author","_cp__boo_sponsorship","_cp__peo_entity_relation","_cp__peo_person_relation","_cp__exh_source_entity","_cp__exh_parent_exhibition","_cp__exh_info_source","_cp__exh_artwork_author","_cp__exh_supporter_entity","_cp__exh_funding_entity_cp__exh_curator","_cp__exh_catalog","_cp__exh_museography","_cp__exh_art_collector"), SUBSTRING_INDEX(meta_value, ": ", -1), NULL) AS s_ext_post_title
        FROM
            wpaef_postmeta 
        WHERE
            meta_key IN
            (
            "_cp__exh_exhibition_site",
            "_cp__ent_parent_entity",
            "_cp__boo_paper_author",
            "_cp__boo_sponsorship",
            "_cp__peo_entity_relation",
            "_cp__peo_person_relation",
            "_cp__exh_source_entity",
            "_cp__exh_parent_exhibition",
            "_cp__exh_info_source",
            "_cp__exh_artwork_author",
            "_cp__exh_supporter_entity",
            "_cp__exh_funding_entity",
            "_cp__exh_curator",
            "_cp__exh_catalog",
            "_cp__exh_museography",
            "_cp__exh_art_collector",
            "_cp__boo_publishing_date",
            "_cp__peo_birth_date",
            "_cp__peo_death_date",
            "_cp__exh_exhibition_start_date",
            "_cp__exh_exhibition_end_date",
            "_cp__exh_exhibition_town",
            "_cp__com_company_headqarter_place",
            "_cp__boo_publishing_place",
            "_cp__peo_country",
            "_cp__ent_town",
            "_cp__exh_coordinates",
            "_cp__ent_coordinates"   
            ) 
    ) AS pm 
    ON
    (p.ID = pm.post_id)
WHERE   
    p.post_status = "publish"
