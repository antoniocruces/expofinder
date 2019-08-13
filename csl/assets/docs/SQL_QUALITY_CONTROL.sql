/* ALL RECORDS */
SELECT DISTINCT
	u.display_name,
	a.post_author, 
	a.post_type,
    'ALL' as c_error_type, 
    'All records' AS s_error_type,
	a.post_title,
	a.ID,
    'N/A' AS c_offending_field,
    'N/A' AS s_offendig_field
FROM 
	wpaef_posts AS a
	INNER JOIN
	wpaef_users AS u
	ON
	a.post_author = u.ID
	AND 
    a.post_type IN ('entity','person','book','company','exhibition') 
	AND 
    a.post_status = 'publish'

/* DUPLICATED */
SELECT DISTINCT
	u.display_name,
	a.post_author, 
	a.post_type,
    'DUP' as c_error_type, 
    'Duplicated record' AS s_error_type,
	a.post_title,
	a.ID,
    'N/A' AS c_offending_field,
    'N/A' AS s_offendig_field
FROM 
	(
    wpaef_posts AS a
	INNER JOIN (
		SELECT post_type, post_title, MIN( id ) AS min_id
		FROM wpaef_posts
		WHERE 
        post_type IN ('entity','person','book','company','exibition') 
		AND post_status = 'publish'
		GROUP BY post_title
		HAVING COUNT( * ) > 1
		) AS b ON a.post_title = b.post_title AND a.post_type = b.post_type
	)
	INNER JOIN
	wpaef_users u
	ON
	a.post_author = u.ID
	AND b.min_id <> a.id
	AND a.post_type IN ('entity','person','book','company','exibition')
	AND a.post_status = 'publish'

/* INVALID GEONAMES TOWN  */
SELECT DISTINCT
	u.display_name,
	a.post_author, 
	a.post_type,
    'TOW' as c_error_type, 
    'Invalid local entity name' AS s_error_type,
	a.post_title,
	a.ID,
    m.meta_key AS c_offending_field,
    m.meta_value AS s_offendig_field
FROM 
	(
    wpaef_posts AS a
	LEFT JOIN 
    wpaef_postmeta m
	ON
    a.ID = m.post_id
    )
	LEFT JOIN
	wpaef_users u
	ON
	a.post_author = u.ID
WHERE
    a.post_type IN ('entity','person','book','company','exhibition')
    AND
    (m.meta_key LIKE '%_town' OR m.meta_key LIKE '%_place')  
    AND
    LENGTH(m.meta_value) - LENGTH(REPLACE(m.meta_value, ';', '')) <> 2 
    AND
	a.post_status = 'publish'

/* SELF REFERENCE */
SELECT DISTINCT
	u.display_name,
	a.post_author, 
	a.post_type,
    'SRF' as c_error_type, 
    'Invalid autolookup field' AS s_error_type,
	a.post_title,
	a.ID,
    m.meta_key AS c_offending_field,
    m.meta_value AS s_offendig_field
FROM 
	(
    wpaef_posts AS a
	LEFT JOIN 
    wpaef_postmeta m
	ON
    a.ID = m.post_id AND m.meta_key IN 
        (
        '_cp__ent_parent_entity',
        '_cp__peo_entity_relation',
        '_cp__peo_person_relation',
        '_cp__boo_paper_author',
        '_cp__boo_sponsorship',
        '_cp__exh_parent_exhibition',
        '_cp__exh_info_source',
        '_cp__exh_artwork_author',
        '_cp__exh_supporter_entity',
        '_cp__exh_funding_entity',
        '_cp__exh_curator',
        '_cp__exh_catalog',
        '_cp__exh_museography'
        )
    )
	LEFT JOIN
	wpaef_users u
	ON
	a.post_author = u.ID
WHERE
    a.post_type IN ('entity','person','book','company','exhibition')
    AND
    SUBSTRING_INDEX(m.meta_value, ': ', 1) NOT REGEXP('(^[0-9]+$)') 
    AND
	a.post_status = 'publish'

/* TAXONOMY LACK */
SELECT 
	x.display_name,
	x.post_author, 
	x.post_type,
    x.c_error_type, 
    x.s_error_type,
	x.post_title,
	x.ID,
    x.c_offending_field,
    x.s_offendig_field
