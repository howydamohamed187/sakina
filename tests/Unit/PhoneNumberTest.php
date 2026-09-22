<?php

namespace Tests\Unit;

use App\Support\PhoneNumber;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    public function test_it_normalizes_egyptian_numbers(): void
    {
        $this->assertSame('+201012345678', PhoneNumber::toE164('EG', '01012345678'));
        $this->assertSame('+201012345678', PhoneNumber::toE164('EG', '1012345678'));
        $this->assertTrue(PhoneNumber::isValid('EG', '01012345678'));
        $this->assertFalse(PhoneNumber::isValid('EG', '0223456789'));
        $this->assertSame('EG', PhoneNumber::countryFromStored('+201012345678'));
        $this->assertSame('1012345678', PhoneNumber::nationalFromStored('+201012345678', 'EG'));
        $this->assertSame('+20 1012345678', PhoneNumber::format('+201012345678'));
        $this->assertSame('🇪🇬 +20', PhoneNumber::compactOptions()['EG']);
    }
}
