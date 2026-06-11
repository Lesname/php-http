CREATE TABLE IF NOT EXISTS request
(
    id BIGINT NOT NULL PRIMARY KEY GENERATED ALWAYS AS IDENTITY,

    service VARCHAR(25) NOT NULL,
    action VARCHAR(60) NOT NULL,

    identity VARCHAR(77),
    identity_type VARCHAR(40) GENERATED ALWAYS AS (REGEXP_REPLACE(identity, '^([a-z]+(\.[a-z]+){0,3})/[0-9a-f-]{36}$', '\\1')) stored,
    identity_id VARCHAR(36) GENERATED ALWAYS AS (REGEXP_REPLACE(identity, '^[a-z]+(\.[a-z]+){0,3}/([0-9a-f-]{36})$', '\\2')) stored,

    ip VARCHAR(39),
    user_agent VARCHAR(255),

    requested_on bigint NOT NULL,

    duration INT NOT NULL,
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

    error JSON
);

CREATE INDEX request_identity on request (identity, requested_on);
CREATE INDEX request_ip on request (ip, requested_on);
CREATE INDEX request_action on request (action, requested_on);
