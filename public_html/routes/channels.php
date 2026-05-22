<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('App.Models.Admin.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    return \App\Models\ConversationParticipant::where('conversation_id', $conversationId)
        ->where('participant_id', $user->id)
        ->where('participant_type', get_class($user))
        ->exists();
});
