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
    session_regenerate_id(TRUE);
    $_SESSION['logged_in'] = TRUE;
    $_SESSION['user'] = (array) $user;
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
function getLogEntries($userid) {
    $db = getDatabaseConnection();
    $stmt = $db->prepare('SELECT * FROM entry WHERE userid = :uid ORDER BY startdt DESC');
    $stmt->execute(['uid' => $userid]);
    return $stmt->fetchAll();
}

/**
 * Adds a log entry.
 */
function addLogEntry($entry) {
    $db = getDatabaseConnection();
    $stmt = $db->prepare('INSERT INTO entry (userid, startdt, enddt, event) VALUES(:uid, :start, :end, :event)');
    return $stmt->execute([
        'uid' => getCurrentUserId(),
        'start' => $entry['start_date'],
        'end' => $entry['end_date'],
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
    return strtotime($entry['enddt']) - strtotime($entry['startdt']);
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

function removeLogEntry($id) {
    // TODO: can't do that now... 😓
}