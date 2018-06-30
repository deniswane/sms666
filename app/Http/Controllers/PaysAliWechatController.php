<?php

namespace App\Http\Controllers;

use App\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yansongda\LaravelPay\Facades\Pay;
use App\Models\User;

class PaysAliWechatController extends Controller
{
    /**支付
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function alipay(Request $request)
    {

        if ($request->isMethod('post')) {
            $post = $request->post();
            $validator = Validator::make($post, [
                'prices' => 'numeric|required',
            ]);
            if ($validator->fails()) {
                return back();
            }
            if ($post['prices'] == 0) {
               return redirect()->back();
            }
            $time = microtime(true);
            list($s1,$s2) = explode('.', $time);
            $order_no = date("YmdHis") . $s2 . rand(1000,9999);
            $config_biz = [
                //商家订单
                'out_trade_no' => $order_no,
                'total_amount' =>$post['prices'] ,
                'subject' => '短信平台支付',
            ];
            return Pay::alipay()->web($config_biz);

        }else{
            return redirect()->back();
        }
    }

    /**同步通知
     * @param Request $request
     * @return mixed
     */
    public function alireturn(Request $request)
    {
        return view('paypal.success');

//        return Pay::alipay()->verify($request->all());

    }

    /**异步通知 更新数据
     * @param Request $request
     */
    public function alinotify(Request $request)
    {

        if (Pay::alipay()->verify($request->all())) {
                $invoice = Invoice::query()->where('invoices',$request->out_trade_no)->first();
                if ($invoice){
                    $invoices = new Invoice();
                    $invoices->merchant_message='';
                    $invoices->commodity_escription='';
                    $invoices->feedback='';
                    $invoices->invoices=$request->out_trade_no;
                    $invoices->money=$request->total_amount;
                    $invoices->paid=1;
                    $invoices->save();


                    Auth::user()->balance +=$request->total_amount;
                    Auth::user()->save();

                    $dt = Carbon::now();
                    $txt =$dt . '   ' .Auth::user()->email.'--'.'冲值'.'--'.$request->total_amount;
                    Storage::disk('local')->append('alipay', $txt);
                }
        } else {
            file_put_contents(storage_path('notify.txt'), "收到异步通知\r\n", FILE_APPEND);
        }

       echo "success";
    }

}
