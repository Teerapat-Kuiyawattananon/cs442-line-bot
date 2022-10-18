<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class lineMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:pushMessage {string}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $message = $this->argument('string');
        $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(config('linebot.access'));
        $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => config('linebot.secret')]);

        $userId = "U7e34782d5ddff5b68b00a2ca9e5fbc3d";

        $bot->pushMessage($userId, new TextMessageBuilder($message));
        $bot->pushMessage($userId, new StickerMessageBuilder('2', '144'));
        return 0;
    }
}
