<?php

namespace RValin\ExceptionListenerBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Type;

class ExceptionItem
{
    const STATUS_OPEN = 'open';
    const STATUS_IGNORE = 'ignored';
    const STATUS_DELETED = 'deleted';
    const STATUS_REGRESSION = 'regression';
    const STATUS_RESOLVED = 'resolved';
    const CRITICAL_LEVEL_HIGH = 'critical';
    const CRITICAL_LEVEL_NORMAL = 'normal';
    const CRITICAL_LEVEL_LOW = 'low';

    /**
     * @var int|null
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="exception_class", type="text")
     * @Type("string")
     * @Serializer\SerializedName("exception_class")
     */
    protected $exceptionClass;

    /**
     * @var string
     * @ORM\Column(name="name", type="string")
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(name="status", type="string")
     */
    protected $status = self::STATUS_OPEN;

    /**
     * @var string
     * @ORM\Column(name="critical_level", type="string")
     */
    protected $criticalLevel = self::CRITICAL_LEVEL_NORMAL;

    /**
     * @var \DateTime
     * @ORM\Column(name="first_saw_on", type="datetime")
     * @Type("DateTime<'d/m/Y H:i'>")
     */
    protected $firstSawOn;

    /**
     * @var \DateTime
     * @ORM\Column(name="last_saw_on", type="datetime")
     * @Type("DateTime<'d/m/Y H:i'>")
     */
    protected $lastSawOn;

    /**
     * @var \DateTime
     * @ORM\Column(name="resolved_date", type="datetime", nullable=true)
     * @Type("DateTime<'d/m/Y H:i'>")
     */
    protected $resolvedDate;

    /**
     * @var string
     * @ORM\Column(name="resolved_version", type="string", nullable=true)
     */
    protected $resolvedVersion;

    /**
     * @var ExceptionOccurrence[]
     */
    protected $occurences;

    /**
     * @var string
     * @ORM\Column(name="language", type="string", nullable=true)
     * @Serializer\Type("string")
     */
    protected $language;

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->occurences = new ArrayCollection();
    }

    /**
     * @param int|null $id
     * @return ExceptionItem
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ExceptionItem
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return ExceptionItem
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getResolvedDate()
    {
        return $this->resolvedDate;
    }

    /**
     * @param \DateTime $resolvedDate
     * @return ExceptionItem
     */
    public function setResolvedDate($resolvedDate)
    {
        $this->resolvedDate = $resolvedDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getResolvedVersion()
    {
        return $this->resolvedVersion;
    }

    /**
     * @param string $resolvedVersion
     * @return ExceptionItem
     */
    public function setResolvedVersion($resolvedVersion)
    {
        $this->resolvedVersion = $resolvedVersion;
        return $this;
    }

    /**
     * @return ExceptionOccurrence[]|ArrayCollection
     */
    public function getOccurences()
    {
        return $this->occurences;
    }

    /**
     * @param ExceptionOccurrence[] $occurences
     * @return ExceptionItem
     */
    public function setOccurences($occurences)
    {
        $this->occurences = $occurences;
        return $this;
    }

    public function addOccurence(ExceptionOccurrence $exceptionOccurrence)
    {
        $this->occurences->add($exceptionOccurrence);

        if ($exceptionOccurrence->getExceptionItem() !== $this) {
            $exceptionOccurrence->setExceptionItem($this);
        }
    }

    public function removeOccurence(ExceptionOccurrence $exceptionOccurrence)
    {
        $this->occurences->removeElement($exceptionOccurrence);
    }

    /**
     * @return \DateTime
     */
    public function getFirstSawOn()
    {
        return $this->firstSawOn;
    }

    /**
     * @param \DateTime $firstSawOn
     * @return ExceptionItem
     */
    public function setFirstSawOn($firstSawOn)
    {
        $this->firstSawOn = $firstSawOn;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastSawOn()
    {
        return $this->lastSawOn;
    }

    /**
     * @param \DateTime $lastSawOn
     * @return ExceptionItem
     */
    public function setLastSawOn($lastSawOn)
    {
        $this->lastSawOn = $lastSawOn;
        return $this;
    }

    /**
     * @return string
     */
    public function getExceptionClass()
    {
        return $this->exceptionClass;
    }

    /**
     * @param string $exceptionClass
     * @return ExceptionItem
     */
    public function setExceptionClass($exceptionClass)
    {
        $this->exceptionClass = $exceptionClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     * @return ExceptionItem
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return string
     */
    public function getCriticalLevel()
    {
        return $this->criticalLevel;
    }

    /**
     * @param string $criticalLevel
     * @return ExceptionItem
     */
    public function setCriticalLevel($criticalLevel)
    {
        $this->criticalLevel = $criticalLevel;
        return $this;
    }
}