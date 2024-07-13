<?php

namespace App\Console\Commands\DuskCommands;

use Illuminate\Console\Command;

class RunPaymentSuccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It will run dusk command';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = $this->call('dusk',['--filter'=>'testPaymentByEnteringReturnedPayPalLink','--path'=>'tests/Browser/PaymentTest.php', '--browse' => true]);
    }
}
