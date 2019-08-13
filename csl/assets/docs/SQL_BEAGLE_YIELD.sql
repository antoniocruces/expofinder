CREATE OR REPLACE VIEW wpaef_xtr_vw_valid_exhibitions_and_beagle_yield AS
SELECT SQL_CACHE
	e.capture_date,
	DATE_FORMAT(b.log_date, "%Y-%m-%d") AS log_date,
	MAX(b.checked_uris) AS checked_uris,
	MAX(b.valid_uris) AS valid_uris,
	MAX(b.checked_entries) AS checked_entries,
	MAX(b.sapfull_entries) AS sapfull_entries,
	MAX(b.added_entries) AS added_entries,
	MAX(e.n_exhibitions) AS valid_exhibitions
FROM 
	(
	SELECT
		DATE_FORMAT(post_date, "%Y-%m-%d") AS capture_date,
		COUNT(DISTINCT ID) AS n_exhibitions
	FROM
		wpaef_xtr_vw_unfolded_exhibition 
	GROUP BY
		DATE_FORMAT(post_date, "%Y-%m-%d")
	) AS e 
	LEFT JOIN 
	wpaef_xtr_beaglecr_log AS b 
	ON
	e.capture_date = DATE_FORMAT(b.log_date, "%Y-%m-%d")
 GROUP BY 
	e.capture_date,
	DATE_FORMAT(b.log_date, "%Y-%m-%d")
 ORDER BY
 	e.capture_date;
 	
 /***/
 
SELECT SQL_CACHE
	CAST(SUBSTRING_INDEX(m.meta_value, ": ", 1) AS UNSIGNED) AS n_person_id,
	SUBSTRING_INDEX(m.meta_value, ": ", -1) AS s_person_name,
	g.meta_value AS s_gender,
	c.meta_value AS s_country,
	y.n_birth_year,
	COUNT(m.meta_id) AS n_exhibitions
FROM 
	wpaef_postmeta AS m 
	LEFT JOIN
	(
		SELECT 
			post_id,
			meta_value
		FROM 
			wpaef_postmeta
		WHERE 
			meta_key = "_cp__peo_gender"
	) AS g 
	ON 
	CAST(SUBSTRING_INDEX(m.meta_value, ": ", 1) AS UNSIGNED) = g.post_id
	LEFT JOIN
	(
		SELECT 
			post_id,
			meta_value
		FROM 
			wpaef_postmeta
		WHERE 
			meta_key = "_cp__peo_country"
	) AS c 
	ON 
	CAST(SUBSTRING_INDEX(m.meta_value, ": ", 1) AS UNSIGNED) = c.post_id
	LEFT JOIN
	(
		SELECT 
			post_id,
			IF(STR_TO_DATE(meta_value, "%Y-%m-%d"), YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")), NULL) AS n_birth_year
		FROM 
			wpaef_postmeta
		WHERE 
			meta_key = "_cp__peo_birth_date"
	) AS y 
	ON 
	CAST(SUBSTRING_INDEX(m.meta_value, ": ", 1) AS UNSIGNED) = y.post_id
WHERE
	m.meta_key = "_cp__exh_artwork_author"
GROUP BY 
 	CAST(SUBSTRING_INDEX(m.meta_value, ": ", 1) AS UNSIGNED),
 	SUBSTRING_INDEX(m.meta_value, ": ", -1),
 	g.meta_value,
 	c.meta_value,
 	y.n_birth_year
 ORDER BY
 	COUNT(m.meta_id) DESC 
