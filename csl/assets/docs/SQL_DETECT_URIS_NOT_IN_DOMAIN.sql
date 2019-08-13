SELECT 
    u.display_name,
    p.post_title,
    p.ID,
    url.meta_value entity_URL,
    uri.meta_value entity_URI,
    REPLACE((REPLACE((SUBSTRING_INDEX(SUBSTRING_INDEX(REPLACE(url.meta_value, '//', ''), '/', 1), '*', -2)), 'http:','')),'https:','') server_domain
FROM
    (
    (
    wpaef_posts p
    INNER JOIN
    wpaef_postmeta uri
    ON 
    p.ID = uri.post_id
    )
    INNER JOIN
    wpaef_postmeta url
    ON
    p.ID = url.post_id
    )
    INNER JOIN
    wpaef_users u
    ON
    p.post_author = u.ID
WHERE
    INSTR(uri.meta_value, REPLACE((REPLACE((SUBSTRING_INDEX(SUBSTRING_INDEX(REPLACE(url.meta_value, '//', ''), '/', 1), '*', -2)), 'http:','')),'https:','')) < 1
    AND
    url.meta_key = "_cp__ent_url"
    AND
    uri.meta_key IN ("_cp__ent_rss_uri", "_cp__ent_html_uri")
    AND
    uri.meta_value NOT LIKE "%facebook.com%"
ORDER BY
    u.display_name,
    p.post_title,
    p.ID    
    