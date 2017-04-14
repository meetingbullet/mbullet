<?php 
// Set default message template type
if (! empty($message) && $message_type === null) {
	$message_type = 'info';
}

$return = [
	"close_modal" => $close_modal,
	"modal_content" => isset($content) ? $content : Template::content(),
	"message_type" => $message_type,
	"message" => $message,
];

echo json_encode($return);
?>