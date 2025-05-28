<?php

return "
SELECT urls.path, urls.pattern
FROM urls
JOIN name ON urls.name_id=name.id
WHERE name.name='%s' AND urls.size=%d;";