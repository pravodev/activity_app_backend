<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\SpeedrunRule;

class UpdateActivity extends FormRequest
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
        $id = request()->segment(3);
        $rules = [
            'type' => 'required|in:value,count,speedrun,alarm,badhabit',
            'description' => 'nullable|string',
            // 'title' => 'required|string|unique:activities,title,'.$id,
            'title' => [
                'required',
                'string',
                "unique:activities,title,{$id},id,deleted_at,NULL"
            ],
            // 'value' => [
            //     'required_if:type,value,speedrun',
            //     new SpeedrunRule(request()->type)
            // ],
            'target' => 'required_unless:type,alarm|numeric',
            'can_change' => 'boolean',
            // 'use_textfield' => 'boolean',
            'color' => 'string',
            'increase_value' => 'nullable|required_unless:count,speedrun|numeric|min:1',
            'is_hide' => 'required|boolean',
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
