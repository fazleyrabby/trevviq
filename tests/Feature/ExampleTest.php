<?php

it('renders the public home page', function () {
    $this->get('/')->assertOk();
});
