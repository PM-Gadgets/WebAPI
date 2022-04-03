<?php

namespace Cosmic5173\WebAPI\request;

abstract class Request {

    private string $url;
    private array $headers;

    public function __construct(string $url, array $headers) {
        $this->url = $url;
        $this->headers = $headers;
    }

    /**
     * @return string
     */
    public function getUrl(): string {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Request
     */
    public function setUrl(string $url): self {
        $this->url = $url;
        return $this;
    }

    public function addHeader(string $header): self {
        $this->headers[] = $header;
        return $this;
    }

    public function removeHeader(int $i): self {
        unset($this->headers[$i]);
        return $this;
    }

    public function clearHeaders(): self {
        $this->headers = [];
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders(): array {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return Request
     */
    public function setHeaders(array $headers): self {
        $this->headers = $headers;
        return $this;
    }

    abstract public function getMode(): int;
}