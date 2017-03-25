<?php

namespace yii2mod\comments\models;

use paulzi\adjacencyList\AdjacencyListBehavior;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii2mod\behaviors\PurifyBehavior;
use yii2mod\comments\Module;
use yii2mod\moderation\enums\Status;
use yii2mod\moderation\ModerationBehavior;
use yii2mod\moderation\ModerationQueryTrait;

/**
 * Class CommentModel
 *
 * @property int $id
 * @property string $entity
 * @property int $entityId
 * @property string $content
 * @property int $parentId
 * @property int $level
 * @property int $createdBy
 * @property int $updatedBy
 * @property string $relatedTo
 * @property string $url
 * @property int $status
 * @property int $createdAt
 * @property int $updatedAt
 *
 * @method ActiveRecord makeRoot()
 * @method ActiveRecord appendTo($node)
 * @method ActiveQuery getDescendants()
 */
class CommentModel extends ActiveRecord
{
    use ModerationQueryTrait;

    /**
     * @var null|array|ActiveRecord[] Comment children
     */
    protected $children;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%comment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['entity', 'entityId'], 'required'],
            ['content', 'required', 'message' => Yii::t('yii2mod.comments', 'Comment cannot be blank.')],
            [['content', 'entity', 'relatedTo', 'url'], 'string'],
            ['status', 'default', 'value' => Status::APPROVED],
            ['status', 'in', 'range' => Status::getConstantsByName()],
            ['level', 'default', 'value' => 1],
            ['parentId', 'validateParentID'],
            [['entityId', 'parentId', 'status', 'level'], 'integer'],
        ];
    }

    /**
     * @param $attribute
     */
    public function validateParentID($attribute)
    {
        if ($this->{$attribute} !== null) {
            $parentCommentExist = static::approved()
                ->andWhere([
                    'id' => $this->{$attribute},
                    'entity' => $this->entity,
                    'entityId' => $this->entityId,
                ])
                ->exists();

            if (!$parentCommentExist) {
                $this->addError('content', Yii::t('yii2mod.comments', 'Oops, something went wrong. Please try again later.'));
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'blameable' => [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'createdBy',
                'updatedByAttribute' => 'updatedBy',
            ],
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'createdAt',
                'updatedAtAttribute' => 'updatedAt',
            ],
            'purify' => [
                'class' => PurifyBehavior::class,
                'attributes' => ['content'],
                'config' => [
                    'HTML.SafeIframe' => true,
                    'URI.SafeIframeRegexp' => '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%',
                    'AutoFormat.Linkify' => true,
                    'HTML.TargetBlank' => true,
                    'HTML.Allowed' => 'a[href], iframe[src|width|height|frameborder], img[src]',
                ],
            ],
            'adjacencyList' => [
                'class' => AdjacencyListBehavior::class,
                'parentAttribute' => 'parentId',
                'sortable' => false,
            ],
            'moderation' => [
                'class' => ModerationBehavior::class,
                'moderatedByAttribute' => false,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('yii2mod.comments', 'ID'),
            'content' => Yii::t('yii2mod.comments', 'Content'),
            'entity' => Yii::t('yii2mod.comments', 'Entity'),
            'entityId' => Yii::t('yii2mod.comments', 'Entity ID'),
            'parentId' => Yii::t('yii2mod.comments', 'Parent ID'),
            'status' => Yii::t('yii2mod.comments', 'Status'),
            'level' => Yii::t('yii2mod.comments', 'Level'),
            'createdBy' => Yii::t('yii2mod.comments', 'Created by'),
            'updatedBy' => Yii::t('yii2mod.comments', 'Updated by'),
            'relatedTo' => Yii::t('yii2mod.comments', 'Related to'),
            'url' => Yii::t('yii2mod.comments', 'Url'),
            'createdAt' => Yii::t('yii2mod.comments', 'Created date'),
            'updatedAt' => Yii::t('yii2mod.comments', 'Updated date'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->parentId > 0) {
                $parentNodeLevel = static::find()->select('level')->where(['id' => $this->parentId])->scalar();
                $this->level = $parentNodeLevel + 1;
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (!$insert) {
            if (array_key_exists('status', $changedAttributes)) {
                $this->beforeModeration();
            }
        }
    }

    /**
     * @return bool
     */
    public function saveComment()
    {
        if ($this->validate()) {
            if (empty($this->parentId)) {
                return $this->makeRoot()->save();
            } else {
                $parentComment = static::findOne(['id' => $this->parentId]);

                return $this->appendTo($parentComment)->save();
            }
        }

        return false;
    }

    /**
     * Author relation
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        $module = Yii::$app->getModule(Module::$name);

        return $this->hasOne($module->userIdentityClass, ['id' => 'createdBy']);
    }

    /**
     * Get comments tree.
     *
     * @param $entity string model class id
     * @param $entityId integer model id
     * @param null $maxLevel
     *
     * @return array|\yii\db\ActiveRecord[] Comments tree
     */
    public static function getTree($entity, $entityId, $maxLevel = null)
    {
        $query = static::approved()->andWhere([
            'entityId' => $entityId,
            'entity' => $entity,
        ])->with(['author']);

        if ($maxLevel > 0) {
            $query->andWhere(['<=', 'level', $maxLevel]);
        }

        $models = $query->orderBy(['parentId' => SORT_ASC, 'createdAt' => SORT_ASC])->all();

        if (!empty($models)) {
            $models = self::buildTree($models);
        }

        return $models;
    }

    /**
     * Build comments tree.
     *
     * @param array $data Records array
     * @param int $rootID parentId Root ID
     *
     * @return array|ActiveRecord[] Comments tree
     */
    protected static function buildTree(&$data, $rootID = 0)
    {
        $tree = [];

        foreach ($data as $id => $node) {
            if ($node->parentId == $rootID) {
                unset($data[$id]);
                $node->children = self::buildTree($data, $node->id);
                $tree[] = $node;
            }
        }

        return $tree;
    }

    /**
     * $_children getter.
     *
     * @return null|array|ActiveRecord[] Comment children
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * $_children setter.
     *
     * @param array|ActiveRecord[] $value Comment children
     */
    public function setChildren($value)
    {
        $this->children = $value;
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return !empty($this->children);
    }

    /**
     * @return string
     */
    public function getPostedDate()
    {
        return Yii::$app->formatter->asRelativeTime($this->createdAt);
    }

    /**
     * @return mixed
     */
    public function getAuthorName()
    {
        if ($this->author->hasMethod('getUsername')) {
            return $this->author->getUsername();
        }

        return $this->author->username;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return nl2br($this->content);
    }

    /**
     * Get avatar of the user
     *
     * @return string
     */
    public function getAvatar()
    {
        if ($this->author->hasMethod('getAvatar')) {
            return $this->author->getAvatar();
        }

        return 'http://www.gravatar.com/avatar/00000000000000000000000000000000?d=mm&f=y&s=50';
    }

    /**
     * Get list of all authors
     *
     * @return array
     */
    public static function getAuthors()
    {
        return ArrayHelper::map(static::find()->joinWith('author')->groupBy('createdBy')->asArray()->all(), 'createdBy', 'author.username');
    }

    /**
     * @return int
     */
    public function getCommentsCount()
    {
        return (int)static::approved()
            ->andWhere(['entity' => $this->entity, 'entityId' => $this->entityId])
            ->count();
    }

    /**
     * @return string
     */
    public function getAnchorUrl()
    {
        return "#comment-{$this->id}";
    }

    /**
     * @return null|string
     */
    public function getViewUrl()
    {
        if (!empty($this->url)) {
            return $this->url . $this->getAnchorUrl();
        }

        return null;
    }

    /**
     * Before moderation event
     *
     * @return bool
     */
    public function beforeModeration()
    {
        $descendantIds = ArrayHelper::getColumn($this->getDescendants()->asArray()->all(), 'id');

        if (!empty($descendantIds)) {
            self::updateAll(['status' => $this->status], ['id' => $descendantIds]);
        }

        return true;
    }
}
