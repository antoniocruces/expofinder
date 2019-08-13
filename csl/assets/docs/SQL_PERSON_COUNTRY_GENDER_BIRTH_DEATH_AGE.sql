CREATE OR REPLACE VIEW wpaef_xtr_vw_person_country_gender_birth_death_age AS
SELECT SQL_CACHE
	s.post_id,
	s.person_country,
	s.person_gender,
	s.person_birth_year,
	s.person_death_year,
	IF(s.person_death_year IS NULL,(IF(YEAR(CURDATE()) - s.person_birth_year < 101, YEAR(CURDATE()) - s.person_birth_year, NULL)), s.person_death_year - s.person_birth_year) AS person_age
FROM
	(
	SELECT
	    post_id,
	    MAX(IF(meta_key = "_cp__peo_country", meta_value, NULL)) as person_country,
	    MAX(IF(meta_key = "_cp__peo_gender", meta_value, NULL)) as person_gender,
	    MIN(IF(meta_key = "_cp__peo_birth_date", YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")), NULL)) as person_birth_year,
	    MAX(IF(meta_key = "_cp__peo_death_date", YEAR(STR_TO_DATE(meta_value, "%Y-%m-%d")), NULL)) as person_death_year
	FROM
	    wpaef_postmeta
	WHERE
	    meta_key IN ("_cp__peo_country","_cp__peo_gender","_cp__peo_birth_date","_cp__peo_death_date")
	    AND
	    meta_value IS NOT NULL
	GROUP BY
		post_id
	) AS s 