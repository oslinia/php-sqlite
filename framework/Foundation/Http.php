<?php

namespace Framework\Foundation;

function template(): string
{
    ob_start();

    extract(Http::$env->buffer->extract);

    require Http::$env->buffer->path;

    return ob_get_clean();
}

class Http extends Routing\Mapper
{
    public static object $env;

    private function status(int $code): void
    {
        if (ob_get_level())
            ob_end_clean();

        header($_SERVER["SERVER_PROTOCOL"] . [
                200 => ' 200 OK',
                301 => ' 301 Moved Permanently',
                302 => ' 302 Moved Temporarily',
                307 => ' 307 Temporary Redirect',
                308 => ' 308 Permanent Redirect',
                404 => ' 404 Not Found',
                500 => ' 500 Internal Server Error',
            ][$code]);
    }

    private function content(int $length, string $mimetype, null|string $encoding): void
    {
        if (str_starts_with($mimetype, 'text/'))
            $mimetype .= '; charset=' . ($encoding ?? 'UTF-8');

        header('content-length: ' . $length);
        header('content-type: ' . $mimetype);
    }

    private function media(string $filename, null|string $encoding): void
    {
        $mime = fn(string $filename) => match (pathinfo($filename, PATHINFO_EXTENSION)) {
            'css' => 'text/css',
            'htm', 'html' => 'text/html',
            'txt' => 'text/plain',
            'xml' => 'text/xml',
            'gif' => 'image/gif',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'svg' => 'image/svg+xml',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'pdf' => 'application/pdf',
            default => 'application/octet-stream'
        };

        $this->status(200);

        $this->content(filesize($filename), $mime($filename), $encoding);

        if ($f = fopen($filename, 'rb')) {
            while (!feof($f))
                echo fread($f, 1024);

            fclose($f);
        }
    }

    private function document(
        string      $body,
        int|null    $code = null,
        null|string $mimetype = null,
        null|string $encoding = null): void
    {
        $this->status($code ?? 200);

        $this->content(strlen($body), $mimetype ?? 'text/plain', $encoding);

        echo $body;
    }

    public function __construct(string $database, string $src)
    {
        parent::init(self::$env->data, $database, $src);

        $response = parent::response();

        if (is_string($response))
            $this->document($response);

        elseif (is_array($response) and is_string($response[0]))
            self::$env->flag ? $this->media(...$response) : $this->document(...$response);

        else
            $this->document('Invalid data for response', 500, encoding: 'ASCII');
    }

    public static function request(string $dirname): void
    {
        $resource = $dirname . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
        $src = $dirname . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;

        self::$env = require 'data/config.php';
        self::$env->media = $resource . 'media' . DIRECTORY_SEPARATOR;
        self::$env->template = $src . 'template' . DIRECTORY_SEPARATOR;

        new static($resource . 'routing.db', $src);
    }

    public static function collect(array $args): null|string
    {
        $name = array_shift($args);

        if ($url = self::$lite->query(sprintf(require 'data/collect.php', $name, $size = count($args)))) {
            [$path, $pattern] = $url->fetchArray(SQLITE3_NUM);

            if (0 < $size)
                foreach ($args as $mask => $value)
                    $path = str_replace('{' . $mask . '}', $value, $path);

            if (preg_match($pattern, $path, $matches))
                return $matches[0];
        }

        return null;
    }
}