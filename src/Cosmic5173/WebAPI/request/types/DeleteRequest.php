<?php

namespace Cosmic5173\WebAPI\request\types;

use Cosmic5173\WebAPI\request\RequestMode;

class DeleteRequest extends GetRequest {

    public static function createDeleteRequest(string $url, array $headers = [], array $params = []): DeleteRequest {
        return new DeleteRequest($url, $headers, $params);
    }

    public function getMode(): int {
        return RequestMode::DELETE;
    }
}