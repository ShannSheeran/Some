<?php

class Uplod{
    protected $size;
    protected $type;
    protected $allow_type;

    public function setSize($size)
    {
        $this->size=$size;
    }

    public function checkSize($config)
    {
        if ($this->size>$config['size']) {
            return false;
        } else {
            return true;
        }
    }
}

if ( !empty( $_FILES ) ) {

    $tempPath = $_FILES[ 'file' ][ 'tmp_name' ];
    $uploadPath = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $_FILES[ 'file' ][ 'name' ];

    move_uploaded_file( $tempPath, $uploadPath );
    $answer = [
        'fielePath' => $uploadPath,
        'msg' => 'ok'
    ];
    $json = json_encode($answer);
    echo $json;

} else {
    echo 'No files';
}

?>