<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /*
     * this is the address the customer will be redirected to once payment
     *  has been completed successfully
     */
    public function success(){
        echo 'success';
    }

    /*
     * this is the address the customer will be redirected to in the event of an
     *  error during the payment process or if payment is cancelled
     */
    public function fail(){
        echo 'fail';
    }

    /*
     * the address of the payment processor. This page is where orders can
     *  be marked as paid or, for example, funds can be sent to the customer’s account on your
     *  website
     */
    public function status(){
        echo 'status';
    }
}
