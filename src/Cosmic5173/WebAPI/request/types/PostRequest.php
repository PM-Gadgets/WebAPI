<?php

namespace Cosmic5173\WebAPI\request\types;

use Cosmic5173\WebAPI\request\RequestMode;

class PostRequest extends GetRequest {

    public static function createPostRequest(string $url, array $headers = [], array $params = []): PostRequest {
        return new PostRequest($url, $headers, $params);
    }

    public function getMode(): int {
        return RequestMode::POST;
    }
}