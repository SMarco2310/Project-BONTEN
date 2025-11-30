<?php


function get_event_image_path($image_path, $default_image = '../public/assets/hero.png') {
    if (empty($image_path)) {

        return $default_image;
    }


    if (strpos($image_path, '../public/') === 0) {
        return substr($image_path, 3);

    }


    if (strpos($image_path, 'public/') === 0) {


        return '../' . $image_path;
    }


    if (strpos($image_path, '/') === false) {

        return '../public/assets/' . $image_path;
    }


    if (strpos($image_path, 'assets/') === 0) {
        return '../public/' . $image_path;

    }


    return $image_path;
}



function get_profile_picture_path($profile_picture, $default_image = '../public/assets/user.jpg') {

    if (empty($profile_picture)) {

        return $default_image;

    }


    if (strpos($profile_picture, '/') === false) {
        return '../public/assets/' . $profile_picture;
    }

    return $profile_picture;
}
?>
