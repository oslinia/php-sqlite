<?php

return (object)[
    'name' => "INSERT INTO name (name) VALUES ('%s');",
    'pattern' => "INSERT INTO pattern (pattern, name) VALUES ('%s', '%s');",
    'urls' => "INSERT INTO urls (name_id, size, path, pattern) VALUES (%s, %d, '%s','%s');",
];