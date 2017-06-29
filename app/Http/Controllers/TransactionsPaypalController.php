<?php

namespace App\Http\Controllers;

use App\Models\TransactionsPaypal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;


use PayPal\Api\Address;
use PayPal\Api\FundingInstrument;
use PayPal\Api\PaymentCard;
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
use ResultPrinter;

class TransactionsPaypalController extends Controller
{
    // ### Api context
    // Use an ApiContext object to authenticate
    // API calls. The clientId and clientSecret for the
    // OAuthTokenCredential class can be retrieved from
    // developer.paypal.com

    private function apiContext()
    {
        $paypal_conf = Config::get('paypal');
        $apiContext = new ApiContext(new OAuthTokenCredential($paypal_conf['client_id'], $paypal_conf['secret']));
        $apiContext->setConfig($paypal_conf['settings']);

        return $apiContext;
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
        public function authh()
    {
        $data = request ()->all ();

    // Create new payer and method
        $payer = new Payer();
        $payer->setPaymentMethod ( "paypal" );

    // Set payment amount
        $amount = new Amount();
        $amount->setCurrency ( "USD" )
            ->setTotal ($data[ 'amount' ]);

    // Set transaction object
        $transaction = new Transaction();
        $transaction->setAmount ( $amount )
            ->setDescription ( "Payment description" );

    // Set redirect urls
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl ( route ( 'paypal.auth.store' ) )
            ->setCancelUrl ( "http://localhost:3000/cancel" );

    // Create the full payment object
        $payment = new Payment();
        $payment->setIntent ( "authorize" )
            ->setPayer ( $payer )
//            ->setRedirectUrls ( $redirectUrls )
            ->setTransactions ( array( $transaction ) );

        /** dd($payment->create($this->apiContext()));exit; **/

        // For Sample Purposes Only.
        $request = clone $payment;

        // ### Create Payment
        // Create a payment by calling the payment->create() method
        // with a valid ApiContext (See bootstrap.php for more on `ApiContext`)
        // The return object contains the state.
        try {
            $payment->create ( $this->apiContext());
        } catch (PayPalConnectionException $ex) {
            dd($payment);
        }

        $transactions = $payment->getTransactions ();
        $relatedResources = $transactions[ 0 ]->getRelatedResources ();
        $authorization = $relatedResources[ 0 ]->getAuthorization ();

        return $authorization;

    }

    public function auth()
    {

        $data = request ()->all ();

// ### Payer
// A resource representing a Payer that funds a payment
// For paypal account payments, set payment method
// to 'paypal'.
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

// ### Itemized information
// (Optional) Lets you specify item wise
// information
        $item1 = new Item();
        $item1->setName('Ground Coffee 40 oz')
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setPrice(7.5);
        $item2 = new Item();
        $item2->setName('Granola bars')
            ->setCurrency('USD')
            ->setQuantity(5)
            ->setPrice(2);

        $itemList = new ItemList();
        $itemList->setItems(array($item1, $item2));

// ### Additional payment details
// Use this optional field to set additional
// payment information such as tax, shipping
// charges etc.
        $details = new Details();
        $details->setShipping(1.2)
            ->setTax(1.3)
            ->setSubtotal(17.50);

// ### Amount
// Lets you specify a payment amount.
// You can also specify additional details
// such as shipping, tax.
        $amount = new Amount();
        $amount->setCurrency ( "USD" )
            ->setTotal ($data[ 'amount' ])
            ->setDetails($details);

// ### Transaction
// A transaction defines the contract of a
// payment - what is the payment for and who
// is fulfilling it.
        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription("Payment description")
            ->setInvoiceNumber(uniqid());

// ### Redirect urls
// Set the urls that the buyer must be redirected to after
// payment approval/ cancellation.
        $baseUrl = 'http://paypal2.dev/paywithpaypal';
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl("$baseUrl/ExecutePayment.php?success=true")
            ->setCancelUrl("$baseUrl/ExecutePayment.php?success=false");

// ### Payment
// A Payment Resource; create one using
// the above types and intent set to 'sale'
        $payment = new Payment();
        $payment->setIntent("authorize")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));

// For Sample Purposes Only.
        $request = clone $payment;

// ### Create Payment
// Create a payment by calling the 'create' method
// passing it a valid apiContext.
// (See bootstrap.php for more on `ApiContext`)
// The return object contains the state and the
// url to which the buyer must be redirected to
// for payment approval
        try {
            $payment->create($this->apiContext());

            $approvalUrl = $payment->getApprovalLink();

            dd($approvalUrl);

            return Redirect::away($approvalUrl);

        } catch (PayPalConnectionException $ex) {
            // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
//            ResultPrinter::printError("Created Payment Authorization Using PayPal. Please visit the URL to Authorize.", "Payment", null, $request, $ex);

            exit(1);
        }

// ### Get redirect url
// The API response provides the url that you must redirect
// the buyer to. Retrieve the url from the $payment->getLinks()
// method
        $approvalUrl = $payment->getApprovalLink();

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
//        ResultPrinter::printResult("Created Payment Authorization Using PayPal. Please visit the URL to Authorize.", "Payment", "<a href='$approvalUrl' >$approvalUrl</a>", $request, $payment);

        return Redirect::away($approvalUrl);
    }








}





















//        try {
//            $payment->create($this->_api_context);
//
//            Session::put('amount', $data['amount']);
//
//            $approvalUrl = $payment->getApprovalLink();
//
//            return Redirect::away($approvalUrl);
//
//        } catch (PayPalConnectionException $ex) {
//            if (Config::get('app.debug')) {
//                Session::put('error','Connection timeout');
//                return Redirect::route('addmoney.paywithpaypal');
//                /** echo "Exception: " . $ex->getMessage() . PHP_EOL; **/
//                /** $err_data = json_decode($ex->getData(), true); **/
//                /** exit; **/
//            } else {
//                Session::put('error','Some error occur, sorry for inconvenient');
//                return Redirect::route('addmoney.paywithpaypal');
//                /** die('Some error occur, sorry for inconvenient'); **/
//            }
//        }













//    public function storeAuth()
//    {
//        // Execute transaction storage
//
//        TransactionsPaypal::create([
//             'id' => Uuid::uuid4(),
//             'user_id' => '1',
//             'payment_id' => $_GET['paymentId'],
//             'amount' => session()->get('amount'),
//             'status' => 'pending',
//         ]);
//
//        return Redirect::route('addmoney.paywithpaypal');
//    }
//
//    public function getMoney($paymentId, $PayerId)
//    {
//        // Get payment object by passing paymentId
//        $payment = Payment::get($paymentId, $this->_api_context);
//
//        // Execute payment with payer id
//        $execution = new PaymentExecution();
//        $execution->setPayerId($PayerId);
//
//        $payment = Payment::get($paymentId, $this->_api_context);
//
//        dd($payment);
//
//        try {
//            // Execute payment
//            $result = $payment->execute($execution, $this->_api_context);
//
//            // Extract authorization id
//            $authid = $payment->transactions[0]->related_resources[0]->authorization->id;
//
//        } catch (PayPalConnectionException $ex) {
//            echo $ex->getCode();
//            echo $ex->getData();
//            die($ex);
//        } catch (Exception $ex) {
//            die($ex);
//        }
//    }
//
//    public function pushMoney()
//    {
//
//    }
//}
