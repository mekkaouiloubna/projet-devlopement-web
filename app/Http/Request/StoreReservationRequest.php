<?php

namespace App\Http\Requests;
use Illuminate\Support\Facades\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && 
        $user = Auth::user();
        $isGuest = $user->isGuest();
    }

    public function rules(): array
    {
        return [
            'resource_id' => 'required|exists:resources,id',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
            'justification' => 'required|string|min:20|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'resource_id.required' => 'Veuillez sélectionner une ressource.',
            'resource_id.exists' => 'La ressource sélectionnée n\'existe pas.',
            'start_date.required' => 'La date de début est obligatoire.',
            'start_date.after' => 'La date de début doit être dans le futur.',
            'end_date.required' => 'La date de fin est obligatoire.',
            'end_date.after' => 'La date de fin doit être après la date de début.',
            'justification.required' => 'La justification est obligatoire.',
            'justification.min' => 'La justification doit contenir au moins 20 caractères.',
            'justification.max' => 'La justification ne peut pas dépasser 1000 caractères.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $resource = \App\Models\Resource::find($this->resource_id);
            
            if ($resource && !$resource->isAvailableForPeriod($this->start_date, $this->end_date)) {
                $validator->errors()->add('resource_id', 'Cette ressource n\'est pas disponible pour la période sélectionnée.');
            }
        });
    }
}