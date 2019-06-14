<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Hiero7\Models\Domain;
use Hiero7\Models\CdnProvider;

class DomainWithCdnProviderGroupMapping implements Rule
{

    protected $domain, $cdnProvider;

    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $cdnProvider = CdnProvider::find($value);
        return (bool) ($this->domain->user_group_id == $cdnProvider->user_group_id);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The Domain And Cdn Provider User_group_id Not Mapping.';
    }
}
