<?php declare(strict_types=1);

namespace VitesseCms\Database;

use VitesseCms\Core\Helpers\InjectableHelper;
use VitesseCms\Database\Interfaces\BaseCollectionInterface;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Core\Models\Datafield;
use VitesseCms\Core\Traits\BaseObjectTrait;
use VitesseCms\Database\Utils\MongoUtil;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Regex;
use Phalcon\Http\Request;
use Phalcon\Mvc\Collection\Behavior\SoftDelete;
use Phalcon\Mvc\Collection\Behavior\Timestampable;
use Phalcon\Mvc\Collection\Exception;
use Phalcon\Mvc\MongoCollection;
use \is_object;
use \is_string;
use \is_array;
use \DateTime;

//TODO aparte admin AbstractCollection maken?
abstract class AbstractCollection
    extends MongoCollection
    implements BaseCollectionInterface
{
    use BaseObjectTrait;

    /**
     * @var array
     */
    public static $findValue = [];

    /**
     * @var string
     */
    public $createdAt;

    /**
     * @var string
     */
    public $updatedOn;

    /**
     * @var string
     */
    public $deletedOn;

    /**
     * @var bool
     */
    public $published;

    /**
     * @var bool
     */
    protected static $findDeletedOn = true;

    /**
     * @var bool
     */
    protected static $findPublished = true;

    /**
     * @var array
     */
    protected static $findOrdering = [];

    /**
     * @var ?array
     */
    protected static $fields;

    /**
     * @var int
     */
    protected static $findLimit = 12;

    /**
     * @var bool
     */
    protected static $findParseFilter = false;

    /**
     * @var string
     */
    protected $adminListName;

    /**
     * @var InjectableInterface
     */
    protected $di;

    /**
     * @var string
     */
    protected $adminListExtra;

    /**
     * @var string
     */
    protected $extraAdminListButtons;

    public function initialize()
    {
        $this->addBehavior(new SoftDelete([
            'field' => 'deletedOn',
            'value' => date('Y-m-d H:i:s'),
        ]));

        $this->addBehavior(
            new Timestampable(
                [
                    'beforeCreate' => [
                        'field' => 'createdAt',
                        'format' => 'Y-m-d H:i:s',
                    ],
                    'beforeUpdate' => [
                        'field' => 'updatedOn',
                        'format' => 'Y-m-d H:i:s',
                    ],
                ]
            )
        );

        if ($this->published === null) :
            $this->published = false;
        endif;

        if (!is_object($this->di)) :
            $this->di = new InjectableHelper();
        endif;
    }

    public function onConstruct()
    {
        if (!is_object($this->di)) :
            $this->di = new InjectableHelper();
        endif;
    }

    /**
     * @deprecated move to listener
     */
    public function afterFetch()
    {
        if (!is_object($this->di)) :
            $this->di = new InjectableHelper();
        endif;
    }

    public function bindByDatagroup(AbstractCollection $datagroup, array $data)
    {
        $fields = $datagroup->_('datafields');
        if (is_array($fields)) :
            foreach ($fields as $field) :
                $datafield = Datafield::findById($field['id']);
                if ($datafield && isset($data[$datafield->_('calling_name')])) :
                    $this->set($datafield->_('calling_name'), $data[$datafield->_('calling_name')]);
                endif;

                if ($datafield && isset($data['BSON_' . $datafield->_('calling_name')])) :
                    $this->set('BSON_' . $datafield->_('calling_name'), $data['BSON_' . $datafield->_('calling_name')]);
                endif;
            endforeach;
        endif;
    }

    public static function find(?array $parameters = null): array
    {
        $params = [
            'deletedOn' => null,
            'published' => true,
        ];

        if (isset($parameters['limit'])) :
            $params['limit'] = $parameters['limit'];

        endif;
        $item = parent::find([$params]);

        self::reset();

        return $item;
    }

    public static function count(?array $parameters = null): int
    {
        $parameters[] = self::buildFindParameters();
        $number = (int)parent::count($parameters);

        self::reset();

        return $number;
    }

    public static function findFirst(?array $parameters = null)
    {
        $parameters[] = self::buildFindParameters();
        if (self::$findOrdering) :
            $parameters['sort'] = self::$findOrdering;
        endif;

        $item = parent::findFirst(
            $parameters
        );

        self::reset();

        return $item;
    }

    public static function buildFindParameters(): array
    {
        if (self::$findDeletedOn) :
            self::setFindValue('deletedOn', null);
        endif;

        if (self::$findPublished) :
            self::setFindValue('published', true);
        endif;

        return self::$findValue;
    }

    public static function reset()
    {
        self::$findValue = [];
        self::$findOrdering = [];
        self::$fields = null;
        self::setFindDeletedOn(true);
        self::setFindPublished(true);
        self::setFindParseFilter(false);
        self::$findLimit = 99;
    }

    public static function setFindPublished(bool $state)
    {
        self::$findPublished = $state;
    }

    public static function setFindDeletedOn(bool $state)
    {
        self::$findDeletedOn = $state;
    }

    public static function setFindParseFilter(bool $state)
    {
        self::$findParseFilter = $state;
    }

    /**
     * @param mixed|string $id
     *
     * @return array|bool|BaseCollectionInterface
     */
    public static function findById($id)
    {
        if (empty($id) || !MongoUtil::isObjectId((string)$id)) :
            return false;
        endif;

        if (!is_object($id)) {
            $mongoId = new ObjectID($id);
        } else {
            $mongoId = $id;
        }

        self::setFindValue('_id', $mongoId);

        return self::findFirst();
    }

    public static function setFindValue(
        string $key,
        $value,
        string $type = 'string'
    ): void
    {
        switch ($type) :
            case 'like':
                self::$findValue[$key] = new Regex(preg_quote($value, '') . '(.)?', 'ig');
                break;
            case 'not':
                self::$findValue[$key] = ['$ne' => $value];
                break;
            case 'greater':
                self::$findValue[$key] = ['$gt' => $value];
                break;
            case 'smaller':
                self::$findValue[$key] = ['$lt' => $value];
                break;
            case 'between':
                self::$findValue[$key] = [
                    '$gt' => $value[0],
                    '$lt' => $value[1]
                ];
                break;
            default:
                self::$findValue[$key] = $value;
                break;
        endswitch;
    }

    public static function addFindOrder(string $key, int $direction = 1)
    {
        self::$findOrdering[$key] = $direction;
    }

    public static function removeFindOrder(string $key)
    {
        if (isset(self::$findOrdering[$key])) :
            unset(self::$findOrdering[$key]);
        endif;
    }

    /**
     * @return BaseCollectionInterface[]
     */
    public static function findAll(): array
    {
        if (self::$findParseFilter) :
            self::parseFilterInput();
        endif;

        $p = [
            self::buildFindParameters(),
            'sort' => self::$findOrdering,
        ];

        $p['limit'] = self::$findLimit;

        if (self::$fields !== null):
            $p['fields'] = self::$fields;
        endif;

        $result = parent::find($p);

        self::reset();

        return $result;
    }

    public static function setFindLimit(int $limit): void
    {
        self::$findLimit = $limit;
    }

    public function getAdminlistName(): string
    {
        if ($this->adminListName !== null):
            return $this->adminListName;
        endif;

        return $this->getNameField();
    }

    public function setAdminListName(string $adminListName): self
    {
        $this->adminListName = $adminListName;

        return $this;
    }

    public static function parseFilterInput()
    {
        $request = new Request();
        $filter = $request->get('filter', null, []);
        $range = $fields = [];

        foreach ($filter as $key => $value) :
            if (is_string($value)) :
                $value = trim($value);
            endif;

            if (!empty($value)) :
                if ($key === 'range' && is_array($value)) :
                    $keys = array_keys($value);
                    $key = $keys[0];
                    $range = explode(',', $value[$keys[0]]);
                    $value = 'range';
                endif;
                if ($key === 'textFields') :
                    $fields = $value;
                    $value = $key;
                endif;

                if ($key === 'datagroup' && isset(self::$findValue['datagroup'])) :
                    $value = 'disabled';
                endif;

                switch ($value):
                    case 'false':
                        self::setFindValue($key, false);
                        break;
                    case 'range':
                        self::setFindValue($key, [
                            '$gte' => (int)$range[0],
                            '$lte' => (int)$range[1],
                        ]);
                        break;
                    case 'textFields':
                        if (is_array($fields) && $request->get('search')) :
                            foreach ($fields as $fieldName => $fieldValue) :
                                self::setFindValue($fieldName, $request->get('search'), 'like');
                            endforeach;
                        endif;
                        break;
                    case 'true':
                        self::setFindValue($key, true);
                        break;
                    case 'disabled':
                        break;
                    default:
                        if (is_array($value)) :
                            self::setFindValue($key, ['$in' => $value]);
                        elseif (is_numeric($value)) :
                            self::setFindValue($key, ['$in' => [(int)$value, $value]]);
                        elseif (MongoUtil::isObjectId($value)):
                            self::setFindValue($key, $value);
                        else :
                            self::setFindValue($key, $value, 'like');
                        endif;
                endswitch;
            endif;
        endforeach;
    }

    public function save(): bool
    {
        if ($this->getId() === null) :
            $this->setId(new ObjectID());
        endif;

        try {
            $result = parent::save();
            $this->afterSave();

            return $result;
        } catch (Exception $exception) {
            throw new Exception('Saving of Item failed : ' . $exception->getMessage());
        }
    }

    public static function setReturnFields(array $fields): void
    {
        self::$fields = $fields;
    }

    public function toArray(): array
    {
        $return = parent::toArray();
        unset(
            $return['_dependencyInjector'],
            $return['_modelsManager'],
            $return['_source'],
            $return['_operationMade'],
            $return['_dirtyState'],
            $return['_connection'],
            $return['_errorMessages'],
            $return['_skipped']
        );

        return $return;
    }

    public function afterSave()
    {
    }

    public function beforeDelete()
    {
    }

    public function afterDelete()
    {
    }

    public function beforePublish()
    {
    }

    public function afterPublish()
    {
    }

    public function addExtraAdminListButtons()
    {
    }

    public function getAdminButtons(): string
    {
        return '';
    }

    public static function setRenderFields(bool $value)
    {
    }

    public function getCreateDate(): DateTime
    {
        return new DateTime($this->createdAt ?? '');
    }

    public function getUpdatedOn(): ?DateTime
    {
        if ($this->updatedOn !== null) :
            return DateTime::createFromFormat('Y-m-d H:i:s', $this->updatedOn);
        endif;

        return null;
    }

    public function isPublished(): bool
    {
        return (bool)$this->published;
    }

    public function setPublished(bool $published): BaseCollectionInterface
    {
        $this->published = $published;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deletedOn !== null;
    }

    public function getAdminListExtra(): string
    {
        return $this->adminListExtra ?? '';
    }

    public function setAdminListExtra(string $adminListExtra): AbstractCollection
    {
        $this->adminListExtra = $adminListExtra;

        return $this;
    }

    public function getExtraAdminListButtons(): string
    {
        return $this->extraAdminListButtons ?? '';
    }

    public function setExtraAdminListButtons(string $extraAdminListButtons): AbstractCollection
    {
        $this->extraAdminListButtons = $extraAdminListButtons;

        return $this;
    }
}
