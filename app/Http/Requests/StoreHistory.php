<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Rules\SpeedrunRule;
use App\Models\Activity;

class StoreHistory extends FormRequest
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
        $activity = Activity::find(request()->activity_id);

        return [
            'activity_id' => 'required|integer|exists:activities,id',
            'date' => 'nullable|date_format:Y-m-d',
            'time' => 'nullable|date_format:H:i:s',
            // 'value' => 'required_without:value_textfield|numeric',
            // 'value_textfield' => 'string',
            'value' => [
                'nullable',
                'bail',
                new SpeedrunRule($activity->type ?? null)
            ],
        ];
    }
}
