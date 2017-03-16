<?php

if ( !empty( $_FILES ) ) {
    $tempPath = $_FILES[ 'logo' ][ 'tmp_name' ];
    $uploadPath = './upload' . '/' . $_FILES[ 'logo' ][ 'name' ];
    if (move_uploaded_file( $tempPath, $uploadPath )) {
        $answer = [
            'fielePath' => $uploadPath,
            'msg' => 'ok'
        ];
        $json = json_encode($answer);
        echo $json;die;
    } else {
        $answer = [
            'fielePath' => '',
            'msg' => 'failed'
        ];
        $json = json_encode($answer);
        echo $json;die;
    }
} else {

    echo 'No files';

}

?>