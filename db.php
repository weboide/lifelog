<?php

function getDatabaseConnection() {
    global $config;
    try {
        return new PDO($config['dbdsn'], $config['dbuser'], $config['dbpassword']);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}
