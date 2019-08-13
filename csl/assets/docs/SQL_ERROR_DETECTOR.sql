/*** Date error detector ***/
SELECT
	m.*,
	DATE(meta_value) AS real_date
FROM
	wpaef_postmeta AS m
WHERE
	m.meta_key LIKE "%date%"
	AND
	m.meta_key <> "_cp__exh_original_publishing_date"
	AND
	(DATE(meta_value) IS NULL OR LENGTH(meta_value) <> 10)
ORDER BY
	DATE(meta_value) DESC,
	m.meta_key ASC,
	m.meta_value ASC