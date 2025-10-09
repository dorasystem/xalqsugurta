<?php

namespace App\Services;

use App\Traits\NappApi;
use Illuminate\Support\Facades\Http;

class PersonService
{
    use NappApi;

    /**
     * Get person info by birth date
     *
     * @param array $data
     * @return array
     */
    public function getPersonInfoByBirthDate(array $data): array
    {
        return $this->getPersonByBirthDate(
            $data['passport_seria'],
            $data['passport_number'],
            $data['birth_date']
        );
    }

    /**
     * Get person info by passport
     *
     * @param array $data
     * @return array
     */
    public function getPersonInfoByPassport(array $data): array
    {
        return $this->getPersonByPassport(
            $data['passport_seria'],
            $data['passport_number']
        );
    }

    /**
     * Get person info by PINFL
     *
     * @param string $pinfl
     * @return array
     */
    public function getPersonInfoByPinfl(string $pinfl): array
    {
        return $this->getPersonByPinfl($pinfl);
    }
}
