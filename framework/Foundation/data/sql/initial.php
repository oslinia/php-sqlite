<?php

return (object)[
    'pattern' => '
DROP TABLE IF EXISTS pattern;
CREATE TABLE pattern (
    pattern TEXT NOT NULL
                 UNIQUE,
    name    TEXT NOT NULL
);',
    'name' => '
DROP TABLE IF EXISTS name;
CREATE TABLE name (
    id   INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT    NOT NULL
                 UNIQUE
);',
    'mask' => '
DROP TABLE IF EXISTS mask;
CREATE TABLE mask (
    name_id INTEGER NOT NULL,
    size    INTEGER NOT NULL,
    i       INTEGER NOT NULL,
    mask    TEXT    NOT NULL,
    FOREIGN KEY (name_id) REFERENCES name (id) ON DELETE CASCADE
);',
    'urls' => '
DROP TABLE IF EXISTS urls;
CREATE TABLE urls (
    name_id INTEGER NOT NULL,
    size    INTEGER NOT NULL,
    path    TEXT    NOT NULL,
    pattern TEXT    NOT NULL,
    FOREIGN KEY (name_id) REFERENCES name (id) ON DELETE CASCADE
);',
];