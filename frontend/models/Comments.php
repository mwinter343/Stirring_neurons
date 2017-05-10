<?php

namespace frontend\models;
use yii\db\Query;
use Yii;

/**
 * This is the model class for table "Comments".
 *
 * @property integer $idComment
 * @property integer $idUser
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
            [['comment'], 'string', 'max' => 45],
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

        if($id = Yii::$app->user->id){
            $idUser = $id;
        }

        $comment->idUser = $idUser;
        $comment->comment = $string;
        //$comment->Created = Date('Y-m-d H:i:s');

        echo $comment->Created;
        if($comment->save()){
            return "comment :" . $string;
        } else {
            return " Failed";
        }
    }

    public function archiveComment($idComment, $idUser = null){
        $comment = Comments::findOne($idComment);
        $comment->Archived = Date('Y-m-d H:i:s');
        $comment->save();
    }
}
