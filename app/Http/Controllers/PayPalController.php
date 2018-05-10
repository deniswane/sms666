<?php

namespace App\Http\Controllers;

use App\Invoice;
use App\Item;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Srmklive\PayPal\Services\AdaptivePayments;
use Srmklive\PayPal\Services\ExpressCheckout;
use Illuminate\Support\Facades\Validator;
use App\Library\Y;

class PayPalController extends Controller
{
    /**
     * @var ExpressCheckout
     */
    protected $provider;
    protected Static $prices;

    public function __construct()
    {
        $this->provider = new ExpressCheckout();
        $this->middleware('auth', ['only' => ['getExpressCheckout']]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function getExpressCheckout(Request $request)
    {
        if ($request->isMethod('post')) {
            $post = $request->post();

            $validator = Validator::make($post, [
                'prices' => 'numeric|required',
            ]);
            if ($validator->fails()) {
                return Y::error($validator->errors());
            }
            if ($post['prices'] == 0) {
                echo json_encode(['code' => 1, 'msg' => '']);
                die;
            }
            $recurring = ($request->get('mode') === 'recurring') ? true : false;
            Session::put(Auth::user()->id . 'prices', $post['prices']);

            $cart = $this->getCheckoutData($post['prices'], $recurring);

            try {

//                $this->provider->setCurrency('EUR')->setExpressCheckout($cart);
                $response = $this->provider->setExpressCheckout($cart, $recurring);
                echo json_encode(['code' => 200, 'msg' => $response['paypal_link']]);
            } catch (\Exception $e) {
                $invoice = $this->createInvoice($cart, 'Invalid');

                session()->put(['code' => 'danger', 'message' => "Error processing PayPal payment for Order $invoice->id!"]);
            }
        }
    }

    /**
     * Process payment on PayPal. 支付
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getExpressCheckoutSuccess(Request $request)
    {


        $recurring = ($request->get('mode') === 'recurring') ? true : false;
        $token = $request->get('token');
        $PayerID = $request->get('PayerID');
        $cart = $this->getCheckoutData($recurring);

        // Verify Express Checkout Token 验证token
        $response = $this->provider->getExpressCheckoutDetails($token);
        if (in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])) {
            if ($recurring === true) {
                $response = $this->provider->createMonthlySubscription($response['TOKEN'], 9.99, $cart['subscription_desc']);
                if (!empty($response['PROFILESTATUS']) && in_array($response['PROFILESTATUS'], ['ActiveProfile', 'PendingProfile'])) {
                    $status = 'Processed';
                } else {
                    $status = 'Invalid';
                }
            } else {
                // Perform transaction on PayPal 执行事务
                $payment_status = $this->provider->doExpressCheckoutPayment($cart, $token, $PayerID);

                $status = $payment_status['PAYMENTINFO_0_PAYMENTSTATUS'];
            }
            $cart['feedback'] = isset($response['PAYMENTREQUEST_0_NOTETEXT']) ? $response['PAYMENTREQUEST_0_NOTETEXT'] : '';
            if ($response['CHECKOUTSTATUS'] == 'PaymentActionNotInitiated') {
                $this->createInvoice($cart, $status);
            }

            Session::forget(Auth::user()->id . 'prices');


            return redirect('paypal/success');
        }
    }

    public function getAdaptivePay()
    {
        $this->provider = new AdaptivePayments();

        $data = [
            'receivers' => [
                [
                    'email' => '641268939@qq.com',
                    'amount' => 10,
                    'primary' => true,
                ],
                [
                    'email' => '641268939@qq.com',
                    'amount' => 5,
                    'primary' => false,
                ],
            ],
            'payer' => 'EACHRECEIVER', //可选描述谁支付了费用，发送者、接收方。。。
            // (Optional) Describes who pays PayPal fees. Allowed values are: 'SENDER', 'PRIMARYRECEIVER', 'EACHRECEIVER' (Default), 'SECONDARYONLY'
            'return_url' => url('payment/success'),
            'cancel_url' => url('payment/cancel'),
        ];

        $response = $this->provider->createPayRequest($data);
        dd($response);
    }

    /**
     * Parse PayPal IPN.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function notify(Request $request)
    {
        if (!($this->provider instanceof ExpressCheckout)) {
            $this->provider = new ExpressCheckout();
        }

        $request->merge(['cmd' => '_notify-validate']);
        $post = $request->all();

        $response = (string)$this->provider->verifyIPN($post);

        $logFile = 'ipn_log_' . Carbon::now()->format('Ymd_His') . '.txt';
        Storage::disk('local')->put($logFile, $response);
    }

    /**
     * Set cart data for processing payment on PayPal.
     *设置购物车数据
     * @param bool $recurring
     *
     * @return array
     */
    protected function getCheckoutData($prices, $recurring = false)
    {
        $data = [];
        $order_id = Invoice::all()->count() + 1;
        if ($recurring === true) {
            $data['items'] = [
                [
                    'name' => 'Monthly Subscription ' . config('paypal.invoice_prefix') . ' #' . $order_id,
                    'price' => 0,
                    'qty' => 1,
                ],
            ];

            $data['return_url'] = url('/paypal/ec-checkout-success?mode=recurring');
            $data['subscription_desc'] = 'Monthly Subscription ' . config('paypal.invoice_prefix') . ' #' . $order_id;
        } else {
            //商品名称及价格
            $user = Auth::user()->id . 'prices';
            if (Session::has($user)) {
                $price = session($user);
            } else {
                $price = $prices;
            }
            $data['items'] = [

                ['price' => $price,
                    'name' => 'Product',
                    'qty' => 1,
                ],
            ];
            $data['return_url'] = url('/paypal/ec-checkout-success');
        }
        //设置币种
//        $this->provider->setCurrency('EUR')->setExpressCheckout($data);
        $time = microtime(true);
        list($s1,$s2) = explode('.', $time);
        $order_no = date("YmdHis") . $s2 . rand(1000,9999);
        $data['invoice_id'] = $order_no;
        //商家描述
        $data['invoice_description'] = '';
        $data['cancel_url'] = url('/');
//        $total = 0;
//
//        foreach ($data['items'] as $item) {
//
//            $total += $item['price'] * $item['qty'];
//        }

        $data['total'] = $price;

        return $data;
    }

    /**
     * Create invoice.发票
     *
     * @param array $cart
     * @param string $status
     *
     * @return \App\Invoice
     */
    protected function createInvoice($cart, $status)

    {

        $invoice = new Invoice();
        $invoice->merchant_message = $cart['invoice_description'];
        $invoice->money = $cart['total'];
        $invoice->invoices = $cart['invoice_id'];
        $invoice->feedback = $cart['feedback'];
        $invoice->commodity_escription = $cart['items'][0]['name'];

        if (!strcasecmp($status, 'Completed') || !strcasecmp($status, 'Processed')) {
            $invoice->paid = 1;
            $user=User::find(Auth::user()->id);
            $user->balance = $user->balance + $cart['total'];
            $user->save();
        } else {
            $invoice->paid = 0;
        }
        $invoice->save();

        return $invoice;
    }

    public function success()
    {

        return view('paypal.success');

    }
}
