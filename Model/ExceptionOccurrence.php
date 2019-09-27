<?php

namespace RValin\ExceptionListenerBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Type;
use WhichBrowser\Parser;

class ExceptionOccurrence
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="url", type="text", nullable=true)
     * @Serializer\Type("string")
     */
    protected $url;

    /**
     * @var string|null
     * @ORM\Column(name="user", type="string", nullable=true)
     * @Serializer\Type("string")
     */
    protected $user;

    /**
     * @var \DateTime
     * @ORM\Column(name="exception_date", type="datetime")
     * @Serializer\SerializedName("exception_date")
     * @Type("DateTime<'d/m/Y H:i:s'>")
     */
    protected $exceptionDate;

    /**
     * @var string
     * @ORM\Column(name="message", type="text")
     * @Serializer\Type("string")
     */
    protected $message;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    protected $exceptionClass;

    /**
     * @var string
     * @ORM\Column(name="code", type="string", nullable=true)
     * @Serializer\Type("string")
     */
    protected $code;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    protected $language;

    /**
     * @var string
     * @ORM\Column(name="file", type="string")
     * @Serializer\Type("string")
     */
    protected $file;

    /**
     * @var int
     * @ORM\Column(name="line", type="integer")
     * @Serializer\Type("integer")
     */
    protected $line;

    /**
     * @var string
     * @ORM\Column(name="trace", type="json_array", nullable=true)
     * @Serializer\Type("array")
     */
    protected $trace;

    /**
     * @var string
     * @ORM\Column(name="extra", type="json_array", nullable=true)
     * @Serializer\Type("array")
     */
    protected $extra;

    /**
     * @var string
     * @ORM\Column(name="app_version", type="string", nullable=true)
     * @Serializer\Type("string")
     * @Serializer\SerializedName("app_version")
     */
    protected $appVersion;

    /**
     * @var array
     */
    private $browserData;

    /**
     * @var ExceptionItem
     */
    protected $exceptionItem;

    public function __construct()
    {
        $this->exceptionDate = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ExceptionOccurrence
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return ExceptionOccurrence
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string|null $user
     * @return ExceptionOccurrence
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExceptionDate()
    {
        return $this->exceptionDate;
    }

    /**
     * @param \DateTime $exceptionDate
     * @return ExceptionOccurrence
     */
    public function setExceptionDate($exceptionDate)
    {
        $this->exceptionDate = $exceptionDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return ExceptionOccurrence
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return ExceptionOccurrence
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     * @return ExceptionOccurrence
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @param int $line
     * @return ExceptionOccurrence
     */
    public function setLine($line)
    {
        $this->line = $line;
        return $this;
    }

    /**
     * @return string
     */
    public function getTrace()
    {
        return json_decode($this->trace, true);
    }

    /**
     * @param array $trace
     * @return ExceptionOccurrence
     */
    public function setTrace(array $trace)
    {
        $this->trace = json_encode($trace);
        return $this;
    }

    public function setExceptionItem(ExceptionItem $exceptionItem)
    {
        if ($this->exceptionItem instanceof ExceptionItem) {
            $this->exceptionItem->removeOccurence($this);
        }

        $this->exceptionItem = $exceptionItem;

        if (!$exceptionItem->getOccurences()->contains($this)) {
            $exceptionItem->addOccurence($this);
        }
    }

    public function getExceptionItem()
    {
        return $this->exceptionItem;
    }

    /**
     * @return string
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @param string $extra
     * @return ExceptionOccurrence
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;
        return $this;
    }

    /**
     * @return string
     */
    public function getAppVersion()
    {
        return $this->appVersion;
    }

    /**
     * @param string $appVersion
     * @return ExceptionOccurrence
     */
    public function setAppVersion($appVersion)
    {
        $this->appVersion = $appVersion;
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
     * @return ExceptionOccurrence
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
     * @return ExceptionOccurrence
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("browser")
     */
    public function getBrowser() {
        if ($this->browserData === null) {
            $this->browserData = empty($this->getExtra()['user_agent']) ? false : new Parser($this->getExtra()['user_agent']);
        }

        return $this->browserData;
    }
}