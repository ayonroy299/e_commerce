<?php

namespace App\Services;

use App\Models\NotificationJob;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send a notification using a template.
     */
    public function send(string $templateCode, $recipient, array $data = [], ?int $branchId = null)
    {
        $template = NotificationTemplate::where('code', $templateCode)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            Log::warning("Notification template not found: {$templateCode}");
            return null;
        }

        $job = NotificationJob::create([
            'branch_id' => $branchId ?? $recipient->branch_id ?? null,
            'template_id' => $template->id,
            'recipient_type' => get_class($recipient),
            'recipient_id' => $recipient->id,
            'payload' => $data,
            'status' => 'pending',
        ]);

        try {
            foreach ($template->channels as $channel) {
                $this->dispatchToChannel($channel, $template, $recipient, $data);
            }

            $job->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            $job->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            Log::error("Failed to send notification: " . $e->getMessage());
        }

        return $job;
    }

    protected function dispatchToChannel(string $channel, $template, $recipient, array $data)
    {
        $subject = $this->render($template->subject, $data);
        $body = $this->render($template->body, $data);

        match ($channel) {
            'email' => $this->sendEmail($recipient, $subject, $body),
            'sms' => $this->sendSms($recipient, $body),
            'whatsapp' => $this->sendWhatsApp($recipient, $body),
            default => Log::warning("Unsupported channel: {$channel}"),
        };
    }

    protected function render(string $string, array $data): string
    {
        foreach ($data as $key => $value) {
            $string = str_replace("{{ $key }}", $value, $string);
            $string = str_replace("{{$key}}", $value, $string);
        }
        return $string;
    }

    protected function sendEmail($recipient, string $subject, string $body)
    {
        if (empty($recipient->email)) return;

        Mail::raw($body, function ($message) use ($recipient, $subject) {
            $message->to($recipient->email)
                ->subject($subject);
        });
    }

    protected function sendSms($recipient, string $body)
    {
        if (empty($recipient->phone)) return;
        
        Log::info("Simulating SMS to {$recipient->phone}: {$body}");
        // Integrate with SMS Gateway (e.g. Twilio, Infobip)
    }

    protected function sendWhatsApp($recipient, string $body)
    {
        if (empty($recipient->phone)) return;

        Log::info("Simulating WhatsApp to {$recipient->phone}: {$body}");
        // Integrate with WhatsApp Business API
    }
}
