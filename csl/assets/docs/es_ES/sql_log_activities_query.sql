SELECT
    u.display_name AS s_display_name,
    u.ID AS n_user_id,
    d_activity_date AS d_activity_date,
    MAX(d_max_logout) AS d_max_logout,
    MAX(n_num_logouts) AS n_num_logouts,
    MAX(d_min_login) AS d_min_login,
    MAX(n_num_logins) AS n_num_logins,
    MAX(n_num_logins) <> MAX(n_num_logouts) AS b_impaired_logouts,
    MAX(d_min_activity) AS d_min_activity,
    MAX(d_max_activity) AS d_max_activity,
    MAX(n_num_activities) AS n_num_activities,
    TIMEDIFF(MAX(d_max_logout), MAX(d_min_login)) AS t_sessions_time,
    TIMEDIFF(MAX(d_max_activity), MAX(d_min_activity)) AS t_activities_time,
    TIMEDIFF(MAX(d_max_logout), MAX(d_min_login)) / MAX(n_num_activities) AS t_activities_per_session,
    TIMEDIFF(MAX(d_max_activity), MAX(d_min_activity)) / MAX(n_num_activities) AS t_activity_average_time 
FROM
    (
    SELECT
        user_id AS n_user_id,
        DATE_FORMAT(activity_date, "%Y %m %d") AS d_activity_date,
        MAX(activity_date) AS d_max_logout,
        COUNT(log_id) AS n_num_logouts,
        NULL AS d_min_login,
        NULL AS n_num_logins,
        NULL AS d_min_activity,
        NULL AS d_max_activity,
        NULL AS n_num_activities
    FROM
    	wpaef_xtr_activity_log
    WHERE
    	activity="log_out"
    GROUP BY
    	user_id,
        DATE_FORMAT(activity_date, "%Y %m %d")

    UNION ALL

    SELECT
        user_id AS n_user_id,
        DATE_FORMAT(activity_date, "%Y %m %d") AS d_activity_date,
        NULL AS d_max_logout,
        NULL AS n_num_logouts,
        MIN(activity_date) AS d_min_login,
        COUNT(log_id) AS n_num_logins,
        NULL AS d_min_activity,
        NULL AS d_max_activity,
        NULL AS n_num_activities
    FROM
    	wpaef_xtr_activity_log
    WHERE
    	activity="log_in"
    GROUP BY
    	user_id,
        DATE_FORMAT(activity_date, "%Y %m %d")

    UNION ALL

    SELECT
        user_id AS n_user_id,
        DATE_FORMAT(activity_date, "%Y %m %d") AS d_activity_date,
        NULL AS d_max_logout,
        NULL AS n_num_logouts,
        NULL AS d_min_login,
        NULL AS n_num_logins,
        MIN(activity_date) AS d_min_activity,
        MAX(activity_date) AS d_max_activity,
        COUNT(log_id) AS n_num_activities
    FROM
    	wpaef_xtr_activity_log
    WHERE
    	object_type <> "@"
    GROUP BY
    	user_id,
        DATE_FORMAT(activity_date, "%Y %m %d")
    ) AS l
    INNER JOIN
    wpaef_users AS u
    ON
    l.n_user_id = u.ID
GROUP BY    
    u.display_name,
    u.ID,
    d_activity_date