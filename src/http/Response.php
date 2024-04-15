<?php
class Response
{
    private int $statusCode;
    private string $body;

    public function __construct(int $statusCode = 200, string $body = "OK")
    {
        $this->statusCode = $statusCode;
        $this->body = $body;
    }

    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }


    public function getBody(): string
    {
        return $this->body;
    }
}
