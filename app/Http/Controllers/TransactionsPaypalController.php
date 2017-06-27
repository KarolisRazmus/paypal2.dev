<?php

namespace App\Http\Controllers;

use App\Models\TransactionsPaypal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;


use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\PaymentExecution;
use Ramsey\Uuid\Uuid;

class TransactionsPaypalController extends Controller
{
    /**
     * @var ApiContext
     */

    private $_api_context;

    public function __construct()
    {
        /** setup PayPal api context **/

        $paypal_conf = Config::get('paypal');
        $this->_api_context = new ApiContext(new OAuthTokenCredential($paypal_conf['client_id'], $paypal_conf['secret']));
        $this->_api_context->setConfig($paypal_conf['settings']);
    }

    public function payWithPaypal()
    {
        return view('front-end.paywithpaypal');
    }
    /**
     * Store a details of payment with paypal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function auth(Request $request)
    {
        $data = request()->all();

// Create new payer and method
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

// Set payment amount
        $amount = new Amount();
        $amount->setCurrency("USD")
            ->setTotal($data['amount']);

// Set transaction object
        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setDescription("Payment description");

// Set redirect urls
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(route('paypal.auth.store'))
            ->setCancelUrl("http://localhost:3000/cancel");

// Create the full payment object
        $payment = new Payment();
        $payment->setIntent("authorize")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));

        /** dd($payment->create($this->_api_context));exit; **/
        try {
            $payment->create($this->_api_context);

            Session::put('amount', $data['amount']);

            $approvalUrl = $payment->getApprovalLink();

            return Redirect::away($approvalUrl);

        } catch (PayPalConnectionException $ex) {
            if (Config::get('app.debug')) {
                Session::put('error','Connection timeout');
                return Redirect::route('addmoney.paywithpaypal');
                /** echo "Exception: " . $ex->getMessage() . PHP_EOL; **/
                /** $err_data = json_decode($ex->getData(), true); **/
                /** exit; **/
            } else {
                Session::put('error','Some error occur, sorry for inconvenient');
                return Redirect::route('addmoney.paywithpaypal');
                /** die('Some error occur, sorry for inconvenient'); **/
            }
        }
    }

    public function storeAuth()
    {
        // Execute transaction storage

        TransactionsPaypal::create([
             'id' => Uuid::uuid4(),
             'user_id' => '1',
             'payment_id' => $_GET['paymentId'],
             'amount' => session()->get('amount'),
             'status' => 'pending',
         ]);

        return Redirect::route('addmoney.paywithpaypal');
    }

    public function getMoney($paymentId, $PayerId)
    {
        // Get payment object by passing paymentId
        $payment = Payment::get($paymentId, $this->_api_context);

        // Execute payment with payer id
        $execution = new PaymentExecution();
        $execution->setPayerId($PayerId);

        $payment = Payment::get($paymentId, $this->_api_context);

        dd($payment);

        try {
            // Execute payment
            $result = $payment->execute($execution, $this->_api_context);

            // Extract authorization id
            $authid = $payment->transactions[0]->related_resources[0]->authorization->id;

        } catch (PayPalConnectionException $ex) {
            echo $ex->getCode();
            echo $ex->getData();
            die($ex);
        } catch (Exception $ex) {
            die($ex);
        }
    }

    public function pushMoney()
    {

    }
}
