<?php

namespace Tests\Unit\Request;

use Tests\TestCase;

class TestRequest extends TestCase
{
    protected $validator, $rules;

    protected function validateField($field, $value)
    {
        return $this->getFieldValidator($field, $value)->passes();
    }

    protected function getFieldValidator($field, $value)
    {
        return $this->validator->make(
            [$field => $value],
            [$field => $this->rules[$field]]
        );
    }
}
