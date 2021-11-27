<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaGallery extends FormRequest
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
            'type' => 'required|in:image,video,youtube',
            'category_id' => 'nullable|exists:categories,id',
            'file' => 'required_unless:type,youtube|file|nullable',
            'value' => 'required_if:type,youtube',
            'thumbnail' => 'required_if:type,video',
        ];
    }
}
