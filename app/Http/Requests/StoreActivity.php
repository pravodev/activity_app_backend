<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\SpeedrunRule;
use Illuminate\Validation\Rule;

class StoreActivity extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'type' => 'required|in:value,count,speedrun,alarm,badhabit',
            // 'title' => 'required|string|unique:activities',
            'title' => [
                'required',
                'string',
                Rule::unique('activities')->whereNull('deleted_at'),
            ],
            'description' => 'nullable|string',
            // 'value' => [
            //     'required_if:type,value,speedrun',
            //     new SpeedrunRule(request()->type)
            // ],
            'target' => 'required_unless:type,alarm|numeric|min:1',
            'can_change' => 'required_if:type,value|boolean',
            // 'use_textfield' => 'required|boolean',
            'color' => 'required|string',
            'increase_value' => 'nullable|required_unless:type,count,speedrun|numeric|min:1',
            'is_hide' => 'required|boolean',
            'criteria' => 'required_if:type,speedrun|in:longer,shorter',
        ];

        if(in_array(request()->type, ['value', 'speedrun', 'badhabit'])) {
            $rules['value'] = ['required'];

            if(request()->type == 'speedrun') {
                array_push($rules['value'], new SpeedrunRule(request()->type));
            } else {
                array_push($rules['value'], 'min:1');
                array_push($rules['value'], 'numeric');
            }
        }

        return $rules;
    }
}
