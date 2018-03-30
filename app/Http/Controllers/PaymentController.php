<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/*
 * client_email     The email address of the customer who paid the invoice
 * client_account   The Payeer account number of the customer who paid the invoice
 * summa_out        The amount of money,minus all fees, transferred to the merchant's account for this payment
 * m_params         A JSON array of data of additional parameters
 **/

class PaymentController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    /*
     * this is the address the customer will be redirected to once payment
     *  has been completed successfully
     */
    public function success() {
        //todo 用户加钱
        echo 'success';
    }

    /*
     * this is the address the customer will be redirected to in the event of an
     *  error during the payment process or if payment is cancelled
     */
    public function fail() {
        // todo 显示错误页面
        echo 'fail';
    }

    /*
     * the address of the payment processor. This page is where orders can
     *  be marked as paid or, for example, funds can be sent to the customer’s account on your
     *  website
     *
     * The handler of payment has to return surely m_orderid with success or error status as is stated in an example above
     */
    public function status() {
        // Rejecting queries from IP addresses not belonging to Payeer
        if (!in_array($_SERVER['REMOTE_ADDR'], array('185.71.65.92', '185.71.65.189', '149.202.17.210'))) return;

        if (isset($_POST['m_operation_id']) && isset($_POST['m_sign'])) {
            $m_key = '5029963';

            $arHash = array(
                $_POST['m_operation_id'],
                $_POST['m_operation_ps'],
                $_POST['m_operation_date'],
                $_POST['m_operation_pay_date'],
                $_POST['m_shop'],
                $_POST['m_orderid'],
                $_POST['m_amount'],
                $_POST['m_curr'],
                $_POST['m_desc'],
                $_POST['m_status']
            );

            if (isset($_POST['m_params'])) {
                $arHash[] = $_POST['m_params'];
            }

            $arHash[] = $m_key;
            # 对照签名，防止数据篡改
            $sign_hash = strtoupper(hash('sha256', implode(':', $arHash)));

            if ($_POST['m_sign'] == $sign_hash && $_POST['m_status'] == 'success') {
                echo $_POST['m_orderid'] . '|success';
                exit;
            }

            echo $_POST['m_orderid'] . '|error';

        }
    }

    public function launchPay(Request $request){
        //todo 大于0的数
        // 校验
//        $this->validate($request, [
//            'amount' => 'required|digits:true'
//        ]);
        echo "准备提交界面".$request->amount;

        $m_shop = '';
        $m_orderid = '1';
        $m_amount = number_format(100, 2, '.', '');
        $m_curr = 'USD';
        $m_desc = base64_encode('Test');
        $m_key = '253445';

        $arHash = array(
            $m_shop,
            $m_orderid,
            $m_amount,
            $m_curr,
            $m_desc
        );


        $arParams = array(
            'success_url' => 'http:///new_success_url',
            //'fail_url' => 'http:///new_fail_url',
            //'status_url' => 'http:///new_status_url',
            'reference' => array(
                'var1' => '1',
                //'var2' => '2',
                //'var3' => '3',
                //'var4' => '4',
                //'var5' => '5',
            ),
        );

        $key = md5(''.$m_orderid);

        $m_params = urlencode(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, json_encode($arParams), MCRYPT_MODE_ECB)));

        $arHash[] = $m_params;
        $arHash[] = $m_key;

        $sign = strtoupper(hash('sha256', implode(':', $arHash)));

        return view('layouts.confirm_launch_payment',compact('m_shop','m_orderid','m_amount','m_curr','m_desc','sign'));

    }
}
