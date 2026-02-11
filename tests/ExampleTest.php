<?php

it('can display protocol information', function () {
    $this->artisan('protocol:info')
        ->expectsOutput('EdStevo Standards is active!')
        ->assertSuccessful();
});
