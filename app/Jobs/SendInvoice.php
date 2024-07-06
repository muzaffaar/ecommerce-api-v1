<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Log;

class SendInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Generate the PDF
        $pdf = Pdf::loadView('invoices.invoice', ['order' => $this->order])->output();

        // Send the email with the PDF attached
        Mail::to($this->order->user->email)->send(new InvoiceMail($this->order, $pdf));
    }

    public function failed(Exception $exception)
    {
        // Handle the failure logic here
        Log::error('Job failed', ['customer_email' => $this->order->user->email, 'order_id' => $this->order->order_id, 'error' => $exception->getMessage()]);
    }
}
