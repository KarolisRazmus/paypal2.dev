<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionsPaypal extends Model
{
    protected $table = 'transactions_paypal';

    protected $fillable = ['id', 'user_id', 'payment_id', 'status', 'amount'];

    protected $paypalRouteName = 'front-end.paywithpaypal';

    public static $STATUS = ['pending' => 'pending', 'failed' => 'failed', 'received' => 'received'];

}
