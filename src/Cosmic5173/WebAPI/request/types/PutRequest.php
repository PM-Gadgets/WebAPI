<?php

namespace Cosmic5173\WebAPI\request\types;

use Cosmic5173\WebAPI\request\Request;
use Cosmic5173\WebAPI\request\RequestMode;

class PutRequest extends Request {

    private string $payload;

    public static function createPutRequest(string $url, array $headers = [], string $payload = ""): PutRequest {
        return new PutRequest($url, $headers, $payload);
    }

    public function __construct(string $url, array $headers, string $payload) {
        parent::__construct($url, $headers);
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function getPayload(): string {
        return $this->payload;
    }

    /**
     * @param string $payload
     * @return PutRequest
     */
    public function setPayload(string $payload): self {
        $this->payload = $payload;
        return $this;
    }

    public function getMode(): int {
        return RequestMode::PUT;
    }
}