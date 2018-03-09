<?php

namespace Illuminate\Tests\Database;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;

class DatabaseEloquentRelationTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testSetRelationFail()
    {
        $parent = new EloquentRelationResetModelStub;
        $relation = new EloquentRelationResetModelStub;
        $parent->setRelation('test', $relation);
        $parent->setRelation('foo', 'bar');
        $this->assertArrayNotHasKey('foo', $parent->toArray());
    }

    public function testTouchMethodUpdatesRelatedTimestamps()
    {
        $builder = m::mock(Builder::class);
        $parent = m::mock(Model::class);
        $parent->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $builder->shouldReceive('getModel')->andReturn($related = m::mock(\stdClass::class));
        $builder->shouldReceive('whereNotNull');
        $builder->shouldReceive('where');
        $builder->shouldReceive('withoutGlobalScopes')->andReturn($builder);
        $relation = new HasOne($builder, $parent, 'foreign_key', 'id');
        $related->shouldReceive('getTable')->andReturn('table');
        $related->shouldReceive('getUpdatedAtColumn')->andReturn('updated_at');
        $related->shouldReceive('shouldTouch')->andReturn(true);
        $now = \Illuminate\Support\Carbon::now();
        $related->shouldReceive('freshTimestampString')->andReturn($now);
        $builder->shouldReceive('update')->once()->with(['updated_at' => $now]);

        $relation->touch();
    }

    public function testCanDisableParentTouchingForAllModels()
    {
        $related = m::mock(EloquentNoTouchingModelStub::class)->makePartial();
        $related->shouldReceive('getUpdatedAtColumn')->never();
        $related->shouldReceive('freshTimestampString')->never();

        $this->assertTrue($related->shouldTouch());

        Model::withoutTouching(function () use ($related) {
            $this->assertFalse($related->shouldTouch());

            $builder = m::mock(Builder::class);
            $parent = m::mock(Model::class);

            $parent->shouldReceive('getAttribute')->with('id')->andReturn(1);
            $builder->shouldReceive('getModel')->andReturn($related);
            $builder->shouldReceive('whereNotNull');
            $builder->shouldReceive('where');
            $builder->shouldReceive('withoutGlobalScopes')->andReturn($builder);
            $relation = new HasOne($builder, $parent, 'foreign_key', 'id');
            $builder->shouldReceive('update')->never();

            $relation->touch();
        });

        $this->assertTrue($related->shouldTouch());
    }

    public function testCanDisableTouchingForSpecificModel()
    {
        $related = m::mock(EloquentNoTouchingModelStub::class)->makePartial();
        $related->shouldReceive('getUpdatedAtColumn')->never();
        $related->shouldReceive('freshTimestampString')->never();

        $anotherRelated = m::mock(EloquentNoTouchingAnotherModelStub::class)->makePartial();

        $this->assertTrue($related->shouldTouch());
        $this->assertTrue($anotherRelated->shouldTouch());

        EloquentNoTouchingModelStub::withoutTouching(function () use ($related, $anotherRelated) {
            $this->assertFalse($related->shouldTouch());
            $this->assertTrue($anotherRelated->shouldTouch());

            $builder = m::mock(Builder::class);
            $parent = m::mock(Model::class);

            $parent->shouldReceive('getAttribute')->with('id')->andReturn(1);
            $builder->shouldReceive('getModel')->andReturn($related);
            $builder->shouldReceive('whereNotNull');
            $builder->shouldReceive('where');
            $builder->shouldReceive('withoutGlobalScopes')->andReturnSelf();
            $relation = new HasOne($builder, $parent, 'foreign_key', 'id');
            $builder->shouldReceive('update')->never();

            $relation->touch();

            $anotherBuilder = m::mock(Builder::class);
            $anotherParent = m::mock(Model::class);

            $anotherParent->shouldReceive('getAttribute')->with('id')->andReturn(2);
            $anotherBuilder->shouldReceive('getModel')->andReturn($anotherRelated);
            $anotherBuilder->shouldReceive('whereNotNull');
            $anotherBuilder->shouldReceive('where');
            $anotherBuilder->shouldReceive('withoutGlobalScopes')->andReturnSelf();
            $anotherRelation = new HasOne($anotherBuilder, $anotherParent, 'foreign_key', 'id');
            $now = \Illuminate\Support\Carbon::now();
            $anotherRelated->shouldReceive('freshTimestampString')->andReturn($now);
            $anotherBuilder->shouldReceive('update')->once()->with(['updated_at' => $now]);

            $anotherRelation->touch();
        });

        $this->assertTrue($related->shouldTouch());
        $this->assertTrue($anotherRelated->shouldTouch());
    }

    public function testParentModelIsNotTouchedWhenChildModelIsIgnored()
    {
        $related = m::mock(EloquentNoTouchingModelStub::class)->makePartial();
        $related->shouldReceive('getUpdatedAtColumn')->never();
        $related->shouldReceive('freshTimestampString')->never();

        $relatedChild = m::mock(EloquentNoTouchingChildModelStub::class)->makePartial();
        $relatedChild->shouldReceive('getUpdatedAtColumn')->never();
        $relatedChild->shouldReceive('freshTimestampString')->never();

        $this->assertTrue($related->shouldTouch());
        $this->assertTrue($relatedChild->shouldTouch());

        EloquentNoTouchingModelStub::withoutTouching(function () use ($related, $relatedChild) {
            $this->assertFalse($related->shouldTouch());
            $this->assertFalse($relatedChild->shouldTouch());

            $builder = m::mock(Builder::class);
            $parent = m::mock(Model::class);

            $parent->shouldReceive('getAttribute')->with('id')->andReturn(1);
            $builder->shouldReceive('getModel')->andReturn($related);
            $builder->shouldReceive('whereNotNull');
            $builder->shouldReceive('where');
            $builder->shouldReceive('withoutGlobalScopes')->andReturnSelf();
            $relation = new HasOne($builder, $parent, 'foreign_key', 'id');
            $builder->shouldReceive('update')->never();

            $relation->touch();

            $anotherBuilder = m::mock(Builder::class);
            $anotherParent = m::mock(Model::class);

            $anotherParent->shouldReceive('getAttribute')->with('id')->andReturn(2);
            $anotherBuilder->shouldReceive('getModel')->andReturn($relatedChild);
            $anotherBuilder->shouldReceive('whereNotNull');
            $anotherBuilder->shouldReceive('where');
            $anotherBuilder->shouldReceive('withoutGlobalScopes')->andReturnSelf();
            $anotherRelation = new HasOne($anotherBuilder, $anotherParent, 'foreign_key', 'id');
            $anotherBuilder->shouldReceive('update')->never();

            $anotherRelation->touch();
        });

        $this->assertTrue($related->shouldTouch());
        $this->assertTrue($relatedChild->shouldTouch());
    }

    public function testIgnoredModelsStateIsResetWhenThereAreExceptions()
    {
        $related = m::mock(EloquentNoTouchingModelStub::class)->makePartial();
        $related->shouldReceive('getUpdatedAtColumn')->never();
        $related->shouldReceive('freshTimestampString')->never();

        $relatedChild = m::mock(EloquentNoTouchingChildModelStub::class)->makePartial();
        $relatedChild->shouldReceive('getUpdatedAtColumn')->never();
        $relatedChild->shouldReceive('freshTimestampString')->never();

        $this->assertTrue($related->shouldTouch());
        $this->assertTrue($relatedChild->shouldTouch());

        try {
            EloquentNoTouchingModelStub::withoutTouching(function () use ($related, $relatedChild) {
                $this->assertFalse($related->shouldTouch());
                $this->assertFalse($relatedChild->shouldTouch());

                throw new \Exception();
            });

            $this->fail('Exception was not thrown');
        } catch (\Exception $exception) {

        }

        $this->assertTrue($related->shouldTouch());
        $this->assertTrue($relatedChild->shouldTouch());
    }

    public function testSettingMorphMapWithNumericArrayUsesTheTableNames()
    {
        Relation::morphMap([EloquentRelationResetModelStub::class]);

        $this->assertEquals([
            'reset' => 'Illuminate\Tests\Database\EloquentRelationResetModelStub',
        ], Relation::morphMap());

        Relation::morphMap([], false);
    }

    public function testSettingMorphMapWithNumericKeys()
    {
        Relation::morphMap([1 => 'App\User']);

        $this->assertEquals([
            1 => 'App\User',
        ], Relation::morphMap());

        Relation::morphMap([], false);
    }

    public function testMacroable()
    {
        Relation::macro('foo', function () {
            return 'foo';
        });

        $model = new EloquentRelationResetModelStub;
        $relation = new EloquentRelationStub($model->newQuery(), $model);

        $result = $relation->foo();
        $this->assertEquals('foo', $result);
    }
}

class EloquentRelationResetModelStub extends Model
{
    protected $table = 'reset';

    // Override method call which would normally go through __call()

    public function getQuery()
    {
        return $this->newQuery()->getQuery();
    }
}

class EloquentRelationStub extends Relation
{
    public function addConstraints()
    {
    }

    public function addEagerConstraints(array $models)
    {
    }

    public function initRelation(array $models, $relation)
    {
    }

    public function match(array $models, \Illuminate\Database\Eloquent\Collection $results, $relation)
    {
    }

    public function getResults()
    {
    }
}

class EloquentNoTouchingModelStub extends Model
{
    protected $table = 'table';
    protected $attributes = [
        'id' => 1,
    ];
}

class EloquentNoTouchingChildModelStub extends EloquentNoTouchingModelStub
{
}

class EloquentNoTouchingAnotherModelStub extends Model
{
    protected $table = 'another_table';
    protected $attributes = [
        'id' => 2,
    ];
}
