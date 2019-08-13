CREATE OR REPLACE VIEW wpaef_xtr_vw_exhibitions_people_country_gender AS

SELECT SQL_CACHE
    p.ID,
    SUBSTRING_INDEX(SUBSTRING_INDEX(t.meta_value,';',-(1)),';',1) AS entity_country,
    SUBSTRING_INDEX(SUBSTRING_INDEX(t.meta_value,';',-(2)),';',1) AS entity_region,
    SUBSTRING_INDEX(t.meta_value,';',1) AS entity_town,
    p.post_title AS entity_name,
    w.exhibition_title as exhibition_title,
    w.exhibition_year,
    w.exhibition_town,
    w.person_role,
    w.person_name,
    w.person_id,
    w.person_country,
    w.person_gender,
    w.person_age
FROM
    wpaef_posts AS p
    INNER JOIN 
    (
    SELECT
        s.ID,
        s.post_title as exhibition_title,
        YEAR(s.date_started) AS exhibition_year,
        s.exhibition_town as exhibition_town,
        s.person_role as person_role,
        s.person_name as person_name,
        s.person_id as person_id,
        s.person_country as person_country,
        s.person_gender as person_gender,
        s.person_age as person_age
    FROM
        (
        SELECT
        	CAST(SUBSTRING_INDEX(e.meta_value, ': ', 1) AS UNSIGNED) AS ID,
            e.meta_id,
            d.post_title as post_title,
            d.meta_value AS date_started,
            et.meta_value as exhibition_town,
            gt.person_role as person_role,
            gt.meta_value as person_name,
            gt.person_id as person_id,
            gt.person_country AS person_country,
            gt.person_gender AS person_gender,
            gt.person_age AS person_age
        FROM
            (
            (
        	wpaef_postmeta e
            INNER JOIN
            (
                SELECT
                    post_id,
                    post_title,
                    meta_value
                FROM
                    wpaef_postmeta
                    INNER JOIN
                    wpaef_posts
                    ON 
                    wpaef_posts.ID = wpaef_postmeta.post_id
                WHERE
                    meta_key = "_cp__exh_exhibition_start_date"
                    AND
                    meta_value IS NOT NULL
            ) AS d
            ON
            e.post_id = d.post_id
            )
            INNER JOIN
            (
                SELECT
                    post_id,
                    post_title,
                    meta_value
                FROM
                    wpaef_postmeta
                    INNER JOIN
                    wpaef_posts
                    ON 
                    wpaef_posts.ID = wpaef_postmeta.post_id
                WHERE
                    meta_key = "_cp__exh_exhibition_town"
                    AND
                    meta_value IS NOT NULL
            ) AS et
            ON
            e.post_id = et.post_id
            )
            INNER JOIN
            (
                SELECT
                    zpm.post_id as post_id,
                    zpp.post_title as post_title,
                    CASE zpm.meta_key WHEN "_cp__exh_artwork_author" THEN "Autor/a" WHEN "_cp__exh_collector" THEN "Coleccionista" WHEN "_cp__exh_curator" THEN "Comisario/a" ELSE NULL END as person_role, 
                    IF(LOCATE(":", zpm.meta_value) > 0, SUBSTRING_INDEX(zpm.meta_value, ": ", 1), NULL) AS person_id,
                    IF(LOCATE(":", zpm.meta_value) > 0, SUBSTRING_INDEX(zpm.meta_value, ": ", -1), zpm.meta_value) AS meta_value,
                    nc.person_country,
                    nc.person_gender,
                    nc.person_birth_year,
                    nc.person_death_year,
                    nc.person_age
                FROM
                    wpaef_postmeta zpm
                    INNER JOIN
                    wpaef_posts zpp
                    ON 
                    zpp.ID = zpm.post_id
                    LEFT JOIN
                    (
                        SELECT
                            post_id,
							person_country,
							person_gender,
							person_birth_year,
							person_death_year,
							person_age
                        FROM
                            wpaef_xtr_vw_person_country_gender_birth_death_age
                    ) AS nc
                    ON 
                    SUBSTRING_INDEX(zpm.meta_value, ": ", 1) = nc.post_id 
                WHERE
                    zpm.meta_value IS NOT NULL
                    AND
                    zpm.meta_key IN ("_cp__exh_artwork_author","_cp__exh_curator","_cp__exh_art_collector")
            ) AS gt
            ON
            e.post_id = gt.post_id
        WHERE
        	e.meta_key = "_cp__exh_supporter_entity"
            AND
            e.meta_value IS NOT NULL
            AND
            TRIM(e.meta_value) != ""
        ) AS s
    GROUP BY
        s.ID,
        s.post_title,
        YEAR(s.date_started),
        s.exhibition_town,
        s.person_role,
        s.person_name,
        s.person_id,
        s.person_country,
        s.person_gender,
        s.person_age
    ) AS w
    ON
    p.ID = w.ID
    INNER JOIN
    (
        SELECT
            post_id,
            meta_value
        FROM
            wpaef_postmeta
        WHERE
            meta_key = "_cp__ent_town"
            AND
            meta_value IS NOT NULL
    ) AS t
    ON 
    p.ID = t.post_id 
WHERE
    p.post_type = "entity"
    AND
    p.post_status = "publish"
ORDER BY
    SUBSTRING_INDEX(SUBSTRING_INDEX(t.meta_value,';',-(1)),';',1),
    SUBSTRING_INDEX(SUBSTRING_INDEX(t.meta_value,';',-(2)),';',1),
    SUBSTRING_INDEX(t.meta_value,';',1),
    t.meta_value,
    p.post_title,
    w.exhibition_title,
    w.exhibition_year