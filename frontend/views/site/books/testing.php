<html>
<style>
    body {
        color: #59ff1f;
    }
    </style>

<body>
    <?php
    /**
     * Created by PhpStorm.
     * User: michael
     * Date: 5/9/17
     * Time: 3:29 PM
     */

    use frontend\models\Comments;

    $comment = new Comments();
    echo $comment->archiveComment(1);
    ?>
</body>


</html>
