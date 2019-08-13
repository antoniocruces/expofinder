/*** ALL UNFOLFDED ***/

CREATE OR REPLACE VIEW wpaef_xtr_vw_wide_all AS
SELECT
	MD5(CONCAT(p.ID, t.term_id, pm.meta_id)) AS n_unique_id,
    p.ID as n_id,
    p.post_title, 
    p.post_type,
    tt.taxonomy, 
    t.name as term,
    pm.meta_id,
    pm.meta_key,
    pm.meta_value,
    STR_TO_DATE(meta_value, "%Y-%m-%d") AS d_date,
    YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")) AS n_year,
    MONTH(STR_TO_DATE(meta_value, "%Y-%m-%d")) AS n_month,
    DAY(STR_TO_DATE(meta_value, "%Y-%m-%d")) AS n_day,
    (YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")) DIV 100)+1 AS n_century,
    IF(YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")) MOD 100 > 49, 2, 1) AS n_half_century,
    IF(meta_key = "_cp__peo_country", meta_key, 
    	IF(meta_key IN (
    		"_cp__exh_exhibition_town",
    		"_cp__com_company_headqarter_place",
    		"_cp__boo_publishing_place",
    		"_cp__peo_country",
    		"_cp__ent_town"
    	), SUBSTRING_INDEX(meta_value, "; ", -1), NULL)
    ) AS s_geo_country,
    IF(meta_key IN (
    	"_cp__exh_exhibition_town",
    	"_cp__com_company_headqarter_place",
    	"_cp__boo_publishing_place",
    	"_cp__peo_country",
    	"_cp__ent_town"
    ), SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, "; ", 2), "; ", -1), NULL) AS s_geo_region,
    IF(meta_key IN (
    	"_cp__exh_exhibition_town",
    	"_cp__com_company_headqarter_place",
    	"_cp__boo_publishing_place",
    	"_cp__peo_country",
    	"_cp__ent_town"
    ), SUBSTRING_INDEX(meta_value, "; ", 1), NULL) AS s_geo_TOWN,
    IF(meta_key IN (
    	"_cp__exh_coordinates",
    	"_cp__ent_coordinates"
    ), CAST(SUBSTRING_INDEX( SUBSTRING_INDEX( meta_value, ",", 1 ), ",", -1 ) AS DECIMAL(11,8)), NULL) AS n_latitude,
	IF(meta_key IN (
		"_cp__exh_coordinates",
		"_cp__ent_coordinates"
	), CAST(SUBSTRING_INDEX( SUBSTRING_INDEX( meta_value, ",", 2 ), ",", -1 ) AS DECIMAL(11,8)), NULL) AS n_longitude,
    IF(meta_key IN (
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
    	"_cp__exh_funding_entity_cp__exh_curator",
    	"_cp__exh_catalog",
    	"_cp__exh_museography",
    	"_cp__exh_art_collector"
    ), CAST(SUBSTRING_INDEX(meta_value, ": ", 1) AS UNSIGNED), NULL) AS n_ext_id,    
    IF(meta_key IN (
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
    	"_cp__exh_funding_entity_cp__exh_curator",
    	"_cp__exh_catalog",
    	"_cp__exh_museography",
    	"_cp__exh_art_collector"
    ), SUBSTRING_INDEX(meta_value, ": ", -1), NULL) AS s_ext_post_title
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
    wpaef_postmeta AS pm
    ON
    p.ID = pm.post_id
WHERE
	p.post_status = "publish"
	AND
	SUBSTRING(pm.meta_key, 1, 5) = "_cp__";
	

/*** DISAGGREGATED ***/

