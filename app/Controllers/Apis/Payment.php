<?php

namespace App\Controllers\Apis;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class Payment extends ResourceController
{
    use ResponseTrait;

    protected $format = 'json';

    private function headers()
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $this->response->setHeader('Content-Type', 'application/json');
    }

    // ----------------------------------------------------------------
    // GET or POST: Get Razorpay credentials
    // ----------------------------------------------------------------
    public function razorpay_credentials()
    {
        $this->headers();

        $db = \Config\Database::connect();
        $builder = $db->table('payment_gateways');
        $razorpaySettings = $builder->where('gateway_name', 'razorpay')
                                    ->where('is_active', 1)
                                    ->get()
                                    ->getRow();

        if (!$razorpaySettings) {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Razorpay credentials not found or disabled.',
                'data'   => null
            ]);
        }
        
        return $this->response->setJSON([
            'status' => true,
            'msg'    => 'Razorpay credentials fetched successfully',
            'data'   => [
                'key_id' => base64_encode($razorpaySettings->key_id),
                'key_secret' => base64_encode($razorpaySettings->key_secret),
                'currency' => 'INR', // Default currency
                'company_name' => 'Healthie4U'
            ]
        ]);
    }
}
