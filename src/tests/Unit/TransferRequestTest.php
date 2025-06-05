<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Requests\TransferRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class TransferRequestTest extends TestCase
{
    public function testTransferRequestValidationPasses()
    {
        $user = User::factory()->create();

        $request = new TransferRequest();

        $data = [
            'receiver_id' => $user->id,
            'amount' => 10.00,
        ];

        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function testTransferRequestFailsWhenReceiverIdMissing()
    {
        $request = new TransferRequest();

        $data = [
            'amount' => 10.00,
        ];

        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('receiver_id', $validator->errors()->messages());
    }

    public function testTransferRequestFailsWhenReceiverIdDoesNotExist()
    {
        $request = new TransferRequest();

        $data = [
            'receiver_id' => 999999,
            'amount' => 10.00,
        ];

        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('receiver_id', $validator->errors()->messages());
    }

    public function testTransferRequestFailsWhenAmountMissing()
    {
        $user = User::factory()->create();

        $request = new TransferRequest();

        $data = [
            'receiver_id' => $user->id,
        ];

        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('amount', $validator->errors()->messages());
    }

    public function testTransferRequestFailsWhenAmountNotNumeric()
    {
        $user = User::factory()->create();

        $request = new TransferRequest();

        $data = [
            'receiver_id' => $user->id,
            'amount' => 'abc',
        ];

        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('amount', $validator->errors()->messages());
    }

    public function testTransferRequestFailsWhenAmountLessThanMinimum()
    {
        $user = User::factory()->create();

        $request = new TransferRequest();

        $data = [
            'receiver_id' => $user->id,
            'amount' => 0,
        ];

        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('amount', $validator->errors()->messages());
    }
}
