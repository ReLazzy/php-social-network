<?php


class Request
{
    private string     $method;
    private string     $uri;
    private array      $body;
    private ?string    $token;
    private ?\stdClass $decoded;
    private array $files;

    public function __construct(string $method, string $uri, array $body, ?string $token, ?array $files)
    {
        $this->method = $method;
        $this->uri    = $uri;
        $this->body   = $body;
        $this->token  = $token;
        $this->files  = $files;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function setDecoded(\stdClass  $decoded)
    {
        $this->decoded = $decoded;
    }

    public function getDecoded()
    {
        return $this->decoded ?? null;
    }
    public function getFile()
    {
        return  $this->files ?? null;
    }


    public function getToken(): ?string
    {
        return $this->token;
    }
}
