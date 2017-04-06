<?php

/**
 * Handle images with Imagick
 * User: hoangduy
 * Date: 11/12/15
 * Time: 4:01 PM
 */
class Image_handler
{
    /*
     * Rotate an image from an Imagick object source
     * RETURN: new image geometry
     */
    public function auto_rotate_image(Imagick &$image)
    {
        $orientation = $image->getImageOrientation();

        switch ($orientation) {
            case imagick::ORIENTATION_BOTTOMRIGHT:
                $image->rotateimage("#000", 180); // rotate 180 degrees
                break;

            case imagick::ORIENTATION_RIGHTTOP:
                $image->rotateimage("#000", 90); // rotate 90 degrees CW
                break;

            case imagick::ORIENTATION_LEFTBOTTOM:
                $image->rotateimage("#000", -90); // rotate 90 degrees CCW
                break;
        }

        // Now that it's auto-rotated, make sure the EXIF data is correct in case the EXIF gets saved with the image!
        $image->setImageOrientation(imagick::ORIENTATION_TOPLEFT);
        
        //return new width and new height
        return $image->getImageGeometry();
    }

    /*
     * Rotate an image and save to file
     * Return: new image geometry if success, FALSE otherwise
     */
    public function auto_rotate_file($image_path, $overwrite = FALSE, $suffix = '_rotated')
    {
        if (file_exists($image_path)) {
            $path_info = pathinfo($image_path);
        } else {
            return FALSE;
        }

        $image = new Imagick($image_path);
        $new_image_geometry = $this->auto_rotate_image($image);

        //Save to file
        if ($overwrite) {
            $new_image_path = $image_path;
        } else {
            $new_image_path = $path_info['dirname'] . '/' . $path_info['filename'] . $suffix . '.' . $path_info['extension'];
        }

        try {
            $image->writeImage($new_image_path);
        } catch (Exception $e) {
            return FALSE;
        }
        return $new_image_geometry;
    }

}