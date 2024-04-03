CREATE TABLE IF NOT EXISTS throttle_request (
    id BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,

    action VARCHAR(60) NOT NULL,
    identity VARCHAR(77),
    ip VARCHAR(39) NOT NULL,

    requested_on bigint(14) NOT NULL,

    response SMALLINT NOT NULL,

    INDEX (action, identity, ip, requested_on),
    INDEX (requested_on)
);
