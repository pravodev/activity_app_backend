<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMediaGallery extends FormRequest
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
        return [
            'type' => 'required|in:image,video,link',
            'category_id' => 'nullable|in:categories,id',
            'image' => 'nullable|file',
            'video' => 'nullable|file',
            'link' => 'nullable|string',
        ];
    }
}
