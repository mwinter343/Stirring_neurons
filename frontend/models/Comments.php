<?php

namespace frontend\models;

use Yii;
use common\models\User;

/**
 * This is the model class for table "Comments".
 *
 * @property integer $idComment
 * @property integer $idUser
 * @property string $username
 * @property string $comment
 * @property string $Created
 * @property string $Archived
 */
class Comments extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Comments';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['idUser', 'comment'], 'required'],
            [['idUser'], 'integer'],
            [['Created', 'Archived'], 'safe'],
            [['username', 'comment'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'idComment' => 'Id Comment',
            'idUser' => 'Id User',
            'username' => 'Username',
            'comment' => 'Comment',
            'Created' => 'Created',
            'Archived' => 'Archived',
        ];
    }

    /**
     * Returns comment data from Comment Table
     * @param $limit - optional max number of comments returned (any number less than 1 [one] will return all)
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getComments($limit = NULL){

        if($limit) {
            $comments = Comments::find()
                ->where('Archived IS NULL')
                ->orderBy('Created')
                ->asArray()
                ->limit($limit)
                ->all();
        } else {
            $comments = Comments::find()
                ->where('Archived IS NULL')
                ->orderBy('Created')
                ->asArray()
                ->all();
        }

        return $comments;
    }

    public function createComment($string, $idUser = 0){

        $comment = new Comments();
        $user = new User();

        if($sesh = Yii::$app->user){
            $idUser = $sesh->id;
            $comment->username = $user->findIdentity($sesh->id)->getUsername();
            print_r($user);
        }

        $comment->idUser = $idUser;
        $comment->comment = $string;
        $comment->Created = Date('Y-m-d H:i:s');

        echo $comment->Created;
        if($comment->save()){
            return "comment :" . $string;
        } else {
            return " Failed";
        }
    }

    public function archiveComment($idComment, $idUser = null){
        if($comment = Comments::findOne($idComment)) {
            if(is_null($comment->Archived)){
                $comment->Archived = Date('Y-m-d H:i:s');
                $comment->save();
                return 1;
            }
        }
        return 0;
    }
}
