<?php

namespace App\Http\Requests\Schedules;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ReplaceWeeklyTemplatesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'templates' => ['required', 'array', 'min:1'],
            'templates.*.day_of_week' => ['required', 'integer', 'between:1,7'],
            'templates.*.start_time' => ['required', 'date_format:H:i'],
            'templates.*.end_time' => ['required', 'date_format:H:i'],
            'templates.*.activity' => ['nullable', 'string', 'max:255'],
            'templates.*.trainer_name' => ['nullable', 'string', 'max:255'],
            'templates.*.location' => ['nullable', 'string', 'max:255'],
            'templates.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $templates = $this->input('templates', []);

            foreach ($templates as $index => $template) {
                $start = $template['start_time'] ?? null;
                $end = $template['end_time'] ?? null;

                if ($start && $end && $end <= $start) {
                    $validator->errors()->add(
                        "templates.$index.end_time",
                        'The end_time must be later than start_time.'
                    );
                }
            }
        });
    }
}