<?php

namespace mindtwo\Appointable\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // check if the appointment is an entire day
            'is_entire_day' => ['sometimes', 'boolean'],
            // start date start and end are not set
            'start_date' => ['required', 'date'],
            // end date start and end are not set
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],

            // start time and end time if is_entire_day is false
            'start_time' => ['exclude_if:is_entire_day,true', 'nullable', 'date_format:H:i:s'],
            'end_time' => ['exclude_if:is_entire_day,true', 'nullable', 'date_format:H:i:s', 'after:start_time'],

            'invitee' => ['sometimes', 'nullable', 'integer'],
            // other fields
            'title' => ['required', 'string'],
            'description' => ['sometimes', 'nullable', 'string'],
            'location' => ['sometimes', 'nullable', 'string'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // if we have start and end we want to split them into date and time
        if ($this->has('start')) {
            try {
                $start = \Carbon\Carbon::parse($this->input('start'));
            } catch (\Throwable $th) {
                throw new HttpResponseException(response()->json(['error' => 'Invalid start date'], 422));
            }

            $this->merge([
                'start_date' => $start->format('Y-m-d'),
                'start_time' => $start->format('H:i:s'),
            ]);
        }

        // if we have an end date we want to split it into date and time
        if ($this->has('end')) {
            try {
                $end = \Carbon\Carbon::parse($this->input('end'));
            } catch (\Throwable $th) {
                throw new HttpResponseException(response()->json(['error' => 'Invalid end date'], 422));
            }

            $this->merge([
                'end_date' => $end->format('Y-m-d'),
                'end_time' => $end->format('H:i:s'),
            ]);
        }

        // if we have start but not end we want to set end to start
        if (! $this->has('end') && ! $this->has('end_date')) {
            $this->merge([
                'end_date' => $this->input('start_date'),
            ]);
        }

        // set the format of the start time and end time to H:i:s
        if ($this->has('start_time') && $startTime = $this->input('start_time')) {
            $this->merge([
                'start_time' => \Carbon\Carbon::parse($startTime)->format('H:i:s'),
            ]);
        }

        if ($this->has('end_time') && $endTime = $this->input('end_time')) {
            $this->merge([
                'end_time' => \Carbon\Carbon::parse($endTime)->format('H:i:s'),
            ]);
        }
    }
}
