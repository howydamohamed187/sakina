<?php

namespace App\Actions;

use App\Models\Customer;
use App\Notifications\CustomerOtpNotification;
use App\Services\OtpService;

class SendVerificationCode
{
    public function __construct(private readonly OtpService $otp) {}

    public static function run(Customer $user, ?string $email = null, ?string $phoneForSms = null, string $type = 'otp'): string
    {
        return app(self::class)($user, $email, $phoneForSms, $type);
    }

    public function __invoke(Customer $user, ?string $email = null, ?string $phoneForSms = null, string $type = 'otp'): string
    {
        $channel = filled($phoneForSms) && blank($email) ? 'sms' : 'email';
        $code = $this->otp->issue($user, $type, $channel);

        if (filled($email) || (blank($phoneForSms) && filled($user->email))) {
            $user->notify(new CustomerOtpNotification($code));
        }

        return $code;
    }
}
