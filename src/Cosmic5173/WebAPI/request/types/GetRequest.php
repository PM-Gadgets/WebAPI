<?php

namespace Cosmic5173\WebAPI\request\types;

use Cosmic5173\WebAPI\request\Request;
use Cosmic5173\WebAPI\request\RequestMode;

class GetRequest extends Request {

    private array $params;

    public static function createGetRequest(string $url, array $headers = [], array $params = []): GetRequest {
        return new GetRequest($url, $headers, $params);
    }

    public function __construct($url, array $headers = [], array $params = []) {
        parent::__construct($url, $headers);
        $this->params = $params;
    }

    public function addParam(string|int|null $value): self {
        $this->params[] = $value;
        return $this;
    }

    public function removeParam(int $i): self {
        unset($this->params[$i]);
        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array {
        return $this->params;
    }

    /**
     * @param array $params
     * @return GetRequest
     */
    public function setParams(array $params): self {
        $this->params = $params;
        return $this;
    }

    public function getMode(): int {
        return RequestMode::GET;
    }
}