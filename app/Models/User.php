<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\CustomerGroup;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// customer_group không nằm trong Fillable: khách không được tự đổi nhóm qua form.
#[Fillable(['name', 'email', 'phone', 'password', 'google_id', 'birthday', 'default_shipping_address', 'default_shipping_district'])]
#[Hidden(['password', 'google_id', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'customer_group' => 'new',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'birthday' => 'date',
            'customer_group' => CustomerGroup::class,
        ];
    }
}
