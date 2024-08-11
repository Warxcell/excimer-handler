<?php

declare(strict_types=1);
/*
 * Copyright (C) 2016-2024 Taylor & Hart Limited
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains the property
 * of Taylor & Hart Limited and its suppliers, if any.
 *
 * All   intellectual   and  technical  concepts  contained  herein  are
 * proprietary  to  Taylor & Hart Limited  and  its suppliers and may be
 * covered  by  U.K.  and  foreign  patents, patents in process, and are
 * protected in full by copyright law. Dissemination of this information
 * or  reproduction  of this material is strictly forbidden unless prior
 * written permission is obtained from Taylor & Hart Limited.
 *
 * ANY  REPRODUCTION, MODIFICATION, DISTRIBUTION, PUBLIC PERFORMANCE, OR
 * PUBLIC  DISPLAY  OF  OR  THROUGH  USE OF THIS SOURCE CODE WITHOUT THE
 * EXPRESS  WRITTEN CONSENT OF RARE PINK LIMITED IS STRICTLY PROHIBITED,
 * AND  IN  VIOLATION  OF  APPLICABLE LAWS. THE RECEIPT OR POSSESSION OF
 * THIS  SOURCE CODE AND/OR RELATED INFORMATION DOES NOT CONVEY OR IMPLY
 * ANY  RIGHTS  TO REPRODUCE, DISCLOSE OR DISTRIBUTE ITS CONTENTS, OR TO
 * MANUFACTURE,  USE, OR SELL ANYTHING THAT IT MAY DESCRIBE, IN WHOLE OR
 * IN PART.
 */

namespace Warxcell\ExcimerHandler;

use JsonException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

use function is_array;
use function is_string;
use function json_encode;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

final readonly class SpeedscopeDataSender
{
    public function __construct(
        private string $url,
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws JsonException
     */
    public function __invoke(string $name, array $data): void
    {
        $this->httpClient->sendRequest(
            $this->requestFactory->createRequest('POST', $this->url)
                ->withBody(
                    $this->streamFactory->createStream(
                        json_encode([
                            'name' => $name,
                            'data' => $this->utf8ize($data),
                        ], flags: JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
                    )
                )
                ->withHeader('Content-Type', 'application/json')
        );
    }

    private function utf8ize(mixed $mixed): mixed
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = $this->utf8ize($value);
            }
        } elseif (is_string($mixed)) {
            return mb_convert_encoding($mixed, 'UTF-8', 'UTF-8');
        }

        return $mixed;
    }
}
