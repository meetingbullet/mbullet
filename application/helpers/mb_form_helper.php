<?php defined('BASEPATH') || exit('No direct script access allowed');
/**
 * Meeting Bullet Form Helper
 *
 * @package   Meeting Bullet
 * @author    Viet Hoang Duc
 */

/**
 * Form helper functions
 *
 * Creates HTML5 extensions for the standard CodeIgniter form helper.
 *
 * These functions also wrap the form elements as necessary to create the
 * styling that the Bootstrap-inspired admin theme requires to make it as simple
 * as possible for a developer to maintain styling with the core. Also makes
 * changing the core a snap.
 *
 * All methods (including overridden versions of the originals) now support
 * passing a final 'label' attribute that will create the label along with the
 * field.
 *
 */

if (! function_exists('mb_form_input')) {
	/**
	 * Returns a properly templated text input field.
	 *
	 * @return string The formatted input element, label tag and wrapping divs.
	 */
	function mb_form_input($type = 'text', $name, $label = '', $required = false, $value = '', $class = 'an-form-control', $addon = '', $placeholder = '', $tooltip = '')
	{
		return "
<div class=\"row\">
	<div class=\"col-md-3 col-sm-12\">
		". iif($label, "<label for=\"$name\" class=\"pull-right\">$label". iif($required, '<span class="required">*</span>') ."</label>") ."
	</div>
	<div class=\"col-md-9 col-sm-12\">
		". iif($addon, "<div class=\"an-input-group\">
			<div class=\"an-input-group-addon\">$addon</div>") ."
			<input type=\"$type\" name=\"$name\" class=\"$class". iif( form_error($name) , ' danger') ."\" placeholder=\"$placeholder\" value=\"". ($value ? set_value($name, $value) : set_value($name)) ."\"/>
		". iif($addon, "</div>") ."
		". iif($tooltip, "<p class=\"an-small-doc-block\">$tooltip</p>") ."
	</div>
</div>
";
	}

	/**
	 * Returns a properly templated text input field.
	 *
	 * @return string The formatted input element, label tag and wrapping divs.
	 */
	function mb_form_input_placeholder($type = 'text', $name, $label = '', $required = false, $value = '', $class = 'an-form-control', $addon = '', $placeholder = '', $tooltip = '')
	{
		return iif($label, "<label for=\"$name\">$label". iif($required, '<span class="required">*</span>') ."</label>") ."
		". iif($addon, "<div class=\"an-input-group\">
			<div class=\"an-input-group-addon\">$addon</div>") ."
			<input type=\"$type\" name=\"$name\" class=\"$class". iif( form_error($name) , ' danger') ."\" placeholder=\"$placeholder\" value=\"". ($value ? set_value($name, $value) : set_value($name)) ."\"/>
		". iif($addon, "</div>") ."
		". iif($tooltip, "<p class=\"an-small-doc-block\">$tooltip</p>");
	}
}