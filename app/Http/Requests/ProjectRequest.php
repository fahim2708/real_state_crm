<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
//            'name' => 'required|string|max:255',
//            'road_no' => 'string',
//            'project_no' => 'string',
//            'face_direction' => 'string',
//            'location' => 'string',
//            'total_number_of_floor' => 'numeric'
        ];
    }
}
