<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (auth()->check()) {
            $role = auth()->user()->role;
            return $role === 'management' || $role === 'ypq';
        }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'package' => 'required|in:awal_shift,saat_bekerja,setelah_istirahat,akhir_shift,leader',
            'question_text' => 'required|string',
            'choices' => 'required|array|min:2',
            // Validasi boolean, karena nanti kita pake checkbox/switch
            'extra_fields' => 'nullable|boolean',
        ];
    }
}
