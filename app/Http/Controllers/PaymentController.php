<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Trigger M-Pesa STK Push
     * Called by Node.js Voice Engine or Frontend
     */
    /**
     * Trigger M-Pesa STK Push
     * Called by Node.js Voice Engine or Frontend
     */
    public function stkPush(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'amount' => 'required|numeric',
            'account_reference' => 'nullable|string', 
            'user_id' => 'required|integer'
        ]);

        $user = User::findOrFail($request->user_id);
        
        // Fetch M-Pesa Integration for this user
        $integration = \App\Models\Integration::where('user_id', $user->id)
            ->where('provider', 'mpesa')
            ->first();

        if (!$integration) {
            return response()->json(['status' => 'error', 'message' => 'M-Pesa not configured for this account'], 400);
        }

        $settings = $integration->settings;

        // Initialize Service with User's credentials (Daraja or IntaSend)
        $mpesa = new \App\Services\MpesaService(array_merge($settings, [
            'mpesa_type' => $settings['mpesa_type'] ?? 'daraja'
        ]));

        $response = $mpesa->stkPush(
            $request->phone_number,
            $request->amount,
            $request->account_reference ?? 'VoiceAgent-' . $user->id
        );

        if (isset($response['error'])) {
            return response()->json(['status' => 'error', 'message' => $response['error'], 'details' => $response['details'] ?? null], 500);
        }

        // Log the Pending Payment
        Payment::create([
            'user_id' => $user->id,
            'phone_number' => $request->phone_number,
            'amount' => $request->amount,
            'transaction_reference' => $response['CheckoutRequestID'],
            'status' => 'pending',
            'provider' => 'mpesa',
            'metadata' => ['account_ref' => $request->account_reference]
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'STK Push initiated successfully',
            'MerchantRequestID' => $response['MerchantRequestID'],
            'CheckoutRequestID' => $response['CheckoutRequestID'],
            'ResponseCode' => '0',
            'CustomerMessage' => $response['CustomerMessage']
        ]);
    }

    /**
     * Handle M-Pesa Callback
     * Logs the transaction and updates appointment status
     */
    public function callback(Request $request)
    {
        Log::info("M-Pesa Callback Received:", $request->all());

        $data = $request->input('Body.stkCallback');
        if (!$data) {
            return response()->json(['status' => 'error', 'message' => 'Invalid Callback Data'], 400);
        }

        $checkoutRequestID = $data['CheckoutRequestID'];
        $resultCode = $data['ResultCode']; // 0 = Success, others = Fail
        $resultDesc = $data['ResultDesc'];

        // Find the payment
        $payment = Payment::where('transaction_reference', $checkoutRequestID)->first();

        if ($payment) {
            if ($resultCode == 0) {
                // 1. Success - Mark Payment as Completed
                $payment->status = 'completed';
                $meta = $data['CallbackMetadata']['Item'];
                $mpesaReceipt = collect($meta)->firstWhere('Name', 'MpesaReceiptNumber')['Value'] ?? 'N/A';
                $payment->transaction_reference = $mpesaReceipt;
                $payment->metadata = array_merge($payment->metadata ?? [], ['callback' => $data]);
                $payment->save();

                // 2. AUTOMATED PAYOUT: Only for Aggregator users
                $integration = \App\Models\Integration::where('user_id', $payment->user_id)
                    ->where('provider', 'mpesa')
                    ->first();

                if (!$integration) {
                    Log::warning("Payout skipped for Payment {$payment->id}: Integration settings missing.");
                    return response()->json(['status' => 'success', 'message' => 'Payment logged, but payout skipped (settings missing)']);
                }

                $setupMode = $integration->settings['mpesa_setup_mode'] ?? 'aggregator';

                if ($setupMode === 'aggregator' && isset($integration->settings['payout_number'])) {
                    $payoutNumber = $integration->settings['payout_number'];
                    Log::info("Initiating Automated Payout for Payment {$payment->id} to {$payoutNumber}");

                    $mpesa = new \App\Services\MpesaService(['mpesa_setup_mode' => 'aggregator']);
                    $transfer = $mpesa->transfer($payoutNumber, $payment->amount, "Settlement for Call ID: {$payment->transaction_reference}");

                    if (isset($transfer['error'])) {
                        Log::error("Payout Failed for Payment {$payment->id}: " . json_encode($transfer));
                    } else {
                        Log::info("Payout Successful for Payment {$payment->id}");
                        // Update payment metadata with transfer ref
                        $payment->metadata = array_merge($payment->metadata ?? [], ['payout' => $transfer]);
                        $payment->save();
                    }
                } else {
                    Log::info("Payout skipped for Payment {$payment->id}: Mode is {$setupMode} or payout number missing.");
                }

            } else {
                // Failed (Cancelled, Insufficient Funds)
                $payment->status = 'failed';
                $payment->metadata = ['error' => $resultDesc];
                $payment->save();
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Return the latest payment state for an internal voice-call reference.
     */
    public function status(Request $request)
    {
        $validated = $request->validate([
            'call_id' => 'required|string|max:255',
        ]);

        $payment = Payment::query()
            ->where('metadata->account_ref', $validated['call_id'])
            ->latest()
            ->first();

        if (! $payment) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json([
            'status' => $payment->status,
            'payment_id' => $payment->id,
        ]);
    }

    /**
     * Simulate a Successful Payment (For Testing without Phone)
     */
    public function simulateCallback(Request $request)
    {
        $ref = $request->input('ref'); // The CheckoutRequestID

        $callbackPayload = [
            "Body" => [
                "stkCallback" => [
                    "MerchantRequestID" => "MOCK-" . uniqid(),
                    "CheckoutRequestID" => $ref,
                    "ResultCode" => 0,
                    "ResultDesc" => "The service request is processed successfully.",
                    "CallbackMetadata" => [
                        "Item" => [
                            ["Name" => "Amount", "Value" => 2000],
                            ["Name" => "MpesaReceiptNumber", "Value" => "QWE" . rand(10000, 99999) . "XYZ"],
                            ["Name" => "TransactionDate", "Value" => date('YmdHis')],
                            ["Name" => "PhoneNumber", "Value" => "254700000000"]
                        ]
                    ]
                ]
            ]
        ];

        // Call our own callback method internally
        $req = new Request();
        $req->replace($callbackPayload);
        return $this->callback($req);
    }
}
