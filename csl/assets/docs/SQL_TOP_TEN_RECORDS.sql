SELECT
    m.meta_key, 
    m.s_town AS s_name,
	COUNT(m.meta_id) AS n_refs
FROM
	wpaef_xtr_vw_postmeta_referenced m
WHERE
    m.meta_key = '_cp__exh_exhibition_town' 
GROUP BY
    m.s_town
ORDER BY
	COUNT(m.meta_id) DESC
LIMIT 10

UNION ALL

SELECT 
    m.meta_key, 
    SUBSTRING_INDEX(M.meta_value,': ',2) AS s_name,
	COUNT(m.meta_id) AS n_refs
FROM
	wpaef_xtr_vw_postmeta_referenced m
WHERE
    m.meta_key = '_cp__exh_artwork_author' 
GROUP BY
    SUBSTRING_INDEX(M.meta_value,': ',2)
    
    
ORDER BY
	COUNT(m.meta_id) DESC
LIMIT 10
