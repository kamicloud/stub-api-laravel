<?php

namespace Kamicloud\StubApi\DTOs;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use JsonSerializable;
use Kamicloud\StubApi\Exceptions\InvalidParameterException;
use Kamicloud\StubApi\Utils\Constants;
use Kamicloud\StubApi\Concerns\ValueHelper;

abstract class DTO implements JsonSerializable, Rule
{
    use ValueHelper;

    /**
     * @param $values
     * @return static|null
     */
    public static function initFromModel($values)
    {
        if (is_string($values)) {
            $values = json_decode($values, true);
        }

        if (!$values) {
            return null;
        }

        $model = new static();

        $model->fromJson($values, $model->getAttributeMap());

        return $model;
    }

    /**
     * @param $values
     * @return static[]
     */
    public static function initFromModels($values)
    {
        if (is_string($values)) {
            $values = json_decode($values, true);
        }

        if ($values === null) {
            return [];
        }

        return array_map(function ($value) {
            return static::initFromModel($value);
        }, $values);
    }

    /**
     * @param Model|array|null $orm
     * @return static|null
     */
    public static function initFromEloquent($orm)
    {
        if ($orm === null) {
            return null;
        }

        $model = new static();

        // array
        $values = $orm;
        if ($orm instanceof Model) {
            $values = $orm->attributesToArray() + $orm->getRelations();
        }

        $attributeMap = $model->getAttributeMap();

        foreach ($attributeMap as $attribute) {
            [$field, $dbField, $rule, $type] = $attribute;

            $isModel = $type & Constants::MODEL;
            $isArray = $type & Constants::ARRAY;
            $isDate = $type & Constants::DATE;

            $value = $values[$dbField] ?? $values[Str::snake($dbField)] ?? null;

            if ($isModel) {
                /** @var DTO $rule */
                if ($isArray) {
                    $model->$field = $rule::initFromEloquents($value);
                } else {
                    $model->$field = $rule::initFromEloquent($value);
                }
            } else {
                $model->$field = $value;
            }
        }

        return $model;
    }

    /**
     * @param Collection|array|null $orms
     * @return static[]
     */
    public static function initFromEloquents($orms)
    {
        if ($orms === null) {
            return [];
        } elseif ($orms instanceof Collection) {
            $orms = $orms->all();
        }


        return array_map(function ($orm) {
            return static::initFromEloquent($orm);
        }, $orms);
    }

    /**
     * @return array
     */
    abstract public function getAttributeMap();

    public function forceDTOScalarTypes()
    {
        $this->forceScalarTypes($this->getAttributeMap());
    }

    public function jsonSerialize()
    {
        return $this->mutableAttributes($this->getAttributeMap());
    }

    public function mock()
    {

    }

    /**
     * @return array
     */
    public function toDBArray()
    {
        return array_reduce($this->getAttributeMap(), function ($c, $attribute) {
            [$field, $dbField] = $attribute;

            if (is_object($this->{$field}) && $this->{$field} instanceof self) {
                $c[$dbField] = $this->{$field}->toDBArray();
            } elseif (is_array($this->{$field})) {
                $c[$dbField] = $this->arrayToDBArray($this->{$field});
            } else {
                $c[$dbField] = $this->{$field};
            }

            return $c;
        }, []);
    }

    protected function arrayToDBArray($items)
    {
        $res = [];
        foreach ($items as $item) {
            if (is_object($item) && $item instanceof self) {
                $one = $item->toDBArray();
            } else {
                $one = $item;
            }
            $res[] = $one;
        }

        return $res;
    }

    /**
     * Determine if the validation rule passes.
     *
     * TODO: Change parameters validation to native laravel code.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $attributeMap = $this->getAttributeMap();
        $validator = Validator::make($value, array_combine(array_column($attributeMap, 0), array_column($attributeMap, 2)));

        if ($validator->fails()) {
            /** @var \Illuminate\Validation\Validator $validator */
            $message = $validator->messages()->first();
            $exception = config('generator.exceptions.invalid-parameter', InvalidParameterException::class);
            throw new $exception("location: $message.");
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return '参数不匹配';
    }
}