/*** PEOPLE ***/
CREATE OR REPLACE VIEW wpaef_xtr_vw_wide_person AS
SELECT
	MD5(CONCAT(p.ID, t.term_id, pm.meta_id)) AS n_unique_id,
    p.ID as n_id,
    p.post_title, 
    p.post_type,
    tt.taxonomy, 
    t.name as term,
    pm.meta_id,
    pm.meta_key,
    pm.meta_value,
    STR_TO_DATE(meta_value, "%Y-%m-%d") AS d_date,
    YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")) AS n_year,
    MONTH(STR_TO_DATE(meta_value, "%Y-%m-%d")) AS n_month,
    DAY(STR_TO_DATE(meta_value, "%Y-%m-%d")) AS n_day,
    (YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")) DIV 100)+1 AS n_century,
    IF(YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")) MOD 100 > 49, 2, 1) AS n_half_century,
    IF(meta_key = "_cp__peo_country", meta_value, NULL) AS s_geo_country,
    NULL AS s_geo_region,
    NULL AS s_geo_town,
    NULL AS n_latitude,
    NULL AS n_longitude,
    IF(meta_key IN ("_cp__peo_entity_relation","_cp__peo_person_relation"), CAST(SUBSTRING_INDEX(meta_value, ": ", 1) AS UNSIGNED), NULL) AS n_ext_id,    
    IF(meta_key IN ("_cp__peo_entity_relation","_cp__peo_person_relation"), SUBSTRING_INDEX(meta_value, ": ", -1), NULL) AS s_ext_post_title
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
    wpaef_postmeta AS pm
    ON
    p.ID = pm.post_id
WHERE
	p.post_type = "person"
	AND
	p.post_status = "publish"
	AND
	SUBSTRING(pm.meta_key, 1, 5) = "_cp__";
	
/*** ENTITIES ***/
CREATE OR REPLACE VIEW wpaef_xtr_vw_wide_entity AS
SELECT
	MD5(CONCAT(p.ID, t.term_id, pm.meta_id)) AS n_unique_id,
    p.ID as n_id,
    p.post_title, 
    p.post_type,
    tt.taxonomy, 
    t.name as term,
    pm.meta_id,
    pm.meta_key,
    pm.meta_value,
    STR_TO_DATE(meta_value, "%Y-%m-%d") AS d_date,
    YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")) AS n_year,
    MONTH(STR_TO_DATE(meta_value, "%Y-%m-%d")) AS n_month,
    DAY(STR_TO_DATE(meta_value, "%Y-%m-%d")) AS n_day,
    (YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")) DIV 100)+1 AS n_century,
    IF(YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")) MOD 100 > 49, 2, 1) AS n_half_century,
    IF(meta_key IN ("_cp__ent_town"), SUBSTRING_INDEX(meta_value, "; ", -1), NULL) AS s_geo_country,
    IF(meta_key IN ("_cp__ent_town"), SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, "; ", 2), "; ", -1), NULL) AS s_geo_region,
    IF(meta_key IN ("_cp__ent_town"), SUBSTRING_INDEX(meta_value, "; ", 1), NULL) AS s_geo_town,
    IF(meta_key IN ("_cp__ent_coordinates"), CAST(SUBSTRING_INDEX( SUBSTRING_INDEX( meta_value, ",", 1 ), ",", -1 ) AS DECIMAL(11,8)), NULL) AS n_latitude,
	IF(meta_key IN ("_cp__ent_coordinates"), CAST(SUBSTRING_INDEX( SUBSTRING_INDEX( meta_value, ",", 2 ), ",", -1 ) AS DECIMAL(11,8)), NULL) AS n_longitude,
    IF(meta_key IN ("_cp__ent_parent_entity"), CAST(SUBSTRING_INDEX(meta_value, ": ", 1) AS UNSIGNED), NULL) AS n_ext_id,    
    IF(meta_key IN ("_cp__ent_parent_entity"), SUBSTRING_INDEX(meta_value, ": ", -1), NULL) AS s_ext_post_title
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
    wpaef_postmeta AS pm
    ON
    p.ID = pm.post_id
WHERE
	p.post_type = "entity"
	AND
	p.post_status = "publish"
	AND
	SUBSTRING(pm.meta_key, 1, 5) = "_cp__";
	
/*** BOOKS ***/
CREATE OR REPLACE VIEW wpaef_xtr_vw_wide_book AS
SELECT
	MD5(CONCAT(p.ID, t.term_id, pm.meta_id)) AS n_unique_id,
    p.ID as n_id,
    p.post_title, 
    p.post_type,
    tt.taxonomy, 
    t.name as term,
    pm.meta_id,
    pm.meta_key,
    pm.meta_value,
    STR_TO_DATE(meta_value, "%Y-%m-%d") AS d_date,
    YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")) AS n_year,
    MONTH(STR_TO_DATE(meta_value, "%Y-%m-%d")) AS n_month,
    DAY(STR_TO_DATE(meta_value, "%Y-%m-%d")) AS n_day,
    (YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")) DIV 100)+1 AS n_century,
    IF(YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")) MOD 100 > 49, 2, 1) AS n_half_century,
    IF(meta_key IN ("_cp__boo_publishing_place"), SUBSTRING_INDEX(meta_value, "; ", -1), NULL) AS s_geo_country,
    IF(meta_key IN ("_cp__boo_publishing_place"), SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, "; ", 2), "; ", -1), NULL) AS s_geo_region,
    IF(meta_key IN ("_cp__boo_publishing_place"), SUBSTRING_INDEX(meta_value, "; ", 1), NULL) AS s_geo_town,
    NULL AS n_latitude,
	NULL AS n_longitude,
    IF(meta_key IN ("_cp__boo_paper_author","_cp__boo_sponsorship"), CAST(SUBSTRING_INDEX(meta_value, ": ", 1) AS UNSIGNED), NULL) AS n_ext_id,    
    IF(meta_key IN ("_cp__boo_paper_author","_cp__boo_sponsorship"), SUBSTRING_INDEX(meta_value, ": ", -1), NULL) AS s_ext_post_title
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
    wpaef_postmeta AS pm
    ON
    p.ID = pm.post_id
WHERE
	p.post_type = "book"
	AND
	p.post_status = "publish"
	AND
	SUBSTRING(pm.meta_key, 1, 5) = "_cp__";

/*** COMPANIES ***/
CREATE OR REPLACE VIEW wpaef_xtr_vw_wide_company AS
SELECT
	MD5(CONCAT(p.ID, t.term_id, pm.meta_id)) AS n_unique_id,
    p.ID as n_id,
    p.post_title, 
    p.post_type,
    tt.taxonomy, 
    t.name as term,
    pm.meta_id,
    pm.meta_key,
    pm.meta_value,
    STR_TO_DATE(meta_value, "%Y-%m-%d") AS d_date,
    YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")) AS n_year,
    MONTH(STR_TO_DATE(meta_value, "%Y-%m-%d")) AS n_month,
    DAY(STR_TO_DATE(meta_value, "%Y-%m-%d")) AS n_day,
    (YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")) DIV 100)+1 AS n_century,
    IF(YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")) MOD 100 > 49, 2, 1) AS n_half_century,
    IF(meta_key IN ("_cp__com_company_headqarter_place"), SUBSTRING_INDEX(meta_value, "; ", -1), NULL) AS s_geo_country,
    IF(meta_key IN ("_cp__com_company_headqarter_place"), SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, "; ", 2), "; ", -1), NULL) AS s_geo_region,
    IF(meta_key IN ("_cp__com_company_headqarter_place"), SUBSTRING_INDEX(meta_value, "; ", 1), NULL) AS s_geo_town,
    NULL AS n_latitude,
	NULL AS n_longitude,
    NULL AS n_ext_id,    
    NULL AS s_ext_post_title
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
    wpaef_postmeta AS pm
    ON
    p.ID = pm.post_id
WHERE
	p.post_type = "company"
	AND
	p.post_status = "publish"
	AND
	SUBSTRING(pm.meta_key, 1, 5) = "_cp__";

/*** EXHIBITIONS ***/
CREATE OR REPLACE VIEW wpaef_xtr_vw_wide_exhibition AS
SELECT
	MD5(CONCAT(p.ID, t.term_id, pm.meta_id)) AS n_unique_id,
    p.ID as n_id,
    p.post_title, 
    p.post_type,
    tt.taxonomy, 
    t.name as term,
    pm.meta_id,
    pm.meta_key,
    pm.meta_value,
    STR_TO_DATE(meta_value, "%Y-%m-%d") AS d_date,
    YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")) AS n_year,
    MONTH(STR_TO_DATE(meta_value, "%Y-%m-%d")) AS n_month,
    DAY(STR_TO_DATE(meta_value, "%Y-%m-%d")) AS n_day,
    (YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")) DIV 100)+1 AS n_century,
    IF(YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")) MOD 100 > 49, 2, 1) AS n_half_century,
    IF(meta_key IN ("_cp__exh_exhibition_town"), SUBSTRING_INDEX(meta_value, "; ", -1), NULL) AS s_geo_country,
    IF(meta_key IN ("_cp__exh_exhibition_town"), SUBSTRING_INDEX(SUBSTRING_INDEX(meta_value, "; ", 2), "; ", -1), NULL) AS s_geo_region,
    IF(meta_key IN ("_cp__exh_exhibition_town"), SUBSTRING_INDEX(meta_value, "; ", 1), NULL) AS s_geo_town,
    IF(meta_key IN ("_cp__exh_coordinates"), CAST(SUBSTRING_INDEX( SUBSTRING_INDEX( meta_value, ",", 1 ), ",", -1 ) AS DECIMAL(11,8)), NULL) AS n_latitude,
    IF(meta_key IN ("_cp__exh_coordinates"), CAST(SUBSTRING_INDEX( SUBSTRING_INDEX( meta_value, ",", 2 ), ",", -1 ) AS DECIMAL(11,8)), NULL) AS n_longitude,
    IF(meta_key IN (
    	"_cp__exh_source_entity",
	    "_cp__exh_parent_exhibition",
	    "_cp__exh_info_source",
	    "_cp__exh_artwork_author",
	    "_cp__exh_supporter_entity",
	    "_cp__exh_funding_entity_cp__exh_curator",
	    "_cp__exh_catalog",
	    "_cp__exh_museography",
	    "_cp__exh_art_collector"
	), CAST(SUBSTRING_INDEX(meta_value, ": ", 1) AS UNSIGNED), NULL) AS n_ext_id,    
    IF(meta_key IN (
    	"_cp__exh_source_entity",
	    "_cp__exh_parent_exhibition",
	    "_cp__exh_info_source",
	    "_cp__exh_artwork_author",
	    "_cp__exh_supporter_entity",
	    "_cp__exh_funding_entity_cp__exh_curator",
	    "_cp__exh_catalog",
	    "_cp__exh_museography",
	    "_cp__exh_art_collector"
	), SUBSTRING_INDEX(meta_value, ": ", -1), NULL) AS s_ext_post_title    
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
    wpaef_postmeta AS pm
    ON
    p.ID = pm.post_id
WHERE
	p.post_type = "exhibition"
	AND
	p.post_status = "publish"
	AND
	SUBSTRING(pm.meta_key, 1, 5) = "_cp__";
