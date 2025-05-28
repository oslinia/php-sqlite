<?php

namespace Framework\Foundation\Routing;

class Rule extends Mapper
{
    private string $path;

    public function __construct(string $path, string $name)
    {
        if (!parent::$flag) {
            $this->path = $path;

            $names = $tokens = array();

            if (preg_match_all('/{([A-Za-z0-9_-]+)}/', $path, $matches)) {
                foreach ($matches[0] as $mask)
                    $tokens[$mask] = '([A-Za-z0-9_-]+)';

                $names = $matches[1];
            }

            parent::$tmp[$path] = 0 === ($size = count($names)) ? [$name, $size] : [$name, $size, $names, $tokens];
        }
    }

    public function where(string ...$args): void
    {
        if (!parent::$flag)
            foreach ($args as $name => $pattern)
                parent::$tmp[$this->path][3]['{' . $name . '}'] = '(' . $pattern . ')';
    }
}