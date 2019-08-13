SELECT 
	u.display_name,
	p.post_author,
	pm.meta_id, 
	pm.post_id,
	pm.meta_key,
	pm.meta_value
FROM 
	wpaef_postmeta pm
INNER JOIN (
	SELECT 
    	post_id, 
		meta_key,
		meta_value
    FROM 
    	wpaef_postmeta 
    GROUP BY 
    	post_id, 
    	meta_key,
    	meta_value
    HAVING 
    	COUNT(post_id) > 1 
		AND 
    	COUNT(meta_key) > 1 
    	AND 
    	COUNT(meta_value) > 1
) px 
ON 
	pm.post_id = px.post_id 
	AND 
	pm.meta_key = px.meta_key 
	AND
	pm.meta_value = px.meta_value
INNER JOIN 
	wpaef_posts p
ON 
	pm.post_id = p.ID 
INNER JOIN 
	wpaef_users u 
ON 
	p.post_author = u.ID 

ORDER BY 
	u.display_name,
	pm.post_id,
	pm.meta_key,
	pm.meta_value;
	
	
/*	
SELECT post_id, meta_key, meta_value  
    FROM  wpaef_postmeta 
    GROUP BY  post_id, meta_key, meta_value  
    HAVING COUNT(*) >1;
*/