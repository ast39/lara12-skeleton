<?php

namespace App\Repositories\Api;

use App\Dto\Api\Test\TestCreateDto;
use App\Dto\Api\Test\TestQueryDto;
use App\Dto\Api\Test\TestUpdateDto;
use App\Models\Api\TestModel;
use App\Scopes\Api\TestScope;
use Illuminate\Contracts\Container\BindingResolutionException;

class TestRepository
{
    protected TestModel $testModel;

    public function __construct(TestModel $testModel)
    {
        $this->testModel = $testModel;
    }

    /**
     * Список тестов
     *
     * @throws BindingResolutionException
     */
    public function getList(TestQueryDto $dto): mixed
    {
        $filter = app()->make(TestScope::class, [
            'queryParams' => array_filter($dto->toArray()),
        ]);

        $query = $this->testModel::query()
            ->filter($filter);

        // Применяем сортировку
        $orderField = $dto->order ?? 'created_at';
        $orderDirection = $dto->reverse ?? 'desc';

        $testList = $query->orderBy($orderField, $orderDirection);

        $limit = $dto->limit ?? 20;
        $page = $dto->page ?? 1;

        return $testList->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Получить тест по ID
     */
    public function getById(int $id): ?TestModel
    {
        $test = $this->testModel::query()
            ->where('id', $id)
            ->first();

        return $test instanceof TestModel ? $test : null;
    }

    /**
     * Добавить тест
     */
    public function create(TestCreateDto $dto): TestModel
    {
        return $this->testModel->create($dto->toArray());
    }

    /**
     * Обновить тест
     */
    public function update(TestModel $test, TestUpdateDto $dto): bool
    {
        $updateData = collect($dto)->except(['id']);
        $updateData = array_filter($updateData->toArray(), function ($e) {
            return ! is_null($e);
        });

        return $test->update($updateData);
    }

    /**
     * Удалить тест
     */
    public function delete(TestModel $test): void
    {
        $test->delete();
    }

    /**
     * Проверить тест на дубль
     */
    public function doubleTestCheck(TestCreateDto $dto): bool
    {
        return $this->testModel::query()
            ->where('title', $dto->title)
            ->count() > 0;
    }
}
