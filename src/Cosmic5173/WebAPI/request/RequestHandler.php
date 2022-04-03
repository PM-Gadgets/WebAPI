<?php

namespace Cosmic5173\WebAPI\request;

use Cosmic5173\WebAPI\thread\RequestThreadPool;
use Cosmic5173\WebAPI\request\types\{GetRequest, PostRequest, PutRequest, DeleteRequest};

final class RequestHandler {

    private static RequestHandler $instance;
    private RequestThreadPool $threadPool;
    /** @var \Closure[] */
    private array $handlers = [];
    public int $requests = 0;

    /**
     * @return \Cosmic5173\WebAPI\request\RequestHandler
     */
    public static function getInstance(): RequestHandler {
        return self::$instance;
    }

    public function __construct(RequestThreadPool $threadPool) {
        self::$instance = $this;
        $this->threadPool = $threadPool;
    }

    /**
     * @return RequestThreadPool
     */
    public function getThreadPool(): RequestThreadPool {
        return $this->threadPool;
    }

    public static function createGetRequest(string $url, array $headers = [], array $params = []): GetRequest {
        return new GetRequest($url, $headers, $params);
    }

    public static function createPostRequest(string $url, array $headers = [], array $params = []): PostRequest {
        return new PostRequest($url, $headers, $params);
    }

    public static function createPutRequest(string $url, array $headers = [], string $payload = ""): PutRequest {
        return new PutRequest($url, $headers, $payload);
    }

    public static function createDeleteRequest(string $url, array $headers = [], array $params = []): DeleteRequest {
        return new DeleteRequest($url, $headers, $params);
    }

    public function sendGetRequest(GetRequest $request, \Closure $closure = null): void {
        $requestId = $this->requests++;
        $this->handlers[$requestId] = $closure;

        $this->threadPool->addRequest($requestId, $request->getUrl(), $request->getMode(), $request->getParams(), "", $request->getHeaders());
    }

    public function sendPostRequest(PostRequest $request, \Closure $closure = null): void {
        $requestId = $this->requests++;
        $this->handlers[$requestId] = $closure;

        $this->threadPool->addRequest($requestId, $request->getUrl(), $request->getMode(), [], json_encode($request->getParams()), $request->getHeaders());
    }

    public function sendPutRequest(PutRequest $request, \Closure $closure = null): void {
        $requestId = $this->requests++;
        $this->handlers[$requestId] = $closure;

        $this->threadPool->addRequest($requestId, $request->getUrl(), $request->getMode(), [], $request->getPayload(), $request->getHeaders());
    }

    public function sendDeleteRequest(DeleteRequest $request, \Closure $closure = null): void {
        $requestId = $this->requests++;
        $this->handlers[$requestId] = $closure;

        $this->threadPool->addRequest($requestId, $request->getUrl(), $request->getMode(), $request->getParams(), "", $request->getHeaders());
    }

    public function addHandler(int $requestId, \Closure $closure): void {
        $this->handlers[$requestId] = $closure;
    }

    public function removeHandler(int $requestId): void {
        unset($this->handlers[$requestId]);
    }

    public function getHandlers(): array {
        return $this->handlers;
    }

    public function checkResults() : void{
        $this->threadPool->readResults($this->handlers);
    }

    public function close() : void{
        $this->threadPool->stopRunning();
    }
}