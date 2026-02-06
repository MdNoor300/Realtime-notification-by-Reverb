<?php

namespace App\Http\Resources\Common;

use App\Models\Pakage;
use App\Models\User_Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $subscription = User_Subscription::where(['user_id' => $this->id, 'active' => 1, 'is_paid' => 1])->orderBy('id', 'desc')->first();

        if ($subscription) {
            $plan = Pakage::find($subscription->subscription_id);
        } else {
            $subscription = null;
            $plan = null;
        }

        return [
            '_id' => $this->id,
            'uid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role == USER ? 'user' : ($this->role == TRAINER ? 'trainer' : 'admin'),
            'image' => $this->image,
            'gender' => $this->gender,
            'address' => $this->address,
            'about' => $this->about,
            'facebook' => $this->facebook,
            'twitter' => $this->twitter,
            'linkedin' => $this->linkedin,
            'instagram' => $this->instagram,
            'youtube' => $this->youtube,
            'whatsapp' => $this->whatsapp,
            'dob' => $this->date_of_birth,
            'occupation' => $this->position,
            'experience' => $this->experience,
            'short_bio' => $this->description,
            'skills' => json_decode($this->skills),
            'hasSubscription' => $subscription && $subscription->active == 1,
            'activeSubscription' => ($plan && $subscription) ? [
                '_id' => $subscription->id,
                'payment' => [
                    'method' => $subscription->method,
                    'status' => $subscription->status == 0 ? 'paid' : 'unpaid',
                    'transaction_id' => $subscription->payment_id,
                    'amount' => $subscription->price,
                ],
                'subscription' => [
                    '_id' => $subscription->subscription_id,
                    'name' => json_decode($plan->name),
                ],
                'currency' => $subscription->currency,
                'price' => $subscription->price,
                'active' => $subscription->active == 1,
                'subscription_type' => $subscription->subscription_type,
            ] : null,
        ];
    }
}
