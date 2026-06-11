CREATE TABLE IF NOT EXISTS throttle_request (
    id BIGINT NOT NULL PRIMARY KEY GENERATED ALWAYS AS IDENTITY,

    action VARCHAR(60) NOT NULL,
    identity VARCHAR(77),
    ip VARCHAR(39) NOT NULL,

    requested_on bigint NOT NULL,
    response SMALLINT NOT NULL
);
create INDEX throttle_request_action_identity_ip_requested on throttle_request (action, identity, ip, requested_on);
create INDEX throttle_request_requested_on on throttle_request (requested_on);
