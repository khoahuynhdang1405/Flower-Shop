<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Subscriber;
use App\Repositories\Contracts\SubscriberRepositoryInterface;

final class SubscriberRepository implements SubscriberRepositoryInterface
{
    public function findById(int $id): ?Subscriber
    {
        return Subscriber::find($id);
    }

    public function findOrCreateByEmail(string $email, string $couponCode): Subscriber
    {
        return Subscriber::query()->createOrFirst(['email' => $email], ['coupon_given' => $couponCode]);
    }
}
