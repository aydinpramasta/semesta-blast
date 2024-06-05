<?php

namespace App\Console\Commands;

use App\Enums\RecipientStatus;
use App\Imports\BlastEmailRecipients;
use App\Models\Recipient;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use function Laravel\Prompts\{progress, spin, table};

class BlastSendEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'semesta:blast-send-email
                            {spreadsheet : Spreadsheet file containing recipient\'s email & content}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'SEMESTA blast send email';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $spreadsheetFile = $this->argument('spreadsheet');

        if (! File::exists($spreadsheetFile)) {
            $this->components->error('The provided file path is not found.');
            return;
        }

        if (! in_array(File::extension($spreadsheetFile), ['xlsx', 'xls', 'csv'])) {
            $this->components->error('The provided file is not supported.');
            return;
        }

        [$recipients, $errors] = spin(
            function () use ($spreadsheetFile) {
                $recipients = Excel::toCollection(new BlastEmailRecipients, $spreadsheetFile)->flatten(depth: 1);

                return $this->validateRecipientsData($recipients->toArray());
            },
            'Loading spreadsheet file...'
        );

        if (! empty($errors)) {
            $this->components->error('Failed to load spreadsheet file because validation failed.');

            table(
                rows: collect($errors)
                    ->map(fn ($error) => ['error' => $error])
            );

            return;
        }

        $recipients = collect($recipients);

        $responses = collect();
        $chunkSize = 100;

        $progress = progress(label: 'Blast sending emails...', steps: $recipients->count());
        $progress->start();
        foreach ($recipients->chunk($chunkSize) as $recipientsChunk) {
            $chunkResponses = $this->blastSendEmails($recipientsChunk);
            Log::debug('Responses', $chunkResponses->toArray());
            $responses->push(...$chunkResponses);
            $progress->advance($chunkSize);
        }
        $progress->finish();

        [$successfulEmails, $failedEmails] = $this->filterResponses($recipients, $responses);

        if ($successfulEmails->isNotEmpty()) {
            spin(
                fn () => $this->saveSuccessfulEmails($successfulEmails),
                'Saving successful recipients to database...',
            );
        }

        if ($failedEmails->isNotEmpty()) {
            $path = spin(
                fn () => $this->saveFailedEmails($failedEmails),
                'Writing failed recipients to a file...',
            );

            $this->components->info("Failed recipients path: {$path}");
        }
    }

    private function validateRecipientsData(array $recipients): array
    {
        $recipients = ['recipients' => $recipients];

        $validator = Validator::make($recipients, [
            'recipients' => ['required', 'array'],
            'recipients.*' => ['required', 'array'],
            'recipients.*.email' => ['required', 'email'],
            'recipients.*.name' => ['required', 'string'],
            'recipients.*.content' => ['required', 'string'],
            'recipients.*.button_link' => ['required', 'url'],
        ], attributes: [
            'recipients.*.email' => 'data on position :first-position is invalid. The email',
            'recipients.*.name' => 'data on position :first-position is invalid. The name',
            'recipients.*.content' => 'data on position :first-position is invalid. The content',
            'recipients.*.button_link' => 'data on position :first-position is invalid. The button link',
        ]);

        if ($validator->fails()) {
            return [null, $validator->errors()->all()];
        }

        return [$validator->validated()['recipients'], null];
    }

    private function blastSendEmails(Collection $recipients): Collection
    {
        $url = config('services.sevima-notification-service.url').'/api/v1/notifications';
        $headers = [
            'app-id' => config('services.sevima-notification-service.app_id'),
            'app-secret' => config('services.sevima-notification-service.app_secret'),
        ];

        $responses = Http::pool(
            fn (Pool $pool) => $recipients
                ->map(fn (array $recipient) => $pool->withHeaders($headers)->post($url, [
                    'channel' => 'email',
                    'subject' => 'Test',
                    'sender_name' => 'noreply SEMESTA',
                    'from' => 'noreply@blast-semesta.com',
                    'to' => $recipient['email'],
                    'message' => view('emails.blast-template', ['content' => $recipient['content']])->render(),
                ]))
                ->toArray()
        );

        return collect($responses);
    }

    private function filterResponses(Collection $recipients, Collection $responses): array
    {
        $recipientsArray = $recipients->toArray();

        $data = collect();

        foreach ($responses as $index => $response) {
            $data->push([
                'request' => $recipientsArray[$index],
                'response' => $response instanceof Response ? $response->json() : null,
                'success' => $response instanceof  Response ? $response->accepted() : false,
            ]);
        }

        return [
            $data->filter(fn (array $recipient) => $recipient['success']),
            $data->filter(fn (array $recipient) => ! $recipient['success']),
        ];
    }

    private function saveSuccessfulEmails(Collection $successfulEmails): void
    {
        $now = now()->toDateTimeString();

        foreach ($successfulEmails->chunk(100) as $chunk) {
            Recipient::query()->insert(
                $chunk->map(fn (array $email) => [
                    'email' => $email['request']['email'],
                    'content' => $email['request']['content'],
                    'status' => RecipientStatus::Requested->value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->toArray(),
            );
        }
    }

    private function saveFailedEmails(Collection $failedEmails)
    {
        $filename = date('Y_m_d_His').'_failed_blast_emails.csv';

        Storage::put($filename, 'Email,Name,Content,Button Link');

        /** @var Collection $chunk */
        foreach ($failedEmails->chunk(100) as $chunk) {
            $contents = $chunk->map(
                fn (array $email) => "{$email['request']['email']},{$email['request']['name']},{$email['request']['content']},{$email['request']['button_link']}"
            )->join(PHP_EOL);

            Storage::append($filename, $contents);
        }

        return Storage::path($filename);
    }
}
