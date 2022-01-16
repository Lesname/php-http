CREATE TABLE IF NOT EXISTS request
(
    id BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,

    service VARCHAR(25) NOT NULL,

    method VARCHAR(8) NOT NULL,
    action VARCHAR(50) NOT NULL,

    identity VARCHAR(77),
    identity_type VARCHAR(40) GENERATED ALWAYS AS (REGEXP_REPLACE(identity, '([a-z]+(\.[a-z]+){1,40})/([0-9a-f\-]{36})', '\\1')) stored,
    identity_id VARCHAR(36) GENERATED ALWAYS AS (REGEXP_REPLACE(identity, '([a-z]+(\.[a-z]+{1,40})/([0-9a-f\-]{36})', '\\2')) stored,
    identity_role VARCHAR(20),

    ip VARCHAR(39),
    user_agent VARCHAR(255),

    requested_on bigint(14) NOT NULL,

    duration INT(6) NOT NULL,
    duration_type VARCHAR(12) GENERATED ALWAYS AS (
        case
            when duration <= 250 THEN 'nearRealTime'
            when duration <= 500 then 'acceptable'
            when duration <= 2000 then 'slow'
            else 'never'
        end
    ) STORED,

    response SMALLINT NOT NULL,
    response_type VARCHAR(12) GENERATED ALWAYS AS (
        case
            when floor(response / 100) = 2 THEN 'success'
            when floor(response / 100) = 4 then 'error.client'
            when floor(response / 100) = 5 then 'error.server'
            else 'unknown'
        end
    ) STORED,

    error JSON,

    INDEX (identity, requested_on),
    INDEX (ip, requested_on),
    INDEX (action, requested_on)
) CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
