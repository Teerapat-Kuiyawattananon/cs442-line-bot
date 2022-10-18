<?php

namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\Exception\InvalidSignatureException;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class ProductController extends Controller
{
    public function webhook(Request $request) {
        $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(config('linebot.access'));
        $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => config('linebot.secret')]);
////        $response = $bot->getMessageContent('<messageId>');
////        if ($response->isSucceeded()) {
////            $tempfile = tmpfile();
////            fwrite($tempfile, $response->getRawBody());
////        } else {
////            error_log($response->getHTTPStatus() . ' ' . $response->getRawBody());
////        }
//
        $signature = $request->header(\LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE);
        if (empty($signature)) {
            abort(400);
        }
//
        Log::info($request->getContent());
        try {
            $events = $bot->parseEventRequest($request->getContent(), $signature);
        } catch (InvalidSignatureException $e) {
            Log::error('Invalid signature');
            abort(400, 'Invalid signature');
        } catch (InvalidEventRequestException $e) {
            Log::error('Invalid event request');
            abort(400, 'Invalid event request');
        }
//
        foreach ($events as $event) {
            if (!($event instanceof MessageEvent)) {
                Log::info('Non message event has come');
                continue;
            }

            if (!($event instanceof MessageEvent\TextMessage)) {
                Log::info('Non text message has come');
//                $bot->replyText($event->getReplyToken(), 'non text');
//                continue;
            } else {
//                $bot->replyText($event->getReplyToken(), 'non text 2');
                $inputText = $event->getText();
                $replyText = '';
            }

            if (!($event instanceof MessageEvent\StickerMessage)) {
                Log::info('Non sticker message has come');
//                continue;
            } else {
//                $bot->replyText($event->getReplyToken(), 'is sticker');
                $inputSticker = $event->getStickerId();
                $inputPackageId = $event->getPackageId();
                $bot->replyText($event->getReplyToken(), "PACKAGE ID : {$inputPackageId} STICKER ID : {$inputSticker}");
            }

            if ($inputText === 'give me 10 scores') {
                $count = Product::count();
                $id = random_int(1, $count);
                $product = Product::find($id);
                $replyText = "Name : {$product->name} Price : {$product->price}";

            } else {
                Log::info('inputText: ' . $inputText);
            }

            $replyToken = $event->getReplyToken();
            $userId = $event->getUserId();
            $profile = $bot->getProfile($userId);
            $profile = $profile->getJSONDecodedBody();
            $displayName = $profile['displayName'];
            $pictureUrl = $profile['pictureUrl'];
            if (isset($profile['statusMessage'])) {
                $statusMessage = $profile['statusMessage'];
            } else {
                $statusMessage = '';
            }

            if ($replyText !== '') {
                $response = $bot->replyText($replyToken, $replyText);

                Log::info($response->getHTTPStatus().':'.$response->getRawBody());
            } else {
                $multiMessageBuilder = new MultiMessageBuilder();
                $multiMessageBuilder->add(new TextMessageBuilder($displayName));
                $multiMessageBuilder->add(new TextMessageBuilder($statusMessage));
                $multiMessageBuilder->add(new ImageMessageBuilder($pictureUrl, $pictureUrl));
                $response = $bot->replyMessage($replyToken, $multiMessageBuilder);
            }
        }

        return response()->json([]);
    }

    public function store(Request $request) {
        $product = new Product();
        $product->name = $request->get('name');
        $product->price = $request->get('price');
        if ($product->save()) {
            $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(config('linebot.access'));
            $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => config('linebot.secret')]);

            $replyText = "ID : {$product->id} Name : {$product->name} Price : {$product->price}";
            $messageBuilder = new TextMessageBuilder($replyText);
            $bot->broadcast($messageBuilder);
            return response()->json([
                'success' => true,
                'message' => 'product created '. $product->id
            ], Response::HTTP_CREATED);
        }

        return response()->json([
            'success' => false,
            'message' => 'create failed'
        ], Response::HTTP_BAD_REQUEST);
    }
}
