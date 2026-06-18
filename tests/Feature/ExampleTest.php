<?php

test('the application redirects the home page to the feedback dashboard', function () {
    $this->get('/')->assertRedirect(route('feedback.index'));
});
