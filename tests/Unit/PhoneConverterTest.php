<?php

declare(strict_types=1);

use App\Library\Converters\Phone as PhoneConverter;

describe('Phone Converter', function () {
    it('does not clear nullable value', function () {
        expect(PhoneConverter::clear(NULL))->toBe(NULL);
    });
    it('clears to not nullable value', function () {
        $phone = fake()->phoneNumber();
        expect(PhoneConverter::clear($phone))->not->toBe(NULL);
    });
    it('clears phone correctly', function () {
        $phone = fake()->phoneNumber();
        $phoneConverted = PhoneConverter::clear($phone);
        expect($phoneConverted)->not->toBe($phone);
    });
    it('does not chop nullable value', function () {
        expect(PhoneConverter::chopSeparators(NULL))->toBe(NULL);
    });
    it('chops to not nullable value', function () {
        $phone = fake()->phoneNumber();
        expect(PhoneConverter::chopSeparators($phone))->not->toBe(NULL);
    });
    it('chops phone correctly', function () {
        $phone = fake()->phoneNumber();
        $phoneConverted = PhoneConverter::clear($phone);
        expect($phoneConverted)->not->toBe($phone);
    });
});
