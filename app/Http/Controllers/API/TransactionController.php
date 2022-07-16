<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;

class TransactionController extends Controller
{
    public function all(Request $request)
    {

        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $food_id = $request->input('food_id');
        $status = $request->input('status');


        // pengambilan data berdasarkan id
        if ($id) {
            $transaction = Transaction::with(['food', 'user'])->find($id);

            if ($transaction) {
                return ResponseFormatter::success(
                    $transaction,
                    'Success to get the transactions data!'
                );
            } else {
                return ResponseFormatter::error(
                    null,
                    'Missing the transactions data!',
                    404
                );
            }
        }

        // query transaction data for user that logged in
        $transaction = Transaction::with(['user', 'food'])->where('user_id', Auth::user()->id);

        // get data by food id
        if ($food_id) {

            $transaction->where('food_id', $food_id);
        }

        if ($status) {

            $transaction->where('stat$status', $status);
        }

        return ResponseFormatter::success(
            $transaction->paginate($limit),
            'Success to get the transactions data!'
        );
    }

    // transaction update (opsional)
    public function update(Request $request, $id)
    {

        $transaction = Transaction::findOrFail($id);

        $transaction->update($request->all());

        return ResponseFormatter::success($transaction, 'Transaction Updated!');
    }

    // checkout
    public function checkout(Request $request)
    {

        $request->validate([
            'food_id' => 'required|exists:food,id',
            'user_id' => 'required|exists:users,id',
            'quantity' => 'required',
            'total' => 'required',
            'status' => 'required',

        ]);

        $transaction = Transaction::create([
            'food_id' => $request->food_id,
            'user_id' => $request->user_id,
            'quantity' => $request->quantity,
            'total' => $request->total,
            'status' => $request->status,
            'payment_url' => '',
        ]);

        // konfigurasi midtrans
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        // panggil transaksi yang telah dibuat
        $transaction = Transaction::with(['food', 'user'])->find($transaction->id);

        // membuat transaksi midtrans

        $midtrans = array(
            'transaction_details' => array(
                'order_id' =>  $transaction->id,
                'gross_amount' => (int) $transaction->total,
            ),
            'customer_details' => array(
                'first_name'    => $transaction->user->name,
                'email'         => $transaction->user->email
            ),
            'enabled_payments' => array('gopay', 'bank_transfer'),
            'vtweb' => array()
        );
        // memanggil midtrans untuk transaksi

        try {
            // Ambil halaman payment midtrans
            $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;

            $transaction->payment_url = $paymentUrl;
            $transaction->save();

            // Redirect ke halaman midtrans
            return ResponseFormatter::success($transaction, 'Transaction Success!');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 'Transaction Failed!');
        }
    }
}
