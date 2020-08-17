<?php

/**
 * Copyright 2020 OpenZipkin Authors
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace ZipkinTests\Unit\Instrumentation\Http\Server\Psr15;

use Zipkin\Instrumentation\Http\Client\Psr18\Request;
use ZipkinTests\Unit\Instrumentation\Http\Client\BaseRequestTest;
use GuzzleHttp\Psr7\Request as Psr7Request;

final class RequestTest extends BaseRequestTest
{
    public static function createRequest(
        string $method,
        string $uri,
        $headers = [],
        $body = null,
        $route = null
    ): array {
        $delegateRequest = new Psr7Request($method, $uri, $headers, $body);
        return [new Request($delegateRequest), $delegateRequest];
    }
}