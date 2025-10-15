<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripeController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a checkout session for subscription
     */
    public function createCheckoutSession(Request $request)
    {
        $tenant = $request->user()->tenant;
        $plan = $request->get('plan', 'starter');
        $interval = $request->get('interval', 'monthly');
        
        $planConfig = config("plans.plans.{$plan}");
        
        if (!$planConfig) {
            return response()->json(['error' => 'Invalid plan'], 400);
        }

        $priceId = $interval === 'yearly' 
            ? $planConfig['stripe_price_yearly']
            : $planConfig['stripe_price_monthly'];

        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $priceId,
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => route('billing') . '?success=true',
                'cancel_url' => route('billing') . '?canceled=true',
                'client_reference_id' => $tenant->id,
                'customer_email' => $tenant->email,
                'metadata' => [
                    'tenant_id' => $tenant->id,
                    'plan' => $plan,
                ],
            ]);

            return response()->json([
                'sessionId' => $session->id,
                'url' => $session->url,
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create portal session for managing subscription
     */
    public function createPortalSession(Request $request)
    {
        $tenant = $request->user()->tenant;

        if (!$tenant->stripe_id) {
            return response()->json(['error' => 'No subscription found'], 400);
        }

        try {
            $session = $tenant->billingPortalUrl(route('billing'));

            return response()->json([
                'url' => $session,
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Webhook handler for Stripe events
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle different event types
        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutCompleted($event->data->object);
                // Vérifier si c'est un add-on
                if (isset($event->data->object->metadata->type) && $event->data->object->metadata->type === 'addon') {
                    $this->handleAddonSubscription($event->data->object);
                }
                break;
                
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event->data->object);
                break;
                
            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;
                
            case 'invoice.payment_succeeded':
                $this->handlePaymentSucceeded($event->data->object);
                break;
                
            case 'invoice.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle checkout completed
     */
    protected function handleCheckoutCompleted($session)
    {
        $tenantId = $session->metadata->tenant_id ?? null;
        $plan = $session->metadata->plan ?? 'starter';

        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
            
            if ($tenant) {
                $tenant->update([
                    'stripe_id' => $session->customer,
                    'plan' => $plan,
                ]);
            }
        }
    }

    /**
     * Handle subscription updated
     */
    protected function handleSubscriptionUpdated($subscription)
    {
        $customer = $subscription->customer;
        $tenant = Tenant::where('stripe_id', $customer)->first();

        if ($tenant) {
            // Update plan based on subscription items
            $priceId = $subscription->items->data[0]->price->id;
            $plan = $this->getPlanFromPriceId($priceId);
            
            if ($plan) {
                $tenant->update(['plan' => $plan]);
            }
        }
    }

    /**
     * Handle subscription deleted
     */
    protected function handleSubscriptionDeleted($subscription)
    {
        $customer = $subscription->customer;
        $tenant = Tenant::where('stripe_id', $customer)->first();

        if ($tenant) {
            $tenant->update([
                'plan' => 'starter',
                'active' => false,
            ]);
        }
    }

    /**
     * Handle payment succeeded
     */
    protected function handlePaymentSucceeded($invoice)
    {
        // Log successful payment
        \Log::info("Payment succeeded for invoice: {$invoice->id}");
    }

    /**
     * Handle payment failed
     */
    protected function handlePaymentFailed($invoice)
    {
        $customer = $invoice->customer;
        $tenant = Tenant::where('stripe_id', $customer)->first();

        if ($tenant) {
            // Send notification or take action
            \Log::error("Payment failed for tenant: {$tenant->id}");
        }
    }

    /**
     * Get plan from Stripe price ID
     */
    protected function getPlanFromPriceId(string $priceId): ?string
    {
        $plans = config('plans.plans');
        
        foreach ($plans as $key => $plan) {
            if ($plan['stripe_price_monthly'] === $priceId || $plan['stripe_price_yearly'] === $priceId) {
                return $key;
            }
        }
        
        return null;
    }

    /**
     * Créer checkout session pour add-on
     */
    public function createAddonCheckout(Request $request)
    {
        $tenant = $request->user()->tenant;
        $addonKey = $request->get('addon');
        
        $addons = config('plans.addons');
        $addon = $addons[$addonKey] ?? null;
        
        if (!$addon) {
            return response()->json(['error' => 'Add-on invalide'], 400);
        }
        
        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $addon['stripe_price'],
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => route('billing') . '?addon_success=true&addon=' . $addonKey,
                'cancel_url' => route('billing') . '?canceled=true',
                'client_reference_id' => $tenant->id,
                'metadata' => [
                    'tenant_id' => $tenant->id,
                    'addon_key' => $addonKey,
                    'type' => 'addon',
                ],
            ]);

            return response()->json([
                'sessionId' => $session->id,
                'url' => $session->url,
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Gérer succès add-on (webhook)
     */
    protected function handleAddonSubscription($subscription)
    {
        $metadata = $subscription->metadata;
        $tenantId = $metadata->tenant_id ?? null;
        $addonKey = $metadata->addon_key ?? null;
        
        if ($tenantId && $addonKey && isset($metadata->type) && $metadata->type === 'addon') {
            $tenant = Tenant::find($tenantId);
            
            if ($tenant) {
                $tenant->activateAddon($addonKey, [
                    'stripe_subscription_id' => $subscription->id,
                ]);
            }
        }
    }
    
    /**
     * Get subscription details
     */
    public function getSubscription(Request $request)
    {
        $tenant = $request->user()->tenant;

        if (!$tenant->subscribed('default')) {
            return response()->json([
                'subscribed' => false,
                'plan' => $tenant->plan,
            ]);
        }

        $subscription = $tenant->subscription('default');

        return response()->json([
            'subscribed' => true,
            'plan' => $tenant->plan,
            'stripe_status' => $subscription->stripe_status,
            'trial_ends_at' => $subscription->trial_ends_at,
            'ends_at' => $subscription->ends_at,
            'on_grace_period' => $subscription->onGracePeriod(),
        ]);
    }
}

