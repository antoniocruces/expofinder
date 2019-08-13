SELECT SQL_CACHE
    r.meta_key AS meta_key,
    g.meta_key AS s_gender, 
    g.meta_value,            
    COUNT(DISTINCT g.post_id) AS n_records 
FROM 
    wpaef_xtr_vw_postmeta_referenced AS r 
    LEFT JOIN
    (
        SELECT
            post_id,
            meta_key,
            meta_value            
        FROM 
            wpaef_postmeta 
        WHERE 
            meta_key = "_cp__peo_gender"
    ) as g 
    ON 
    g.post_id = r.n_ref_id
WHERE 
    r.meta_key IN ("_cp__exh_artwork_author","_cp__exh_art_collector","_cp__exh_curator")
    AND
    r.n_ref_id IS NOT NULL
GROUP BY 
    r.meta_key,
    g.meta_key,
    g.meta_value;            

/*** ***/
SELECT SQL_CACHE
    g.meta_key AS s_gender, 
    g.meta_value,            
    COUNT(DISTINCT g.post_id) AS n_records 
FROM 
    wpaef_postmeta AS g 
WHERE 
    g.meta_key = "_cp__peo_gender"
    AND
    g.meta_value IN
GROUP BY 
    g.meta_key,
    g.meta_value;            

