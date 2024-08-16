<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Mail\RegistrationPaymentMail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use App\Repositories\ApiPaymentRepositoryInterface;

use App\Models\SequenceNumber;
use App\Models\Package;
use App\Models\PaymentTransaction;
use App\Models\PaymentProfile;
use App\Models\User;

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Agreement;
use PayPal\Api\Payer;
use PayPal\Api\Plan;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\PayerInfo;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

use Gate;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    const MODULE_NAME   = 'payment';
    const CAN_ACCESS    = 'access ' . self::MODULE_NAME;
    const CAN_SAVE      = 'save ' . self::MODULE_NAME;
    const CAN_UPDATE    = 'update ' . self::MODULE_NAME;

    private $role;

    public function __construct( ApiPaymentRepositoryInterface $apiPaymentRepository)
    {
        $this->apiPaymentRepository = $apiPaymentRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // abort_if(Gate::denies(self::CAN_ACCESS), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $data = Package::where('deleted_at', 0)
                       ->orderBy('sequence', 'asc')
                        ->get();        
        
        $role   = $this->getRole();          
        return view('client.payment.index')->with(compact('data', 'role'));

    }

    /**
     * Payment Method Selection
     */
    public function paymentMethod($role, $id)
    {
        $data   = Package::where('id', $id)->first();        
        $role   = $this->getRole();          
        return view('client.payment.method')->with(compact('data','role'));;
    }

    /**
     * Credit Card Payment 
     * Authorize.net
     */
    public function creditCard($role, $id)
    {
        $countries = config('country');
        $data   = Package::where('id', $id)->first();        
        $role   = $this->getRole();

        return view('client.payment.card')->with(compact('data','role', 'countries'));;
    }

  


    /**
     * Paypal Payment
     * 
     * @param Request $request
     * 
     * @return JsonResponse
     */
    public function payPal(Request $request)
    {
        $request->validate([
            'package_id' => 'required',
            'order_number' => 'required'
        ]);

        return DB::transaction(function() use ($request) {

            $user    = User::find(Auth::user()->id);

            $package = Package::find($request->package_id);

            $total_credits = $user->credits + $package->max_questions;

            User::where('id', $user->id)
                ->update([ 'credits' => $total_credits]);

            $paymentTransactionArray = [
                'user_id'           => $user->id,
                'currency'          => 'USD', 
                'payment_source'    => 'AddFund',
                'site'              => 'TYP',
                'last4'             => '',
                'brand'             => '',
                'amount'            => '',
                'package'           => '',
                'credits'           => '',
                'status'            => 'Authorized/Pending Capture',
                'payment_status'    => 0       
            ];

            $paymentTransactionArray['order_number']    = $request->order_number;
            $paymentTransactionArray['payment_provider']= 'PAYPAL';
            $paymentTransactionArray['payment_method']  = 'PAYPAL';
            $paymentTransactionArray['amount']          = $package->rate;
            $paymentTransactionArray['package']         = $package->name;
            $paymentTransactionArray['credits']         = $package->max_questions;
            $paymentTransactionArray['payment_status']  = 1;

            $paymentTransaction = PaymentTransaction::updateOrCreate($paymentTransactionArray);

            Mail::to($user->email)->send(new RegistrationPaymentMail($user, $paymentTransaction));

            return new JsonResponse([
                'error' => false,
                'message' => 'Added fund successfully!',
                'redirect_url' => route('clientdashboard.index', ['userType' => get_user_type()['userType']]),
            ], Response::HTTP_CREATED);
        });
    }

    /**
     * Initiate Payment 
     */
    public function payment(Request $request)
    {
        $payment = $this->setPayment($request);
        $package_info   = Package::find($request['id']);
        $this->apiPaymentRepository->init();
        $response = $this->apiPaymentRepository->execute('checkout', $payment);
        


        if ($response['data']['status'] =="Ok") {
            $user = auth()->user();

            $total_credits = $user->credits + $payment['sku_denomination'];
            $status = User::where('id', $user->id)
                            ->update([ 'credits' => $total_credits]);

            $user = auth()->user();
            
            $transaction_date = Carbon::now();
            $user_info = User::where('id', $user->id)->first();
                                
            $invoice = [
                        'username'          => $user_info->username,
                        'fullname'          => $user_info->first_name . " " . $user_info->last_name,
                        'order_number'      => $response['data']['order_number'],
                        'card_last_four'    => $response['data']['card_last_four'],
                        'card_type'         => $response['data']['card_type'],
                        'amount'            => $payment['amount'],
                        'package'           =>  $payment['sku'] . " - " . $payment['sku_denomination'] . " messages",
                        'transaction_date'  => $transaction_date->toDateTimeString()
            ];
           
            $paymentTransaction = PaymentTransaction::updateOrCreate(
                                                    [
                                                        'user_id'       => $user->id,
                                                        'order_number'  => $response['data']['order_number'], 
                                                        'currency' => 'USD', 
                                                        'payment_provider'  => 'AUTHORIZED.NET',
                                                        'payment_source'    => 'AddFund',
                                                        'payment_method'    => 'CREDIT CARD',
                                                        'site'              => 'TYP',
                                                        'last4'             => $response['data']['card_last_four'],
                                                        'brand'             => $response['data']['card_type'],
                                                        'amount'            => $payment['amount'],
                                                        'package'           => $package_info->name,
                                                        'credits'           => $payment['sku_denomination'],
                                                        'status'            => 'Authorized/Pending Capture',
                                                        'payment_status'    => 0       
                                                    ]                               
                                    );
            if (!empty($request['save-info']))               
            {
                $paymentProfile = PaymentProfile::updateOrCreate(
                                                        [
                                                            'card_last_four'=>$response['data']['card_last_four']
                                                        ]
                                                        ,
                                                        [
                                                            'user_id'       => $user->id,
                                                            'profile_id'    => $response['data']['profile_id'], 
                                                            'address'       => $request['address'], 
                                                            'city'          => $request['city'],
                                                            'state'         => $request['state'],
                                                            'zip'           => $request['zip'],
                                                            'country'       => $request['country'],
                                                            'card_brand'    => $response['data']['card_type'],
                                                            'card_last_four' => $response['data']['card_last_four'],
                                                            'card_expiration_year'  => $payment['expiration_year'],    
                                                            'card_expiration_month' => $payment['expiration_month']
                                                        ]                               
                                                    );                        
            }

            // Mail::to($user->email)
            //         ->cc(config('typ.admin_notification_email'))
            //         ->send( new PaymentNotification($invoice ) ); 

            return redirect('/client/clientdashboard')->with('message', $total_credits);
        }
        else
        {
            \Session::flash('message', $response['data']['message']);
            return redirect()->back()->withInput($request->all());;
        }


    }
     /**
     * Set Payment attributes
     */
    private function setPayment($request)
    {

        $sequence       = SequenceNumber::where('type', 'payment')->increment('sequence_numbers');
        $sequence_info  = SequenceNumber::where('type', 'payment')->first();                        
        $order_number   = str_pad($sequence_info->sequence_numbers, 10, "0", STR_PAD_LEFT);
        $package_info   = Package::find($request['id']);
        if (!empty($package_info))
        {
            $data =     [ 
                            "firstname" => $request['firstname'],
                            "lastname"  => $request['lastname'],
                            "address"   => $request['address'],
                            "city"      => $request['city'],
                            "state"     => $request['state'],
                            "country"   => $request['country'],
                            "zip"       => $request['zip'],
                            "card_number"       => $request['card_number'],
                            "expiration_year"   => $request['card_expiration_year'],
                            "expiration_month"  => str_pad($request['card_expiration_month'], 2, "0", STR_PAD_LEFT),
                            "cvv"               => $request['cvv'],
                            "sku"               => 'Goods or Services', 
                            "sku_denomination"  => $package_info->max_questions,
                            "amount"            => $package_info->rate,
                            "order_number"      => $order_number,
                            "coupon"            => "",
                            "coupon_amount_discount"    => "",
                            "payment_source"    => "AddFund",
                            "site"              => "WPH",
                            "currency"          => "USD",
                    ];
        }
        else
        {
            // return redirect('client/packages');
        }
        return $data;
    }

    /**
     *  Get Role , can be refactored as helper
     */

    private function getRole()
    {
        $roles = Auth::user()->roles;
        return  strtolower($roles[0]['name']);
    }
}
