<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Requests\DepositRequest;
use Illuminate\Support\Facades\Validator;

class DepositRequestTest extends TestCase
{
    public function test_deposit_request_validation_passes()
    {
        $request = new DepositRequest();

        $data = [
            'amount' => 10.00,
        ];

        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_deposit_request_validation_fails_when_amount_missing()
    {
        $request = new DepositRequest();

        $data = []; 

        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('amount', $validator->errors()->messages());
    }

    public function test_deposit_request_validation_fails_when_amount_not_numeric()
    {
        $request = new DepositRequest();

        $data = [
            'amount' => 'abc',
        ];

        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('amount', $validator->errors()->messages());
    }

    public function test_deposit_request_validation_fails_when_amount_less_than_minimum()
    {
        $request = new DepositRequest();

        $data = [
            'amount' => 0,
        ];

        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('amount', $validator->errors()->messages());
    }
}
