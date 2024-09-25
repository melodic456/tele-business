<?php
// TOKEN
$token = "7280128962:AAH1_W-4o6I1z6-00nnSJ9v_-drn2PFBqD0"; // bot token
$admin = "7374728124"; // userID of your account



// Added Functions

function removeElementFromArray(&$array, $valueToRemove) {
    $key = array_search($valueToRemove, $array);
    if ($key !== false) {
        unset($array[$key]);
        return true; // Indicate successful removal
    } else {
        return false; // Indicate that the value was not found
    }
}


function loadData() {
    $filePath = 'messages.json'; 
    if (file_exists($filePath)) {
        $jsonData = file_get_contents($filePath);
        return json_decode($jsonData, true);
    } else {
        return []; 
    }
}

// Function to save data back to the JSON file
function saveData($data) {
    $filePath = 'messages.json';
    $jsonData = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($filePath, $jsonData);
}

// BOT
function bot($method, $datas = [])
{
    global $token;
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => 'https://api.telegram.org/bot' . $token . '/' . $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => $datas
    ));
    return json_decode(curl_exec($ch));
}
// ================================================ \\

//UPDATE
$update = json_decode(file_get_contents('php://input'));
if (isset($update)) {
    @$message = $update->message;
    if (isset($message)) {
        @$text = $message->text;
        @$chat_id = $message->chat->id;
        @$caption = $message->caption;
        //file id
        @$sticker_id = $message->sticker->file_id;
//        @$photo_id = $message->photo[count($message->photo) - 1]->file_id ?? null;
	@$photo_id = null;
	if (isset($message->photo) && is_array($message->photo)) {
            @$photo_id = $message->photo[count($message->photo) - 1]->file_id;
        }
        @$video_id = $message->video->file_id;
        @$voice_id = $message->voice->file_id;
        @$file_id = $message->document->file_id;
        @$music_id = $message->audio->file_id;
        @$animation_id = $message->animation->file_id;
        @$video_note_id = $message->video_note->file_id;
    }

    // business updates
    if (isset($update->business_message)) {
        @$b_message = $update->business_message;
        @$b_id = $b_message->business_connection_id;
        @$b_text = $b_message->text;
        @$b_message_id = $b_message->message_id;
        @$b_chat_id = $b_message->chat->id;
    }
}
// db
$db =  json_decode(file_get_contents('db.json'), true);
$step = $db['step'];
// keyboards
$home = json_encode(['resize_keyboard' => true, 'keyboard' => [[['text' => "Add auto reply ✉️"]], [['text' => "remove auto reply 🚫"]], [['text' => "Add Interval"]], [['text' => "Add new Admins"]], [['text' => "Remove an Admin"]]]]);
$back = json_encode(['resize_keyboard' => true, 'keyboard' => [[['text' => "Back 🔙"]]]]);
// ================================================ \\

