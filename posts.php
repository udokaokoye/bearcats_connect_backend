
<?php
include './connection.php';
require_once('./verifyToken.php');
use Ramsey\Uuid\Uuid;



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // verifyToken();
    $userId = $_POST['userId'];
    $caption = $_POST['caption'];
    $location = $_POST['location'];
    $orientation = $_POST['orientation'];
    $postType = 'null';
    $upload_dir = 'Images' . DIRECTORY_SEPARATOR;
    $allImages = [];
    // $taggedUsers = $_POST['taggedUsers'];

    // if ($taggedUsers !== 'null') {
    //     echo json_encode($taggedUsers);
    // } else {
    //     echo json_encode("Not found");
    // }
    // return;

    if (isset($_FILES['files']['error'])) {
        if (isset($_FILES['files'])) {
            foreach ($_FILES['files']['tmp_name'] as $key => $value) {
                $file_tmpname = $_FILES['files']['tmp_name'][$key];
                $file_name = $_FILES['files']['name'][$key];
                $file_size = $_FILES['files']['size'][$key];
                $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                $filepath = $upload_dir . $userId . "_" . $file_name . ".jpeg";
                // echo json_encode($file_name);
                // return;

                if (file_exists($filepath)) {
                    $filepath = $upload_dir . $userId . "_" . Uuid::uuid4() . "_" . ".jpeg";
                    if (move_uploaded_file($file_tmpname, $filepath)) {
                        array_push($allImages, URLROOT . $filepath);
                    }
                    // else {                    
                    //     echo "Error uploading {$file_name} <br />";
                    // }

                } else {
                    if (move_uploaded_file($file_tmpname, $filepath)) {
                        array_push($allImages, URLROOT . $filepath);
                    }
                    // else {                    
                    //     echo "Error uploading {$file_name} <br />";
                    // }
                }
            }
        }
    }


    $query = "INSERT INTO `posts` (`user_id`, `caption`, `location`, `type`) VALUES (
        '" . mysqli_real_escape_string($link, $userId) . "', 
        '" . mysqli_real_escape_string($link, $caption) . "', 
        '" . mysqli_real_escape_string($link, $location) . "', 
        '" . mysqli_real_escape_string($link, $postType) . "'
    )";

    if (mysqli_query($link, $query)) {

        $query = "SELECT id FROM `posts` WHERE `user_id` = '$userId' ORDER BY `id` DESC LIMIT 1";
        $result = mysqli_query($link, $query);
        $postId = mysqli_fetch_array($result)[0];

        $serializedImages = serialize($allImages);
        $serializedOrientations = serialize($orientation);

        // ! PERFORM NEXT QUERY TO INSERT DATA INTO THE DATABASE.
        // foreach ($allImages as $key => $value) {
        if (count($allImages) > 0) {
            $query = "INSERT INTO `post_media` (`post_id`, `media_url`, `orientation`) VALUES (
        '" . mysqli_real_escape_string($link, $postId) . "', 
        '" . mysqli_real_escape_string($link, $serializedImages) . "', 
        '" . mysqli_real_escape_string($link, $serializedOrientations) . "'
    )";

            mysqli_query($link, $query);
        }

        if (isset($_POST['taggedUsers'])) {
            $tagUsers = explode(',', $_POST['taggedUsers']);
            // echo json_encode($_POST['taggedUsers']);
            // return;
            foreach ($tagUsers as $key => $value) {
                // echo json_encode($value);
                // return;
                $tagUserQuery = "INSERT INTO `postTags` (`post_id`, `user_id`, `tagged_userid`) VALUES (
            '".mysqli_real_escape_string( $link, $postId )."', 
            '".mysqli_real_escape_string( $link, $userId )."', 
            '".mysqli_real_escape_string( $link, $value )."'
                )";
                mysqli_query($link, $tagUserQuery);
            }
        }
        // }
        // $query = "INSERT INTO `post_media` (`post_id`, `media_url`) VALUES (
        //     '".mysqli_real_escape_string( $link, $postId )."', 
        //     '".mysqli_real_escape_string( $link, $allImages[0] )."'
        // )";
        // echo json_encode($id);
        echo json_encode("Completed");
    } else {
        echo json_encode("FALSE");
    }
} else {
    echo json_encode("UN-AUTHORIZED");
}
