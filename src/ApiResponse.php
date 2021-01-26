<?php

namespace Hogus\Response;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response as FoundationResponse;

/**
 * Trait ApiResponse
 * @package Hogus\Response
 */
trait ApiResponse
{
    /**
     * @var int
     */
    protected $statusCode = FoundationResponse::HTTP_OK;

    /**
     * @var array
     */
    protected $content = [];

    /**
     * @var int
     */
    protected $code = 0;

    /**
     * @var string
     */
    protected $message = 'success';

    protected static $warp = 'data';
    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @param $message
     * @return $this
     */
    protected function message($message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param null $data
     * @return $this
     */
    protected function content($data = null): self
    {
        $this->content = [
            'code'       => $this->getCode(),
            'message'    => $this->getMessage(),
            'timestamps' => time()
        ];

        if (!is_null($data)) {
            $content = $data;

            if ($data instanceof ResourceCollection) {
                $content = $this->wrap([$data::$wrap ?? self::$warp => $data->resolve()], $data->additional);

                if ($data->resource instanceof AbstractPaginator) {

                    $paginated = $data->resource->toArray();

                    $meta = Arr::only($paginated, [
                        'current_page',
                        'total',
                        'per_page'
                    ]);

                    $content = $this->wrap($content, ['meta' => $meta]);
                }
            }
            elseif ($data instanceof AbstractPaginator) {
                $meta = Arr::only($data->toArray(), [
                    'current_page',
                    'total',
                    'per_page'
                ]);
                $content = $this->wrap([self::$warp => $data->items()], ['meta' => $meta]);
            }
            elseif ($data instanceof JsonResource) {
                $content = $this->wrap($data->resolve(), $data->additional);
            }

            $this->setContentData($content);
        }
        return $this;
    }

    /**
     * wrap
     *
     * @param $data
     * @param array $with
     * @param array $additional
     * @return array
     */
    protected function wrap($data, $with = [], $additional = []): array
    {
        if ($data instanceof Collection) {
            $data = $data->all();
        }

        return array_merge_recursive($data, $with, $additional);
    }

    /**
     * @param $data
     * @return $this
     */
    protected function setContentData($data): self
    {
        Arr::set($this->content, 'data', $data);

        return $this;
    }

    /**
     * @param $code
     * @return $this
     */
    private function code($code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    protected function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    private function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return array
     */
    protected function getContent(): array
    {
        return $this->content;
    }

    /**
     * @param null $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function response($data = null)
    {
        $this->content($data);

        if (func_num_args() === 2) {
            $this->setStatusCode(func_get_arg(1));
        }

        return response()->json($this->getContent(), $this->getStatusCode());
    }

    /**
     * @param $data
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseData($data = null, $message = 'success', $code = 0)
    {
        return $this->message($message)->code($code)->response($data);
    }

    /**
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseSuccess($message = 'success', $code = 0)
    {
        return $this->message($message)->code($code)->response();
    }

    /**
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseFailed($message = 'failed', $code = 1)
    {
        return $this->message($message)->code($code)->response();
    }

    /**
     * @param string $message
     * @param int $code
     * @param array|string|int|null $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseMessage($message = 'success', $code = 0, $data = null)
    {
        return $this->message($message)->code($code)->response($data);
    }
}
