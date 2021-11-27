<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\SpeedrunRule;
use App\Models\Activity;

class UpdateHistory extends FormRequest
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
            'activity_id' => 'integer|exists:activities,id',
            'date' => 'date_format:Y-m-d',
            'time' => 'date_format:H:i:s',
            // 'value' => 'numeric',
            // 'value_textfield' => 'string',
            'value' => [
                'nullable',
                'bail',
                new SpeedrunRule($activity->type ?? null)
            ],
        ];
    }
}
