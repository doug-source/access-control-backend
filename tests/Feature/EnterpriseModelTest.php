<?php

use App\Models\Enterprise;

describe('EnterpriseModel', function () {
    it('initialize correctly', function () {
        $enterpriseList = Enterprise::factory(count: 1)->create();
        expect($enterpriseList->isEmpty())->toBe(FALSE);
    });
    it('initialize with id equal 1 correctly', function () {
        $enterpriseList = Enterprise::factory(count: 1)->create();
        expect($enterpriseList->first()->id)->toBe(1);
    });
    it('initialize with name property correctly', function () {
        $enterpriseList = Enterprise::factory(count: 1)->create();
        expect($enterpriseList->first()->name)->not()->toBe(NULL);
    });
    it('initialize with icon property correctly', function () {
        $enterpriseList = Enterprise::factory(count: 1)->create();
        expect($enterpriseList->first()->icon)->not()->toBe(NULL);
    });
});
