<?php

namespace Alighorbani\Responder\Foundation;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Jenssegers\Mongodb\Eloquent\Model as Mongodb;
use Illuminate\Http\Resources\Json\JsonResource;
use Alighorbani\Responder\Contract\SingleJsonResource;
use Illuminate\Support\Collection as SupportCollection;
use Alighorbani\Responder\Contract\ResponderInterface;

abstract class BaseResponder implements ResponderInterface
{
    protected static array $responseMessage;

    /**
     * @var string
     */
    protected static string $defaultResponseMaker;

    /**
     * @var array
     */
    protected static array $responseMakers = [];

    public function __construct()
    {
        static::$responseMessage = config('message-alias', []);
    }

    public function resourceResponse($data, $title = null, $targetCollection = null, $responseMaker = null)
    {
        $this->respondWhenCollectionEmpty($data);

        $title = $this->getDataNormalizedByMappingKeyword($title);

        [$data, $pagination] = $this->getDataNormalizedByCollection($data, $targetCollection);

        return $this->prepareResponseByData($title, $data, $pagination, $responseMaker);
    }

    protected function respondWhenCollectionEmpty($data)
    {
        $isEmptyEloquentObject = ($data instanceof Collection && $data->count() == 0);

        $isEmptyVariable = is_null($data) || empty($data);

        // when resource is really empty send 204 status code
        if ($isEmptyVariable || $isEmptyEloquentObject) {
            abort(Response::HTTP_NO_CONTENT);
        }
    }

    protected function getDataNormalizedByMappingKeyword($title)
    {
        if (is_null($title)) {
            return 'resource found.';
        }

        if (array_key_exists($title, self::$responseMessage)) {
            return self::$responseMessage[$title];
        }

        return $title;
    }

    protected function getDataNormalizedByCollection($data, $collection)
    {
        $pagination = null;

        if (is_null($collection)) {
            return [$data, $pagination];
        }

        if (!class_exists($collection)) {
            throw new InvalidArgumentException('The Resource you called dont exists!');
        }

        // handle use single resource (deprecated soon)
        if (in_array(SingleJsonResource::class, class_implements($collection))) {
            return [$collection::toSingleArray($data), $pagination];
        }

        // handle single resource response
        $isSupportIterable = $data instanceof Mongodb || $data instanceof SupportCollection || $data instanceof Collection;

        $notCountable = (!in_array(\Countable::class, class_implements($data)));

        if ($isSupportIterable && $notCountable) {
            return [resolve($collection, ['resource' => $data]), $pagination];
        }

        if ($data instanceof LengthAwarePaginator || $data instanceof \Illuminate\Pagination\Paginator) {

            $items = $data->items();

            if (empty($items)) {
                abort(Response::HTTP_NO_CONTENT);
            }

            $pagination = [
                'totalObjects' => $data->total(),
                'perPage' => $data->perPage(),
                'currentPage' => $data->currentPage(),
                'lastPage' => $data->lastPage(),
            ];

            $data = $items;
        }

        if (!in_array(JsonResource::class, class_parents($collection))) {
            throw new InvalidArgumentException(sprintf("class (%s) dosn't extend the JsonResource Class Check it ", $collection));
        }

        return [$collection::collection($data), $pagination];
    }

    protected function prepareResponseByData($title, $data, $pagination, callable|string|null $responseMaker)
    {
        $result = [];

        if (is_null($responseMaker))
            $result = ["success" => true, "title" => $title, "result" => $data];

        elseif (is_string($responseMaker))
            $result = $this->callResponseMaker($responseMaker, $data, $title);

        elseif (is_callable($responseMaker))
            $result = call_user_func($responseMaker, $data, $title);

        if (!is_null($pagination)) {
            $result['pagination'] = $pagination;
        }

        return response($result);
    }

    private function callResponseMaker(string $responseMakerKey, $data, $title)
    {
        if (!array_key_exists($responseMakerKey, self::$responseMakers)) {
            throw new \InvalidArgumentException(sprintf('the response maker (%s) that you called dose not exists!', $responseMakerKey));
        }

        return call_user_func(self::$responseMakers[$responseMakerKey], $data, $title);
    }

    public function successfulResponse($success = true, $data = null, $messageTitle = 'nothing ...', $statusCode = Response::HTTP_OK)
    {
        if (array_key_exists($messageTitle, self::$responseMessage)) {
            $messageTitle = self::$responseMessage[$messageTitle];
        }

        $info = [
            "success" => $success,
            "title" => $messageTitle,
        ];

        if (!is_null($data)) {
            $info["result"] = $data ?? new Arr();
        }

        return response($info, $statusCode);
    }

    public function serverError($message = 'internal server error has occurred!', $data = null)
    {
        if (array_key_exists($message, self::$responseMessage)) {
            $message = self::$responseMessage[$message];
        }

        $response = ['success' => false, 'message' => $message];

        if (!is_null($data)) {
            $response['result'] = $data;
        }

        return response($response, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function unauthenticError()
    {
        return response(["message" => "Unauthenticated."], Response::HTTP_UNAUTHORIZED);
    }

    public function validationError($message = 'The given data was invalid!', $errorsBag = null)
    {
        if (array_key_exists($message, self::$responseMessage)) {
            $message = self::$responseMessage[$message];
        }

        return response(['message' => $message, 'errors' => $errorsBag], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function setDefaultResponseMaker(string $defaultMaker)
    {
        if (!is_null(self::$defaultResponseMaker)) {
            throw new \Exception(sprintf('you try to overwrite the default response maker but we can save one default param => (%s)', $this->defaultResponseMaker));
        }

        if (!array_key_exists($defaultMaker, self::$responseMakers)) {
            throw new InvalidArgumentException(sprintf('the default maker that you called => (%s) dose not exists!'));
        }

        self::$defaultResponseMaker = $defaultMaker;
    }

    public function setResponseMaker(string $key, callable $callback)
    {
        if (!array_key_exists($key, self::$responseMakers)) {
            self::$responseMakers[$key] = $callback;
        }
    }
}
