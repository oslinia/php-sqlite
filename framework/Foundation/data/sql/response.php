<?php

return (object)[
    'pattern' => 'SELECT * FROM pattern;',
    'mask' => "
SELECT mask.i, mask.mask
FROM mask
JOIN name ON mask.name_id=name.id
WHERE name.name='%s' AND mask.size=%d ORDER BY mask.i;",
];