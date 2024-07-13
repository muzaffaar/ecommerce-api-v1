<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Session;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Facades\File;

class PaymentTest extends DuskTestCase
{
    public function testPaymentByEnteringReturnedPayPalLink()
    {
        $link = File::get(storage_path('app/paypal_link.txt'));

        $this->browse(function (Browser $browser) use ($link) {
            $browser->visit($link)
                    ->pause(4000)
                    ->type('login_email', "huypomish@gmail.com")
                    ->screenshot('username')
                    ->click('#btnNext')
                    ->pause(4000)
                    ->type('login_password', "123123123")
                    ->screenshot('pass')
                    ->click('#btnLogin')
                    ->pause(4000) 
                    ->click('#payment-submit-btn')
                    ->screenshot('submit')
                    ->pause(4000);
                    
            $browser->driver->switchTo()->window(collect($browser->driver->getWindowHandles())->last());

            $capturedLink = $browser->driver->getCurrentURL();

            echo "Captured Link: " . $capturedLink . "\n";
            $filePath = storage_path('app/paypal_success.txt');
            File::put($filePath, $capturedLink);

            // Verify if the file was successfully written
            if (File::exists($filePath)) {
                echo "Captured link saved to: " . $filePath . "\n";
            } else {
                echo "Failed to save captured link to file: " . $filePath . "\n";
            }
        });
    }
}
