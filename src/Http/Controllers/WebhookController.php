<?php

namespace UvealSnow\ConektaCashier\Controllers;

use PDF;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use UvealSnow\ConektaCashier\Order;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use UvealSnow\ConektaCashier\Mail\ConektaCharge;
use UvealSnow\ConektaCashier\Mail\PaymentConfirmed;

class WebhookController extends Controller
{
    public function orderPaid(Request $request)
    {
        // DB::table('tests')->insert([
        //     'data' => json_encode($request->data)
        // ]);

        if ($request->type === 'order.paid') {
            $order = Order::where('conekta_order', $request->data['object']['id'])->firstOrFail();

            $order->status = $request->data['object']['charges']['data'][0]['status'];
            $order->save();

            if ($order->subscriptions->count() > 0) {
                foreach ($order->subscriptions as $subscription) {
                    $next_payment_date = $subscription->getNextEndDate();

                    $subscription->fill([
                        'conekta_order_id' => $request->data['object']['id'],
                        'ends_at' => $next_payment_date
                    ])->save();

                    $temp = public_path() . "/tmp/$charge->id.pdf";

                    PDF::loadView('cashier::receipt', [
                        'order' => $order,
                    ])->save($temp);

                    Mail::to($order->user)->queue(new PaymentConfirmed(
                        $order->user,
                        $order->conekta_order,
                        $next_payment_date,
                        $temp
                    ));

                    unlink($temp);
                }
            }
        }

        return response('ok');
    }

    public function test()
    {
        $user = User::find(1);
        // $sub = $user->subscriptions[0];

        // $user->initConekta();

        // $order = $user->createConektaOrder([$sub->asLineItem()]);

        // $charge = $user->chargeOrder($order, [
        //     'type' => 'spei',
        // ]);

        $order = $user->orders->first();

        $temp = public_path() . "/tmp/$order->id.pdf";
        $next_payment_date = now()->addDays(30);

        // PDF::loadView('cashier::stub', [
        //     'charge' => $charge,
        // ])->save($temp)->stream('download.pdf');

        PDF::loadView('cashier::receipt', [
            'order' => $order,
        ])->save($temp)->stream('download.pdf');

        return new PaymentConfirmed(
            $order->user,
            $order->conekta_order,
            $next_payment_date,
            $temp
        );
    }
}
