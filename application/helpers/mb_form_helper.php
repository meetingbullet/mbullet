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
    function mb_form_input($type = 'text', $name, $label = '', $required = false, $value = '', $class = 'an-form-control', $addon = '', $placeholder = '', $tooltip = '', $extra = '')
    {
        return "
<div class=\"row\">
    <div class=\"col-md-3 col-sm-12\">
        ". iif($label, "<label for=\"$name\" class=\"pull-right\">$label". iif($required, '<span class="required">*</span>') ."</label>") ."
    </div>
    <div class=\"col-md-9 col-sm-12\">
        ". iif($addon, "<div class=\"an-input-group\">
            <div class=\"an-input-group-addon\">$addon</div>") ."
            <input type=\"$type\" name=\"$name\" $extra class=\"$class". iif( form_error($name) , ' danger') ."\" placeholder=\"$placeholder\" value=\"". ($value ? set_value($name, $value) : set_value($name)) ."\"/>
        ". iif($addon, "</div>") ."
        ". iif($tooltip, "<p class=\"an-small-doc-block\">$tooltip</p>") ."
    </div>
</div>
";
    }
}

if (! function_exists('mb_form_dropdown')) {
	/**
	 * Returns a properly templated dropdown field.
	 *
	 * @return string The formatted input element, label tag and wrapping divs.
	 */
    function mb_form_dropdown($data, $options = array(), $selected = array(), $label = '', $extra = '', $tooltip = '', $required = false)
    {
        if (! is_array($data)) {
            $data = array('name' => $data);
        }

        if (! isset($data['id'])) {
            $data['id'] = $data['name'];
        }

        $output = _parse_form_attributes($data, array());

        if (! is_array($selected)) {
            $selected = array($selected);
        }

        // If no selected option was submitted, attempt to set it automatically
        if (count($selected) === 0) {
            // If the name appears in the $_POST array, grab the value
            if (isset($_POST[$data['name']])) {
                $selected = array($_POST[$data['name']]);
            }
        }

        $options_vals = '';
        foreach ($options as $key => $val) {
            $key = (string) $key;
            if (is_array($val) && ! empty($val)) {
                $options_vals .= "<optgroup label='{$key}'>" . PHP_EOL;

                foreach ($val as $optgroup_key => $optgroup_val) {
                    $sel = in_array($optgroup_key, $selected) ? ' selected="selected"' : '';
                    $options_vals .= "<option value='{$optgroup_key}'{$sel}>{$optgroup_val}</option>" . PHP_EOL;
                }
                $options_vals .= '</optgroup>'.PHP_EOL;
            } else {
                $sel = in_array($key, $selected) ? ' selected="selected"' : '';
                $options_vals .= "<option value='{$key}'{$sel}>{$val}</option>" . PHP_EOL;
            }
        }

        $error = '';
        if (function_exists('form_error') && form_error($data['name'])) {
            $error   = ' error';
            $tooltip = '<span class="help-inline">' . form_error($data['name']) . '</span>';
        }

        return "
<div class=\"row\">
    <div class=\"col-md-3 col-sm-12\">
        ". iif($label, "<label for=\"{$data['id']}\" class=\"pull-right\">{$label}". iif($required, '<span class="required">*</span>') ."</label>") ."
    </div>
    <div class=\"col-md-9 col-sm-12\">
        <select {$output} {$extra}>
            {$options_vals}
        </select>
        {$tooltip}
    </div>
</div>";
    }
}

if (! function_exists('mb_form_input_placeholder')) {
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