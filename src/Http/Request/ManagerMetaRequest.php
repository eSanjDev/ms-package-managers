<?php

namespace Esanj\Manager\Http\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManagerMetaRequest extends FormRequest
{
    public function rules(): array
    {
        $manager = $this->route('manager');
        $meta = $manager->getMeta($this->input('key'));

        return [
            'key' => [
                'required',
                'string',
                Rule::unique('manager_metas', 'key')
                    ->where('manager_id', $manager->id)
                    ->ignore($meta?->id)
            ],
            'value' => ['nullable', 'string'],
        ];
    }
}
