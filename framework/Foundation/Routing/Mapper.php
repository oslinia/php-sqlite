<?php

namespace Framework\Foundation\Routing;

use Framework\Http\Path;

use SQLite3;

class Mapper
{
    protected static array $endpoint = [];
    protected static bool $flag;
    protected static array $tmp;
    protected static array $url;
    protected static SQLite3 $lite;
    private string $sql;

    private function initial($database): void
    {
        $sql = require $this->sql . 'initial.php';

        $db = new SQLite3($database);
        $db->exec($sql->pattern);
        $db->exec($sql->name);
        $db->exec($sql->mask);
        $db->exec($sql->urls);
        $db->close();

        self::$tmp = array();
    }

    private function mask(array $items, string $select, int $size, $pattern): string
    {
        [, , $mask, $replace] = $items;
        $values = ' ';

        for ($i = 0, $c = count($mask); $i < $c; ++$i)
            $values .= "($select, $size, $i, '$mask[$i]')" . (next($mask) ? ', ' : ';');

        self::$lite->exec('INSERT INTO mask (name_id, size, i, mask) VALUES' . $values);

        return str_replace(array_keys($replace), array_values($replace), $pattern);
    }

    private function data(): void
    {
        $unique = null;
        $sql = require $this->sql . 'data.php';

        foreach (self::$tmp as $path => $items) {
            [$name, $size] = $items;

            $name === $unique || self::$lite->exec(sprintf($sql->name, $name));

            $unique = $name;

            $pattern = '/^' . str_replace('/', '\/', $path) . '$/';

            $select = "(SELECT id FROM name WHERE name='$name')";

            0 === $size || $pattern = $this->mask($items, $select, $size, $pattern);

            self::$lite->exec(sprintf($sql->pattern, $pattern, $name));

            self::$lite->exec(sprintf($sql->urls, $select, $size, $path, $pattern));
        }
    }

    protected function init(string $data, string $database, string $src): void
    {
        $this->sql = $data . 'sql' . DIRECTORY_SEPARATOR;

        self::$flag = is_file($database);
        self::$flag || $this->initial($database);

        require $src . 'app' . DIRECTORY_SEPARATOR . 'routing.php';

        self::$lite = new SQLite3($database);

        self::$flag || !self::$tmp || $this->data();
    }

    protected function response(): array|string
    {
        $sql = require $this->sql . 'response.php';

        $patterns = self::$lite->query($sql->pattern);

        self::$url = explode('?', $_SERVER['REQUEST_URI'], 2);

        while ([$pattern, $name] = $patterns->fetchArray(SQLITE3_NUM))
            if (preg_match($pattern, self::$url[0], $matches))
                if (isset(self::$endpoint[$name])) {
                    [$class, $method, $middleware] = self::$endpoint[$name];

                    $value = array_slice($matches, 1);

                    if (0 < $size = count($value)) {
                        if ($masks = self::$lite->query(sprintf($sql->mask, $name, $size))) {
                            $s = null;
                            $tokens = [];

                            while ([$i, $mask] = $masks->fetchArray(SQLITE3_NUM)) {
                                $s = $i;
                                $tokens[$mask] = $value[$i];
                            }

                            if ($s + 1 === $size) {
                                array_unshift($middleware, new Path($tokens));

                                return new $class()->$method(...$middleware);
                            }
                        }
                    } else {
                        return new $class()->$method(...$middleware);
                    }
                }

        return ['Not Found', 404, null, 'ASCII'];
    }

    protected function ssl(): bool
    {
        return isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS'] && '443' === $_SERVER['SERVER_PORT'];
    }
}