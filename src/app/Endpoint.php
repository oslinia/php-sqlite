<?php

namespace Application;

use Framework\Http\{Path, Response};

function middleware(string $argument): string
{
    return 'Middleware: ' . $argument;
}

class Endpoint extends Response
{
    public function __invoke(): array
    {
        return [$this->url_path('style.css'), null, null, 'ASCII'];
    }

    public function media(Path $path): array
    {
        return $this->render_media($path->name);
    }

    public function page(Path $path): array
    {
        header('Header: Template');
        $this->context(lang: 'ru', url: $this->url);

        return $this->render_template(substr($path->name, 0, -4) . 'php');
    }

    public function archive(Path $path, callable $middleware): array
    {
        $this->charset('ASCII');

        $body = 'Path year: ' . $path->year;

        if (isset($path->month))
            $body .= ' month: ' . $path->month;

        if (isset($path->day))
            $body .= ' day: ' . $path->day;

        $current = PHP_EOL . PHP_EOL . $this->url->scheme() . '://' . $this->url->host() . $this->url->path();

        return $this->response($body . PHP_EOL .
            $this->url_for('archive', year: '2025', month: '05', day: '25') . PHP_EOL .
            $this->url_for('archive', year: '2025', month: '05') . PHP_EOL .
            $this->url_for('archive', year: '2025') . PHP_EOL .
            $middleware('argument') . PHP_EOL .
            $this->url->scheme() . '://' . $this->url->host() . $this->url->path()
        );
    }
}