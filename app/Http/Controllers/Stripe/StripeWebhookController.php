<?php

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Services\Stripe\StripeWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request, StripeWebhookService $service): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_secret')
            );
        } catch (SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        } catch (\Exception $e) {
            return response('Webhook error', 400);
        }

        $data = $event->toArray();

        match ($event->type) {
            'checkout.session.completed' => $service->handleCheckoutCompleted($data),
            'invoice.payment_failed' => $service->handlePaymentFailed($data),
            default => null,
        };

        return response('OK', 200);
    }
}