FROM
(
SELECT DISTINCT
	u.display_name,
	a.post_author, 
	a.post_type,
    'TAX' as c_error_type, 
    'Lack of taxonomy' AS s_error_type,
	a.post_title,
	a.ID,
    'N/A' AS c_offending_field,
    'N/A' AS s_offendig_field
FROM 
    (
    wpaef_posts AS a
	LEFT JOIN
	wpaef_users AS u
	ON
	a.post_author = u.ID
    )
    LEFT JOIN 
    (
        SELECT 
            tr.object_id 
        FROM
            wpaef_term_relationships tr 
            LEFT JOIN 
            wpaef_term_taxonomy tt
            ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
        WHERE
            tt.taxonomy IN 
            (
            'tax_typology',
            'tax_ownership'
            )
    ) AS t
    ON a.ID = t.object_id 
WHERE
    a.post_type IN ('entity')
    AND
	a.post_status = 'publish'
    AND
    t.object_id IS NULL

UNION ALL

SELECT DISTINCT
	u.display_name,
	a.post_author, 
	a.post_type,
    'TAX' as c_error_type, 
    'Lack of taxonomy' AS s_error_type,
	a.post_title,
	a.ID,
    'N/A' AS c_offending_field,
    'N/A' AS s_offendig_field
FROM 
    (
    wpaef_posts AS a
	LEFT JOIN
	wpaef_users AS u
	ON
	a.post_author = u.ID
    )
    LEFT JOIN 
    (
        SELECT 
            tr.object_id 
        FROM
            wpaef_term_relationships tr 
            LEFT JOIN 
            wpaef_term_taxonomy tt
            ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
        WHERE
            tt.taxonomy IN 
            (
            'tax_activity'
            )
    ) AS t
    ON a.ID = t.object_id     
WHERE
    a.post_type IN ('person')
    AND
	a.post_status = 'publish'
    AND
    t.object_id IS NULL
    
UNION ALL

SELECT DISTINCT
	u.display_name,
	a.post_author, 
	a.post_type,
    'TAX' as c_error_type, 
    'Lack of taxonomy' AS s_error_type,
	a.post_title,
	a.ID,
    'N/A' AS c_offending_field,
    'N/A' AS s_offendig_field
FROM 
    (
    wpaef_posts AS a
	LEFT JOIN
	wpaef_users AS u
	ON
	a.post_author = u.ID
    )
    LEFT JOIN 
    (
        SELECT 
            tr.object_id 
        FROM
            wpaef_term_relationships tr 
            LEFT JOIN 
            wpaef_term_taxonomy tt
            ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
        WHERE
            tt.taxonomy IN 
            (
            'tax_publisher'
            )
    ) AS t
    ON a.ID = t.object_id     
WHERE
    a.post_type IN ('book')
    AND
	a.post_status = 'publish'
    AND
    t.object_id IS NULL

UNION ALL

SELECT DISTINCT
	u.display_name,
	a.post_author, 
	a.post_type,
    'TAX' as c_error_type, 
    'Lack of taxonomy' AS s_error_type,
	a.post_title,
	a.ID,
    'N/A' AS c_offending_field,
    'N/A' AS s_offendig_field
FROM 
    (
    wpaef_posts AS a
	LEFT JOIN
	wpaef_users AS u
	ON
	a.post_author = u.ID
    )
    LEFT JOIN 
    (
        SELECT 
            tr.object_id 
        FROM
            wpaef_term_relationships tr 
            LEFT JOIN 
            wpaef_term_taxonomy tt
            ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
        WHERE
            tt.taxonomy IN 
            (
            'tax_isic4_category'
            )
    ) AS t
    ON a.ID = t.object_id     
WHERE
    a.post_type IN ('company')
    AND
	a.post_status = 'publish'
    AND
    t.object_id IS NULL

UNION ALL

SELECT DISTINCT
	u.display_name,
	a.post_author, 
	a.post_type,
    'TAX' as c_error_type, 
    'Lack of taxonomy' AS s_error_type,
	a.post_title,
	a.ID,
    'N/A' AS c_offending_field,
    'N/A' AS s_offendig_field
FROM 
    (
    wpaef_posts AS a
	LEFT JOIN
	wpaef_users AS u
	ON
	a.post_author = u.ID
    )
    LEFT JOIN 
    (
        SELECT 
            tr.object_id 
        FROM
            wpaef_term_relationships tr 
            LEFT JOIN 
            wpaef_term_taxonomy tt
            ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
        WHERE
            tt.taxonomy IN 
            (
            'tax_exhibition_type',
            'tax_artwork_type',
            'tax_topic',
            'tax_movement',
            'tax_period'
            )
    ) AS t
    ON a.ID = t.object_id     
WHERE
    a.post_type IN ('exhibition')
    AND
	a.post_status = 'publish'
    AND
    t.object_id IS NULL
) AS x

/* MANDATORY FIELDS */
SELECT
	x.display_name,
	x.post_author, 
	x.post_type,
    x.c_error_type, 
    x.s_error_type,
	x.post_title,
	x.ID,
    x.c_offending_field,
    x.s_offendig_field