// strat message
if (isset($message) and $chat_id == $admin) {

    //handle text messages
    if ($text == '/start') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "Hi welcome to Business Account Manager Bot! 🤖\n\nTo use the bot, just go to the telegram business section in your profile, enter the chatbot section and enter the bot username. 💼\n\nNote: Only premium users can use this option. ℹ️", 'reply_markup' => $home]);
    } elseif ($text == 'Back 🔙' || $text == "Done!") {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "Hi welcome to Business Account Manager Bot! 🤖\n\nTo use the bot, just go to the telegram business section in your profile, enter the chatbot section and enter the bot username. 💼\n\nNote: Only premium users can use this option. ℹ️", 'reply_markup' => $home]);
        $db['step'] = "";
        file_put_contents("db.json", json_encode($db));
    }
    // add AUTO-REPLY
    elseif ($text == 'Add auto reply ✉️') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "To set up an auto-reply, type the message you want the bot to reply to (you'll send a reply to this text in the next step)", 'reply_markup' => $back]);
        $db['step'] = "add-1";
        file_put_contents("db.json", json_encode($db));
    }
    // remove existing auto-reply 
    elseif ($text == 'remove auto reply 🚫') {
        if (count($db['data']) > 0) {
            foreach ($db['data'] as $item) {
                $list .= "<code>{$item['text']}</code>\n---\n";
            }
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => $list, 'parse_mode' => 'html',]);
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => "To remove an item from the auto-reply, copy and paste one of the above", 'reply_markup' => $back]);
            $db['step'] = "remove";
            file_put_contents("db.json", json_encode($db));
        } else {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => "auto-reply list is empty!", 'reply_markup' => $home]);
        }
    }
    elseif ($text == 'Add Interval') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "Enter the interval in seconds for the auto-reply to be sent", 'reply_markup' => $back]);
        $db['step'] = "add-interval";
        file_put_contents("db.json", json_encode($db));
    }
    elseif ($step == 'add-interval') {
        $db['interval'] = $text;
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "Interval set to {$text} seconds", 'reply_markup' => $home]);
        $db['step'] = "";
        file_put_contents("db.json", json_encode($db));
    }

    elseif ($text == 'Add new Admins') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "Enter the user ID of the new admin", 'reply_markup' => $back]);
        $db['step'] = "add-admin";
        file_put_contents("db.json", json_encode($db));
    } elseif ($step == 'add-admin') {
        if (!in_array($text, $db['admins'])) {
            $db['admins'][] = $text;
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => "Admin added successfully!", 'reply_markup' => $home]);
            $db['step'] = "";
            file_put_contents("db.json", json_encode($db));
        } else {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => "This user is already an admin!", 'reply_markup' => $home]);
        }
    }

    elseif ($text == 'Remove an Admin') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "Enter the user ID of the admin you want to remove", 'reply_markup' => $back]);
        $db['step'] = "remove-admin";
        file_put_contents("db.json", json_encode($db));
    } elseif ($step == 'remove-admin') {
        if (in_array($text, $db['admins'])) {
            removeElementFromArray($db['admins'], $text);
            // $db['admins'] = array_remove($db['admins'], $text);
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => "Admin Removed successfully!", 'reply_markup' => $home]);
            $db['step'] = "";
            file_put_contents("db.json", json_encode($db));
        } else{
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => "Admin not found!", 'reply_markup' => $home]);
        }
    }
    // handle text messages
    // else {
    //     bot('sendMessage', ['chat_id' => $chat_id, 'text' => "Invalid command! ❌", 'reply_markup' => $home]);
    // }

    // handle steps
    elseif ($step == 'add-1') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "✅ Successfully created.\n\nSend your content to answer this text (it can include any type of content such as: text, photo, video, gif, sticker, voice, etc.)", 'reply_markup' => $back]);
        $db['data'][] = [
            'text' => $text,
            'answers' => []
        ];
        $db['step'] = "add-2";
        file_put_contents("db.json", json_encode($db));
    } elseif ($step == 'add-2') {
        end($db['data']);
        $last_key = key($db['data']);

        // check message type
        if (isset($text)) {
            $type = "text";

            // convert premium emoji
            if (isset($message->entities)) {
                $i = 0;
                foreach ($message->entities as $entity) {
                    if ($entity->type == "custom_emoji") {
                        $offset = $i + $entity->offset;
                        $emoji = '<tg-emoji emoji-id="' . $entity->custom_emoji_id . '">' . mb_substr($text, $offset, 1, "UTF-8") . '</tg-emoji>';
                        $text = mb_substr($text, 0, $offset, "UTF-8")
                            . $emoji
                            . mb_substr($text, $offset + 1, null, "UTF-8");
                        $i = $i + mb_strlen($emoji) - $entity->length;
                    }
                }
            }
            $content = $text;
        } elseif (isset($sticker_id)) {
            $type = "sticker";
            $content = $sticker_id;
        } elseif (isset($photo_id)) {
            $type = "photo";
            $content = $photo_id;
        } elseif (isset($video_id)) {
            $type = "video";
            $content = $video_id;
        } elseif (isset($voice_id)) {
            $type = "voice";
            $content = $voice_id;
        } elseif (isset($file_id)) {
            $type = "file";
            $content = $file_id;
        } elseif (isset($music_id)) {
            $type = "music";
            $content = $music_id;
        } elseif (isset($animation_id)) {
            $type = "animation";
            $content = $animation_id;
        } elseif (isset($video_note_id)) {
            $type = "video_note";
            $content = $video_note_id;
        }
        if (isset($caption)) {
            // convert premium emoji
            if (isset($message->caption_entities)) {
                $i = 0;
                foreach ($message->caption_entities as $entity) {
                    if ($entity->type == "custom_emoji") {
                        $offset = $i + $entity->offset;
                        $emoji = '<tg-emoji emoji-id="' . $entity->custom_emoji_id . '">' . mb_substr($caption, $offset, 1, "UTF-8") . '</tg-emoji>';
                        $caption = mb_substr($caption, 0, $offset, "UTF-8")
                            . $emoji
                            . mb_substr($caption, $offset + 1, null, "UTF-8");
                        $i = $i + mb_strlen($emoji) - $entity->length;
                    }
                }
            }
        }

        // save 
        if (isset($type) and isset($content)) {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => "✅ The answer has been added to your desired text\n\nYou can submit more content or click on 'Done!' ", 'reply_markup' =>  json_encode(['resize_keyboard' => true, 'keyboard' => [[['text' => "Done!"]]]])]);
            $db['data'][$last_key]["answers"][] = [
                'type' => $type,
                'content' => $content,
                'caption' => $caption
            ];
            file_put_contents("db.json", json_encode($db));
        } else {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => "There was a problem with the content you sent, please send another content", 'reply_markup' =>  json_encode(['resize_keyboard' => true, 'keyboard' => [[['text' => "Done!"]]]])]);
        }
    } elseif ($step == 'remove') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "✅ Successfully removed ", 'reply_markup' => $home]);
        foreach ($db['data'] as $key => $item) {
            if ($item['text'] == $text) {
                unset($db['data'][$key]);
            }
        }
        $db['step'] = "";
        file_put_contents("db.json", json_encode($db));
    }
}

