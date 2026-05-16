<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SalesReportMail extends Mailable
{

    public function __construct(
        public readonly string $period,       // daily | weekly | monthly | yearly
        public readonly string $dateFrom,
        public readonly string $dateTo,
        public readonly string $periodLabel,
        public readonly array  $stats,        // revenue, txn_count, avg_basket, items_sold
        public readonly array  $branchBreakdown,
        public readonly array  $topProducts,
    ) {}

    public function envelope(): Envelope
    {
        $titles = [
            'daily'   => 'Daily Sales Report',
            'weekly'  => 'Weekly Sales Report',
            'monthly' => 'Monthly Sales Report',
            'yearly'  => 'Yearly Sales Report',
        ];

        return new Envelope(
            subject: '[Vapour Garage] ' . ($titles[$this->period] ?? 'Sales Report') . ' — ' . $this->periodLabel,
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.sales-report');
    }
}
