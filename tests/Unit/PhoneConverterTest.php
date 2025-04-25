<?php

declare(strict_types=1);

use App\Library\Converters\Phone as PhoneConverter;

describe('Phone Converter', function () {
    it('does not convert nullable value', function () {
        expect(PhoneConverter::clear(NULL))->toBe(NULL);
    });
    it('converts to not nullable value', function () {
        $phone = fake()->phoneNumber();
        expect(PhoneConverter::clear($phone))->not->toBe(NULL);
    });
    it('converts phone correctly', function () {
        $phone = fake()->phoneNumber();
        $phoneConverted = PhoneConverter::clear($phone);
        expect($phoneConverted)->not->toBe($phone);
    });
});
