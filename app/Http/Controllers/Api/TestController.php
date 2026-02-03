<?php

namespace App\Http\Controllers\Api;

use App\Dto\Api\Test\TestCreateDto;
use App\Dto\Api\Test\TestQueryDto;
use App\Dto\Api\Test\TestUpdateDto;
use App\Http\Requests\Api\Test\TestCreateRequest;
use App\Http\Requests\Api\Test\TestQueryRequest;
use App\Http\Requests\Api\Test\TestUpdateRequest;
use App\Http\Resources\Api\ErrorResource;
use App\Http\Resources\Api\MessageResource;
use App\Http\Resources\Api\TestResource;
use App\Services\Api\TestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Test',
    description: 'Операции с тестами'
)]
class TestController extends ApiController
{
    protected TestService $testService;

    public function __construct(TestService $testService)
    {
        $this->testService = $testService;
    }

    /**
     * Получение списка всех тестов
     */
    #[OA\Get(
        path: '/v1/test',
        operationId: 'testList',
        tags: ['Test'],
        summary: 'Получение всех тестов',
        description: 'Возвращает список всех тестов с пагинацией',
        security: [['apiAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Номер страницы',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                required: false,
                description: 'Количество записей на странице',
                schema: new OA\Schema(type: 'integer', example: 15)
            ),
            new OA\Parameter(
                name: 'order',
                in: 'query',
                required: false,
                description: 'Поле для сортировки',
                schema: new OA\Schema(type: 'string', example: 'created_at')
            ),
            new OA\Parameter(
                name: 'reverse',
                in: 'query',
                required: false,
                description: 'Направление сортировки',
                schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], example: 'desc')
            ),
            new OA\Parameter(
                name: 'query',
                in: 'query',
                required: false,
                description: 'Поисковый запрос',
                schema: new OA\Schema(type: 'string', example: 'тест')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список тестов успешно получен',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: TestResource::class)
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Внутренняя ошибка сервера',
                content: new OA\JsonContent(ref: ErrorResource::class)
            ),
        ]
    )]
    public function index(TestQueryRequest $request): JsonResponse
    {
        return $this->execute(function () use ($request) {
            $data = $request->validated();
            $list = $this->testService->testList(new TestQueryDto($data));

            return TestResource::collection($list)
                ->response()
                ->setStatusCode(200);
        });
    }

    /**
     * Получение теста по ID
     */
    #[OA\Get(
        path: '/v1/test/{id}',
        operationId: 'testGetById',
        tags: ['Test'],
        summary: 'Получение теста по ID',
        description: 'Получает тест по указанному ID',
        security: [['apiAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID теста',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Тест успешно получен',
                content: new OA\JsonContent(ref: TestResource::class)
            ),
            new OA\Response(
                response: 500,
                description: 'Внутренняя ошибка сервера',
                content: new OA\JsonContent(ref: ErrorResource::class)
            ),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        return $this->execute(function () use ($id) {
            $test = $this->testService->testById($id);

            return TestResource::make($test)
                ->response()
                ->setStatusCode(200);
        });
    }

    /**
     * Создание нового теста
     */
    #[OA\Post(
        path: '/v1/test',
        operationId: 'testStore',
        tags: ['Test'],
        summary: 'Создание нового теста',
        description: 'Создает новый тест',
        security: [['apiAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title'],
                properties: [
                    new OA\Property(
                        property: 'title',
                        type: 'string',
                        title: 'Название',
                        description: 'Название теста',
                        example: 'Тест 1'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        title: 'Описание',
                        description: 'Описание теста',
                        example: 'Описание теста'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Тест успешно создан',
                content: new OA\JsonContent(ref: TestResource::class)
            ),
            new OA\Response(
                response: 500,
                description: 'Внутренняя ошибка сервера',
                content: new OA\JsonContent(ref: ErrorResource::class)
            ),
        ]
    )]
    public function store(TestCreateRequest $request): JsonResponse
    {
        return $this->execute(function () use ($request) {
            $data = $request->validated();
            $test = $this->testService->store(new TestCreateDto($data));

            return TestResource::make($test)
                ->response()
                ->setStatusCode(201);
        }, true);
    }

    /**
     * Обновление теста
     */
    #[OA\Put(
        path: '/v1/test/{id}',
        operationId: 'testUpdate',
        tags: ['Test'],
        summary: 'Обновление теста',
        description: 'Обновляет тест по указанному ID',
        security: [['apiAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID теста',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title'],
                properties: [
                    new OA\Property(
                        property: 'title',
                        type: 'string',
                        title: 'Название',
                        description: 'Название теста',
                        example: 'Тест 1'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        title: 'Описание',
                        description: 'Описание теста',
                        example: 'Описание теста'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Тест успешно обновлен',
                content: new OA\JsonContent(ref: MessageResource::class)
            ),
            new OA\Response(
                response: 500,
                description: 'Внутренняя ошибка сервера',
                content: new OA\JsonContent(ref: ErrorResource::class)
            ),
        ]
    )]
    public function update(TestUpdateRequest $request, int $id): JsonResponse
    {
        return $this->execute(function () use ($request, $id) {
            $data = $request->validated();
            $data['id'] = $id;

            $this->testService->update(new TestUpdateDto($data));

            return MessageResource::make([
                'status' => true,
                'code' => 200,
                'msg' => 'Тест обновлен',
            ])
                ->response()
                ->setStatusCode(200);
        }, true);
    }

    /**
     * Удаление теста
     */
    #[OA\Delete(
        path: '/v1/test/{id}',
        operationId: 'testDestroy',
        tags: ['Test'],
        summary: 'Удаление теста',
        description: 'Удаляет тест по указанному ID',
        security: [['apiAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID теста',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Тест успешно удален (no content)'
            ),
            new OA\Response(
                response: 500,
                description: 'Внутренняя ошибка сервера',
                content: new OA\JsonContent(ref: ErrorResource::class)
            ),
        ]
    )]
    public function destroy(int $id): JsonResponse|Response
    {
        return $this->execute(function () use ($id) {
            $this->testService->destroy($id);

            // 204 без тела; явно без Content-Type, чтобы не ломать клиентов (Postman и др.)
            return response('', Response::HTTP_NO_CONTENT);
        }, true);
    }
}
