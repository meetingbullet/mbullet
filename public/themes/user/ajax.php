<?php 
// Set default message template type
if (! empty($message) && $message_type === null) {
	$message_type = 'info';
}

echo json_encode([
	"close_modal" => isset($close_modal) ? $close_modal : 1,
	"modal_content" => isset($content) ? $content : Template::content(),
	"message_type" => isset($message_type) ? $message_type : null,
	"message" => isset($message) ? $message : '',
]);