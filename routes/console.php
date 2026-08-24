<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/** One-off smoke test for the Mailtrap integration — confirms the API key actually works end to end. */
Artisan::command('send-mail', function () {
    $email = (new MailtrapEmail)
        ->from(new Address('hello@demomailtrap.co', 'Marketplace Pop Culture'))
        ->to(new Address('ahmedhusain1998@gmail.com'))
        ->subject('Test Mailtrap — Marketplace Pop Culture')
        ->category('Integration Test')
        ->text('Cet email confirme que l\'intégration Mailtrap fonctionne.');

    $response = MailtrapClient::initSendingEmails(
        apiKey: config('services.mailtrap-sdk.apiKey')
    )->send($email);

    $this->info('Email envoyé.');
    dump(ResponseHelper::toArray($response));
})->purpose('Send a Mailtrap test email');
