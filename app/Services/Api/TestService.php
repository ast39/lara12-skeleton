<?php

namespace App\Services\Api;

use App\Dto\Api\Test\TestCreateDto;
use App\Dto\Api\Test\TestQueryDto;
use App\Dto\Api\Test\TestUpdateDto;
use App\Exceptions\Api\TestException;
use App\Models\Api\TestModel;
use App\Repositories\Api\TestRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TestService
{
    protected TestRepository $testRepository;

    public function __construct(TestRepository $testRepository)
    {
        $this->testRepository = $testRepository;
    }

    /**
     * Получить список тестов
     */
    public function testList(TestQueryDto $dto): Collection|LengthAwarePaginator
    {
        return $this->testRepository->getList($dto);
    }

    /**
     * Получить тест по ID
     *
     * @throws TestException
     */
    public function testById(int $id): TestModel
    {
        $test = $this->testRepository->getById($id);

        if (! $test) {
            throw TestException::notFound();
        }

        return $test;
    }

    /**
     * Создать тест
     *
     * @throws TestException
     */
    public function store(TestCreateDto $dto): TestModel
    {
        if ($this->testRepository->doubleTestCheck($dto)) {
            throw TestException::doubleTest();
        }

        return $this->testRepository->create($dto);
    }

    /**
     * Обновить тест
     *
     * @throws TestException
     */
    public function update(TestUpdateDto $dto): TestModel
    {
        $test = $this->testById($dto->id);

        $this->testRepository->update($test, $dto);

        return $this->testRepository->getById($dto->id);
    }

    /**
     * Удалить тест
     *
     * @throws TestException
     */
    public function destroy(int $id): void
    {
        $test = $this->testById($id);

        $this->testRepository->delete($test);
    }
}
