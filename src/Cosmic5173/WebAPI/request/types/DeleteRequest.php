<?php

namespace Cosmic5173\WebAPI\request\types;

use Cosmic5173\WebAPI\request\RequestMode;

class DeleteRequest extends GetRequest {

    public function getMode(): int {
        return RequestMode::DELETE;
    }
}