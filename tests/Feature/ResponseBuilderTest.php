<?php

use App\Library\Builders\Phrase;
use App\Library\Builders\Response;
use App\Library\Enums\PhraseKey;
use Illuminate\Http\Response as HttpResponse;

describe('Response Builder', function () {
    it('runs invalidJSON correctly', function () {
        $msg = Phrase::pickSentence(PhraseKey::ParameterRequired)->toString();
        $response = Response::invalidJSON($msg);
        expect($response->original)->toMatchArray([
            'errors' => ['status' => [$msg]]
        ]);
    });
    it('runs successJSON no data and complete properties correctly', function () {
        $response = Response::successJSON();
        expect($response->original->count())->toBe(0);
    });
    it('runs successJSON no data property and with complete property correctly', function () {
        $response = Response::successJSON(complete: TRUE);
        expect($response->original)->toMatchArray([
            'message' => 'OK',
            'status' => HttpResponse::HTTP_OK,
            'data' => null,
        ]);
    });
    it('runs successJSON with data property and no complete property correctly', function () {
        $data = 'Executed!';
        $response = Response::successJSON(data: $data);
        expect($response->original)->toBe($data);
    });
    it('runs successJSON with data and complete properties correctly', function () {
        $data = 'Executed!';
        $response = Response::successJSON(data: $data, complete: TRUE);
        expect($response->original)->toMatchArray([
            'message' => 'OK',
            'status' => HttpResponse::HTTP_OK,
            'data' => $data,
        ]);
    });
});
