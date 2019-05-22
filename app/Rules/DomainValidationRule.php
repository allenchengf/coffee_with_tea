<?php

namespace App\Rules;

use Hiero7\Enums\InputError;
use Illuminate\Contracts\Validation\Rule;
use Hiero7\Traits\DomainHelperTrait;

class DomainValidationRule implements Rule
{
    use DomainHelperTrait;


    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return (bool)$this->validateDomain($value);

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return InputError::getDescription(InputError::DOMAIN_VERIFICATION_ERROR);

    }
}
