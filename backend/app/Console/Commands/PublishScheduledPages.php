<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

class PublishScheduledPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:publish-scheduled-pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish scheduled pages automatically';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = Page::where('status', 'draft')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->update([
                'status' => 'published',
            ]);

        $this->info("{$count} scheduled page(s) published successfully.");

        return self::SUCCESS;
    }
}