FROM
(
SELECT DISTINCT
	u.display_name,
	a.post_author, 
	a.post_type,
    'MAN' as c_error_type, 
    'Lack of mandatory fields' AS s_error_type,
	a.post_title,
	a.ID,
    'N/A' AS c_offending_field,
    'N/A' AS s_offendig_field
FROM 
	(
    wpaef_posts AS a
	LEFT JOIN 
    (
    SELECT
        pm.post_id,
        pm.meta_key,
        pm.meta_value
    FROM
        wpaef_postmeta AS pm
    WHERE
        pm.meta_key IN
            (
            '_cp__ent_town',
            '_cp__ent_address'    
            )  
    ) AS m
	ON
    a.ID = m.post_id
    )
	LEFT JOIN
	wpaef_users u
	ON
	a.post_author = u.ID
WHERE
    a.post_type IN ('entity')
    AND
	a.post_status = 'publish'
    AND
    m.post_id IS NULL

UNION ALL

SELECT DISTINCT
	u.display_name,
	a.post_author, 
	a.post_type,
    'MAN' as c_error_type, 
    'Lack of mandatory fields' AS s_error_type,
	a.post_title,
	a.ID,
    'N/A' AS c_offending_field,
    'N/A' AS s_offendig_field
FROM 
	(
    wpaef_posts AS a
	LEFT JOIN 
    (
    SELECT
        pm.post_id,
        pm.meta_key,
        pm.meta_value
    FROM
        wpaef_postmeta AS pm
    WHERE
        pm.meta_key IN
            (
            '_cp__peo_country'    
            )  
    ) AS m
	ON
    a.ID = m.post_id
    )
	LEFT JOIN
	wpaef_users u
	ON
	a.post_author = u.ID
WHERE
    a.post_type IN ('person')
    AND
	a.post_status = 'publish'
    AND
    m.post_id IS NULL
    
UNION ALL

SELECT DISTINCT
	u.display_name,
	a.post_author, 
	a.post_type,
    'MAN' as c_error_type, 
    'Lack of mandatory fields' AS s_error_type,
	a.post_title,
	a.ID,
    'N/A' AS c_offending_field,
    'N/A' AS s_offendig_field
FROM 
	(
    wpaef_posts AS a
	LEFT JOIN 
    (
    SELECT
        pm.post_id,
        pm.meta_key,
        pm.meta_value
    FROM
        wpaef_postmeta AS pm
    WHERE
        pm.meta_key IN
            (
            '_cp__boo_publishing_place'
            '_cp__boo_paper_author'    
            )  
    ) AS m
	ON
    a.ID = m.post_id
    )
	LEFT JOIN
	wpaef_users u
	ON
	a.post_author = u.ID
WHERE
    a.post_type IN ('book')
    AND
	a.post_status = 'publish'
    AND
    m.post_id IS NULL
    
UNION ALL

SELECT DISTINCT
	u.display_name,
	a.post_author, 
	a.post_type,
    'MAN' as c_error_type, 
    'Lack of mandatory fields' AS s_error_type,
	a.post_title,
	a.ID,
    'N/A' AS c_offending_field,
    'N/A' AS s_offendig_field
FROM 
	(
    wpaef_posts AS a
	LEFT JOIN 
    (
    SELECT
        pm.post_id,
        pm.meta_key,
        pm.meta_value
    FROM
        wpaef_postmeta AS pm
    WHERE
        pm.meta_key IN
            (
            '_cp__com_company_headquarter_place'    
            )  
    ) AS m
	ON
    a.ID = m.post_id
    )
	LEFT JOIN
	wpaef_users u
	ON
	a.post_author = u.ID
WHERE
    a.post_type IN ('company')
    AND
	a.post_status = 'publish'
    AND
    m.post_id IS NULL
    
UNION ALL

SELECT DISTINCT
	u.display_name,
	a.post_author, 
	a.post_type,
    'MAN' as c_error_type, 
    'Lack of mandatory fields' AS s_error_type,
	a.post_title,
	a.ID,
    'N/A' AS c_offending_field,
    'N/A' AS s_offendig_field
FROM 
	(
    wpaef_posts AS a
	LEFT JOIN 
    (
    SELECT
        pm.post_id,
        pm.meta_key,
        pm.meta_value
    FROM
        wpaef_postmeta AS pm
    WHERE
        pm.meta_key IN
            (
            '_cp__exh_exhibition_start_date',    
            '_cp__exh_exhibition_end_date',    
            '_cp__exh_exhibition_town',    
            '_cp__exh_exhibition_site',    
            '_cp__exh_address',    
            '_cp__exh_artwork_author',    
            '_cp__exh_supporter_entity'    
            )  
    ) AS m
	ON
    a.ID = m.post_id
    )
	LEFT JOIN
	wpaef_users u
	ON
	a.post_author = u.ID
WHERE
    a.post_type IN ('exhibition')
    AND
	a.post_status = 'publish'
    AND
    m.post_id IS NULL
) AS x