<?php

namespace App\Http\Requests\Subscriptions;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'subscription_plan_id' => ['required', 'integer', 'exists:subscription_plans,id'],
            'stripe_checkout_session_id' => ['nullable', 'string', 'max:255'],
            'stripe_subscription_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}