// $originalString = "Hello";
// Handle messages to Bussiness Account
// $send_reply = "yes";
if (isset($b_text)) {
    foreach ($db['data'] as $item) {
        if ($item['text'] == $b_text) {

            $data = loadData();

            // Check if the user already exists
            if (isset($data[$b_chat_id])) {
                $currentTime = time();
            
                // Check for duplicate messages within the last 5 minutes
                foreach ($data[$b_chat_id] as $existingMessage) {
                    $messageTime = strtotime($existingMessage['time']);
                    $timeDifference = $currentTime - $messageTime;
            
                    if ($existingMessage['message'] === $b_text && $timeDifference <= $db['interval']) { // 5 minutes = 300 seconds
                        echo "Duplicate message detected. Please try again later.";
                        exit; // Stop further processing
                    }
                }
            
                // No duplicates, append the new message
                $data[$b_chat_id][] = [
                    'message' => $b_text,
                    'time' => date('Y-m-d H:i:s')
                ];
            } else {
                // New user, create a new entry
                $data[$b_chat_id] = [
                    [
                        'message' => $b_text,
                        'time' => date('Y-m-d H:i:s')
                    ]
                ];
            }
            saveData($data);
            
            // if ($b_id !== "8BVyU4oFSVf6AQAAvTozMBwbhsA"){
                if (!in_array($b_id, $db['admins'])) {
                exit;
            }
            foreach ($item['answers'] as $index => $answer) {
                // check message type
                switch ($answer["type"]) {
                    case "text":
                        bot('sendMessage', ['business_connection_id' => $b_id, 'chat_id' => $b_chat_id, 'text' => $answer['content'] . $b_id, 'parse_mode' => "html", 'disable_web_page_preview' => true, 'reply_parameters' => $index == 0 ? json_encode(['message_id' => $b_message_id]) : null]);
                        break;
                    case "sticker":
                        bot('sendSticker', ['business_connection_id' => $b_id, 'chat_id' => $b_chat_id, 'caption' => $answer['caption'], 'sticker' => $answer['content'], 'parse_mode' => "html", 'disable_web_page_preview' => true, 'reply_parameters' => $index == 0 ? json_encode(['message_id' => $b_message_id]) : null]);

                        break;
                    case "photo":
                        bot('sendPhoto', ['business_connection_id' => $b_id, 'chat_id' => $b_chat_id, 'caption' => $answer['caption'], 'photo' => $answer['content'], 'parse_mode' => "html", 'disable_web_page_preview' => true, 'reply_parameters' => $index == 0 ? json_encode(['message_id' => $b_message_id]) : null]);

                        break;
                    case "video":
                        bot('sendVideo', ['business_connection_id' => $b_id, 'chat_id' => $b_chat_id, 'caption' => $answer['caption'], 'video' => $answer['content'], 'parse_mode' => "html", 'disable_web_page_preview' => true, 'reply_parameters' => $index == 0 ? json_encode(['message_id' => $b_message_id]) : null]);

                        break;
                    case "voice":
                        bot('sendVoice', ['business_connection_id' => $b_id, 'chat_id' => $b_chat_id, 'caption' => $answer['caption'], 'voice' => $answer['content'], 'parse_mode' => "html", 'disable_web_page_preview' => true, 'reply_parameters' => $index == 0 ? json_encode(['message_id' => $b_message_id]) : null]);

                        break;
                    case "file":
                        bot('sendDocument', ['business_connection_id' => $b_id, 'chat_id' => $b_chat_id, 'caption' => $answer['caption'], 'document' => $answer['content'], 'parse_mode' => "html", 'disable_web_page_preview' => true, 'reply_parameters' => $index == 0 ? json_encode(['message_id' => $b_message_id]) : null]);

                        break;
                    case "music":
                        bot('sendAudio', ['business_connection_id' => $b_id, 'chat_id' => $b_chat_id, 'caption' => $answer['caption'], 'audio' => $answer['content'], 'parse_mode' => "html", 'disable_web_page_preview' => true, 'reply_parameters' => $index == 0 ? json_encode(['message_id' => $b_message_id]) : null]);

                        break;
                    case "animation":
                        bot('sendAnimation', ['business_connection_id' => $b_id, 'chat_id' => $b_chat_id, 'caption' => $answer['caption'], 'animation' => $answer['content'], 'parse_mode' => "html", 'disable_web_page_preview' => true, 'reply_parameters' => $index == 0 ? json_encode(['message_id' => $b_message_id]) : null]);

                        break;
                    case "video_note":
                        bot('sendVideoNote', ['business_connection_id' => $b_id, 'chat_id' => $b_chat_id, 'caption' => $answer['caption'], 'video_note' => $answer['content'], 'parse_mode' => "html", 'disable_web_page_preview' => true, 'reply_parameters' => $index == 0 ? json_encode(['message_id' => $b_message_id]) : null]);

                        break;
                }
                // }
            }
        }

        // Save the updated data back to the file
        
        // }
    }
}
// $send_reply = "yes";
