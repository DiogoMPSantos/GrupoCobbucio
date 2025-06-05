<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Requests\ReverseRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Str;

class ReverseRequestTest extends TestCase
{
    public function test_reverse_request_validation_passes()
    {
        $user = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'sender_id' => $user->id,
            'receiver_id' => User::factory()->create()->id,
            'type' => Transaction::TYPE_TRANSFER,
            'amount' => 100,
            'reference' => (string) Str::uuid(),
            'status' => Transaction::STATUS_COMPLETED,
        ]);

        $request = new ReverseRequest();

        $validator = Validator::make([
            'reference' => $transaction->reference,
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_reverse_request_validation_fails_with_invalid_reference()
    {
        $request = new ReverseRequest();

        $validator = Validator::make([
            'reference' => (string) Str::uuid(), // UUID que não existe
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('reference', $validator->errors()->toArray());
    }

    public function test_reverse_request_fails_when_reference_missing()
    {
        $request = new ReverseRequest();

        $data = []; // missing reference

        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('reference', $validator->errors()->messages());
    }

    public function test_reverse_request_fails_when_reference_invalid_uuid()
    {
        $request = new ReverseRequest();

        $data = [
            'reference' => 'invalid-uuid-string',
        ];

        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('reference', $validator->errors()->messages());
    }

    public function test_reverse_request_fails_when_reference_does_not_exist()
    {
        $request = new ReverseRequest();

        $data = [
            'reference' => '00000000-0000-0000-0000-000000000000', // UUID, but not existing
        ];

        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('reference', $validator->errors()->messages());
    }
}
