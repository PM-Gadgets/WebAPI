<?php

namespace Cosmic5173\WebAPI\thread;

class RequestRecvQueue extends \Threaded {

    public function publishResult(int $requestId, ?string $result) : void{
        $this[] = serialize([$requestId, $result]);
    }

    public function fetchResults(&$requestId, &$result) : bool{
        $row = $this->shift();
        if(is_string($row)){
            [$requestId, $result] = unserialize($row, ["allowed_classes" => true]);
            return true;
        }
        return false;
    }
}