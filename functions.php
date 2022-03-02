<?php

require_once(__DIR__.'/db.php');

/**
 * Redirects to a given URI.
 */
function redirect($uri) {
    header('Location: '.$uri);
    exit();
}

/**
 * Fetches a user by user id.
 */

function load_user_by_id($id) {
    $db = getDatabaseConnection();
    $stmt = $db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}

/**
 * Checks if a username and password are valid.
 */
function checkLogin($username, $password) {
    $db = getDatabaseConnection();
    $stmt = $db->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if($user && password_verify($password, $user['password'])) {
        return $user;
    }

    return FALSE;
}

/**
 * Sets up a new user session.
 */
function loggedin_init($user) {
    // Make sure we issue a new session ID and free all old session variables.
    session_regenerate_id(TRUE);
    session_unset();
    $_SESSION['logged_in'] = TRUE;
    $_SESSION['user'] = (array) $user;
}

/**
 * Sets up a remember-me auth token. Make sure to run this before any output is produced.
 */
function auth_token_setup($user) {
    // Implementation from https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence#secure-remember-me-cookies.
    $db = getDatabaseConnection();
    $stmt = $db->prepare('INSERT INTO auth_tokens (selector, hashedValidator, userid, expires)
        VALUES(:selector, :hashedValidator, :userid, :expires);');

    $selector = bin2hex(random_bytes(6));
    $validator = bin2hex(random_bytes(32));
    $expiration = time() + 4*86400;
    $stmt->execute([
        'userid' => $user['id'],
        'selector' => $selector,
        'hashedValidator' => hash('sha256', $validator),
        'expires' => $expiration,
    ]);
    setcookie('rememberme', $selector.':'.$validator, $expiration, '/', '', TRUE, TRUE);
}

/**
 * Returns the remember-me cookie information if it exists.
 */
function get_remember_me_cookie() {
    if(!empty($_COOKIE['rememberme'])) {
        $parts = explode(':', $_COOKIE['rememberme']);
        if(count($parts) === 2) {
            return [
                'selector' => $parts[0],
                'validator' => $parts[1],
            ];
        }
    }

    return NULL;
}

/**
 * Checks a remember-me token.
 * Returns a user entity if successful, NULL otherwise.
 */
function validate_auth_token() {
    // Implementation from https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence#secure-remember-me-cookies.
    if($rememberme = get_remember_me_cookie()) {
        $selector = $rememberme['selector'];
        $validator = $rememberme['validator'];
        $db = getDatabaseConnection();
        $stmt = $db->prepare('SELECT * FROM auth_tokens WHERE selector = :sel');
        $stmt->execute([
            'sel' => $selector,
        ]);
        $hashedValidator = hash('sha256', $validator);
        foreach($stmt->fetchAll() as $auth) {
            if(hash_equals($auth['hashedValidator'], $hashedValidator) && ((int)$auth['expires']) > time()) {
                return load_user_by_id($auth['userid']);
            }
        }
    }

    return NULL;
}

/**
 * Logs out the current user.
 */
function user_logout() {
    // If there's a rememberme token, invalidate it.
    if($rememberme = get_remember_me_cookie()) {
        $db = getDatabaseConnection();
        $stmt = $db->prepare('UPDATE auth_tokens SET expires = :expires WHERE selector = :sel AND userid = :uid');
        $stmt->execute([
            'sel' => $rememberme['selector'],
            'uid' => getCurrentUserId(),
            'expires' => time(),
        ]);
        setcookie('rememberme', '', time(), '/', '', TRUE, TRUE);
    }
    session_unset();
    session_destroy();
    redirect('/');
}

/**
 * Returns the current user name.
 */
function getCurrentUserName() {
    return $_SESSION['user']['username'];
}

/**
 * Returns the current user id.
 */
function getCurrentUserId() {
    return $_SESSION['user']['id'];
}

/**
 * Returns whether or not the user is logged in.
 */
function isLoggedIn() {
    return !empty($_SESSION['logged_in']);
}

/**
 * Returns the log entries from the database.
 */
function getLogEntries($userid, $startdt = NULL, $enddt = NULL) {
    $db = getDatabaseConnection();
    $query = 'SELECT * FROM entry WHERE userid = :uid';
    $params = ['uid' => $userid];

    if(!is_null($startdt)) {
        $query .= ' AND startdt >= :start';
        $params['start'] = $startdt;
    }
    if(!is_null($enddt)) {
        $query .= ' AND startdt <= :end';
        $params['end'] = $enddt;
    }
    $query .= ' ORDER BY startdt DESC';
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Adds a log entry.
 */
function addLogEntry($entry) {
    $db = getDatabaseConnection();
    $stmt = $db->prepare('INSERT INTO entry (userid, startdt, enddt, event) VALUES(:uid, :start, :end, :event)');
    return $stmt->execute([
        'uid' => $entry['userid'],
        'start' => $entry['startdt'],
        'end' => $entry['enddt'],
        'event' => $entry['event'],
    ]);
}

/**
 * Returns list of tags.
 */
function getTags() {
    return [
        'tag1',
        'tag2',
        'tag2',
    ];
}

/**
 * Returns the time spent for the entry.
 */
function getEntryDuration($entry) {
    if(empty($entry['enddt'])) {
        return 0;
    }
    return $entry['enddt'] - $entry['startdt'];
}

/**
 * Returns a displayable date time for a given unix timestamp and timezone.
 */
function displayDate($timestamp, $timezone) {
    $tzo = new \DateTimeZone($timezone);
    $time = new \DateTime();
    $time->setTimestamp($timestamp)->setTimezone($tzo);
    return $time->format('H:i');
}

/**
 * Returns a log entry by ID.
 */
function getLogEntry($id) {
    $db = getDatabaseConnection();
    $stmt = $db->prepare('SELECT * FROM entry WHERE id = :uid LIMIT 1');
    $stmt->execute(['uid' => $id]);
    return $stmt->fetch();
}

function updateLogEntry($id, $fields) {
    $db = getDatabaseConnection();
    $set_part = '';
    foreach($fields as $key => $value) {
        $set_part .= $key.'=:'.$key.',';
    }
    $stmt = $db->prepare('UPDATE entry SET '.trim($set_part,',').' WHERE id = :pkid LIMIT 1');
    return $stmt->execute(array_merge(['pkid' => $id], $fields));
}

function deleteLogEntry($id) {
    $db = getDatabaseConnection();
    $stmt = $db->prepare('DELETE FROM entry WHERE id = :id LIMIT 1');
    return $stmt->execute([':id' => $id]);
}