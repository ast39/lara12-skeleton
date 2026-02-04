<?php

namespace Tests\Unit\Api;

use App\Dto\Api\Test\TestCreateDto;
use App\Dto\Api\Test\TestUpdateDto;
use App\Exceptions\Api\TestException;
use App\Models\Api\TestModel;
use App\Repositories\Api\TestRepository;
use App\Services\Api\TestService;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class TestServiceTest extends TestCase
{
    public function test_store_throws_exception_on_double_test(): void
    {
        /** @var TestRepository&MockObject $repo */
        $repo = $this->createMock(TestRepository::class);
        $service = new TestService($repo);

        $dto = new TestCreateDto([
            'title' => 'Тест 1',
            'description' => null,
        ]);

        $repo->expects($this->once())
            ->method('doubleTestCheck')
            ->with($this->callback(fn ($arg) => $arg instanceof TestCreateDto && $arg->title === 'Тест 1'))
            ->willReturn(true);

        $repo->expects($this->never())->method('create');

        $this->expectException(TestException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('Тест с таким названием уже существует');

        $service->store($dto);
    }

    public function test_update_fetches_entity_updates_and_returns_fresh_model(): void
    {
        /** @var TestRepository&MockObject $repo */
        $repo = $this->createMock(TestRepository::class);
        $service = new TestService($repo);

        $existing = new TestModel();
        $existing->id = 10;

        $fresh = new TestModel();
        $fresh->id = 10;
        $fresh->title = 'Updated';

        $dto = new TestUpdateDto([
            'id' => 10,
            'title' => 'Updated',
            'description' => 'Desc',
        ]);

        $repo->expects($this->exactly(2))
            ->method('getById')
            ->with(10)
            ->willReturnOnConsecutiveCalls($existing, $fresh);

        $repo->expects($this->once())
            ->method('update')
            ->with(
                $this->identicalTo($existing),
                $this->callback(
                    fn ($arg) => $arg instanceof TestUpdateDto
                        && $arg->id === 10
                        && $arg->title === 'Updated'
                )
            )
            ->willReturn(true);

        $result = $service->update($dto);

        $this->assertSame(10, $result->id);
        $this->assertSame('Updated', $result->title);
    }
}
