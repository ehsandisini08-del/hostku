<?php

test('root redirects to home page', function () {
    $response = $this->get('/');

    $response->assertRedirect('/home');
});
